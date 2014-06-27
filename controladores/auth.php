<?php
ob_start();
require_once(dirname(__FILE__) . '/config.inc');

class authException extends Exception{}

class authCtrl{
    
  protected $db;
  var $login_url;
  var $app_url;

     public function __construct($http = false){        
        $this->db = new mysqli(db_host, db_user, db_pass, db_bd);  

        $this->login_url = path . "/" . login_url; 
        $this->app_url = path . "/" . app_url; 
  
    if($http)
        try{
        
          $this->rutas();  //iniciamos las rutas
 
        }catch(authException $e)
        {

        echo $e->getMessage(); 
        die;
        // imprimimos el error solo para modo desarrollo
        }

     }


     public function redir($url){
             echo ('<script type="text/javascript">document.location.href = "'. $url .'"; </script>');
     }

     public function redir_to_login($params = false){
        if(!$params)
        $this->redir($this->login_url);
        else
        $this->redir($this->login_url . $params);           
     }

     public function redir_to_app($params = false){
        if(!$params)
        $this->redir($this->app_url);
        else
        $this->redir($this->app_url . $params);    
     }

     public function no($msg = false){
          
           $msg = $msg ? $msg : 'no_autorizado';

           echo json_encode(array('error' => true, 'mensaje' => $msg));

     }

     public function ok($rs, $msg = false){
         
         $json = array('error' => false, 'data' => $rs);

         if($msg)
          $json['msg'] = $msg;

         echo json_encode($json);

     }

     protected function refrescar_token($token, $usr, $es_infinito = false){

          $now = time();

          //generamos un nuevo token
           $_token = $_SERVER['HTTP_USER_AGENT'] . $usr . $now . $_SERVER["REMOTE_ADDR"];

           $_token = $this->gen_token($_token); 

           $time = (!$es_infinito) ? time() + ttl : 'all';  
           $_time = $time === 'all' ? time() + ((3600 * 24) * 30) : 0;  // le damos 30 días de vida, en caso de que el usuario halla seleccionado recordar



           //actualizamos la cookie http
           if(!REST_API)
                $c = setcookie(cookie_name, serialize(array($usr, $token)), $_time,'/', dominio, cookie_https, true);        

                if(!$c AND !REST_API)
                  throw new authException("Error extendiendo la Cookie");

                $this->db->query("UPDATE " . tb_prefijo ."credenciales SET time ='{$time}' WHERE usr = '{$usr}' AND token = '{$token}' LIMIT 1") or die($this->db->error);        

     }

     protected function token_expiro($token, $usr){
        
        $ua = md5($_SERVER["HTTP_USER_AGENT"]);
        $ip = $_SERVER["REMOTE_ADDR"];

        if(!filter_var($ip, FILTER_VALIDATE_IP))
          return false;

            $cred = $this->db->query("SELECT time FROM " . tb_prefijo ."credenciales WHERE usr = '{$usr}' AND token = '{$token}' AND ua = '{$ua}' AND ip = '{$ip}' LIMIT 1") or die($this->db->error);
            


            if($cred->num_rows > 0)
            {

              $cred = $cred->fetch_assoc();
             

              if(!is_numeric($cred['time']) ) return 'all';
              
              $cred['time'] = (int) $cred['time']; 



              return !!((time() - $cred['time']) > ttl);

            }else
                return true;


     }


     public function validar_token(){

          $credencial = $this->get_credencial();
          $token = $credencial[1];
          $usr = $credencial[0];


          $validacion = $this->token_expiro($token, $usr);


            if($validacion === 'all')
                return true;               
            else if(!$validacion)
                {
                  $this->refrescar_token($token, $usr);
                  return true;
                }
            else if($validacion)            
                return false;                
           
     }


     protected function gen_token($token){

       for($i = 0; $i < 5; $i++)
           $token = $this->hash_($token);

        return $token;

     }


     public function get_permisos(){

        if(!$this->validar_token())
            return false;

         $credencial = $this->get_credencial();

         if(!$credencial)
           return false;

         $_email = $credencial[0];

           $usr = $this->db->query("SELECT permisos  FROM " . tb_prefijo ."usuarios WHERE _email = '{$_email}' LIMIT 1") or die($this->db->error);

           if($usr->num_rows > 0){

              $usr = $usr->fetch_assoc();
              return json_decode($usr['permisos'], true);

           }else
           return false;

     }


