<?php

include('../controladores/auth.php');


$auth = new authCtrl;


 if(!$auth->esta_logueado())
  $auth->redir_to_login();  // si no esta logueado lo mandamos a la pagina de login 

  //validamos si tiene permisos para esta vista 
    
  if(!$auth->validar_permisos('usuarios', 'r')) // r es lectura 
    	die('no autorizado');

	  //si necesitamos validar lectura y escritura lo hacemos asi 
	  //  $auth->validar_permisos('home');
 	  // los permisos son un JSON String salvado en el campo Permisos de la tabla
	  // usuarios. Ej.

	  // {'home' : {'r' : true , 'w' : false}, 'usuarios' : {'r':true , 'w' : true}}
	  //  'home' : {'r' : true , 'w' : false}  -> home es el nombre del modulo


?>


<div class="to-center w6 tlc to-center">
   		<p>
     Vista <strong>Usuarios</strong> &nbsp;&nbsp;<a href='salir'>Salir</a>
      </p>
     </div>