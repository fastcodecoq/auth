<?php
include('controladores/auth.php');


$app = new authCtrl;

if($app->esta_logueado())
  $app->redir_to_app();  // si esta logueado lo mandamos a la pagina del app

?>

<html>
   <head>
     <title>Mi app</title>
     <link rel="stylesheet" href="libs/normalize-css/normalize.css">
     <link rel="stylesheet" href="libs/bootstrap/dist/css/bootstrap.min.css">
     <link rel="stylesheet" href="recursos/css/estilo.css">
   </head>
   <body>

   	<div class='container w8'>
              	<br>
              	<br>
              	<h3 class="tlc">Gomosoft AUTH</h3>
              	<br>
              	<br>

   	<div class="to-center w5">
   	    <form action="controladores/auth.php?auth" method="POST" name="login">
          <div class="to-center w8" >
          <table class='wfull'>
            <tbody>
              <tr>
                <td>
            <span class='block tlf'>Correo</span><br>
            <input type="email" placeholder="email@mail.com" name="email" class="input-big" style="width:100%">
                </td>
              </tr>
              <tr class='pass'>
                <td>
              	<br>

              <span class="block tlf">Clave</span><br>
            <input type="password" class="input-big wfull"  name="clave"/>
                </td>
              </tr>
              <tr>
                <td>
                	<br>
                	<br>
                  <div class="block">
                  <div class="pull-left">
                  <label style="width:auto">Recordarme <input type="checkbox" name="remember"></label>
                </div>
                <div class="pull-right">
                  <a href="#" style="line-height: 2.5;" class="activar">Recuperar Clave</a>
                </div>
              </div>
                   <br>
                   <br>
                   <br>
            <button class="btn btn-primary btn-large block wfull" >Ingresar</button>
                </td>
              </tr>
            </tbody>
          </table>
              </div>            
         </form>
     </div>
 </div>

   </body>
</html>