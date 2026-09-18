<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Seed realistic vehicle breakdown, diagnostic and agency repair orders.
     */
    public function run(): void
    {
        // Clean existing orders to prevent duplicates when reseeding
        OrderItem::query()->delete();
        Order::query()->delete();

        $cases = [
            [
                'customer_name' => 'Carlos Mendoza',
                'vehicle'       => 'VW Jetta 2019 (GTO-842-B) - Diagnostico sobrecalentamiento motor',
                'status'        => 'in_progress',
                'created_at'    => now()->subHours(5),
                'items'         => [
                    ['sku' => 'BOMB-AGUA-GEN', 'qty' => 1],
                    ['sku' => 'TERM-MOT-82C',  'qty' => 1],
                    ['sku' => 'ANT-5050-GAL',  'qty' => 2],
                    ['sku' => 'SIL-RTV-NEGRA', 'qty' => 1],
                ],
            ],
            [
                'customer_name' => 'Mariana Lopez Salgado',
                'vehicle'       => 'Mazda 3 2021 (PXR-519-C) - Chirrido severo en frenos y pedal esponjoso',
                'status'        => 'open',
                'created_at'    => now()->subHours(2),
                'items'         => [
                    ['sku' => 'FREN-PAST-DEL',  'qty' => 1],
                    ['sku' => 'FREN-DISC-VENT', 'qty' => 1],
                    ['sku' => 'LIQ-FREN-DOT4',  'qty' => 2],
                    ['sku' => 'BRAKE-CLEAN-400','qty' => 2],
                ],
            ],
            [
                'customer_name' => 'Roberto Gomez Trevino',
                'vehicle'       => 'Ford Ranger 2017 (NL-3021-F) - Falla de arranque / No da marcha',
                'status'        => 'done',
                'created_at'    => now()->subDays(1),
                'items'         => [
                    ['sku' => 'BAT-12V-65AH', 'qty' => 1],
                    ['sku' => 'BUJ-COBRE-1P', 'qty' => 4],
                ],
            ],
            [
                'customer_name' => 'Andrea Navarro Vega',
                'vehicle'       => 'Chevrolet Aveo 2018 (JAL-993-A) - Perdida de potencia y Check Engine parpadeando',
                'status'        => 'in_progress',
                'created_at'    => now()->subHours(8),
                'items'         => [
                    ['sku' => 'BOB-ENC-IND',   'qty' => 2],
                    ['sku' => 'BUJ-IRID-X4',   'qty' => 1],
                    ['sku' => 'FILT-GAS-INL',  'qty' => 1],
                    ['sku' => 'CARB-CLEAN-AER','qty' => 1],
                ],
            ],
            [
                'customer_name' => 'Javier Estrada Rios',
                'vehicle'       => 'Nissan Versa 2020 (EDOMEX-771-V) - Diagnostico de golpe en suspension y direccion',
                'status'        => 'delivered',
                'created_at'    => now()->subDays(3),
                'items'         => [
                    ['sku' => 'AMORT-DEL-GAS', 'qty' => 2],
                    ['sku' => 'TERM-DIR-EXT',  'qty' => 2],
                    ['sku' => 'BIEL-BAR-ESTAB','qty' => 2],
                    ['sku' => 'LIQ-DIR-1L',    'qty' => 1],
                ],
            ],
            [
                'customer_name' => 'Sofia Morales Quintanilla',
                'vehicle'       => 'Honda Civic 2016 (CDMX-542-K) - Mantenimiento mayor de agencia y cambio de bandas',
                'status'        => 'delivered',
                'created_at'    => now()->subDays(2),
                'items'         => [
                    ['sku' => 'ACE-5W30-5L',  'qty' => 1],
                    ['sku' => 'FILT-OIL',     'qty' => 1],
                    ['sku' => 'FILT-AIR',     'qty' => 1],
                    ['sku' => 'BAN-DIST-TIM', 'qty' => 1],
                    ['sku' => 'BAN-SERP-6PK', 'qty' => 1],
                ],
            ],
            [
                'customer_name' => 'Daniel Orozco Luna',
                'vehicle'       => 'Toyota RAV4 2019 (QRO-810-D) - Fuga de aceite de transmision y jaloneo en cambios',
                'status'        => 'open',
                'created_at'    => now()->subMinutes(45),
                'items'         => [
                    ['sku' => 'ATF-MERCV-1L',   'qty' => 4],
                    ['sku' => 'SIL-RTV-NEGRA',  'qty' => 1],
                    ['sku' => 'BRAKE-CLEAN-400','qty' => 1],
                ],
            ],
        ];

        foreach ($cases as $case) {
            $total = 0;
            $itemsData = [];

            foreach ($case['items'] as $item) {
                $product = Product::where('sku', $item['sku'])->first();
                if ($product) {
                    $unitPrice = (float) $product->price;
                    $subtotal = $unitPrice * $item['qty'];
                    $total += $subtotal;

                    $itemsData[] = [
                        'product_id' => $product->id,
                        'quantity'   => $item['qty'],
                        'unit_price' => $unitPrice,
                        'subtotal'   => $subtotal,
                        'created_at' => $case['created_at'],
                        'updated_at' => $case['created_at'],
                    ];
                }
            }

            $order = Order::create([
                'customer_name' => $case['customer_name'],
                'vehicle'       => $case['vehicle'],
                'status'        => $case['status'],
                'total'         => $total,
                'created_at'    => $case['created_at'],
                'updated_at'    => $case['created_at'],
            ]);

            foreach ($itemsData as $itemRow) {
                $itemRow['order_id'] = $order->id;
                OrderItem::create($itemRow);
            }
        }
    }
}
