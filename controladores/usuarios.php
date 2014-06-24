<?php 
ob_start();
require_once(dirname(__FILE__) . "/config.inc");


class usrsCtrl{
 
     protected $db;

     public function __construct(){
     	  $this->db = new mysqli(db_host, db_user, db_pass, db_bd);     
        $this->run();	  
     }

     public function get(){

          require_once('./auth.php');

           $auth = new authCtrl;

           if(!$auth->validar_permisos('usr','r'))
               {
               echo json_encode(array('error' => true, 'message' => 'no_autorizado'));
               die;
              }

     	$cltes = $this->db->query("SELECT id, nombre, email, fecha_creacion, ultimo_ingreso, clave_pendiente FROM usuarios") or die ($this->db->error);
     	  $rs = array();


     	  while($row = $cltes->fetch_assoc())
     	  	  {
     	  		foreach ($row as $key => $val)
     	  	      $row[$key] = utf8_encode( html_entity_decode($val, null, "ISO-8859-1"));

                    if($row['ultimo_ingreso'] > 0)
                     $row['ultimo_ingreso'] = str_replace(' ', ' a las ',date('Y-m-d H:i:s', $row['ultimo_ingreso']));
     	  	     	  	
     	  	  	 $rs[] = $row;

     	  	  	}


     	 return $rs;

     }

     


     public function find($email){

          require_once('./auth.php');

           $auth = new authCtrl;

           if(!$auth->validar_permisos('usr','r'))
               {
               echo json_encode(array('error' => true, 'message' => 'no_autorizado'));
               die;
              }


     	  foreach ($_GET as $key => $val)
     	  	$_GET[$key] = "'" . addslashes(utf8_decode($val)) . "'";

            $where = array();
    	  

            if(isset($_GET['email']))
               $where[] = "email = {$_GET['email']}"; 


            $where = implode(',', $where);

     	  $usr = $this->db->query("SELECT * FROM usuarios WHERE {$where} LIMIT 1") or die($this->db->error);
     	  $rs = array();


     	  while($row = $usr->fetch_assoc())     	  	
     	  	  {
     	  		foreach ($row as $key => $val)
     	  	      $row[$key] = utf8_encode($val);
     	  	     	  	
     	  	  	 $rs[] = $row;

     	  	  }



     	return $rs;

     }

     protected function hash_($pass){ return crypt($pass, '$6$' . substr(md5(uniqid(rand(), true)), 0, 22) . '$'); }


     public function post(){

           header('Content-type: application/json; charset=utf-8');          

           require_once('./auth.php');

           $auth = new authCtrl;

          if(!$auth->validar_permisos('usr'))
               {
               echo json_encode(array('error' => true, 'message' => 'no_autorizado'));
               die;
              }

     	 $email = $_POST['email'];

           if(isset($_POST['clave']))
           $clave = $_POST['clave'];

     	  foreach ($_POST as $key => $val)
     	  	$_POST[$key] = "'" . addslashes(htmlentities($val, null, 'UTF-8')) . "'";


            $vals = array();


            if(!isset($_POST['clave']) OR empty($_POST['clave']))
     	  {
            $vals['nombre'] = $_POST['nombre'];
            $vals['email'] = $_POST['email'];
     	      $vals['_email'] = "'" . md5($email) . "'";
     	      $vals['permisos'] = $_POST['permisos'];
            }else{
            $vals['nombre'] = $_POST['nombre'];
            $vals['clave'] = "'" . $this->hash_($clave) . "'";
            $vals['email'] = $_POST['email'];
            $vals['_email'] = "'" . md5($email) . "'";
            $vals['permisos'] = $_POST['permisos'];
            $vals['clave_pendiente'] = -1;
            }
     	  
            $vals["fecha_creacion"] = "'" . date("Y-m-d") . "'";

     	  $vals = implode(',', $vals);



     	  $_usr = $this->db->query("SELECT id FROM usuarios WHERE email = {$_POST['email']}");

     	  if($_usr->num_rows > 0)
     	  {        
     	  	echo json_encode(array('error' => true, 'message' => 'usuario_duplicado'));
     	  	die;
     	  }

            if(!isset($_POST['clave']))
            $query = "INSERT INTO usuarios (nombre, email, _email, permisos, fecha_creacion) VALUES ({$vals})";
            else
     	  $query = "INSERT INTO usuarios (nombre, clave, email, _email, permisos, clave_pendiente, fecha_creacion) VALUES ({$vals})";

     	  $this->db->query($query) or die($this->db->error);

     	  if($this->db->affected_rows > 0)
     	  	echo json_encode(array('error' => false, 'message' => 'usuario_creado', 'usuarios' => $this->get()));
     	  else
     	  	echo json_encode(array('error' => true, 'message' => 'usuario_no_creado', 'usuarios' => $this->get()));


     }


     public function delete(){
          
           header('Content-type: application/json; charset=utf-8');

           require_once('./auth.php');

           $auth = new authCtrl;

           if(!$auth->validar_permisos('usr'))
               {
               echo json_encode(array('error' => true, 'message' => 'no_autorizado'));
               die;
              }

     	$id = (int) $_GET['id'];

     	$query = "DELETE FROM usuarios WHERE id = {$id} LIMIT 1";

     	$this->db->query($query) or die($this->db->error);

     	if($this->db->affected_rows > 0)
			echo json_encode(array('error' => false, 'message' => 'usuario_eliminado', 'usuarios' => $this->get()));
     	  else
     	  	echo json_encode(array('error' => true, 'message' => 'usuario_no_eliminado', 'usuarios' => $this->get()));


     } 

