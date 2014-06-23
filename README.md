Gomosoft Auth
=============

Sistema de logueo en PHP + MySQL. Este es un sistema de logueo prediseñado, basado en autenticación de usuarios por tokens y http cookies (para persistir la sesion lado cliente, en caso de usar el sistema como REST API).



Instalación
-----------

* Descarga el zip del repo pulsando el botón "Download zip" -->
* Crea una base de datos MySQL.
* Modifica el archivo /controladores/config.inc con los datos de la base de datos, y el resto a tu preferencia.
* Restaura el MySQL dump que esta dentro de la carpeta /auth.
* Activa el módulo rewrite de Apache (mod_rewrite).
* Copia la carpeta /auth al directorio accesible para tu server (www). 
* Abre tu navegador y tipea la ruta que apunte a donde has copiado el app. Ej. localhost/auth
* Ingresa con los siguientes datos:
 
  Usuaio: admin@admin.com
  Clave: admin

* Listo comienza a desarrollar tu aplicación


Instruciones
------------

* Para crear nuevos usuarios solo basta con hacer una petición POST a /controladores/usuarios.php. Ver estructura de la tabla usuarios para definir los campos requeridos.

* Para eliminar un usuario se debe hacer una petición DELETE /controladores/usuarios.php pasando como parametro el id del usuario. Ej. /controladores/usuarios.php?id=2

* Los permisos deben ser suministrados como JSON raw (Un Objeto Javascript parseado como cadena). Estructuta del JSON:


```javascript
  {
  "home":{"r":true,"w":true}, 
  "usuarios" : {"r":true,"w":false}
  }

```
Como podemos observar cada modulo, tiene dos parametros r (lectura) o w (escritura). 

 *para validar permisos llamamos al método BOOL validar_permisos (authCtrl->validar_permisos()). Tomemos como referencia el modulo usuarios, para exlicar la validación de permisos:
  

  ```php
     $auth = new authCtrl;

    //para validar un privilegio en especifico:

     if($auth->validar_permisos('usuarios','r'))
      // tiene permisos lectura
     else
      // no tiene permiso lectura

    //para validar ambos privilegios
     if($auth->validar_permisos('usuarios'))
       //tiene lectura y escritura
     else
       // no tiene lectura y escritura

   ```   

 *podemos redireccionar en cualquier momento usando lo métodos VOID authCtrl->redir_to_login() y authCtrl->redir_to_app().


Librerias incluidas
-------------------

* AngularJS (1.2.18)
* jQuery (2.1.1)
* Bootstrap (3.1.1)
* Animate.css
* Lesscss
* Normalize.css
* Typehead.js




Creditos
--------

@gomosoft [Gomosoft](http://gomosoft.com).

Preguntas adicionales: jgomez@gomosoft.com
