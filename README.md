# Tachibana - Imageboard liviano en PHP
Tachibana es un imageboard fuertemente inspirado en Devox y 4chan, ofreciendo una interfaz moderna y compacta, junto a la adición de algunas ideas propias.

Se enfoca en ser más funcional que en el anonimato.

## Requisitos
- PHP 8.0 o superior, con las siguientes extensiones instaladas y habilitadas:
  - `pdo_mysql`
  - `gd`
  - `mbstring`
  - `imagick` (opcional, ofrece miniaturas GIF)
- MySQL o MariaDB
- Servidor web (Apache o nginx)

## Recomendaciones
Si bien Tachibana viene con un `.htaccess` para Apache, este no debe ser usado del todo. 

## Instalación
1. Clona el repositorio y extraelo en el directorio de tu servidor web usando:
```bash
   git clone https://github.com/cherryh4ck/Tachibana.git
```
2. Asegurate que el usuario que ejecuta PHP tenga permisos de escritura sobre la carpeta raiz del proyecto.
3. Accede a la URL que hayas puesto para acceder a Tachibana.
4. Sigue los pasos y Tachibana estará instalado.

Puedes acceder a la cuenta de administrador usando:
  - Usuario: **root**
  - Contraseña: **admin**

Es **importante** que cambies las credenciales de la cuenta lo antes posible.

## FAQ
**1. ¿Se usó IA generativa en el proyecto?**

> Sí, se usó IA generativa para crear y desarrollar aun más ciertos apartados, más que nada para acelerar el desarrollo. Obviamente, me hago cargo de testearlo correctamente y modificar algo si es necesario, porque si no, sería cualquier cosa ya. En lo personal no es algo que me guste del todo, pero como este proyecto también es parte de un proyecto escolar, tuve que hacerlo para entregarlo a tiempo.

**2. ¿Cuando se lanzará una versión completa?**

> No tengo mucha idea, pero esperemos que sea pronto. Gran parte de lo planeado ya fue implementado, pero aún faltan cosas como para considerarlo "completo".

**3. ¿Es seguro hostear esto?**

> No del todo. Hay muchas cosas por terminar y hay ciertos apartados de Tachibana que son ciertamente vulnerables. Aunque tal vez se pueda hacer más seguro con ciertas configuraciones en el servidor web (que es algo que deberías hacer desde un principio).

## Licencia
Puedes leer la licencia en [`LICENSE`](LICENSE).
