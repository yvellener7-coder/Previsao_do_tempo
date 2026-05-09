<?php

class HomeController
{
    public function index(PDO $pdo): void
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $baseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($baseUrl === '' || $baseUrl === '.') {
            $baseUrl = '';
        }

        require __DIR__ . '/../views/home.php';
    }
}
