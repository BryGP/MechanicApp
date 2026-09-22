<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Services\AdminPinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private ?string $previousPin = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousPin = AdminPinService::getPin();
        AdminPinService::setPin('9841');
    }

    protected function tearDown(): void
    {
        if ($this->previousPin !== null) {
            AdminPinService::setPin($this->previousPin);
        }
        parent::tearDown();
    }

    public function test_can_fetch_sat_catalogs(): void
    {
        $response = $this->getJson('/api/invoices/catalogs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'regimenes_fiscales',
                    'usos_cfdi',
                    'formas_pago',
                    'metodos_pago',
                    'emisor',
                ],
            ]);
    }

    public function test_rejects_invalid_rfc_format(): void
    {
        $response = $this->postJson('/api/invoices', [
            'rfc_receptor'            => 'INVALIDO123',
            'razon_social_receptor'   => 'CLIENTE DE PRUEBA',
            'regimen_fiscal_receptor' => '612',
            'codigo_postal_receptor'  => '06000',
            'uso_cfdi'                => 'G03',
            'forma_pago'              => '03',
            'metodo_pago'             => 'PUE',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rfc_receptor']);
    }

    public function test_can_issue_cfdi_invoice_from_order(): void
    {
        $product = Product::create([
            'sku'         => 'TEST-BRK-01',
            'name'        => 'Balatas Delanteras Cerámicas',
            'description' => 'Juego de pastillas de freno',
            'price'       => 1000.00,
            'cost'        => 500.00,
            'stock'       => 10,
        ]);

        $order = Order::create([
            'customer_name' => 'JUAN PEREZ GONZALEZ',
            'vehicle'       => 'Nissan Versa 2021',
            'status'        => 'done',
            'total'         => 1160.00,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity'   => 1,
            'unit_price' => 1160.00,
            'subtotal'   => 1160.00,
        ]);

        $payload = [
            'order_id'                => $order->id,
            'rfc_receptor'            => 'PEGJ850101XY1',
            'razon_social_receptor'   => 'JUAN PEREZ GONZALEZ',
            'regimen_fiscal_receptor' => '612',
            'codigo_postal_receptor'  => '06000',
            'uso_cfdi'                => 'G03',
            'forma_pago'              => '04',
            'metodo_pago'             => 'PUE',
        ];

        $response = $this->postJson('/api/invoices', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.rfc_receptor', 'PEGJ850101XY1')
            ->assertJsonPath('data.status', 'vigente')
            ->assertJsonPath('data.subtotal', 1000)
            ->assertJsonPath('data.iva_trasladado', 160)
            ->assertJsonPath('data.total', 1160);

        $this->assertDatabaseHas('invoices', [
            'rfc_receptor' => 'PEGJ850101XY1',
            'status'       => 'vigente',
            'total'        => 1160.00,
        ]);

        $invoice = Invoice::first();
        $this->assertNotNull($invoice->uuid);
        $this->assertStringContainsString('cfdi:Comprobante', $invoice->xml_content);
        $this->assertStringContainsString('tfd:TimbreFiscalDigital', $invoice->xml_content);
    }

    public function test_can_download_xml(): void
    {
        $order = Order::create([
            'customer_name' => 'EMPRESA SA DE CV',
            'vehicle'       => 'Ford F-150',
            'status'        => 'done',
            'total'         => 580.00,
        ]);

        $invoice = (new \App\Services\CfdiService())->createInvoice([
            'order_id'                => $order->id,
            'rfc_receptor'            => 'CTE010101ABC',
            'razon_social_receptor'   => 'CLIENTE DE PRUEBA SA DE CV',
            'regimen_fiscal_receptor' => '601',
            'codigo_postal_receptor'  => '03100',
            'uso_cfdi'                => 'G01',
            'forma_pago'              => '03',
            'metodo_pago'             => 'PUE',
        ]);

        $response = $this->get('/api/invoices/' . $invoice->id . '/xml');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->assertStringContainsString($invoice->uuid, $response->getContent());
    }

    public function test_cancellation_requires_admin_pin(): void
    {
        $order = Order::create([
            'customer_name' => 'PEDRO LOPEZ',
            'vehicle'       => 'VW Jetta',
            'status'        => 'done',
            'total'         => 232.00,
        ]);

        $invoice = (new \App\Services\CfdiService())->createInvoice([
            'order_id'                => $order->id,
            'rfc_receptor'            => 'LOPP900202H12',
            'razon_social_receptor'   => 'PEDRO LOPEZ PEREZ',
            'regimen_fiscal_receptor' => '605',
            'codigo_postal_receptor'  => '11560',
            'uso_cfdi'                => 'S01',
            'forma_pago'              => '01',
            'metodo_pago'             => 'PUE',
        ]);

        // Attempt without PIN -> 403 Forbidden
        $responseWithoutPin = $this->postJson('/api/invoices/' . $invoice->id . '/cancel', [
            'motivo' => '02',
        ]);
        $responseWithoutPin->assertStatus(403);

        // Attempt with wrong PIN -> 403 Forbidden
        $responseWrongPin = $this->withHeaders(['X-Admin-Pin' => '0000'])
            ->postJson('/api/invoices/' . $invoice->id . '/cancel', [
                'motivo' => '02',
            ]);
        $responseWrongPin->assertStatus(403);

        // Attempt with correct PIN -> 200 OK
        $responseCorrectPin = $this->withHeaders(['X-Admin-Pin' => '9841'])
            ->postJson('/api/invoices/' . $invoice->id . '/cancel', [
                'motivo' => '02',
            ]);
        $responseCorrectPin->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelada');

        $this->assertDatabaseHas('invoices', [
            'id'     => $invoice->id,
            'status' => 'cancelada',
        ]);
    }

    public function test_cannot_invoice_order_that_already_has_active_invoice(): void
    {
        $order = Order::create([
            'customer_name' => 'RICARDO GARZA JAIME',
            'vehicle'       => 'Tesla Model 3',
            'status'        => 'done',
            'total'         => 7725.60,
        ]);

        // First invoice succeeds
        $firstResponse = $this->postJson('/api/invoices', [
            'order_id'                => $order->id,
            'rfc_receptor'            => 'GARJ8211043K9',
            'razon_social_receptor'   => 'RICARDO GARZA JAIME',
            'regimen_fiscal_receptor' => '605',
            'codigo_postal_receptor'  => '64720',
            'uso_cfdi'                => 'G03',
            'forma_pago'              => '04',
            'metodo_pago'             => 'PUE',
        ]);
        $firstResponse->assertStatus(201);

        // Second invoice for the SAME order MUST fail with 422 Unprocessable Entity
        $secondResponse = $this->postJson('/api/invoices', [
            'order_id'                => $order->id,
            'rfc_receptor'            => 'GARJ8211043K9',
            'razon_social_receptor'   => 'RICARDO GARZA JAIME',
            'regimen_fiscal_receptor' => '605',
            'codigo_postal_receptor'  => '64720',
            'uso_cfdi'                => 'G03',
            'forma_pago'              => '04',
            'metodo_pago'             => 'PUE',
        ]);

        $secondResponse->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
        
        $this->assertStringContainsString('ya se encuentra facturada', $secondResponse->json('errors.order_id.0'));
    }

    public function test_can_invoice_order_if_previous_invoice_was_cancelled(): void
    {
        $order = Order::create([
            'customer_name' => 'RICARDO GARZA JAIME',
            'vehicle'       => 'Tesla Model 3',
            'status'        => 'done',
            'total'         => 7725.60,
        ]);

        $firstInvoice = (new \App\Services\CfdiService())->createInvoice([
            'order_id'                => $order->id,
            'rfc_receptor'            => 'GARJ8211043K9',
            'razon_social_receptor'   => 'RICARDO GARZA JAIME',
            'regimen_fiscal_receptor' => '605',
            'codigo_postal_receptor'  => '64720',
            'uso_cfdi'                => 'G03',
            'forma_pago'              => '04',
            'metodo_pago'             => 'PUE',
        ]);

        // Cancel the first invoice
        $firstInvoice->update([
            'status'            => 'cancelada',
            'fecha_cancelacion' => now(),
            'motivo_cancelacion' => '02',
        ]);

        // Now creating a new invoice for this order MUST be allowed
        $secondResponse = $this->postJson('/api/invoices', [
            'order_id'                => $order->id,
            'rfc_receptor'            => 'GARJ8211043K9',
            'razon_social_receptor'   => 'RICARDO GARZA JAIME',
            'regimen_fiscal_receptor' => '605',
            'codigo_postal_receptor'  => '64720',
            'uso_cfdi'                => 'G03',
            'forma_pago'              => '04',
            'metodo_pago'             => 'PUE',
        ]);

        $secondResponse->assertStatus(201)
            ->assertJsonPath('data.status', 'vigente');
    }
}
