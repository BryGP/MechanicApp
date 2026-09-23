<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Class AdminPinService
 * 
 * Centralized service managing Administrator PIN storage, persistence, and verification.
 * Strictly avoids storing plaintext credentials: stores exclusively Bcrypt hashes on disk
 * and in cache, removing legacy plaintext files automatically.
 * 
 * @package App\Services
 */
class AdminPinService
{
    const CACHE_KEY = 'workshop_admin_pin_hash';
    const STORAGE_FILE = 'admin_pin.hash';
    const LEGACY_STORAGE_FILE = 'admin_pin.txt';

    /**
     * Retrieves the currently active administrator security PIN Bcrypt hash.
     * Automatically migrates and purges legacy plaintext credentials if found.
     * 
     * @return string
     */
    public static function getPin(): string
    {
        // 1. Check fast in-memory cache
        $cachedHash = Cache::get(self::CACHE_KEY);
        if ($cachedHash !== null && is_string($cachedHash) && str_starts_with($cachedHash, '$2y$')) {
            return $cachedHash;
        }

        // 2. Check persistent disk hash file
        $filePath = storage_path('app/' . self::STORAGE_FILE);
        if (file_exists($filePath)) {
            $fileHash = trim((string) file_get_contents($filePath));
            if (!empty($fileHash) && str_starts_with($fileHash, '$2y$')) {
                Cache::forever(self::CACHE_KEY, $fileHash);
                return $fileHash;
            }
        }

        // 3. Migrate and immediately purge legacy plaintext file if it exists
        $legacyFilePath = storage_path('app/' . self::LEGACY_STORAGE_FILE);
        if (file_exists($legacyFilePath)) {
            $legacyPlainPin = trim((string) file_get_contents($legacyFilePath));
            @unlink($legacyFilePath); // Purge plaintext file from disk
            if (!empty($legacyPlainPin)) {
                $migratedHash = Hash::make($legacyPlainPin);
                self::saveHashToDiskAndCache($migratedHash);
                return $migratedHash;
            }
        }

        // 4. Fallback to hashed environment variable or default
        $defaultPin = (string) env('ADMIN_PIN', config('app.admin_pin', '1234'));
        $defaultHash = Hash::make(!empty($defaultPin) ? $defaultPin : '1234');
        self::saveHashToDiskAndCache($defaultHash);

        return $defaultHash;
    }

    /**
     * Persists a newly established administrator security PIN as a Bcrypt hash.
     * Never stores plaintext credentials on disk.
     * 
     * @param string $newPin Plaintext PIN or existing Bcrypt hash
     * @return void
     */
    public static function setPin(string $newPin): void
    {
        $trimmed = trim($newPin);

        // If already a Bcrypt hash (starts with $2y$), store directly; otherwise, hash it
        $hash = str_starts_with($trimmed, '$2y$') ? $trimmed : Hash::make($trimmed);

        self::saveHashToDiskAndCache($hash);

        // Ensure legacy plaintext file is destroyed
        $legacyFilePath = storage_path('app/' . self::LEGACY_STORAGE_FILE);
        if (file_exists($legacyFilePath)) {
            @unlink($legacyFilePath);
        }
    }

    /**
     * Validates whether an incoming plaintext PIN matches the active administrator Bcrypt hash.
     * 
     * @param string|null $pin
     * @return bool
     */
    public static function verify(?string $pin): bool
    {
        if (empty($pin)) {
            return false;
        }

        return Hash::check(trim((string) $pin), self::getPin());
    }

    /**
     * Internal helper to persist hash in storage and cache.
     * 
     * @param string $hash
     * @return void
     */
    private static function saveHashToDiskAndCache(string $hash): void
    {
        Cache::forever(self::CACHE_KEY, $hash);

        $dir = storage_path('app');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . '/' . self::STORAGE_FILE, $hash);
    }
}
