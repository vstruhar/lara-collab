<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;

class NoteEncryption
{
    private const PBKDF2_ALGO = 'sha256';

    private const PBKDF2_ITERATIONS = 600000;

    private const KEY_LENGTH = 32;

    public static function generateSalt(): string
    {
        return base64_encode(random_bytes(16));
    }

    public static function encrypt(string $passcode, string $salt, ?string $content): string
    {
        return self::encrypter($passcode, $salt)->encryptString((string) $content);
    }

    public static function decrypt(string $passcode, string $salt, string $payload): string
    {
        return self::encrypter($passcode, $salt)->decryptString($payload);
    }

    public static function isValidPasscode(string $passcode, string $salt, ?string $payload): bool
    {
        if (blank($payload) || blank($salt)) {
            return false;
        }

        try {
            self::decrypt($passcode, $salt, $payload);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }

    private static function encrypter(string $passcode, string $salt): Encrypter
    {
        $key = hash_pbkdf2(
            self::PBKDF2_ALGO,
            $passcode,
            $salt,
            self::PBKDF2_ITERATIONS,
            self::KEY_LENGTH,
            true,
        );

        return new Encrypter($key, (string) config('app.cipher'));
    }
}
