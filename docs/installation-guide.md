# 📖 Guía de Instalación – TechStore

## 1. Requisitos Previos

Antes de instalar el sistema, asegúrate de contar con lo siguiente:  
— **Servidor web:** Apache (recomendado: XAMPP o LAMP stack).  
— **PHP:** Versión 8.2 o superior.  
— **Base de datos:** MySQL/MariaDB.  
— **Composer:** Para gestión de dependencias PHP.  
— **Navegador moderno:** Chrome, Firefox o Edge.  

---

## 2. Clonar el Proyecto
```bash
git clone <https://github.com/StevenRv13/TechStore-ITI523.git>
cd TechStore
```

---

## 3. Instalar Dependencias
El proyecto utiliza **Composer** para gestionar librerías (ej. DomPDF para reportes).  
Ejecuta en la raíz del proyecto:

```bash
composer install
```

Esto instalará las dependencias definidas en `composer.json`.  

---

## 4. Configuración de la Base de Datos

1. Crea una nueva base de datos en MySQL llamada `techstore_db`.  
   ```sql
   CREATE DATABASE techstore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Importa el archivo SQL incluido en el proyecto:  
   ```bash
   mysql -u root -p techstore_db < techstore_db
   ```

   Esto creará las tablas `usuarios`, `productos`, `categorias`, `carrito`, `pedidos`, `detalles_pedido` y cargará datos iniciales.

---

## 5. Configuración de Conexión
Edita el archivo `config/database.php` e ingresa tus credenciales de conexión:

```php
$host = "127.0.0.1";
$dbname = "techstore_db";
$username = "root";
$password = "";
```

> ⚠️ Cambia `root` y `""` por tu usuario y contraseña de MySQL.

---

## 6. Configuración del Servidor
— Copia el proyecto dentro de la carpeta `htdocs` (si usas XAMPP) o en `/var/www/html` (si usas LAMP).  
— Verifica que el archivo `.htaccess` esté habilitado para manejar rutas amigables.  
— En XAMPP asegúrate de tener activados los módulos **Apache** y **MySQL**.  

---

## 7. Acceso a la Aplicación
— Abre en el navegador:  
```
http://localhost/TechStore/index.php
```

— Página principal → Catálogo de productos.  
— `pages/auth/login.php` → Inicio de sesión.  
— `pages/auth/register.php` → Registro de usuarios.  
— `admin/dashboard.php` → Panel administrativo (requiere usuario admin).  

---

## 8. Usuarios de Prueba
El sistema incluye usuarios de ejemplo cargados desde `techstore_db`

— **Administrador**  
  - Correo: `stevenramirezv@gmail.com`  
  - Contraseña: la definida en `steven123` (hash bcrypt).

— **Usuario estándar**  
  - Correo: `manu@gmail.com`
  - Contraseña: igual, definida en la tabla `usuarios`. `manu123`

> ⚠️ Puedes actualizar las contraseñas manualmente en phpMyAdmin o con `password_hash()` en PHP.

---

## 9. Generación de Reportes
El sistema utiliza **DomPDF** para exportar reportes a PDF.  
— Asegúrate de tener instalada la dependencia con `composer install`.  
— Accede a `admin/reports-pdf.php` para generar reportes.  

---

## 10. Problemas Comunes
— **Pantalla en blanco:** habilita `display_errors` en `php.ini`.  
— **Error de conexión MySQL:** revisa credenciales en `config/database.php`.  
— **CSS/JS no cargan:** revisa que `.htaccess` esté habilitado en Apache (`mod_rewrite`).  


