<?php

namespace App\Core;

class CSRF
{
    public static function getToken(): string
    {
        Session::start();
        $token = Session::get('_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    public static function validate(?string $token): bool
    {
        Session::start();
        $sessionToken = Session::get('_csrf_token');
        if (!$token || !$sessionToken) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
