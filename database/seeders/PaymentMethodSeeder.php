<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['code' => 'duitnow',  'name' => 'DuitNow',            'active' => true],
            ['code' => 'tng',      'name' => 'Touch \'n Go eWallet', 'active' => true],
            ['code' => 'grab',     'name' => 'GrabPay',            'active' => true],
            ['code' => 'boost',    'name' => 'Boost',              'active' => true],
            ['code' => 'shopeepay','name' => 'ShopeePay',          'active' => true],
            ['code' => 'online',   'name' => 'Online Banking',     'active' => true],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(['code' => $method['code']], $method);
        }
    }
}
