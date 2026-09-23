<?php

namespace Tests\Feature;

use App\Reports\ReportNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class ReportErrorHandlingTest
 * 
 * Verifies that the ReportController strictly distinguishes between:
 * - 404 Not Found: When a requested report ID does not exist in app/Reports.
 * - 500 Internal Server Error: When the report template exists but its SQL query
 *   fails or an unexpected server exception is thrown.
 */
class ReportErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test requesting an invalid report ID in run() yields HTTP 404.
     */
    public function test_non_existent_report_returns_404_in_run(): void
    {
        $response = $this->getJson('/api/reports/reporte_totalmente_inexistente/run');

        $response->assertStatus(404);
        $response->assertJson([
            'message' => "Reporte con ID 'reporte_totalmente_inexistente' no fue encontrado en app/Reports."
        ]);
    }

    /**
     * Test requesting an invalid report ID in pdf() yields HTTP 404.
     */
    public function test_non_existent_report_returns_404_in_pdf(): void
    {
        $response = $this->get('/api/reports/reporte_totalmente_inexistente/pdf');

        $response->assertStatus(404);
        $response->assertJson([
            'message' => "Reporte con ID 'reporte_totalmente_inexistente' no fue encontrado en app/Reports."
        ]);
    }

    /**
     * Test requesting a valid existing report template returns HTTP 200 with data structure.
     */
    public function test_existing_report_returns_200_in_run(): void
    {
        $response = $this->getJson('/api/reports/stock_critico/run');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'report' => ['id', 'title', 'category', 'icon', 'description', 'query'],
            'count',
            'data',
        ]);
    }

    /**
     * Test that an SQL failure (QueryException) during report execution yields HTTP 500.
     */
    public function test_sql_query_exception_returns_500_in_run(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->andThrow(new QueryException(
                'mysql',
                'SELECT * FROM broken_table',
                [],
                new \Exception("Table 'mechanic_app.broken_table' doesn't exist")
            ));

        $response = $this->getJson('/api/reports/stock_critico/run');

        $response->assertStatus(500);
        $response->assertJsonStructure([
            'message',
            'error'
        ]);
        $response->assertJson([
            'message' => 'Error al ejecutar la consulta SQL del reporte.'
        ]);
    }

    /**
     * Test that an unexpected runtime failure yields HTTP 500.
     */
    public function test_unexpected_runtime_exception_returns_500_in_run(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->andThrow(new \RuntimeException('Conexión con el motor de base de datos perdida.'));

        $response = $this->getJson('/api/reports/stock_critico/run');

        $response->assertStatus(500);
        $response->assertJsonStructure([
            'message'
        ]);
        $this->assertStringContainsString('Error interno al procesar el reporte', $response->json('message'));
    }
}
