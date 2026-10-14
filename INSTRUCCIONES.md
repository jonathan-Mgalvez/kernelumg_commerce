# FerroTek — Proyecto final Desarrollo Web (Laravel + MySQL)

## Cómo correrlo
1. Abrir **XAMPP Control Panel** y darle *Start* a **MySQL**.
2. En una terminal dentro de `C:\xampp\htdocs\ferrotek`:
   ```
   php artisan serve
   ```
3. Abrir http://127.0.0.1:8000

Si se borró la base de datos o se quiere reiniciar con datos de ejemplo:
```
php artisan migrate:fresh --seed
```
Si se cambian estilos (clases de Tailwind): `npm run build`

## Compartirlo con un enlace público (túnel de Cloudflare)
Doble clic en `C:\xampp\tunel\iniciar-ferrotek.bat`: enciende MySQL y Laravel, abre el túnel y copia al portapapeles un enlace `https://....trycloudflare.com`.
Funciona mientras esa ventana esté abierta. El enlace cambia cada vez. Usa la base de datos local.

## Usuarios de prueba (contraseña: `password`)
| Rol | Correo |
|---|---|
| Administrador | admin@ferrotek.test |
| Gestor de inventario | inventario@ferrotek.test |
| Cliente | cliente@ferrotek.test |
| Cliente con un borrador en curso | ana.morales@constructoraandina.test |

Los datos de prueba (39 productos en 7 categorías, 9 usuarios, 17 cotizaciones en todos los estados y 7 mensajes) están en `database/seeders/DemoSeeder.php`.

## Módulos
- **Público:** Inicio, Nosotros (misión, visión, valores, servicios), Catálogo con filtros y búsqueda, detalle de producto, Contacto (se guarda en BD).
- **Cliente:** registro/login, Cotizador (descuento por volumen, flete, IVA 12 %, PDF, WhatsApp), Historial.
- **Admin (`/admin`):** resumen, CRUD de Productos (con imagen), CRUD de Categorías, Lotes y Almacén, Cotizaciones (cambiar estado), Mensajes, Usuarios (roles).

## Comercio electrónico (agregado el 2026-10-07)
- **Roles:** `admin` (todo), `inventario` (productos, categorías, almacén, ofertas, pedidos y revisar cotizaciones de clientes; **no cotiza**: no ve el cotizador, el historial ni los botones "Cotizar") y `cliente`. El admin cambia roles y puede **bloquear** cuentas en *Usuarios*.
- **Productos desde la página:** `/admin/productos` → *Nuevo producto* (con imagen). Se guarda en MySQL, aparece en el catálogo y la IA lo encuentra al momento. El listado tiene acciones masivas: activar, desactivar, ajustar precio en %, sumar stock y eliminar.
- **Ofertas** (`/admin/ofertas`, página pública `/ofertas`): descuento por producto con fechas de inicio y fin. Se ve tachado en el catálogo, se aplica en el carrito y la IA la menciona.
- **Carrito y checkout** (`/carrito`): el stock se bloquea dentro de una transacción (`lockForUpdate`) y se descuenta al confirmar. Pago simulado: transferencia, depósito o contra entrega. Código: `app/Services/CarritoService.php` y `PedidoService.php`.
- **Pedidos:** el cliente los ve en `/mis-pedidos` (seguimiento y cancelar si está pendiente); el panel los gestiona en `/admin/pedidos`. Cancelar devuelve el stock. Cada cambio envía un correo (con `MAIL_MAILER=log` queda en `storage/logs`).
- **Cotización → pedido:** una cotización *aprobada* y vigente tiene el botón *Convertir en pedido*, que respeta los precios cotizados.
- **Auditoría** (`/admin/auditoria`, solo admin): altas, cambios y bajas de productos, categorías, ofertas, pedidos, usuarios y estados de cotización (trait `app/Models/Concerns/Auditable.php`).
- **Mantenimiento:** `php artisan ferrotek:purgar` vence cotizaciones caducadas y borra borradores vacíos y carritos abandonados (más de 30 días). Está programado a diario (`php artisan schedule:work`).
- **Datos de ejemplo:** `database/seeders/ComercioSeeder.php` (ofertas, pedidos y el usuario de inventario).

### Revertir estos cambios
En `C:\xampp\htdocs\_respaldos` están el código (`.zip`) y la base (`.sql`) de antes. Con MySQL encendido:
```
powershell -ExecutionPolicy Bypass -File C:\xampp\htdocs\_respaldos\revertir.ps1
```
O solo la base de datos: `php artisan migrate:rollback` (deshace las 6 migraciones nuevas).

## Asistente de cotización con IA (Gemini)
Solo lo ven los usuarios con rol **cliente** (admin e inventario usan el cotizador manual). En `/cotizador`, el cliente escribe en lenguaje natural ("500 varillas de 1/2 y 30 cajas de E6011") y Gemini arma la cotización usando **Function Calling**:
- `buscar_productos`: busca en la tabla `products` de MySQL.
- `agregar_producto` y `cambiar_cantidad`: modifican el borrador usando `CotizacionService`, respetando el stock.
- `ver_cotizacion`: devuelve las partidas y los totales.

Los precios, descuentos, flete e IVA los calcula siempre el servidor, nunca la IA.

Configuración en `.env`: `GEMINI_API_KEY`, `GEMINI_MODEL` (por defecto `gemini-3.5-flash-lite`) y `GEMINI_MODEL_RESPALDO`. Sin key, el chat no aparece y el cotizador manual sigue funcionando. Código: `app/Services/AsistenteCotizacion.php`.

## Base de datos (MySQL `ferrotek`)
`users` 1—N `quotes` 1—N `quote_items` N—1 `products` N—1 `categories`; además `contact_messages`.
Comercio: `users` 1—1 `carts` 1—N `cart_items`; `users` 1—N `orders` 1—N `order_items` N—1 `products`; `orders` N—1 `quotes` (opcional); `products` 1—N `offers`; `audits` (bitácora).

## Dónde está cada cosa
- Rutas: `routes/web.php`
- Reglas del cotizador y datos de la empresa: `config/empresa.php`
- Lógica del cotizador (aquí se conectará la IA más adelante): `app/Services/CotizacionService.php`
- Colores del diseño de Stitch: `tailwind.config.js` y `resources/css/app.css`
- Pruebas: `php artisan test`
