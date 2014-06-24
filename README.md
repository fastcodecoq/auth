Gomosoft Auth 
=============

Sistema de logueo prediseñado, basado en autenticación por tokens y http cookies (para persistir la sesion lado cliente, en caso de usar el sistema como REST API). En su estructura contiene PHP para backend y MySQL para base de datos. 

Esta herramienta te ahorrará horas de desarrollo, el cual podrás invertir en los modulos de tu app. Además incluye un conjuto de librerías, listas para que las uses de acuerdo a tus necesidades (librerias incluidas se detallan abajo).

Demo 
----

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

1. Descarga [Gomosoft Auth](https://github.com/gomosoft/auth/archive/master.zip).
2. Crea una base de datos MySQL.
3. Modifica el archivo /controladores/config.inc con los datos de la base de datos, y el resto a tu preferencia.
4. Restaura el MySQL dump que esta dentro de la carpeta /auth.
5. Activa el módulo rewrite de Apache (mod_rewrite).
6. Copia la carpeta /auth al directorio accesible para tu server (www). 
7. Abre tu navegador y tipea la ruta que apunte a donde has copiado el app. Ej. localhost/auth
8. Ingresa con los siguientes datos:
 
  Usuaio: admin@admin.com
  Clave: admin

9. Listo comienza a desarrollar tu aplicación.


Instalación a través de GIT
---------------------------

1. $ git clone https://github.com/gomosoft/auth.git
2. cd auth/controladores
3. gedit config.inc
4. editamos las lineas de acuerdo a nuestro entorno de desarrollo.
5. Abre tu navegador y tipea la ruta que apunte a donde has copiado el app. Ej. localhost/auth
6. Inicia sesión con los siguientes datos:
 
  Usuaio: admin@admin.com
  Clave: admin

7. Listo comienza a desarrollar tu aplicación.


Instruciones
------------

* Para crear nuevos usuarios solo basta con hacer una petición `POST a /controladores/usuarios.php`. Ver estructura de la tabla usuarios para definir los campos requeridos.

Los permisos deben ser suministrados como JSON RAW (Un Objeto Javascript parseado como cadena). Estructuta del JSON:


```javascript
  {
  "home":{"r":true,"w":true}, 
  "usuarios" : {"r":true},  //no tiene permiso escritura
  "reportes" : {"w":true}  //no tiene permiso lectura
  //... todos los modulos que necesites
  }

```
Como podemos observar cada modulo, tiene un objeto con dos variables r (lectura) o w (escritura). 

* para validar permisos llamamos al método BOOL validar_permisos. Tomemos como referencia el modulo usuarios, para exlicar la validación de permisos:
  
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


* Para eliminar un usuario se debe hacer una petición `DELETE a /controladores/usuarios.php` pasando como parametro el id del usuario. Ej. /controladores/usuarios.php?id=2

* Para actualizar un usuario se debe hacer una petición `PUT a /controladores/usuarios.php` pasando como parametros los campos que se actualizarán.

* Para cambiar la clave se hace una petición `PUT a /controladores/usuarios.php?cambiar_clave`, pasando como parametro  clave antigua, nueva clave y confirmación de nueva clave `[clave,nva_clave,_nva_clave]`, ver el archivo /controladores/usuarios.php, para mayor comprensión.

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

Activar REST API
----------------

1. Cambiamos dentro del archivo de configuración /controladores/config.inc, la siguiente linea:

```php
define("REST_API",false); 
```
por

```php
define("REST_API",true); 
```

2. Debemos permitir Cross Domain Request, para ello hacemos lo siguiente:

**Apache.**

 1. Activamos el modulo headers `$ sudo a2enmod headers`.
 2. Reiniciamos el servicio Apache `$ sudo service restart apache2`.
 3. Descomentamos en `/.htaccess` las siguientes líneas:

```apache
<IfModule mod_headers.c>

  Header set Access-Control-Allow-Origin "ip_del_cliente"

</IfModule>
```


**Nginx.**

 1. Añadimos la línea `add_header Access-Control-Allow-Origin ip_del_cliente;`, al archivo de configuración de nuestro sitio Nginx.
 2. Reinicamos el servicio Nginx `sudo service nginx restart`.


Usando como REST API
--------------------

* Obtener permisos 
```
GET auth.php?perms&token=<token>&uid=<uid>
```

* Validar permisos
```
GET auth.php?validar_perms&token=<token>&uid=<uid>& modulo=<modulo | opcional>&privilegio=<privilegio | opcional>
```

* Validar si un usario esta logueado 
```
GET auth.php?esta_logueado&token=<token>&uid=<uid>
```

El resto de procesos, como validación de permisos y de autenticacion, se puede hacer desde los demás controladores que agreguemos a nuestro API. Ej:

```php
 //este es un controlador personalizado de mi API REST

 require_once('ruta/a/auth.php');
 $auth = new AuthCtrl;

//esto hará el proceso de validación
 if(!$auth->esta_logueado())
   $auth->no(); //realiza la operacion correspondiente para requests no autorizados
 else
   {

     //esta logueado hagamos algo

     //VOID authCtrl:ok($rs <mixed>[, $msg <mixed>]) imprime una respuesta JSON valida.    

     $auth->ok('esta logueado'); 

   }
```

Notas: 

1. El registro, la creación y logueo de usuarios, debe hacerse en el servidor del API. 
2. Trata de dar las respuestas en JSON. 


**Respuesta.**

Cuando el modo REST API esta activo, la respuesta se da en JSON:

```javascript
 
 { 
   error : <boolean>
   ,data : <mixed>  //datos solicitados
   , mensaje : <String>
 }

```


Descargas
---------

* [Gomosoft Auth más reciente](https://github.com/gomosoft/auth/archive/master.zip)
* [Gomosoft Auth v1.0b (estable)](http://gomosoft.com/d/v1.0.b.zip)

Funcionalidades en camino
-------------------------

* Modulo usuarios (crear, modificar, eliminar, cambiar contraseña)
* Modulo recuperar contraseña


Creditos
--------

@gomosoft [Gomosoft](http://gomosoft.com).

Preguntas: jgomez@gomosoft.com

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