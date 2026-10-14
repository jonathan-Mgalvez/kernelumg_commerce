<?php

// Datos institucionales y reglas comerciales de FerroTek (empresa ficticia).
return [
    'nombre' => 'FerroTek',
    'eslogan' => 'Suministros Industriales y Gestión de Stock B2B',
    'descripcion' => 'FerroTek es una ferretería industrial guatemalteca que abastece a constructoras, talleres y contratistas con acero estructural, fijaciones, herramientas y consumibles de soldadura, con certificados de calidad y entrega a pie de obra.',

    'mision' => 'Abastecer a la industria de la construcción y la manufactura en Guatemala con materiales certificados, precios justos y cotizaciones inmediatas, para que cada obra avance sin retrasos.',
    'vision' => 'Ser en 2030 la ferretería industrial de referencia en Centroamérica por su inventario confiable, su servicio técnico y su plataforma digital de cotización.',
    'valores' => [
        ['icon' => 'verified', 'titulo' => 'Calidad certificada', 'texto' => 'Trabajamos solo con materiales que cumplen normas ASTM, AWS, ANSI y DIN.'],
        ['icon' => 'handshake', 'titulo' => 'Compromiso', 'texto' => 'Cumplimos fechas de entrega pactadas con cada cliente.'],
        ['icon' => 'visibility', 'titulo' => 'Transparencia', 'texto' => 'Stock real y precios claros en cada cotización.'],
        ['icon' => 'engineering', 'titulo' => 'Asesoría técnica', 'texto' => 'Personal capacitado para recomendar el material correcto.'],
    ],
    'servicios' => [
        ['icon' => 'request_quote', 'titulo' => 'Cotizaciones en línea', 'texto' => 'Arma tu cotización desde el catálogo y descárgala en PDF al instante.'],
        ['icon' => 'local_shipping', 'titulo' => 'Entrega a pie de obra', 'texto' => 'Flete bonificado en pedidos mayores a Q5,000 dentro del área metropolitana.'],
        ['icon' => 'content_cut', 'titulo' => 'Corte y doblez de varilla', 'texto' => 'Habilitado de acero según planos estructurales.'],
        ['icon' => 'inventory_2', 'titulo' => 'Venta por mayor', 'texto' => 'Descuentos por volumen para constructoras y contratistas.'],
    ],

    'contacto' => [
        'direccion' => 'Calzada Atanasio Tzul 22-00, Zona 12, Ciudad de Guatemala',
        'telefono' => '+502 2440-0000',
        'whatsapp' => '50255550000',
        'correo' => 'ventas@ferrotek.test',
        'horario' => 'Lunes a viernes 7:00–17:00 · Sábado 7:00–12:00',
    ],

    // Reglas del cotizador
    'iva' => 0.12,
    'envio_costo' => 350.00,
    'envio_gratis_desde' => 5000.00,
    'vigencia_dias' => 15,
    // Descuento por volumen por línea: cantidad mínima => % de descuento (de mayor a menor)
    'descuentos_volumen' => [
        500 => 8.5,
        100 => 5.0,
        50 => 2.5,
    ],
];
