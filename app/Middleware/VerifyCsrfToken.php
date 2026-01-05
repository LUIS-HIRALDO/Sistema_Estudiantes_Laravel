<?php

namespace App\Middleware;

use App\Response;

class VerifyCsrfToken
{
    public function handle()
    {
        if ($this->isReadingOperation()) {
            return true;
        }

        $token = $this->getToken();
        if (!$this->validToken($token)) {
            Response::json(['error' => 'Token CSRF inválido'], 419);
        }

        return true;
    }

    private function isReadingOperation()
    {
        return in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'OPTIONS']);
    }

    private function getToken()
    {
        return $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    }

    private function validToken($token)
    {
        return $token !== null && hash_equals($_SESSION['_token'] ?? '', $token);
    }
}
