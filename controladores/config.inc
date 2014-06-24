<?php

         define("db_user","gomosoft"); //usuario de la base de datos
         define("db_bd","auth"); //el nombre de la base de datos del app, por defecto es auth
         define("db_pass","****"); //la clave de la base de datos
         define("db_host","localhost");  
         define("so","linux");
         define("idioma","español");
         define("charset","utf-8");
         define("zona_horaria","America/Bogota");
         define("dominio",""); //si es diferente a localhost colocarlo aqui para mayor seguridad  
                               // ej. define("dominio","midominio.com");
         define("REST_API",false); //cambiar a true para usar como REST API
         define("cookie_name","auth"); //nombre de la cookie
         define("cookie_https",false); //colocalo true si deseas que la cookie solo se sirva a través https
         define("path",""); // si el app funcionará en un directorio diferente aqui, colocalo.
         define("login_url","/localhost/auth");  //cambiar por la ruta a su archivo login         
         define("app_url","/localhost/auth/app");  //cambiar por la ruta a su archivo login         
         define("ttl", 7200);  //tiempo de vida del token en segundos



