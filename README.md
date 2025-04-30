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

## Requerimientos

* Docker

## Instalación

1. Clonar el repositorio
2. Abrir la dirección del repositorio

    ```bash
    cd turdoteca
    ```
3. Ejecutar Docker

    ```bash
    docker compose up
    ```

4. Para la inicialización de los libros abrir [localhost:8080/bookFiles/?added](http://localhost:8080/bookFiles/?added)

    Tardará un poco, y luego aparecerán los libros agregados

5. Abrir [localhost:8080](http://localhost:8080) y estará la página principal.

## ¿Cómo funciona?

Turdoteca está diseñada enteramente sobre PHP, utiliza código de HTML dentro de las peticiones de PHP para renderizar los contenidos, CSS para estilizarlos y JSON para el almacenamiento de los datos. Los procesos del programa se explican a detalles [aquí](./doc/how_works.md).