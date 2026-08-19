<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        Currency::updateOrCreate(
            ['code' => 'USD'],
            ['symbol' => '$', 'decimals' => 2, 'exchange_rate' => 83, 'is_default' => false, 'enabled' => true]
        );

        Currency::updateOrCreate(
            ['code' => 'INR'],
            ['symbol' => '₹', 'decimals' => 2, 'exchange_rate' => 1, 'is_default' => true, 'enabled' => true]
        );

        Currency::whereNotIn('code', ['INR', 'USD'])->update([
            'is_default' => false,
            'enabled' => false,
        ]);
    }
}
