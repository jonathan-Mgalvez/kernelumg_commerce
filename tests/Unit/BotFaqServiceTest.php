<?php

namespace Tests\Unit;

use App\Services\Bot\BotFaqService;
use PHPUnit\Framework\TestCase;

class BotFaqServiceTest extends TestCase
{
    private BotFaqService $faqService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faqService = new BotFaqService();
    }

    public function test_detecta_intencion_de_logistica_con_palabras_clave_variadas(): void
    {
        $inputs = [
            '¿Cuál es el tiempo de entrega en la capital?',
            'Necesito saber sobre el envio a departamentos',
            'costo del flete y tiempos'
        ];

        foreach ($inputs as $input) {
            $response = $this->faqService->processQuery($input);
            $this->assertEquals('LOGISTICA_ENTREGA', $response['intent']);
            $this->assertNotEmpty($response['answer']);
            $this->assertIsArray($response['related_options']);
        }
    }

    public function test_deriva_a_fallback_ante_consultas_fuera_de_dominio(): void
    {
        $response = $this->faqService->processQuery('quiero saber el clima de hoy');
        $this->assertEquals('FALLBACK', $response['intent']);
        $this->assertNull($response['matched_keyword']);
    }
}