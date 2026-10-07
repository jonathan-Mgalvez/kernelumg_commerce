<?php

namespace App\Services\Bot;

class BotFaqService
{
    protected array $faqKnowledgeBase = [
        [
            'intent' => 'LOGISTICA_ENTREGA',
            'keywords' => ['entrega', 'tiempo', 'envio', 'flete', 'cobertura', 'departamentos', 'tardanza', 'cuanto tarda'],
            'answer' => 'Los despachos en la ciudad capital se realizan en un periodo de 24 horas hábiles. Para envíos departamentales, el tiempo de transporte estimado es de 48 a 72 horas mediante servicios de encomienda autorizados.',
            'options' => [
                ['label' => '¿Métodos de pago?', 'query' => 'metodos de pago'],
                ['label' => '¿Políticas de garantía?', 'query' => 'garantia']
            ]
        ],
        [
            'intent' => 'GARANTIA_CALIDAD',
            'keywords' => ['garantia', 'falla', 'defecto', 'soporte', 'reparacion', 'desperfecto', 'averia'],
            'answer' => 'Todos nuestros productos disponen de 12 meses de garantía directa contra cualquier defecto de fábrica. Es necesario conservar el número de pedido o la factura electrónica emitida por la plataforma.',
            'options' => [
                ['label' => '¿Cómo solicitar devolución?', 'query' => 'devolucion'],
                ['label' => 'Tiempos de entrega', 'query' => 'tiempo de entrega']
            ]
        ],
        [
            'intent' => 'METODOS_PAGO',
            'keywords' => ['pago', 'tarjeta', 'cuotas', 'transferencia', 'contraentrega', 'efectivo', 'banco', 'deposito'],
            'answer' => 'Aceptamos transferencias bancarias directas, depósitos monetarios, pago contra entrega en efectivo al recibir su paquete y cobro con tarjetas de crédito o débito.',
            'options' => [
                ['label' => '¿Tiempos de despacho?', 'query' => 'tiempo de entrega'],
                ['label' => 'Cotizar productos ahora', 'query' => 'cotizar']
            ]
        ],
        [
            'intent' => 'POLITICA_DEVOLUCION',
            'keywords' => ['devolucion', 'cambio', 'retorno', 'reclamo', 'reembolso', 'devolver'],
            'answer' => 'Dispone de un plazo máximo de 5 días hábiles a partir de la recepción de su compra para gestionar un cambio o devolución. El artículo debe conservarse en su empaque original sin alteraciones.',
            'options' => [
                ['label' => 'Términos de garantía', 'query' => 'garantia'],
                ['label' => 'Formulario de contacto', 'query' => 'contacto']
            ]
        ],
        [
            'intent' => 'DERIVACION_CONTACTO',
            'keywords' => ['asesor', 'soporte humano', 'telefono', 'oficina', 'contacto', 'humano', 'ayuda'],
            'answer' => 'Puede remitir su requerimiento específico completando nuestro Formulario de Contacto institucional o comunicarse directamente a las líneas de atención técnica detalladas en la sección Quiénes Somos.',
            'options' => [
                ['label' => 'Tiempos de entrega', 'query' => 'tiempo de entrega'],
                ['label' => 'Métodos de pago', 'query' => 'metodos de pago']
            ]
        ]
    ];

    public function processQuery(string $input): array
    {
        $normalizedInput = mb_strtolower(trim($input), 'UTF-8');

        foreach ($this->faqKnowledgeBase as $entry) {
            foreach ($entry['keywords'] as $keyword) {
                if (str_contains($normalizedInput, $keyword)) {
                    return [
                        'intent' => $entry['intent'],
                        'matched_keyword' => $keyword,
                        'answer' => $entry['answer'],
                        'related_options' => $entry['options']
                    ];
                }
            }
        }

        return [
            'intent' => 'FALLBACK',
            'matched_keyword' => null,
            'answer' => 'No encontré una respuesta directa para su consulta. Puede formular otra pregunta o remitir su solicitud a través de nuestro formulario de contacto institucional.',
            'related_options' => [
                ['label' => 'Tiempos de entrega', 'query' => 'tiempo de entrega'],
                ['label' => 'Métodos de pago', 'query' => 'metodos de pago'],
                ['label' => 'Garantía técnica', 'query' => 'garantia']
            ]
        ];
    }
}