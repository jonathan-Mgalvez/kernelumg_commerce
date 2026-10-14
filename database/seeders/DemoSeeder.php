<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\User;
use App\Services\CotizacionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Datos de prueba adicionales para demostrar el sitio: más productos y categorías,
 * clientes, cotizaciones en todos los estados (repartidas en varios meses) y mensajes.
 *
 *   php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $cats = Category::pluck('id', 'slug');

        $cats['plomeria'] = Category::firstOrCreate(['slug' => 'plomeria'], [
            'name' => 'Plomería', 'icon' => 'plumbing', 'description' => 'Tubería PVC, accesorios y llaves de paso.',
        ])->id;
        $cats['electricidad'] = Category::firstOrCreate(['slug' => 'electricidad'], [
            'name' => 'Electricidad', 'icon' => 'electrical_services', 'description' => 'Cable, tableros, breakers y accesorios eléctricos.',
        ])->id;
        $cats['seguridad'] = Category::firstOrCreate(['slug' => 'seguridad-industrial'], [
            'name' => 'Seguridad Industrial', 'icon' => 'health_and_safety', 'description' => 'Equipo de protección personal certificado.',
        ])->id;

        // [categoría, sku, nombre, subtítulo, norma, precio, unidad, stock, mínimo, ubicación]
        $productos = [
            ['acero-estructuras', 'FT-ST-4430', 'Barras de Acero Corrugado 5/8" x 12m', 'Grado 60 · Alta Resistencia', 'ASTM A615', 182.40, 'unidad', 960, 150, 'Bahía C-16'],
            ['acero-estructuras', 'FT-ST-4440', 'Barras de Acero Corrugado 1/4" x 6m', 'Grado 40 · Estribos', 'ASTM A615', 19.75, 'unidad', 5400, 800, 'Bahía C-12'],
            ['acero-estructuras', 'FT-ST-5210', 'Tubo Estructural Cuadrado 2" x 2" Chapa 14', 'Acero Negro · 6m', 'ASTM A500', 265.00, 'unidad', 74, 20, 'Patio P-02'],
            ['acero-estructuras', 'FT-ST-5320', 'Angular de Acero 2" x 3/16" (6m)', 'Laminado en Caliente', 'ASTM A36', 198.50, 'unidad', 15, 25, 'Patio P-02'],
            ['acero-estructuras', 'FT-ST-6120', 'Malla Electrosoldada 6x6 10/10 (rollo)', 'Para Losas y Pisos', 'ASTM A185', 1450.00, 'unidad', 32, 10, 'Patio P-04'],
            ['acero-estructuras', 'FT-ST-6200', 'Alambre de Amarre Calibre 16 (rollo 25 lb)', 'Recocido Negro', 'ASTM A853', 165.00, 'unidad', 210, 50, 'Bahía C-10'],
            ['fijaciones', 'FT-FX-0410', 'Clavo de Acero para Concreto 2.5" (Caja 1 lb)', 'Templado · Punta Diamante', 'ASTM F1667', 32.00, 'caja', 380, 60, 'Gaveta F-02'],
            ['fijaciones', 'FT-FX-0520', 'Tornillo Autorroscante 1/4" x 1" (Caja 100u)', 'Punta Broca · Cabeza Hexagonal', 'SAE J78', 89.00, 'caja', 140, 30, 'Gaveta F-07'],
            ['fijaciones', 'FT-FX-0610', 'Varilla Roscada Galvanizada 1/2" x 1m', 'Rosca UNC Completa', 'ASTM A307', 38.50, 'unidad', 9, 40, 'Gaveta F-09'],
            ['herramientas', 'FT-TL-8210', 'Nivel de Aluminio 24" con 3 Burbujas', 'Perfil Reforzado', 'ANSI B89', 145.00, 'unidad', 48, 10, 'Pasillo H-01'],
            ['herramientas', 'FT-TL-8330', 'Cinta Métrica 8m Carcasa Antigolpes', 'Hoja Recubierta de Nylon', 'EC Clase II', 79.90, 'unidad', 120, 25, 'Pasillo H-01'],
            ['herramientas', 'FT-TL-8450', 'Juego de Llaves Combinadas 12 Piezas', 'Cromo Vanadio · 8-19 mm', 'DIN 3113', 389.00, 'unidad', 26, 8, 'Pasillo H-03'],
            ['herramientas', 'FT-PW-7120', 'Sierra Circular 7-1/4" 1800W', 'Guía Láser · Base de Magnesio', '120V - 60Hz', 1290.00, 'unidad', 7, 5, 'Pasillo H-05'],
            ['soldadura-corte', 'FT-WD-1050', 'Electrodo E7018 1/8" (Caja 20kg)', 'Bajo Hidrógeno · Estructural', 'AWS A5.1', 640.00, 'caja', 42, 15, 'Almacén Soldadura'],
            ['soldadura-corte', 'FT-WD-2010', 'Soldadora Inversora 200A', 'Electrodo y TIG Lift · Bivoltaje', 'IEC 60974-1', 2650.00, 'unidad', 11, 4, 'Almacén Soldadura'],
            ['soldadura-corte', 'FT-AB-3360', 'Disco Flap de Desbaste 4-1/2" Grano 60', 'Óxido de Zirconio', 'EN 13743', 18.25, 'unidad', 0, 200, 'Almacén Soldadura'],
            ['plomeria', 'FT-PL-1001', 'Tubo PVC 1/2" x 6m 315 PSI', 'Agua Potable Fría', 'ASTM D2241', 36.00, 'unidad', 600, 120, 'Bodega B-01'],
            ['plomeria', 'FT-PL-1004', 'Tubo PVC 4" x 6m Drenaje', 'Pared Delgada 100 PSI', 'ASTM D3034', 189.00, 'unidad', 95, 30, 'Bodega B-02'],
            ['plomeria', 'FT-PL-2010', 'Llave de Paso de Bronce 1/2"', 'Roscada · Palanca', 'NSF 61', 68.00, 'unidad', 85, 20, 'Bodega B-05'],
            ['plomeria', 'FT-PL-3001', 'Pegamento PVC 1/4 de Galón', 'Secado Rápido', 'ASTM D2564', 92.00, 'unidad', 14, 24, 'Bodega B-05'],
            ['electricidad', 'FT-EL-1012', 'Cable THHN Calibre 12 (rollo 100m)', 'Cobre · 600V', 'UL 83', 595.00, 'unidad', 60, 15, 'Bodega E-01'],
            ['electricidad', 'FT-EL-2020', 'Breaker Termomagnético 1 Polo 20A', 'Enchufable', 'UL 489', 58.00, 'unidad', 230, 50, 'Bodega E-03'],
            ['electricidad', 'FT-EL-3008', 'Centro de Carga 8 Circuitos', 'Monofásico · 125A', 'UL 67', 475.00, 'unidad', 18, 6, 'Bodega E-03'],
            ['seguridad', 'FT-SG-0101', 'Casco de Seguridad Tipo I Clase E', 'Suspensión de Matraca', 'ANSI Z89.1', 75.00, 'unidad', 150, 40, 'Bodega S-01'],
            ['seguridad', 'FT-SG-0210', 'Guantes de Cuero para Soldador (par)', 'Forro Térmico · 14"', 'EN 12477', 69.00, 'unidad', 90, 30, 'Bodega S-01'],
            ['seguridad', 'FT-SG-0330', 'Lentes de Seguridad Antiempañantes', 'Policarbonato Claro', 'ANSI Z87.1', 28.00, 'unidad', 310, 60, 'Bodega S-02'],
            ['seguridad', 'FT-SG-0450', 'Arnés de Cuerpo Completo con Línea de Vida', '1 Anillo Dorsal · 140 kg', 'ANSI Z359.11', 845.00, 'unidad', 4, 6, 'Bodega S-02'],
        ];

        foreach ($productos as [$cat, $sku, $nombre, $sub, $norma, $precio, $unidad, $stock, $min, $ubic]) {
            Product::updateOrCreate(['sku' => $sku], [
                'category_id' => $cats[$cat],
                'name' => $nombre,
                'subtitle' => $sub,
                'standard' => $norma,
                'description' => "{$nombre}. {$sub}. Cumple la norma {$norma}.",
                'price' => $precio,
                'unit' => $unidad,
                'stock' => $stock,
                'min_stock' => $min,
                'location' => $ubic,
            ]);
        }

        // Clientes de prueba (contraseña: password)
        $clientes = collect([
            ['Ana Lucía Morales', 'ana.morales@constructoraandina.test', 'Constructora Andina', '5512-3344'],
            ['Carlos Pérez', 'cperez@taller-perez.test', 'Taller Industrial Pérez', '4433-2211'],
            ['María José Castillo', 'mjcastillo@edificaciones.test', 'Edificaciones del Sur', '3021-8890'],
            ['Jorge Hernández', 'jorge.h@metalurgica.test', 'Metalúrgica Hernández', '5788-1020'],
            ['Sofía Ramírez', 'sofia.ramirez@gmail.test', null, '4100-2525'],
            ['Luis Fernando Gómez', 'lfgomez@ingenieria-lf.test', 'Ingeniería LF', '5566-7788'],
            ['Daniela Ruiz', 'druiz@remodelaciones.test', 'Remodelaciones Ruiz', '3344-5566'],
        ])->map(fn ($c) => User::firstOrCreate(['email' => $c[1]], [
            'name' => $c[0], 'company' => $c[2], 'phone' => $c[3], 'password' => 'password',
        ]))->push(User::where('email', 'cliente@ferrotek.test')->first())->filter()->values();

        // Cotizaciones: [días atrás, estado, [[sku, cantidad], ...]]
        $cotizaciones = [
            [2, 'enviada', [['FT-ST-4430', 300], ['FT-ST-6200', 20], ['FT-SG-0101', 12]]],
            [4, 'enviada', [['FT-PL-1001', 80], ['FT-PL-2010', 10], ['FT-PL-3001', 4]]],
            [6, 'aprobada', [['FT-ST-4410', 600], ['FT-ST-4440', 400], ['FT-ST-6120', 6]]],
            [9, 'aprobada', [['FT-EL-1012', 8], ['FT-EL-2020', 24], ['FT-EL-3008', 3]]],
            [12, 'rechazada', [['FT-PW-7120', 2], ['FT-TL-8450', 3]]],
            [15, 'enviada', [['FT-WD-1050', 6], ['FT-WD-2010', 1], ['FT-SG-0210', 6]]],
            [20, 'aprobada', [['FT-FX-0914', 1200], ['FT-FX-0331', 0], ['FT-FX-0220', 300]]],
            [26, 'aprobada', [['FT-TL-8802', 15], ['FT-TL-8330', 20], ['FT-TL-8210', 8]]],
            [33, 'vencida', [['FT-ST-5101', 6], ['FT-ST-5210', 20]]],
            [41, 'aprobada', [['FT-PL-1004', 40], ['FT-PL-1001', 120]]],
            [47, 'rechazada', [['FT-SG-0450', 4], ['FT-SG-0330', 50]]],
            [58, 'vencida', [['FT-AB-3341', 200], ['FT-WD-1022', 10]]],
            [66, 'aprobada', [['FT-ST-4410', 250], ['FT-ST-6003', 60]]],
            [80, 'aprobada', [['FT-EL-1012', 15], ['FT-EL-2020', 60]]],
            [95, 'vencida', [['FT-PW-7019', 3], ['FT-TL-8120', 2]]],
        ];

        $svc = app(CotizacionService::class);
        $direcciones = ['Obra Residencial Las Palmas, Zona 16', 'Taller central, Villa Nueva', 'Proyecto Torre Reforma, Zona 10',
            'Bodega Mixco, Km 15 Carretera Interamericana', 'Colonia El Maestro, Zona 12'];

        foreach ($cotizaciones as $i => [$dias, $estado, $lineas]) {
            $cliente = $clientes[$i % $clientes->count()];
            $quote = $svc->borradorActivo($cliente);

            foreach ($lineas as [$sku, $cantidad]) {
                $producto = Product::where('sku', $sku)->first();
                if ($producto && $cantidad > 0) {
                    $svc->agregarProducto($quote, $producto, $cantidad);
                }
            }

            $svc->enviar($quote, $direcciones[$i % count($direcciones)], $i % 3 === 0 ? 'Coordinar entrega con el maestro de obra.' : null);

            $fecha = Carbon::now()->subDays($dias)->setTime(8 + $i % 9, ($i * 7) % 60);
            $quote->forceFill([
                'status' => $estado,
                'created_at' => $fecha,
                'updated_at' => $fecha,
                'valid_until' => $fecha->copy()->addDays(config('empresa.vigencia_dias')),
            ])->save();
        }

        // Un borrador en curso para un cliente (para ver el cotizador con productos)
        $borrador = $svc->borradorActivo($clientes[0]);
        $svc->agregarProducto($borrador, Product::where('sku', 'FT-ST-4430')->first(), 120);
        $svc->agregarProducto($borrador, Product::where('sku', 'FT-SG-0101')->first(), 6);

        // Mensajes de contacto
        $mensajes = [
            ['Roberto Juárez', 'rjuarez@example.test', '5599-0011', 'Cotización de vigas IPR', 'Buen día, necesito precio de 20 vigas W8x15 de 6m con entrega en Escuintla. ¿Manejan flete al interior?', false],
            ['Patricia Lemus', 'plemus@example.test', null, 'Horario sábado', '¿Hasta qué hora atienden los sábados? Quiero recoger material personalmente.', false],
            ['Ing. Mario Soto', 'msoto@example.test', '4455-6677', 'Certificados de colada', 'Para nuestra licitación necesitamos los certificados de colada de la varilla grado 60. ¿Pueden enviarlos por correo?', false],
            ['Andrea Velásquez', 'avelasquez@example.test', '3322-1100', 'Crédito para empresas', '¿Ofrecen crédito a 30 días para constructoras? Somos clientes frecuentes.', true],
            ['Kevin López', 'klopez@example.test', null, 'Garantía taladro', 'Compré un taladro percutor hace 2 meses y el mandril se traba. ¿Cómo hago válida la garantía?', true],
            ['Gabriela Ortiz', 'gortiz@example.test', '5011-2233', 'Corte y doblez de varilla', 'Tengo planos estructurales para una casa de 2 niveles, ¿me pueden cotizar el habilitado de acero?', false],
        ];
        foreach ($mensajes as $i => [$nombre, $correo, $tel, $asunto, $texto, $leido]) {
            $fecha = Carbon::now()->subDays($i * 3 + 1)->setTime(9 + $i, 15);
            ContactMessage::create([
                'name' => $nombre, 'email' => $correo, 'phone' => $tel,
                'subject' => $asunto, 'message' => $texto, 'read' => $leido,
            ])->forceFill(['created_at' => $fecha, 'updated_at' => $fecha])->save();
        }
    }
}
