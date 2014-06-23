
<html>
   <head>
     <title>Mi app - Home</title>
     <link rel="stylesheet" href="../libs/normalize-css/normalize.css">
     <link rel="stylesheet" href="../libs/bootstrap/dist/css/bootstrap.min.css">
     <link rel="stylesheet" href="../libs/animate.css/animate.min.css">
     <link rel="stylesheet" href="../recursos/css/estilo.css">
   </head>
   <body>

   	<div class='container w8 to-center animated fadeInUp'>
              	<br>
              	<br>
              	<br>
              	<br>

      <?php 
      
      $vista = isset($_GET['v']) ? htmlentities($_GET['v']) : 'home';
      include("./vistas/{$vista}.php"); 

      ?>
 </div>

   </body>
</html>