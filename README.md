![Turdoteca](./doc/assets/turdoteca_logo.svg)
# Turdoteca

Turdoteca es una biblioteca virtual desarrollada en PHP, HTML, CSS y JSON, está adaptada para ser multiplataforma.

## Características

* **Catálogo** de libros
* **Búsqueda** de libros con filtros por género
* **Inicio** de sesión
* **Registro** de usuario
* Ver los detalles de la **cuenta de usuario**
* Muestra los **detalles** de un libro
* A los usuarios registrados les permite **leer**, dar **like** y **comentar** un libro
* Los usuarios registrados pueden **solicitar libros** a los administradores
* Mantiene el **progreso del libro**
* En el **modo lectura** se puede cambiar el **tema y tamaño de letra**
* A los administradores les permite **listar**, **añadir**, **cambiar las portadas** y ver las **estadísticas** de los libros

## Instalación

### Requerimientos

* Windows 10 o superior
* php 8 o superior

1. En el archivo php.ini en Module Settings se debe agregar la siguiente línea

    ```ini
    extension=php_zip.dll
    ```

    con ella podemos utilizar la librería ZipArchive para la descompresión de los archivos EPub.

2. Iniciar el servicio de php
3. Para la inicialización de los libros abrir `/bookFiles/?added`
4. Una vez que se listen los libros abrir `/bookFiles/dashboard/?goldenapple` para la administración de la biblioteca.

## ¿Cómo funciona?

Turdoteca está diseñada enteramente sobre PHP, utiliza código de HTML dentro de las peticiones de PHP para renderizar los contenidos, CSS para estilizarlos y JSON para el almacenamiento de los datos. Los procesos del programa se explican a detalles [aquí](./doc/how_works.md).