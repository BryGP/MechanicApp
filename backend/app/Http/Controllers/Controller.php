<?php

namespace App\Http\Controllers;

/**
 * ============================================================================
 * CLASE: Controller (Clase Base Abstracta)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Sirve como cimiento arquitectónico y punto de herencia común para todos los 
 * controladores de la API REST del sistema (ExpenseController, OrderController,
 * ProductController, ReportController).
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Arquitectura Ultraligera de Laravel 11:
 *   A diferencia de versiones anteriores de Laravel que incluían múltiples traits
 *   pesados por defecto, en Laravel 11 esta clase se mantiene minimalista y 
 *   desacoplada, ofreciendo el lienzo perfecto para compartir middlewares,
 *   métodos auxiliares de respuesta JSON y filtros globales de seguridad.
 * ============================================================================
 */
abstract class Controller
{
    // =========================================================================
    // SECCIÓN: MÉTODOS Y SERVICIOS COMPARTIDOS ENTRE CONTROLADORES
    // =========================================================================
    // Reservado para helpers globales, formateadores de respuesta o auditoría API.
}
