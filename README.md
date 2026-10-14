# FerroTek

Tienda y cotizador en línea para una ferretería industrial. Proyecto final de Desarrollo Web hecho con **Laravel 12 + MySQL + Tailwind**.

- **Público:** inicio, nosotros, catálogo con filtros, ofertas y contacto.
- **Cliente:** registro/login, cotizador (descuento por volumen, flete, IVA, PDF), asistente con IA (Gemini), carrito, checkout y seguimiento de pedidos.
- **Administrador / Inventario:** panel con productos, categorías, almacén, ofertas, pedidos, cotizaciones, mensajes, usuarios y auditoría.

La descripción completa de los módulos y reglas de negocio está en [INSTRUCCIONES.md](INSTRUCCIONES.md).

---

## Qué necesitas instalar

| Programa | Versión | Para qué |
|---|---|---|
| [XAMPP](https://www.apachefriends.org/) | PHP **8.2 o superior** y MySQL/MariaDB | Servidor PHP y base de datos |
| [Composer](https://getcomposer.org/download/) | 2.x | Dependencias de PHP |
| [Node.js](https://nodejs.org/) | 20.19 o superior (LTS) | Compilar los estilos (Tailwind/Vite) |
| [Git](https://git-scm.com/downloads) | cualquiera | Clonar el repositorio |

> PHP debe tener activas las extensiones habituales de Laravel (`mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `gd`, `zip`). En XAMPP vienen activas por defecto.

Comprueba las versiones en una terminal:

```bash
php -v
composer -V
node -v
npm -v
git --version
```

---

## Paso a paso para correrlo

### 1. Clonar la rama `ferrotek`

```bash
git clone -b ferrotek https://github.com/jonathan-Mgalvez/kernelumg_commerce.git ferrotek
cd ferrotek
```

(Si ya clonaste el repo completo: `git checkout ferrotek`.)

### 2. Encender MySQL

Abre el **XAMPP Control Panel** y pulsa *Start* en **MySQL**. (Apache no es necesario: usaremos `php artisan serve`.)

### 3. Crear la base de datos vacía

Entra a http://localhost/phpmyadmin → *Nueva* → nombre `ferrotek` → cotejamiento `utf8mb4_unicode_ci` → *Crear*.

O por terminal (en Windows el cliente está en `C:\xampp\mysql\bin`):

```bash
mysql -u root -e "CREATE DATABASE ferrotek CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. Instalar las dependencias

```bash
composer install
npm install
```

### 5. Crear el archivo `.env`

El `.env` guarda la configuración y las claves, por eso **no se sube a GitHub**. Cópialo desde el ejemplo:

```bash
# Windows (PowerShell o CMD)
copy .env.example .env

# Linux / macOS / Git Bash
cp .env.example .env
```

Abre `.env` y revisa estas líneas (con XAMPP por defecto ya coinciden; `DB_PASSWORD` va vacío):

```env
DB_DATABASE=ferrotek
DB_USERNAME=root
DB_PASSWORD=
```

Si tu MySQL usa otra contraseña o puerto, cámbialo aquí.

### 6. Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 7. Crear las tablas y cargar los datos de ejemplo

```bash
php artisan migrate --seed
```

Esto crea todas las tablas y carga: 39 productos en 7 categorías, 9 usuarios, cotizaciones en todos los estados, ofertas, pedidos y mensajes de ejemplo.

> Para borrar todo y empezar de cero: `php artisan migrate:fresh --seed`.

### 8. Enlazar la carpeta de imágenes

Para que se vean las imágenes de productos que se suban desde el panel:

```bash
php artisan storage:link
```

### 9. Compilar los estilos

```bash
npm run build
```

(La carpeta `public/build` no está en el repositorio; hay que generarla en cada equipo. Si vas a modificar estilos, usa `npm run dev` en otra terminal.)

### 10. Levantar el servidor

```bash
php artisan serve
```

Abre http://127.0.0.1:8000

---

## Usuarios de prueba

Contraseña de todos: `password`

| Rol | Correo | Qué puede hacer |
|---|---|---|
| Administrador | admin@ferrotek.test | Todo el panel `/admin` |
| Gestor de inventario | inventario@ferrotek.test | Productos, categorías, almacén, ofertas, pedidos |
| Cliente | cliente@ferrotek.test | Cotizador con IA, carrito, pedidos |
| Cliente con borrador | ana.morales@constructoraandina.test | Cliente con una cotización en curso |

---

## Asistente de cotización con IA (opcional)

El chat del cotizador usa **Gemini**. Sin configurarlo, el chat no aparece y todo lo demás funciona.

1. Crea una key gratuita en https://aistudio.google.com/apikey
2. Pégala en tu `.env`:
   ```env
   GEMINI_API_KEY=tu_clave_aqui
   ```
3. Si ya tenías el servidor corriendo, limpia la caché de configuración:
   ```bash
   php artisan config:clear
   ```
4. Inicia sesión como **cliente** (`cliente@ferrotek.test`) y entra a *Cotizador*.

> Cada persona usa su propia key. **Nunca subas el `.env` a GitHub.**

---

## Comandos útiles

```bash
php artisan test                    # correr las pruebas
php artisan migrate:fresh --seed    # reiniciar la base con datos de ejemplo
php artisan ferrotek:purgar         # vencer cotizaciones caducadas y limpiar carritos viejos
php artisan schedule:work           # ejecutar las tareas programadas (la purga corre a diario a las 02:00)
php artisan config:clear            # limpiar caché de configuración tras cambiar el .env
```

Los correos de pedidos no se envían: con `MAIL_MAILER=log` quedan en `storage/logs/laravel.log`.

---

## Problemas frecuentes

| Síntoma | Solución |
|---|---|
| `SQLSTATE[HY000] [2002] No se puede establecer una conexión` | MySQL no está encendido en XAMPP (paso 2). |
| `Unknown database 'ferrotek'` | Falta crear la base de datos (paso 3). |
| `Access denied for user 'root'` | La contraseña de `DB_PASSWORD` en `.env` no coincide con tu MySQL. |
| `No application encryption key has been specified` | Falta `php artisan key:generate` (paso 6). |
| `Vite manifest not found` / la página se ve sin estilos | Falta `npm run build` (paso 9). |
| No se ven las imágenes de productos nuevos | Falta `php artisan storage:link` (paso 8). |
| Cambié el `.env` y no pasa nada | Ejecuta `php artisan config:clear` y reinicia `php artisan serve`. |
| Error `Class ... not found` o `vendor/autoload.php` | Falta `composer install` (paso 4). |
| El puerto 8000 está ocupado | `php artisan serve --port=8080` |

---

## Estructura rápida

- `routes/web.php` — todas las rutas
- `app/Services/` — lógica de negocio (cotizador, carrito, pedidos, asistente IA)
- `app/Http/Controllers/Admin/` — panel administrativo
- `config/empresa.php` — datos de la empresa y reglas del cotizador (descuentos, flete, IVA)
- `database/seeders/` — datos de ejemplo
- `resources/views/` — vistas Blade
- `tests/` — pruebas automáticas
