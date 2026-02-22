<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethodRequest;
use App\Models\PaymentMethod;
use Inertia\Inertia;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('name')
            ->get()
            ->map(fn (PaymentMethod $pm) => [
                'id' => $pm->id,
                'code' => $pm->code,
                'name' => $pm->name,
                'active' => $pm->active,
                'qr_codes_count' => $pm->qrCodes()->count(),
            ]);

        return Inertia::render('Admin/PaymentMethods/Index', [
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(PaymentMethodRequest $request)
    {
        PaymentMethod::create($request->validated());

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Kaedah bayaran ditambahkan dengan jayanya.');
    }

    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validated();

        // Remove 'code' if present (immutable field)
        unset($validated['code']);

        $paymentMethod->update($validated);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Kaedah bayaran dikemaskini dengan jayanya.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->isInUse()) {
            return redirect()->back()
                ->with('error', 'Tidak boleh padam kaedah bayaran yang masih digunakan.');
        }

        $paymentMethod->delete();

        return redirect()->back()
            ->with('success', 'Kaedah bayaran telah dipadamkan.');
    }
}
