<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PaymentMethodController extends Controller
{
    // GET /api/v1/payment-methods
    public function index(): JsonResponse
    {
        $methods = Cache::remember('payment_methods_active', 3600, fn () =>
            PaymentMethod::active()->orderBy('name')->get()
        );

        return response()->json($methods);
    }
}
