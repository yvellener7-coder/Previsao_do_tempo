<?php

class PasswordReset
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $userId, string $rawToken, string $expiresAt): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => $expiresAt,
        ]);
    }

    public function findValidByToken(string $rawToken): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM password_resets WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['token_hash' => hash('sha256', $rawToken)]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