     public function validar_permisos($mod,$permiso = NULL){


         if(!$this->validar_token())
            return false;

         $permisos = $this->get_permisos();


         if($permiso != NULL){

          if(isset($permisos[$mod]))
           { 
            if(isset($permisos[$mod][$permiso]))
                 return $permisos[$mod][$permiso];
              else
                   return false;
            }
            else{return false;}

          }else{
            
            if(isset($permisos[$mod]))
             { 

            if(isset($permisos[$mod]['r']) AND isset($permisos[$mod]['w']))
                 return ($permisos[$mod]['r'] AND $permisos[$mod]['w']);
              else
                   return false;

            }else{return false;}

          }

     }


     


    public function get_credencial(){

      if(!isset($_COOKIE[cookie_name]) && !REST_API)
        return false;     

      if(!REST_API)
      return unserialize($_COOKIE[cookie_name]);
      else
      return array($_GET['uid'], $_GET['token']);

    }

    protected function eliminar_credencial($credencial){

               if(!REST_API)
               setcookie(cookie_name, '', time() - 1800 ,'/', dominio, cookie_https, true);        

               $usr = $credencial[0];
               $token = $credencial[1];
               $ua = md5($_SERVER['HTTP_USER_AGENT']);
               $ip = $_SERVER['REMOTE_ADDR'];

               if(!filter_var($ip, FILTER_VALIDATE_IP))
                return false;

               $this->db->query("DELETE FROM " . tb_prefijo ."credenciales WHERE usr = '{$usr}' AND token = '{$token}' AND ua = '{$ua}' AND ip = '{$ip}'") or die($this->db->error);


              return true;

    }


    public function esta_logueado(){ $rs = !!$this->validar_token(); if(!$rs) $this->eliminar_credencial($this->get_credencial()); return $rs; }    

     public function logout(){

        $credencial = $this->get_credencial();

        if(!$credencial)
               {
              $this->redir($this->login_url);  //no es un usuario valido                              
               die;
               }

            $usr = $credencial[0];
            $token = $credencial[1];
            $ua = md5($_SERVER['HTTP_USER_AGENT']);
            $ip = $_SERVER['REMOTE_ADDR'];

          $cred = $this->db->query("SELECT id FROM " . tb_prefijo ."credenciales WHERE usr = '{$usr}' AND token = '{$token}' AND ua = '{$ua}' AND ip = '{$ip}' LIMIT 1");

            if($cred->num_rows > 0)
            {

               //eliminamos la credencial
               $this->eliminar_credencial($credencial);
                
               // redireccionamos a la pagina de login
              $this->redir_to_login();  //no es un usuario valido               

            }else
              $this->redir_to_login();  //no es un usuario valido               
               
             

     }


     public function activar_pass(){


       require_once(dirname(__FILE__) . '/usuarios.php');

       $usr = new usrsCtrl;
       $errors = array();

       if(!isset($_POST['email']) OR empty($_POST['email']))
        $errors[] = "email_invalido";

       if(!isset($_POST['clave']) OR empty($_POST['clave']))
        $errors[] = "clave_invalida";

       if(!isset($_POST['_clave']) OR empty($_POST['_clave']))
        $errors[] = "segunda_clave_invalida";

       if(count($errors) > 0)
       {
        $this->redir($this->login_url . '?errors=' . json_encode($errors));
        die;
       }

       $email = md5($_POST['email']);
       $clave = $_POST['clave'];
       $_clave = $_POST['_clave'];

       if($usr->ini_clave($email, $clave, $_clave, true))
        $this->redir($this->login_url . '?activada');
       else
        $this->redir($this->login_url . '?errors');

     }

     protected function hash_($val){ return crypt($val, '$6$'. substr(md5(uniqid(rand(), true)), 0, 22) . '$'); }
     protected function val_pass($pass, $passh){ return !!(crypt($pass, $passh) === $passh); }

