<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Insert or update seed products into the database.
     */
    public function run(): void
    {
        $products = [
            // Lubricantes y Fluidos
            [
                'name'      => 'Aceite Sintetico 5W-30 (Garrafa 5L)',
                'sku'       => 'ACE-5W30-5L',
                'price'     => 750.00,
                'stock'     => 18,
                'min_stock' => 5,
            ],
            [
                'name'      => 'Aceite Semisintetico 10W-40 (Garrafa 5L)',
                'sku'       => 'ACE-10W40-5L',
                'price'     => 620.00,
                'stock'     => 14,
                'min_stock' => 4,
            ],
            [
                'name'      => 'Aceite Mineral 20W-50 (1L)',
                'sku'       => 'ACE-20W50-1L',
                'price'     => 140.00,
                'stock'     => 25,
                'min_stock' => 8,
            ],
            [
                'name'      => 'Aceite 10W-30 (1L)',
                'sku'       => 'ACE-10W30',
                'price'     => 180.00,
                'stock'     => 22,
                'min_stock' => 5,
            ],
            [
                'name'      => 'Liquido de Frenos DOT 4 (500ml)',
                'sku'       => 'LIQ-FREN-DOT4',
                'price'     => 115.00,
                'stock'     => 14,
                'min_stock' => 6,
            ],
            [
                'name'      => 'Anticongelante Refrigerante 50/50 (Galon)',
                'sku'       => 'ANT-5050-GAL',
                'price'     => 230.00,
                'stock'     => 12,
                'min_stock' => 4,
            ],
            [
                'name'      => 'Fluido de Transmision ATF Mercon V (1L)',
                'sku'       => 'ATF-MERCV-1L',
                'price'     => 195.00,
                'stock'     => 8,
                'min_stock' => 4,
            ],
            [
                'name'      => 'Liquido de Direccion Hidraulica (1L)',
                'sku'       => 'LIQ-DIR-1L',
                'price'     => 160.00,
                'stock'     => 9,
                'min_stock' => 3,
            ],

            // Filtracion
            [
                'name'      => 'Filtro de Aceite Universal Sintetico',
                'sku'       => 'FILT-OIL',
                'price'     => 95.00,
                'stock'     => 35,
                'min_stock' => 10,
            ],
            [
                'name'      => 'Filtro de Aire Motor Multimarca',
                'sku'       => 'FILT-AIR',
                'price'     => 120.00,
                'stock'     => 15,
                'min_stock' => 5,
            ],
            [
                'name'      => 'Filtro de Combustible Gasolina En Linea',
                'sku'       => 'FILT-GAS-INL',
                'price'     => 145.00,
                'stock'     => 11,
                'min_stock' => 4,
            ],
            [
                'name'      => 'Filtro de Cabina Polen Aire Acondicionado',
                'sku'       => 'FILT-CAB-AC',
                'price'     => 175.00,
                'stock'     => 3,
                'min_stock' => 5,
            ],

            // Sistema de Frenos
            [
                'name'      => 'Pastillas de Freno Delanteras Ceramicas',
                'sku'       => 'FREN-PAST-DEL',
                'price'     => 680.00,
                'stock'     => 12,
                'min_stock' => 4,
            ],
            [
                'name'      => 'Pastillas de Freno Traseras Semimetalicas',
                'sku'       => 'FREN-PAST-TRAS',
                'price'     => 520.00,
                'stock'     => 2,
                'min_stock' => 4,
            ],
            [
                'name'      => 'Discos de Freno Delanteros Ventilados (Par)',
                'sku'       => 'FREN-DISC-VENT',
                'price'     => 1250.00,
                'stock'     => 6,
                'min_stock' => 2,
            ],
            [
                'name'      => 'Limpiador de Frenos Aerosol (400ml)',
                'sku'       => 'BRAKE-CLEAN-400',
                'price'     => 90.00,
                'stock'     => 28,
                'min_stock' => 10,
            ],

            // Motor y Encendido
            [
                'name'      => 'Bujia de Iridio Laser (Juego 4 pzas)',
                'sku'       => 'BUJ-IRID-X4',
                'price'     => 580.00,
                'stock'     => 16,
                'min_stock' => 5,
            ],
            [
                'name'      => 'Bujia de Cobre Tradicional (Pza)',
                'sku'       => 'BUJ-COBRE-1P',
                'price'     => 65.00,
                'stock'     => 40,
                'min_stock' => 12,
            ],
            [
                'name'      => 'Bobina de Encendido Individual',
                'sku'       => 'BOB-ENC-IND',
                'price'     => 890.00,
                'stock'     => 5,
                'min_stock' => 2,
            ],
            [
                'name'      => 'Banda de Distribucion / Tiempo',
                'sku'       => 'BAN-DIST-TIM',
                'price'     => 420.00,
                'stock'     => 4,
                'min_stock' => 2,
            ],
            [
                'name'      => 'Banda de Serpentina Accesorios 6PK',
                'sku'       => 'BAN-SERP-6PK',
                'price'     => 280.00,
                'stock'     => 7,
                'min_stock' => 3,
            ],
            [
                'name'      => 'Bomba de Agua con Junta',
                'sku'       => 'BOMB-AGUA-GEN',
                'price'     => 790.00,
                'stock'     => 1,
                'min_stock' => 3,
            ],
            [
                'name'      => 'Termostato de Motor 82C',
                'sku'       => 'TERM-MOT-82C',
                'price'     => 210.00,
                'stock'     => 8,
                'min_stock' => 3,
            ],

            // Suspension y Direccion
            [
                'name'      => 'Amortiguador Delantero de Gas (Pza)',
                'sku'       => 'AMORT-DEL-GAS',
                'price'     => 980.00,
                'stock'     => 8,
                'min_stock' => 2,
            ],
            [
                'name'      => 'Terminal de Direccion Exterior',
                'sku'       => 'TERM-DIR-EXT',
                'price'     => 310.00,
                'stock'     => 10,
                'min_stock' => 4,
            ],
            [
                'name'      => 'Bieleta de Barra Estabilizadora',
                'sku'       => 'BIEL-BAR-ESTAB',
                'price'     => 260.00,
                'stock'     => 12,
                'min_stock' => 4,
            ],

            // Quimicos, Electrico y Consumibles
            [
                'name'      => 'Bateria Automotriz 12V 65Ah',
                'sku'       => 'BAT-12V-65AH',
                'price'     => 2150.00,
                'stock'     => 4,
                'min_stock' => 2,
            ],
            [
                'name'      => 'Limpiador Cuerpo de Aceleracion (Aerosol)',
                'sku'       => 'CARB-CLEAN-AER',
                'price'     => 98.00,
                'stock'     => 20,
                'min_stock' => 6,
            ],
            [
                'name'      => 'Silicona Formadora de Juntas RTV Negra (85g)',
                'sku'       => 'SIL-RTV-NEGRA',
                'price'     => 110.00,
                'stock'     => 15,
                'min_stock' => 5,
            ],
            [
                'name'      => 'Foco Halogeno H4 12V 60/55W',
                'sku'       => 'FOCO-HAL-H4',
                'price'     => 85.00,
                'stock'     => 0,
                'min_stock' => 4,
            ],
        ];

        foreach ($products as $item) {
            Product::updateOrCreate(
                ['sku' => $item['sku']],
                $item
            );
        }
    }
}
