<?php

namespace App\Services;

use Hashids\Hashids;

class HashidService
{
    private static ?Hashids $instance = null;

    /**
     * Get the singleton Hashids instance.
     */
    private static function getInstance(): Hashids
    {
        if (self::$instance === null) {
            $salt = config('app.key') ?: 'cpsp_secure_salt_hashids';
            self::$instance = new Hashids($salt, 7, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        }
        return self::$instance;
    }

    /**
     * Encode an integer ID into a short hashid string.
     */
    public static function encode(int|string|null $id): string
    {
        if ($id === null || $id === '') {
            return '';
        }

        if (!is_numeric($id)) {
            return (string) $id;
        }

        return self::getInstance()->encode((int) $id);
    }

    /**
     * Decode a hashid string back to integer ID.
     * Returns null if invalid, or the integer if valid.
     * Backward-compatible if passed an integer.
     */
    public static function decode(mixed $hash): ?int
    {
        if ($hash === null || $hash === '') {
            return null;
        }

        if (is_numeric($hash)) {
            return (int) $hash;
        }

        $decoded = self::getInstance()->decode((string) $hash);
        return !empty($decoded) ? (int) $decoded[0] : null;
    }
}
