<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Class AdminPinService
 * 
 * Centralized service managing Administrator PIN storage, persistence, and verification.
 * Supports persistent file storage in storage/app/admin_pin.txt, database/in-memory cache,
 * and environment variable fallback.
 * 
 * @package App\Services
 */
class AdminPinService
{
    const CACHE_KEY = 'workshop_admin_pin';
    const STORAGE_FILE = 'admin_pin.txt';

    /**
     * Retrieves the currently active administrator security PIN.
     * 
     * @return string
     */
    public static function getPin(): string
    {
        // 1. Check fast cache
        $pin = Cache::get(self::CACHE_KEY);
        if ($pin !== null && $pin !== '') {
            return (string) $pin;
        }

        // 2. Check persistent disk file
        $filePath = storage_path('app/' . self::STORAGE_FILE);
        if (file_exists($filePath)) {
            $filePin = trim((string) file_get_contents($filePath));
            if (!empty($filePin)) {
                Cache::forever(self::CACHE_KEY, $filePin);
                return $filePin;
            }
        }

        // 3. Fallback to environment variable or system default
        $envPin = (string) env('ADMIN_PIN', config('app.admin_pin', '1234'));
        return !empty($envPin) ? $envPin : '1234';
    }

    /**
     * Persists a newly established administrator security PIN.
     * 
     * @param string $newPin
     * @return void
     */
    public static function setPin(string $newPin): void
    {
        $cleanPin = trim($newPin);
        Cache::forever(self::CACHE_KEY, $cleanPin);

        $dir = storage_path('app');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/' . self::STORAGE_FILE, $cleanPin);
    }

    /**
     * Validates whether an incoming PIN matches the active administrator PIN.
     * 
     * @param string|null $pin
     * @return bool
     */
    public static function verify(?string $pin): bool
    {
        if (empty($pin)) {
            return false;
        }

        return trim((string) $pin) === self::getPin();
    }
}