     public function auth(){


           $email = addslashes($_POST['email']);
           $_email = md5($_POST['email']);                  
           $clave = $_POST['clave'];


           // _email es un campo en la tabla usuarios que corresponde al mail en md5
           // es un metodo para evitarnos ataques en el login a la base de datos
           $usr = $this->db->query("SELECT clave, nombre  FROM " . tb_prefijo ."usuarios WHERE _email = '{$_email}' AND clave_pendiente = -1 LIMIT 1") or die($this->db->error);


           //si el query arroja mas de una fila, procederemos a validar si las claves coinciden
           if($usr->num_rows > 0){
              
               $usr = $usr->fetch_assoc();

               //validamos las claves. 
               if($this->val_pass($clave, $usr['clave']))
               {   
                    // el usuario es valido
                    // procedemos a crear un token

                 $now = time();
                 $token = $_SERVER['HTTP_USER_AGENT'] . $email . $now . $_SERVER["REMOTE_ADDR"];
                 $ip = $_SERVER["REMOTE_ADDR"];
             
             //validamos si la ip es valida, evitando que nos hagan XSS 
             if(!filter_var($ip, FILTER_VALIDATE_IP))
              return $this->redir_to_login();

                 $ua = explode(" ",$_SERVER['HTTP_USER_AGENT']);
                 $ua = array("so" => $ua[2], "browser" => $ua[8]);

                 $cliente = json_encode($ua);
                 $ua = md5($_SERVER['HTTP_USER_AGENT']);

                 $token = $this->gen_token($token);               

                 $this->db->query("UPDATE " . tb_prefijo ."usuarios SET ultimo_ingreso = {$now} WHERE _email = '{$_email}'");                 

                 //si el usuario seleccionó recordar
                 //colocamos el token con tiempo de vida infinito
                 //sino solo le damos 30 mins de vida
                 $remember = !!(isset($_POST['remember']) && !empty($_POST['remember']));

                 $time = $remember ? 'all' : $now + ttl;

                
                 $this->db->query("INSERT INTO " . tb_prefijo ."credenciales (usr, token, time, cliente, ip, ua) VALUES ('{$_email}', '{$token}', '{$time}', '{$cliente}', '{$ip}', '{$ua}')") or die($this->db->error);                

                //instanciamos una cookie http con los de la credencial

                 // si el usuario ha seleccionado recordar le damos un
                 // tiempo de vida a la cookie de 30 días 
                 // sino le damos 2 horas de vida (media jornada laboral)                  
                  $time = $remember ? time() + ( (3600 * 24) * 30 ) : 0;

                  if(!REST_API)
                  $c = setcookie(cookie_name, serialize(array($_email,$token)), $time ,'/', dominio, cookie_https, true);         

                  if(!$c AND !REST_API)                   
                  throw new authException("Error creando la Cookie");



                 //hemos hecho todas las validaciones ahora redirigmos al app                 
                 $this->redir($this->app_url . "?usr={$usr['nombre']}&token={$token}&uid={$_email}");                 
                 

               }else                 
              $this->redir($this->login_url . '?auth=false');  //no es un usuario valido                                
           }else
              $this->redir($this->login_url . '?auth=false');  //no es un usuario valido               
            

     }


    protected function rutas(){
        
        $app = $this;
        $verbo = $_SERVER['REQUEST_METHOD'];

     switch ($verbo) {
  

    case 'POST':
      
      if(isset($_GET['auth']))
            $app->auth();
      else if(isset($_GET['activar_pass']))
           {
            header('Content-type: text/html; charset=utf-8');
            echo $app->activar_pass();
           }

    break;

    case 'GET':
       
        if(isset($_GET['logout']))
          $app->logout();   
        else if(isset($_GET['perms']) AND REST_API) //metodos solo disponibles en REST API
            $app->ok($app->get_permisos());  
        else if(isset($_GET['validar_perms']) AND REST_API)
           if(isset($_GET['privilegio']))
            $app->ok($app->validar_permisos($_GET['modulo'], $_GET['privilegio']));  
           else if(isset($_GET['modulo']))
            $app->ok($app->validar_permisos($_GET['modulo']));
           else
            $app->no('params_invalidos');
        else if(isset($_GET['esta_logueado']))
            echo $app->ok($app->esta_logueado());

        
    break;

       }
     }

}


if($_SERVER["REQUEST_METHOD"])  
  new AuthCtrl(true); //iniciaos el controlador en modo HTTP