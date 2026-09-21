<?php

namespace App\Reports;

/**
 * ============================================================================
 * CLASE: ClientesFrecuentesReport (Reporte Comercial de Clientes VIP y Flotillas)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Segmenta y clasifica la cartera de clientes del taller mecánico. Identifica 
 * a los clientes recurrentes, propietarios de flotillas comerciales y cuentas 
 * corporativas que generan el mayor volumen de facturación y visitas.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Métricas Financieras Consolidadas en Base de Datos:
 *   Calcula el Lifetime Value (LTV) del cliente, su facturación total acumulada, 
 *   el conteo de visitas al taller y el ticket promedio por servicio.
 * - Trazabilidad Vehicular Histórica:
 *   Detecta el último vehículo atendido ('MAX(vehicle)') y la fecha exacta de su 
 *   última visita para campañas de reenganche o mantenimiento preventivo.
 * ============================================================================
 */
class ClientesFrecuentesReport
{
    // =========================================================================
    // SECCIÓN: DEFINICIÓN DE METADATOS Y SENTENCIA SQL ANALÍTICA
    // =========================================================================

    /**
     * // Función para obtener la configuración y consulta de clientes frecuentes
     * 
     * Retorna los metadatos y la sentencia SQL agrupada por cliente para 
     * ranking de facturación y visitas al taller.
     *
     * @return array
     */
    public static function info(): array
    {
        return [
            'id'          => 'clientes_vip',
            'title'       => 'Clientes VIP y Rentabilidad por Flotilla',
            'category'    => 'Ventas',
            'icon'        => 'orders',
            'description' => 'Identifica a los clientes que más visitas registran y mayor facturación aportan al taller, junto con su ticket promedio.',
            'query'       => "
                SELECT 
                    customer_name AS cliente,
                    COUNT(*) AS total_visitas,
                    ROUND(SUM(total), 2) AS facturacion_total,
                    ROUND(AVG(total), 2) AS ticket_promedio,
                    MAX(vehicle) AS ultimo_vehiculo_atendido,
                    DATE_FORMAT(MAX(created_at), '%Y-%m-%d') AS fecha_ultima_visita
                FROM orders
                WHERE customer_name IS NOT NULL AND customer_name != ''
                GROUP BY customer_name
                ORDER BY facturacion_total DESC
            ",
        ];
    }
}
