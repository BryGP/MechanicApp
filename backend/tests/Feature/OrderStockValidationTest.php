<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStockValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_order_when_requesting_more_stock_than_available(): void
    {
        $part = Product::create([
            'sku'        => 'TEST-BRK-LOW',
            'name'       => 'Sensor de Desgaste de Frenos',
            'price'      => 480.00,
            'stock'      => 2,
            'min_stock'  => 4,
            'is_service' => 0,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Cliente Prueba Stock',
            'vehicle'       => 'BMW 330i (Placas TEST-001)',
            'items'         => [
                [
                    'product_id' => $part->id,
                    'qty'        => 5, // Solicita 5 cuando solo hay 2
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);

        // Verificar que el stock en base de datos permanezca intacto
        $part->refresh();
        $this->assertEquals(2, $part->stock);
        $this->assertEquals(0, Order::count());
    }

    public function test_allows_order_when_stock_is_sufficient_and_decrements_properly(): void
    {
        $part = Product::create([
            'sku'        => 'TEST-BRK-OK',
            'name'       => 'Pastillas de Freno Delanteras',
            'price'      => 1200.00,
            'stock'      => 10,
            'min_stock'  => 2,
            'is_service' => 0,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Cliente Aprobado',
            'vehicle'       => 'Audi A4 2021',
            'items'         => [
                [
                    'product_id' => $part->id,
                    'qty'        => 3,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $part->refresh();
        $this->assertEquals(7, $part->stock); // 10 - 3 = 7
        $this->assertEquals(1, Order::count());
    }

    public function test_services_can_be_ordered_without_stock_limitations(): void
    {
        $service = Product::create([
            'sku'        => 'TEST-SERV-LABOR',
            'name'       => 'Servicio de Mano de Obra',
            'price'      => 650.00,
            'stock'      => 0,
            'min_stock'  => 0,
            'is_service' => 1,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Cliente Servicio',
            'vehicle'       => 'Tesla Model 3',
            'items'         => [
                [
                    'product_id' => $service->id,
                    'qty'        => 8,
                ],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertEquals(1, Order::count());
    }
}
