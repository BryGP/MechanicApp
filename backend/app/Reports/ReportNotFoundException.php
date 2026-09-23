<?php

namespace App\Reports;

use Exception;

/**
 * Class ReportNotFoundException
 * 
 * Thrown when an analytics report template identifier is requested
 * but cannot be located in the app/Reports registry.
 * Maps to HTTP 404 Not Found.
 * 
 * @package App\Reports
 */
class ReportNotFoundException extends Exception
{
}
