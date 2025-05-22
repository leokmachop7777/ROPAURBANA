## Descripción 🧑‍💻

Este es un proyecto de página web para la venta de ropa, desarrollado utilizando tecnologías como **PHP**, **HTML**, **CSS** y **JavaScript**. Además, se implementaron funcionalidades dinámicas mediante el uso de la librería **AJAX** para mejorar la experiencia del usuario sin necesidad de recargar la página constantemente.

La plataforma permite mostrar productos, gestionar interacciones con el usuario y establecer una base para la implementación de un sistema de carrito de compras y manejo de datos mediante bases de datos MySQL.

## Autor –  
**Leonardo Camacho**

* [LinkedIn](https://www.linkedin.com/in/leonardo-camacho-45a09b266/)

## Ver ejemplo en vivo

[ENLACEGITHUBPAGES](ENLACEGITHUBPAGES)

## Instalación ⚙️

Para ejecutar este proyecto localmente, sigue estos pasos:

1. **Clona el repositorio:**

   ```bash
   git clone https://github.com/tuusuario/nombre-del-repositorio.git
Coloca los archivos en tu servidor local:

Copia la carpeta del proyecto dentro del directorio htdocs si estás usando XAMPP, o en la carpeta pública correspondiente si usas otro entorno local.

Configura la base de datos:

Crea una base de datos en phpMyAdmin (por ejemplo: tienda_ropa).

Importa el archivo .sql incluido en el proyecto para crear las tablas y datos iniciales.

Edita la configuración de conexión a la base de datos:

Abre el archivo donde se configura la conexión (por ejemplo, config.php) y asegúrate de que los datos coincidan con los de tu servidor local:

php
Copiar
Editar
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tienda_ropa';
Inicia el servidor local:

Abre XAMPP (u otra herramienta) y activa Apache y MySQL.

Abre el navegador y accede al proyecto:
http://localhost/nombre-del-proyecto/

### 📦 Importación de la base de datos

Para que la aplicación funcione correctamente, es necesario importar la base de datos que contiene las tablas del sistema. Sigue estos pasos:

1. Abre **phpMyAdmin** en tu navegador:
http://localhost/phpmyadmin

2. Crea una nueva base de datos con el nombre:
tu_tienda_urbana_db

3. Una vez creada, haz clic sobre ella y luego selecciona la pestaña **"Importar"** en el menú superior.

4. En la sección **"Archivo a importar"**, haz clic en **"Elegir archivo"** y selecciona el archivo `tu_tienda_urbana_db.sql` que se encuentra en la carpeta `/database/` de este proyecto.

5. Haz clic en **"Continuar"** para completar la importación.

Después de esto, todas las tablas necesarias estarán listas en tu entorno local.

## Contratación  
Si quieres contratarme puedes escribirme a (leonardo44camacho@gmail.com) para consultas.

## Licencia 🪪  
MIT Public License v3.0  
No puede usarse comercialmente.
