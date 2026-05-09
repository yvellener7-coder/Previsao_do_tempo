<?php

class User
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT id, name, email FROM users ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, language_preference, theme_preference FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function create(string $name, string $email, string $password): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)'
        );

        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updatePassword(int $userId, string $newPassword): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        $stmt->execute([
            'id' => $userId,
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
    }
}
