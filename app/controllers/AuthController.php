<?php

class AuthController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function register(): void
    {
        $body = Request::json();
        $name = trim((string) ($body['name'] ?? ''));
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        $password = (string) ($body['password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            JsonResponse::send(['error' => 'name, email e password são obrigatórios'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            JsonResponse::send(['error' => 'Email inválido'], 422);
        }
        if (strlen($password) < 6) {
            JsonResponse::send(['error' => 'Password deve ter pelo menos 6 caracteres'], 422);
        }

        $userModel = new User($this->pdo);
        if ($userModel->findByEmail($email)) {
            JsonResponse::send(['error' => 'Email já registado'], 409);
        }

        $userId = $userModel->create($name, $email, $password);
        $_SESSION['user_id'] = $userId;

        JsonResponse::send(['message' => 'Registo concluído', 'user_id' => $userId], 201);
    }

    public function login(): void
    {
        $body = Request::json();
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        $password = (string) ($body['password'] ?? '');

        if ($email === '' || $password === '') {
            JsonResponse::send(['error' => 'email e password são obrigatórios'], 422);
        }

        $userModel = new User($this->pdo);
        $user = $userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            JsonResponse::send(['error' => 'Credenciais inválidas'], 401);
        }

        $_SESSION['user_id'] = (int) $user['id'];
        JsonResponse::send(['message' => 'Login efetuado']);
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        JsonResponse::send(['message' => 'Logout efetuado']);
    }

    public function forgotPassword(): void
    {
        $body = Request::json();
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        if ($email === '') {
            JsonResponse::send(['error' => 'email é obrigatório'], 422);
        }

        $userModel = new User($this->pdo);
        $user = $userModel->findByEmail($email);
        if (!$user) {
            JsonResponse::send(['message' => 'Se o email existir, você receberá instruções de recuperação.']);
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        $resetModel = new PasswordReset($this->pdo);
        $resetModel->create((int) $user['id'], $token, $expiresAt);

        $appUrl = $this->getAppUrl();
        $resetUrl = sprintf('%s/?reset_token=%s', $appUrl, $token);
        $subject = 'Recuperação de senha - RENE METEO';
        $html = '<p>Olá ' . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . ',</p>' .
            '<p>Recebemos uma solicitação para redefinir sua senha em RENE METEO.</p>' .
            '<p><a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '">Clique aqui para redefinir sua senha</a></p>' .
            '<p>Se o link não funcionar, use este token na página de recuperação:</p>' .
            '<p><strong>' . $token . '</strong></p>' .
            '<p>O token expira em 1 hora.</p>' .
            '<p>Se você não solicitou, ignore este email.</p>';

        $mailer = new MailService();
        $sent = $mailer->send($email, $subject, $html);

        if ($sent) {
            JsonResponse::send(['message' => 'Se o email existir, você receberá instruções de recuperação em breve.']);
        }

        JsonResponse::send([
            'message' => 'Não foi possível enviar o email de recuperação. Use o token abaixo para redefinir sua senha.',
            'reset_url' => $resetUrl,
            'reset_token' => $token,
        ]);
    }

    private function getAppUrl(): string
    {
        $baseUrl = getenv('APP_BASE_URL');
        if ($baseUrl !== false && $baseUrl !== '') {
            return rtrim($baseUrl, '/');
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }

    public function resetPassword(): void
    {
        $body = Request::json();
        $token = trim((string) ($body['token'] ?? ''));
        $newPassword = (string) ($body['new_password'] ?? '');

        if ($token === '' || $newPassword === '') {
            JsonResponse::send(['error' => 'token e new_password são obrigatórios'], 422);
        }
        if (strlen($newPassword) < 6) {
            JsonResponse::send(['error' => 'A nova password deve ter pelo menos 6 caracteres'], 422);
        }

        $resetModel = new PasswordReset($this->pdo);
        $resetRow = $resetModel->findValidByToken($token);
        if (!$resetRow) {
            JsonResponse::send(['error' => 'Token inválido ou expirado'], 400);
        }

        $userModel = new User($this->pdo);
        $userModel->updatePassword((int) $resetRow['user_id'], $newPassword);
        $resetModel->markUsed((int) $resetRow['id']);

        JsonResponse::send(['message' => 'Password alterada com sucesso']);
    }

    public function me(): void
    {
        $userId = Auth::requireUserId();
        $userModel = new User($this->pdo);
        $user = $userModel->findById($userId);

        JsonResponse::send(['user' => $user]);
    }
}
