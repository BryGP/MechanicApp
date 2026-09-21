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
}