     public function cambiar_clave($clave, $nva_clave, $_nva_clave){

            if($nva_clave != $_nva_clave)
               return -2;  //las claves no coinciden

            require_once('./auth.php');
            $auth = new authCtrl;

            if(!$usr = $auth->get_credencial()) return false;

            $usr = $usr[0];
            
            $query = "SELECT clave FROM usuarios WHERE _email = '{$usr}' LIMIT 1";
            $_usr = $this->db->query($query);

            if($_usr->num_rows > 0){

            $_usr = $_usr->fetch_assoc();

                        
            if($auth->val_pass($clave, $_usr['clave']))
            {

            $nva_clave = $this->hash_($nva_clave); //hasheamos la nueva clave            
            $query = "UPDATE usuarios SET clave = '{$nva_clave}' WHERE _email = '{$usr}' LIMIT 1";

            $this->db->query($query) or die($this->db->error);

            if($this->db->affected_rows > 0)
            return 1;  // se hizo el cambio
            else
            return -3; // no se pudo hacer el cambio 

            }else
            return -1; //la clave antigua no es correcta

            }else
            return -3; // no se pudo encontrar el usuario
                 

     }   




     public function ini_clave($_email, $clave, $_clave, $activacion = false){

     

          if(!$activacion){
           require_once('./auth.php');

           $auth = new authCtrl;

           if(!$auth->validar_permisos('usr'))
               return false;
           }

          if($clave != $_clave)
          return false;
        

          $usr = $this->db->query("SELECT clave_pendiente FROM usuarios WHERE (_email = '{$_email}' AND clave_pendiente = 1) LIMIT 1") or die($this->db->error);


          if($usr->num_rows > 0)
          {
               $usr = $usr->fetch_assoc();
               if($usr['clave_pendiente'] == 1)
               {


                    $clave = $this->hash_($clave);

                    $query = "UPDATE usuarios SET clave = '{$clave}', clave_pendiente = -1 WHERE (_email = '{$_email}' AND clave_pendiente = 1) LIMIT 1 ";

                    $this->db->query($query) or die($this->db->error);


                    if($this->db->affected_rows > 0)
                       return true;
                    else
                       return false;

                $this->db->close();


               }else
                return false;
          }

     }


  


     public function put(){

           require_once('./auth.php');

           $auth = new authCtrl;

           if(!$auth->validar_permisos('usr'))
               {
               echo json_encode(array('error' => true, 'message' => 'no_autorizado'));
               die;
              }

     	parse_str(file_get_contents("php://input"), $_PUT);

     	foreach ($_PUT as $key => $val)
     	  	$_PUT[$key] = "'" . addslashes(htmlentities($val)) . "'";

     	  var_dump($_PUT);

     	  $vals = array();
     	  $vals['nombre'] = $_PUT['nombre'];
     	  $vals['nit'] = $_PUT['nit'];
     	  $vals['direccion'] = $_PUT['direccion'];
     	  $vals['ciudad'] = $_PUT['ciudad'];
     	  $vals['telefono'] = $_PUT['telefono'];

     	  $set = array();

     	  foreach ($vals as $key => $val) 
     	  	 $set[] = "{$key} = {$val}";

     	$set = implode(',' , $set);
     	$id = $_PUT['id'];

     	$query = "UPDATE clientes SET {$set} WHERE id = {$id} LIMIT 1";

     	$this->db->query($query) or die($this->db->error);

     	if($this->db->affected_rows > 0)
			echo json_encode(array('error' => false, 'message' => 'cliente_actualizado'));
     	  else
     	  	echo json_encode(array('error' => true, 'message' => 'cliente_no_actualizado'));
     }



     protected function run(){

        $app = $this;
       $verbo = $_SERVER['REQUEST_METHOD'];

  switch ($verbo) {
    case 'GET':
      
      if(isset($_GET['find']))
        echo json_encode($app->find($_GET['email']));
      else if(isset($_GET['nit']))
        $app->getNits();
      else if(isset($_GET['nombre']))
        $app->getNames();     
      else
        echo json_encode($app->get());

      break;

    case 'POST':
           
             if(isset($_GET['completar']))
             $app->ini_clave();
             else if(!isset($_GET['activar_pass']))
             $app->post();

    break;

    case 'DELETE':
    
       $app->delete();
    
    break;

    case 'PUT':
    
    if(isset($_GET['cambiar_clave']))
       {
        parse_str(file_get_contents("php://input"), $_PUT);
        
        $erros = array();

        if(!isset($_PUT['clave']) OR empty($_PUT['clave']))
          $errors[] = 'clave_antigua_no_valida';

        if(!isset($_PUT['nva_clave']) OR empty($_PUT['nva_clave']))
          $errors[] = 'suminitrar_clave';

        if(!isset($_PUT['_nva_clave']) OR empty($_PUT['_nva_clave']))
          $errors[] = 'suminitrar_clave';

        $clave = $_PUT['clave'];
        $nva_clave = $_PUT['nva_clave'];
        $_nva_clave = $_PUT['_nva_clave'];

        $app->cambiar_clave();
       }
    else
       $app->put();

    break;
    

     }

 }



if($_SERVER["REQUEST_METHOD"])
  new usrsCtrl;