<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bot\QuotationCalculateRequest;
use App\Services\Bot\BotFaqService;
use App\Services\Bot\QuotationCalculatorService;
use App\Services\Bot\QuotationToCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BotApiController extends Controller
{
    protected BotFaqService $faqService;
    protected QuotationCalculatorService $calculatorService;
    protected QuotationToCartService $toCartService;

    public function __construct(
        BotFaqService $faqService,
        QuotationCalculatorService $calculatorService,
        QuotationToCartService $toCartService
    ) {
        $this->faqService = $faqService;
        $this->calculatorService = $calculatorService;
        $this->toCartService = $toCartService;
    }

    public function faq(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'max:150']
        ]);

        $result = $this->faqService->processQuery($request->input('query'));

        return response()->json([
            'status' => 'success',
            'data' => $result
        ], 200);
    }

    public function calculate(QuotationCalculateRequest $request): JsonResponse
    {
        $userId = Auth::guard('sanctum')->check() 
            ? Auth::guard('sanctum')->id() 
            : (Auth::check() ? Auth::id() : null);

        $result = $this->calculatorService->calculateAndPersist(
            $request->validated('items'),
            $request->validated('session_token'),
            $userId
        );

        return response()->json([
            'status' => 'success',
            'data' => $result
        ], 201);
    }

    public function convertToCart(Request $request): JsonResponse
    {
        $request->validate([
            'quotation_id' => ['required', 'integer', 'exists:quotations,id'],
            'session_token' => ['required', 'string', 'max:100']
        ]);

        $userId = Auth::guard('sanctum')->check() 
            ? Auth::guard('sanctum')->id() 
            : (Auth::check() ? Auth::id() : null);

        $result = $this->toCartService->convert(
            $request->input('quotation_id'),
            $request->input('session_token'),
            $userId
        );

        $hasWarnings = !empty($result['warnings']);

        return response()->json([
            'status' => $hasWarnings ? 'partial_success' : 'success',
            'message' => $hasWarnings 
                ? 'Artículos agregados con ajustes de inventario.' 
                : 'La cotización fue trasladada íntegramente al carrito.',
            'data' => $result
        ], 200);
    }
}