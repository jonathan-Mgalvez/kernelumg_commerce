<?php

namespace App\Services;

use RuntimeException;

/** Error del asistente con IA; el código es el estado HTTP que se devuelve al navegador. */
class AsistenteException extends RuntimeException {}
