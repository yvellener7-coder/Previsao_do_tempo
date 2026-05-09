<?php

class Auth
{
    public static function userId(): ?int
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return (int) $_SESSION['user_id'];
    }

    public static function requireUserId(): int
    {
        $userId = self::userId();
        if ($userId === null) {
            JsonResponse::send(['error' => 'Não autenticado'], 401);
        }

        return $userId;
    }
}
