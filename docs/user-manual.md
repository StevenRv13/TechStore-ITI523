# 📖 Manual de Usuario – TechStore

Este manual describe el uso del sistema **TechStore**, tanto para clientes como para administradores.


# 🔑 Autenticación

## Registro de Usuario
Para crear una cuenta nueva en TechStore, el usuario debe:  
— Ingresar sus datos personales: **nombre, apellido, correo electrónico, contraseña, teléfono y dirección** (opcional).  
— Confirmar la contraseña (mínimo 6 caracteres).  
— Aceptar los **términos y condiciones** y la **política de privacidad**.  
— (Opcional) Suscribirse al boletín de ofertas y noticias.  
— Presionar el botón **“Crear Cuenta”**.  

El sistema valida automáticamente que:  
— El correo no esté registrado previamente.  
— Las contraseñas coincidan y cumplan el mínimo de caracteres.  

Si el registro es exitoso, se muestra un mensaje y el usuario es redirigido al login.  

## Inicio de Sesión
Para acceder al sistema, el usuario debe:  
— Ingresar su **correo electrónico** y **contraseña**.  
— (Opcional) Activar la casilla **“Recordarme”**.  
— Hacer clic en el botón **“Iniciar Sesión”**.  

El sistema valida los datos y si son correctos:  
— Se inicia la sesión.  
— Se guarda la fecha/hora del último acceso.  
— Se redirige automáticamente a la página principal (`index.php`).  

En caso de error (correo o contraseña incorrectos), se muestra un mensaje de advertencia.  

## Cierre de Sesión
Desde cualquier página, el usuario puede **cerrar sesión**.  
El sistema elimina la sesión activa y muestra un mensaje confirmando la salida.  



# 🛒 Catálogo y Compras

## Página Principal
En la página inicial (`index.php`), el usuario puede:  
— Ver un **hero section** con promociones y productos destacados.  
— Acceder al menú de **categorías** (Smartphones, Laptops, Tablets, Accesorios).  
— Usar la **barra de búsqueda** para encontrar productos rápidamente.  
— Consultar su **carrito de compras** desde la barra de navegación.  

## Catálogo de Productos
En el catálogo (`catalog.php`):  
— Los usuarios pueden explorar todos los productos o filtrar por categoría.  
— Existe un buscador interno y filtros por rango de precio y disponibilidad.  
— Cada producto muestra: nombre, imagen, precio y botón **“Agregar al carrito”**.  

## Carrito de Compras
En la vista de carrito (`shopping-cart.php`):  
— Se listan los productos añadidos con: imagen, nombre, precio unitario y cantidad.  
— El usuario puede:  
  — Aumentar o reducir la cantidad de cada producto.  
  — Eliminar productos del carrito.  
— En la sección de **Resumen del Pedido** se calcula automáticamente:  
  — Subtotal.  
  — IVA (13%).  
  — Costo de envío (gratis en compras mayores a ₡50.000).  
  — Total final.  
— El botón **“Proceder al Pago”** redirige al proceso de checkout.  

## Checkout – Finalizar Compra
En la página de pago (`checkout.php`):  
— El sistema muestra un resumen con: productos seleccionados, subtotal, impuestos y total.  
— El usuario debe ingresar:  
  — Dirección de envío.  
  — Confirmar sus datos de contacto (nombre, correo, teléfono).  
  — Elegir un **método de pago** (ej. tarjeta de crédito o PayPal).  
— Una vez confirmada la compra, se genera:  
  — Un **número de pedido único** (ej: `TS202508291234`).  
  — Los detalles del pedido se guardan en la base de datos.  
  — Se descuenta el stock de cada producto.  
— El carrito se vacía automáticamente y se redirige al comprobante.  

## Confirmación de Pedido
En la vista de confirmación (`order-success.php`):  
— El usuario recibe un mensaje **“¡Pedido Confirmado!”**.  
— Se muestran:  
  — Número de pedido y número de seguimiento.  
  — Fecha de compra.  
  — Cantidad de productos.  
  — Método de pago seleccionado.  
  — Dirección de envío.  
— El estado inicial del pedido es **“En procesamiento”**.  
— Se agradece al cliente y se ofrecen accesos directos a:  
  — Volver al inicio.  
  — Seguir comprando.  


# 🛠️ Panel de Administración

## Acceso
— Solo los usuarios con rol **Administrador** pueden ingresar al panel (`admin/dashboard.php`).  
— Desde el encabezado se puede volver a la tienda o cerrar sesión.  
— El menú lateral incluye accesos a: **Dashboard, Productos, Pedidos, Usuarios, Reportes**.  

## Dashboard
El **dashboard** muestra un resumen general del sistema:  
— Total de productos, pedidos y usuarios.  
— Productos más vendidos.  
— Alertas de productos con stock bajo (< 10).  

## Gestión de Productos
En `products-admin.php`:  
— Se muestra la lista de productos con: ID, imagen, nombre, categoría, precio, stock y estado.  
— El administrador puede:  
  — **Agregar productos** mediante un formulario (nombre, categoría, precio, stock, imagen).  
  — Editar información de productos existentes.  
  — Cambiar estado (activo/inactivo).  

## Gestión de Pedidos
En `orders-admin.php`:  
— Se listan todos los pedidos con: número, cliente, correo, productos, total, estado y fecha.  
— Se pueden actualizar los estados del pedido: **Pendiente, Procesando, Enviado, Entregado, Cancelado**.  
— Cada pedido muestra un detalle completo: número, estado, método de pago, dirección de envío y total.  

## Gestión de Usuarios
En `users-admin.php`:  
— Lista de usuarios con: nombre, correo, fecha de registro, estado y rol.  
— El administrador puede:  
  — Activar o desactivar cuentas de clientes.  
  — Ver estadísticas: total de usuarios, activos, clientes con compras, administradores.  
— Los administradores no pueden ser desactivados por seguridad.  

## Reportes
En `reports.php`:  
— Se muestran estadísticas de ventas (mensuales y por productos) con tablas y gráficos.  
— Opciones disponibles:  
  — Exportar reportes a **PDF** (`reports-pdf.php`).  
  — Imprimir directamente desde el navegador.  

En `reports-pdf.php`:  
— Se genera automáticamente un documento en PDF con:  
  — Ventas por categoría.  
  — Top 10 productos más vendidos.  
— Los reportes incluyen la fecha de generación y se descargan en el dispositivo.  

