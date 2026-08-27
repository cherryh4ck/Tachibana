# Tachibana - Imageboard liviano en PHP
Tachibana es un imageboard fuertemente inspirado en Devox y 4chan, ofreciendo una interfaz moderna y compacta, junto a la adición de algunas ideas propias. 

## Requisitos
- PHP 8.0 o superior, con las siguientes extensiones habilitadas:
  - `pdo_mysql`
  - `gd`
  - `mbstring`
  - `imagick` (opcional, ofrece miniaturas GIF)
- MySQL o MariaDB
- Servidor web (Apache o nginx)

# Recomendaciones
Si bien Tachibana viene con un `.htaccess` para Apache, este no debe ser usado 

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

# Licencia
Puedes leer la licencia en [`LICENSE`](LICENSE).
