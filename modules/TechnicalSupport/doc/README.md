# Manual de Documentación de usuario del sistema KAVAC

El Manual de Documentación del sistema KAVAC a nivel de usuario tiene como
objetivo facilitar el aprendizaje en el uso del sistema, presenta información
acerca de todas las funcionalidades básicas que el sistema ofrece, así como
capturas de pantallas útiles para el seguimiento de la explicación de los
procesos.

## Comenzando

Este manual de usuario ha sido desarrollado usando el generador de sitios
estáticos "Mkdocs" y el tema "Material-Mkdocs", a continuación se listan los
requisitos e instrucciones para la instalación de los paquetes y gestión de
manuales de usuarios.

## Pre-requisitos

Se requiere de un Sistema Operativo de 64 bits y la instalación de algunos
paquetes para el correcto funcionamiento de la aplicación:

    Python >= 3.5
    curl
    python3-pip

MkDocs es compatible con las versiones 3.5, 3.6, 3.7, 3.8 y pypy3 de Python.

## Instalación

Usaremos $ para describir los comandos que se ejecutaran con usuario regular.

### Instalar pip

Si utiliza una versión reciente de Python, el administrador de paquetes de
Python, pip, probablemente esté instalado de forma predeterminada. Para
actualizar pip a su última versión se debe ejecutar el siguiente comando:

    $ pip install --upgrade pip

De caso contrario, si requiere instalar pip por primera vez, debe hacer uso del
paquete curl y ejecuta el siguiente comando:

    $ curl https://bootstrap.pypa.io/get-pip.py -o get-pip.py

Ejecutar el siguiente comando en el mismo directorio donde se ha descargado el
archivo get-pip.py

    $ python get-pip.py

### Instalación de Mkdocs

Instale Mkdocs usando pip:

    $ pip install mkdocs

Ejecute el siguiente comando para verificar la correcta instalación, debe
obtener como respuesta del comando la versión de mkdocs:

    $ mkdocs --version

### Instalación de extensiones requeridas para Mkdocs

    $ pip install mkdocs-material
    $ pip install mkdocs-minify-plugin
    $ pip install mkdocs-material-extensions
    $ pip install mkdocs-with-pdf
    $ pip install mkdocs-with-pdf-plugin
    $ pip install mkdocs-minify

### Comandos básicos de Mkdocs

Ejecute el siguiente comando para crear un nuevo proyecto de documentación:

    $ mkdocs new nombre_del_manual

Ejecute el siguiente comando en el mismo directorio de su proyecto para iniciar
el servidor:

    $ mkdocs serve

Ingrese en la dirección http://127.0.0.1:8000/ desde su navegador web para ver
la página inicial generada.

Ejecute el siguiente comando en el mismo directorio de su proyecto para
construir la documentación a partir de las fuentes generadas:

    $ mkdocs build

Ejecute los siguientes comandos para ver una lista de comandos disponibles:

    $ mkdocs --help

    $ mkdocs build --help

### Configurar el tema Material-Mkdocs

Para agregar el tema a un nuevo archivo de documentación basta con añadir las
siguientes líneas al archivo .yml de configuración de la documentación:

    theme:
        name: material

## Construcción del sitio

### Documentación de usuario KAVAC

Para la gestión de los manuales de documentación del sistema KAVAC se deben
seguir los siguientes pasos:

Ingresar al directorio donde se ubica el manual de documentación e iniciar el
servidor ejecutando el comando:

    $ mkdocs serve

Ingrese en la url http://127.0.0.1:8000/ de su navegador para web ver la página
inicial generada. Al realizar cambios en los archivos de documentación o el
archivo de configuración (archivo .yml), se cargarán automáticamente y serán
visibles en el navegador.

Una vez finalizados los cambios construya el sitio ejecutando el comando:

    $ mkdocs build

Esto le creara o sobreescribirá si ya existen los archivos html donde puede
visualizar el manual desde el navegador web.

### Tema Material-Mkdocs

Para realizar modificaciones en el tema se recomienda seguir la documentación
de [Material for Mkdocs](https://squidfunk.github.io/mkdocs-material/)

## Documentación base de Mkdocs

    * [Mkdocs](https://www.mkdocs.org/)
    * [Material for Mkdocs](https://squidfunk.github.io/mkdocs-material/)
