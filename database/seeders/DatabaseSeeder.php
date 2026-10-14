<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\User;
use App\Services\CotizacionService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuarios
        $admin = User::factory()->create([
            'name' => 'Administrador FerroTek',
            'email' => 'admin@ferrotek.test',
            'password' => 'password',
        ]);
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        $cliente = User::factory()->create([
            'name' => 'Marcos Ramírez',
            'email' => 'cliente@ferrotek.test',
            'password' => 'password',
            'company' => 'Constructora del Norte',
            'phone' => '5555-1234',
        ]);

        // Categorías
        $cats = collect([
            ['name' => 'Acero y Estructuras', 'slug' => 'acero-estructuras', 'icon' => 'foundation', 'description' => 'Varilla corrugada, perfiles, vigas y lámina.'],
            ['name' => 'Fijaciones', 'slug' => 'fijaciones', 'icon' => 'hardware', 'description' => 'Tornillería, anclajes, tuercas y arandelas.'],
            ['name' => 'Herramientas', 'slug' => 'herramientas', 'icon' => 'construction', 'description' => 'Herramienta manual y eléctrica profesional.'],
            ['name' => 'Soldadura y Corte', 'slug' => 'soldadura-corte', 'icon' => 'local_fire_department', 'description' => 'Electrodos, discos abrasivos y equipo de soldadura.'],
        ])->mapWithKeys(fn ($c) => [$c['slug'] => Category::create($c)]);

        // Productos (los 6 primeros son los de la maqueta de Stitch)
        $productos = [
            ['acero-estructuras', 'FT-ST-4410', 'Barras de Acero Corrugado 1/2" x 12m', 'Grado 60 · Siderúrgica Certificada', 'ASTM A615', 'Acero grado 60 norma ASTM A615 para refuerzo estructural y losas de concreto. Certificación de colada incluida.', 115.85, 'unidad', 1840, 200, 'Bahía C-14', '/img/productos/varilla-corrugada.jpg', true],
            ['soldadura-corte', 'FT-WD-1022', 'Electrodo para Soldadura E6011 (Caja 20kg)', '1/8" (3.2mm) · Celulósico de Alta Penetración', 'AWS A5.1', 'Revestimiento celulósico de penetración profunda para soldadura en toda posición sobre aceros al carbono.', 500.75, 'caja', 85, 20, 'Almacén Soldadura', '/img/productos/electrodo-e6011.jpg', true],
            ['herramientas', 'FT-TL-8802', 'Martillo de Acero Antivibración 20oz', 'Forjado en Una Pieza · Empuñadura TPR', 'ANSI B173.1', 'Cuerpo integral de acero al carbono tratado térmicamente con cámara de absorción sónica que reduce la fatiga articular.', 222.30, 'unidad', 142, 30, 'Pasillo H-02', '/img/productos/martillo-20oz.jpg', true],
            ['fijaciones', 'FT-FX-0914', 'Tornillo Hexagonal Galvanizado G5 1/2" x 3"', 'Paso Grueso 13 Hilos · Galvanizado en Caliente', 'SAE J429 G5', 'Fijación estructural para uniones mecánicas pesadas y montajes de perfilería. Resistencia a tracción nominal de 120,000 PSI.', 3.75, 'unidad', 14500, 2000, 'Gaveta F-11', '/img/productos/tornillo-hex-g5.jpg', true],
            ['soldadura-corte', 'FT-AB-3341', 'Disco de Corte Abrasivo Metal 7" x 1.6mm', 'Eje 7/8" · Óxido de Aluminio Reforzado', 'EN 12413', 'Malla doble de fibra de vidrio para máxima seguridad en amoladoras angulares de alta velocidad. Corte limpio y sin rebaba.', 16.75, 'unidad', 3200, 500, 'Almacén Soldadura', '/img/productos/disco-corte-7.jpg', true],
            ['herramientas', 'FT-PW-7019', 'Taladro Percutor Industrial 850W Mandril 1/2"', 'Caja de Engranajes Metálica · 0-3000 RPM', '220V - 50/60Hz', 'Motor de alto torque con protección contra sobrecargas para perforación continua en mampostería, vigas de acero y madera.', 748.80, 'unidad', 58, 10, 'Pasillo H-05', '/img/productos/taladro-percutor.jpg', true],
            ['acero-estructuras', 'FT-ST-4420', 'Barras de Acero Corrugado 3/8" x 6m', 'Grado 40 · Uso Residencial', 'ASTM A615', 'Varilla para columnas, soleras y refuerzo de muros en construcción residencial.', 42.50, 'unidad', 2600, 300, 'Bahía C-15', null, false],
            ['acero-estructuras', 'FT-ST-5101', 'Viga IPR W8x15 Acero A36 (6m)', 'Perfil Laminado en Caliente', 'ASTM A36', 'Perfil estructural para entrepisos, marcos rígidos y naves industriales.', 2850.00, 'unidad', 24, 8, 'Patio P-01', null, false],
            ['acero-estructuras', 'FT-ST-6003', 'Lámina Galvanizada Calibre 26 (12 pies)', 'Acanalada · Recubrimiento Z180', 'ASTM A653', 'Lámina para techos y cerramientos con alta resistencia a la corrosión.', 118.00, 'unidad', 6, 40, 'Patio P-03', null, false],
            ['fijaciones', 'FT-FX-0220', 'Ancla Expansiva de Acero 3/8" x 3"', 'Zincado · Para Concreto', 'ICC-ES', 'Anclaje mecánico para fijar estructuras y equipos sobre concreto sólido.', 6.90, 'unidad', 4200, 800, 'Gaveta F-04', null, false],
            ['fijaciones', 'FT-FX-0331', 'Tuerca Hexagonal G5 1/2" (Caja 100u)', 'Galvanizada · Rosca UNC', 'SAE J995', 'Tuerca de alta resistencia compatible con tornillería grado 5.', 145.00, 'caja', 0, 15, 'Gaveta F-12', null, false],
            ['herramientas', 'FT-TL-8120', 'Amoladora Angular 7" 2200W', 'Arranque Suave · Guarda Ajustable', '220V - 60Hz', 'Amoladora de servicio pesado para corte y desbaste de metal y concreto.', 985.00, 'unidad', 12, 10, 'Pasillo H-05', null, false],
        ];

        foreach ($productos as [$cat, $sku, $name, $sub, $std, $desc, $price, $unit, $stock, $min, $loc, $img, $feat]) {
            Product::create([
                'category_id' => $cats[$cat]->id,
                'sku' => $sku, 'name' => $name, 'subtitle' => $sub, 'standard' => $std,
                'description' => $desc, 'price' => $price, 'unit' => $unit,
                'stock' => $stock, 'min_stock' => $min, 'location' => $loc,
                'image' => $img, 'featured' => $feat,
            ]);
        }

        // Una cotización de ejemplo enviada por el cliente (como la de la maqueta)
        $svc = app(CotizacionService::class);
        $quote = $svc->borradorActivo($cliente);
        $svc->agregarProducto($quote, Product::where('sku', 'FT-ST-4410')->first(), 500);
        $svc->agregarProducto($quote, Product::where('sku', 'FT-WD-1022')->first(), 30);
        $svc->agregarProducto($quote, Product::where('sku', 'FT-AB-3341')->first(), 50);
        $svc->agregarProducto($quote, Product::where('sku', 'FT-TL-8802')->first(), 4);
        $svc->enviar($quote, 'Obra Torre Norte, Av. Industrial 402', 'Entregar jueves antes de las 14:00 hrs.');

        ContactMessage::create([
            'name' => 'Lucía Pérez',
            'email' => 'lucia@example.com',
            'phone' => '4444-0000',
            'subject' => 'Consulta de disponibilidad',
            'message' => '¿Tienen varilla de 5/8" en existencia para entrega la próxima semana?',
        ]);

        // Datos de prueba adicionales (más productos, clientes, cotizaciones y mensajes)
        $this->call(DemoSeeder::class);

        // Inventario, ofertas y pedidos de ejemplo (comercio electrónico)
        $this->call(ComercioSeeder::class);
    }
}
