<?php

namespace Tests\Unit;

use App\Models\Institution;
use App\Models\PaymentMethod;
use App\Models\QrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeInstitutionAndPm(): array
    {
        $institution = Institution::create([
            'name' => 'Test Mosque',
            'category' => 'mosque',
            'state' => 'KL',
            'city' => 'KL',
        ]);

        $pm = PaymentMethod::create([
            'code' => 'tng',
            'name' => 'Touch n Go',
            'active' => true,
        ]);

        return [$institution, $pm];
    }

    public function test_scope_active_returns_only_active_qr_codes(): void
    {
        [$institution, $pm] = $this->makeInstitutionAndPm();
        // Additional payment methods — unique constraint on (institution_id, payment_method_id)
        $pm2 = PaymentMethod::create(['code' => 'grab', 'name' => 'GrabPay', 'active' => true]);
        $pm3 = PaymentMethod::create(['code' => 'boost', 'name' => 'Boost', 'active' => true]);

        QrCode::create(['institution_id' => $institution->id, 'payment_method_id' => $pm->id, 'status' => 'active']);
        QrCode::create(['institution_id' => $institution->id, 'payment_method_id' => $pm2->id, 'status' => 'inactive']);
        QrCode::create(['institution_id' => $institution->id, 'payment_method_id' => $pm3->id, 'status' => 'pending']);

        $active = QrCode::active()->get();

        $this->assertCount(1, $active);
        $this->assertEquals('active', $active->first()->status);
    }

    public function test_qr_code_belongs_to_institution(): void
    {
        [$institution, $pm] = $this->makeInstitutionAndPm();

        $qrCode = QrCode::create([
            'institution_id' => $institution->id,
            'payment_method_id' => $pm->id,
            'status' => 'active',
        ]);

        $this->assertInstanceOf(Institution::class, $qrCode->institution);
        $this->assertEquals($institution->id, $qrCode->institution->id);
    }

    public function test_qr_code_belongs_to_payment_method(): void
    {
        [$institution, $pm] = $this->makeInstitutionAndPm();

        $qrCode = QrCode::create([
            'institution_id' => $institution->id,
            'payment_method_id' => $pm->id,
            'status' => 'active',
        ]);

        $this->assertInstanceOf(PaymentMethod::class, $qrCode->paymentMethod);
        $this->assertEquals('tng', $qrCode->paymentMethod->code);
    }

    public function test_qr_code_can_be_soft_deleted(): void
    {
        [$institution, $pm] = $this->makeInstitutionAndPm();

        $qrCode = QrCode::create([
            'institution_id' => $institution->id,
            'payment_method_id' => $pm->id,
            'status' => 'active',
        ]);

        $qrCode->delete();

        $this->assertNull(QrCode::find($qrCode->id));
        $this->assertNotNull(QrCode::withTrashed()->find($qrCode->id));
    }
}
