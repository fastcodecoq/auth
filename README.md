Gomosoft Auth 
=============

Sistema de logueo prediseñado, basado en autenticación por tokens y http cookies (para persistir la sesion lado cliente, en caso de usar el sistema como REST API). En su estructura contiene PHP para backend y MySQL para base de datos. 

Esta herramienta te ahorrará horas de desarrollo, el cual podrás invertir en los modulos de tu app. Además incluye un conjuto de librerías, listas para que las uses de acuerdo a tus necesidades (librerias incluidas se detallan abajo).

DEMO 
-----

usuario: admin@admin.com 

clave: admin

[DEMO](http://gomosoft.com/auth)


Librerias incluidas
-------------------

* AngularJS (1.2.18)
* jQuery (2.1.1)
* Bootstrap (3.1.1)
* Animate.css
* Lesscss
* Normalize.css
* Typehead.js



Instalación
-----------

* Descarga [Gomosoft Auth](https://github.com/gomosoft/auth/archive/master.zip).
* Crea una base de datos MySQL.
* Modifica el archivo /controladores/config.inc con los datos de la base de datos, y el resto a tu preferencia.
* Restaura el MySQL dump que esta dentro de la carpeta /auth.
* Activa el módulo rewrite de Apache (mod_rewrite).
* Copia la carpeta /auth al directorio accesible para tu server (www). 
* Abre tu navegador y tipea la ruta que apunte a donde has copiado el app. Ej. localhost/auth
* Ingresa con los siguientes datos:
 
  Usuaio: admin@admin.com
  Clave: admin

* Listo comienza a desarrollar tu aplicación.


Instalación a través de GIT
---------------------------

* $ git clone https://github.com/gomosoft/auth.git
* cd auth/controladores
* gedit config.inc
* editamos las lineas de acuerdo a nuestro entorno de desarrollo.
* Abre tu navegador y tipea la ruta que apunte a donde has copiado el app. Ej. localhost/auth
* Inicia sesión con los siguientes datos:
 
  Usuaio: admin@admin.com
  Clave: admin

* Listo comienza a desarrollar tu aplicación.


Instruciones
------------

* Para crear nuevos usuarios solo basta con hacer una petición POST a /controladores/usuarios.php. Ver estructura de la tabla usuarios para definir los campos requeridos.

Los permisos deben ser suministrados como JSON raw (Un Objeto Javascript parseado como cadena). Estructuta del JSON:


```javascript
  {
  "home":{"r":true,"w":true}, 
  "usuarios" : {"r":true},  //no tiene permiso escritura
  "reportes" : {"w":true}  //no tiene permiso lectura
  //... todos los modulos que necesites
  }

```
Como podemos observar cada modulo, tiene un objeto con dos variables r (lectura) o w (escritura). 

 *para validar permisos llamamos al método BOOL validar_permisos (authCtrl->validar_permisos()). Tomemos como referencia el modulo usuarios, para exlicar la validación de permisos:
  

  ```php
     require_once('ruta/a/auth.php');
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


* Para eliminar un usuario se debe hacer una petición DELETE a /controladores/usuarios.php pasando como parametro el id del usuario. Ej. /controladores/usuarios.php?id=2

* Para eliminar un usuario se debe hacer una petición PUT a /controladores/usuarios.php pasando como parametros los campos que se actualizarán. Ej. /controladores/usuarios.php

* Para cambiar la clave se hace una petición GET a /controladores/usuarios.php?cambiar_clave, pasando como parametro usuario (email), clave antigua, clave y confirmación de clave [email, clave,_clave], ver el archivo /controladores/usuarios.php, para mayor comprensión.

* Redireccionar en cualquier momento usando lo métodos disponibles: 
```php
require_once('ruta/a/auth.php');
$auth = new authCtrl;

// para redireccionar al login
 $auth->redir_to_login()

// para redirecionar al app
 $auth->redir_to_app().
```
* Validar si el usuario esta logueado:

```php
include('ruta/a/auth.php');

$auth = new authCtrl;

 if(!$auth->esta_logueado())
     //esta logueado
 else
    //no esta logueado
```

Descargas
---------

* [Gomosoft Auth más reciente](https://github.com/gomosoft/auth/archive/master.zip)
* [Gomosoft Auth v1.0b (estable)](http://gomosoft.com/d/v1.0.b.zip)


Creditos
--------

@gomosoft [Gomosoft](http://gomosoft.com).

Preguntas adicionales: jgomez@gomosoft.com

Licencia GPLv3
--------------


This file is part of Gomosoft Auth.

Gomosoft Auth is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Gomosoft Auth is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Gomosoft Auth.  If not, see <http://www.gnu.org/licenses/>.