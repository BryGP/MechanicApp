<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class AdminPinSecurityTest
 * 
 * Verifies that destructive endpoints (DELETE orders, DELETE expenses)
 * strictly enforce the X-Admin-Pin header and reject unauthorized attempts.
 */
class AdminPinSecurityTest extends TestCase
{
    use RefreshDatabase;

    private ?string $originalPin = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalPin = \App\Services\AdminPinService::getPin();
        \App\Services\AdminPinService::setPin('1234');
    }

    protected function tearDown(): void
    {
        if ($this->originalPin !== null) {
            \App\Services\AdminPinService::setPin($this->originalPin);
        }
        parent::tearDown();
    }

    public function test_delete_expense_without_pin_is_forbidden(): void
    {
        $expense = Expense::create([
            'concept'      => 'Test Security Expense',
            'category'     => 'otros',
            'amount'       => 150.00,
            'expense_date' => '2026-09-21',
        ]);

        // Attempt without header
        $response = $this->deleteJson("/api/expenses/{$expense->id}");
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Acceso denegado: Se requiere un PIN de administrador válido para ejecutar esta operación.'
        ]);

        // Attempt with wrong PIN
        $wrongResponse = $this->deleteJson("/api/expenses/{$expense->id}", [], [
            'X-Admin-Pin' => '9999'
        ]);
        $wrongResponse->assertStatus(403);

        // Attempt with correct PIN (default 1234)
        $validResponse = $this->deleteJson("/api/expenses/{$expense->id}", [], [
            'X-Admin-Pin' => '1234'
        ]);
        $validResponse->assertStatus(200);

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_delete_order_without_pin_is_forbidden(): void
    {
        $order = Order::create([
            'customer_name' => 'Security Test Client',
            'vehicle'       => 'Mazda 3',
            'status'        => 'open',
            'total'         => 500.00,
        ]);

        // Attempt without header
        $response = $this->deleteJson("/api/orders/{$order->id}");
        $response->assertStatus(403);

        // Attempt with correct PIN
        $validResponse = $this->deleteJson("/api/orders/{$order->id}", [], [
            'X-Admin-Pin' => '1234'
        ]);
        $validResponse->assertStatus(200);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_verify_pin_endpoint(): void
    {
        $responseValid = $this->postJson('/api/admin/pin/verify', ['pin' => '1234']);
        $responseValid->assertStatus(200);
        $responseValid->assertJson(['valid' => true]);

        $responseInvalid = $this->postJson('/api/admin/pin/verify', ['pin' => '0000']);
        $responseInvalid->assertStatus(401);
        $responseInvalid->assertJson(['valid' => false]);
    }

    public function test_change_pin_and_enforce_new_pin_on_deletion(): void
    {
        // 1. Change PIN to 5678
        $changeResponse = $this->postJson('/api/admin/pin/change', [
            'current_pin' => '1234',
            'new_pin'     => '5678',
        ]);
        $changeResponse->assertStatus(200);
        $changeResponse->assertJson(['success' => true]);

        // 2. Create an expense to delete
        $expense = Expense::create([
            'concept'      => 'Security Check Expense',
            'category'     => 'otros',
            'amount'       => 100.00,
            'expense_date' => '2026-09-22',
        ]);

        // 3. Attempt delete with old PIN 1234 -> should be 403 Forbidden!
        $oldResponse = $this->deleteJson("/api/expenses/{$expense->id}", [], [
            'X-Admin-Pin' => '1234'
        ]);
        $oldResponse->assertStatus(403);

        // 4. Attempt delete with new PIN 5678 -> should be 200 OK!
        $newResponse = $this->deleteJson("/api/expenses/{$expense->id}", [], [
            'X-Admin-Pin' => '5678'
        ]);
        $newResponse->assertStatus(200);
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);

        // Reset PIN back to 1234
        $this->postJson('/api/admin/pin/change', [
            'current_pin' => '5678',
            'new_pin'     => '1234',
        ]);
    }
}
