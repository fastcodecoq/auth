<?php
ob_start();
require_once(dirname(__FILE__) . '/config.inc');

class authException extends Exception{}

class authCtrl{
		
	protected $db;
	var $login_url;
	var $app_url;
  var $REST_API;

     public function __construct($REST_API = false){     	  
     	  $this->db = new mysqli(db_host, db_user, db_pass, db_bd);  // cambiar por tu base de datos

     	  $this->login_url = path . "/" . login_url; 
		    $this->app_url = path . "/" . app_url; 
        $this->REST_API = $REST_API;   	  

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

     protected function refrescar_token($token, $usr, $es_infinito = false){

     			$now = time();

     			//generamos un nuevo token
           $_token = $_SERVER['HTTP_USER_AGENT'] . $usr . $now . $_SERVER["REMOTE_ADDR"];

				   $_token = $this->gen_token($_token); 

				   $ttl = (!$es_infinito) ? time() + (3600 * 2) : -7200;  

				   //actualizamos la cookie http
                $c = setcookie(cookie_name, serialize(array($usr, $token)), $ttl,'/', dominio, false, true);				 

                if(!$c)
                	throw new authException("Error extendiendo la Cookie");

                $this->db->query("UPDATE credenciales SET ttl='{$ttl}' WHERE usr = '{$usr}' AND token = '{$token}' LIMIT 1") or die($this->db->error);     		

     }

     protected function token_expiro($token, $usr){
     		
     		$ua = md5($_SERVER["HTTP_USER_AGENT"]);
     		$ip = $_SERVER["REMOTE_ADDR"];

     		if(!filter_var($ip, FILTER_VALIDATE_IP))
     			return true;

            $cred = $this->db->query("SELECT ttl FROM credenciales WHERE usr = '{$usr}' AND token = '{$token}' AND ua = '{$ua}' AND ip = '{$ip}' LIMIT 1") or die($this->db->error);
            


            if($cred->num_rows > 0)
            {

            	$cred = $cred->fetch_assoc();
            

            	if($cred['ttl'] < -3600) return -7200;
         

            	return ($cred['ttl'] - time()) < 0 ;

            }else
                return true;


     }


     public function validar_token(){

     		  $credencial = $this->get_credencial();
     		  $token = $credencial[1];
     		  $usr = $credencial[0];


     		  $validacion = $this->token_expiro($token, $usr);


     	      if(is_numeric($validacion))
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

           $usr = $this->db->query("SELECT permisos  FROM usuarios WHERE _email = '{$_email}' LIMIT 1") or die($this->db->error);

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

    	if(!isset($_COOKIE[cookie_name]) && !$this->REST_API)
    		return false;    	

      if(!$this->REST_API)
    	return unserialize($_COOKIE[cookie_name]);
      else
      return array(md5($_GET['user']), $_GET['token']);

    }


    public function esta_logueado(){ return !!$this->validar_token(); }    

     public function logout(){

     		$credencial = $this->get_credencial();

     		if(!$credencial)
               {
              $this->redir($this->login_url);  //no es un usuario valido                              
               die;
               }

            $usr = $credencial[0];
            $token = $credencial[1];

     	    $cred = $this->db->query("SELECT id FROM credenciales WHERE usr = '{$usr}' AND token = '{$token}' LIMIT 1");

            if($cred->num_rows > 0)
            {

               //eliminamos la cookie http
               setcookie(cookie_name, '', time() - 1800 ,'/', dominio, false, true);				 


               $this->db->query("DELETE FROM credenciales WHERE usr = '{$usr}' AND token = '{$token}'") or die($this->db->error);


               if($this->db->affected_rows === 0)
               	throw new authException("Error intentando eliminar el token");
               	
               // redireccionamos a la pagina de login
              $this->redir_to_login();  //no es un usuario valido               

            }else
              $this->redir_to_login();  //no es un usuario valido               
               
             

     }


     public function activar_pass(){


     	 require_once('./usuarios.php');

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
           $usr = $this->db->query("SELECT clave, nombre  FROM usuarios WHERE _email = '{$_email}' AND clave_pendiente = -1 LIMIT 1") or die($this->db->error);


           //si el query arroja mas de una fila, procederemos a validar si las claves coinciden
           if($usr->num_rows > 0){
              
               $usr = $usr->fetch_assoc();

               //hacemos el hash del password. Este mismo hash se usa al momento del registro

               if($this->val_pass($clave, $usr['clave']))
               {   
                    // el usuario es valido
                    // procedemos a crear un token

                 $now = time();
                 $token = $_SERVER['HTTP_USER_AGENT'] . $email . $now . $_SERVER["REMOTE_ADDR"];
                 $ip = $_SERVER["REMOTE_ADDR"];
     		     
     		     if(!filter_var($ip, FILTER_VALIDATE_IP))
     		     	return $this->redir_to_login();

                 $ua = explode(" ",$_SERVER['HTTP_USER_AGENT']);
                 $ua = array("so" => $ua[2], "browser" => $ua[8]);

                 $cliente = json_encode($ua);
                 $ua = md5($_SERVER['HTTP_USER_AGENT']);

				 $token = $this->gen_token($token);               

                 $this->db->query("UPDATE usuarios SET ultimo_ingreso = {$now} WHERE _email = '{$_email}'");                 

                 //si el usuario seleccionó recordar
                 //colocamos el token con tiempo de vida infinito
                 //sino solo le damos 30 mins de vida
                 $remember = isset($_POST['remember']);

                 $ttl = $remember ? -7200 : $now + 1800;

                
                 $this->db->query("INSERT INTO credenciales (usr, token, ttl, cliente, ip, ua) VALUES ('{$_email}', '{$token}', '{$ttl}', '{$cliente}', '{$ip}', '{$ua}')") or die($this->db->error);                

                //instanciamos una cookie http con los de la credencial

                 // si el usuario ha seleccionado recordar le damos un
                 // tiempo de vida a la cookie de 30 días 
                 // sino le damos 2 horas de vida (media jornada laboral)
                  $ttl = $remember ? time() + ((3600 * 24)*30) : time() + ( 3600 * 2 ); 

                  $c = setcookie(cookie_name, serialize(array($_email,$token)), $ttl ,'/', dominio, false, true);				 

                  if(!$c)                  	
                	throw new authException("Error creando la Cookie");

                 //hemos hecho todas las validaciones ahora redirigmos al app                 
                 $this->redir($this->app_url . "?usr={$usr['nombre']}&token={$token}&uid={$email}");                 
                 

               }else                 
              $this->redir($this->login_url . '?auth=false');  //no es un usuario valido                                
           }else
              $this->redir($this->login_url . '?auth=false');  //no es un usuario valido               
             


       // adjunto dejo las tablas y los metodos crear, el resto les corresponde a ustedes:
       // hacer un controlador con los siguientes metodos: 
       // 1. private token_expiro: debe verificar si un token no ha expirado
       // 2. private refrescar_token: debe refrescar un token.       
       // 3. public verificar_token: debe verificar que un token no corresponde a un usuario, ... 
       // ... luego debe implementar el metodo token_expiro para validar que aun puede ser usado ...
       // ... y por ultimo si el token aun estaba en tiempo de vida, debe refrescarlo, en caso de ...
       // ... que no tenga tiempo de vida (ttl) infinito
       

     }

}

function _main(){

	$app = new authCtrl;
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
		    else if(isset($_GET['perms']))
		        echo json_encode($app->get_permisos());  
		    else if(isset($_GET['test_perms']))
		        var_dump($app->validar_permisos('usr'));  
		    
		break;

	}

}

try{

 _main();  // para usar como REST API instanciar así _main(true);

}catch(authException $e){
  echo $e->getMessage(); 
  die;
	// manejo del error solo para modo desarrollo
}