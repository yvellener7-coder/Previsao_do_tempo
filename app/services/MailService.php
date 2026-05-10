<?php

class MailService
{
    public function send(string $to, string $subject, string $htmlBody): bool
    {
        $fromEmail = getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@renemeteo.com';
        $fromName = getenv('MAIL_FROM_NAME') ?: 'RENE METEO';

        if ($this->smtpConfigured()) {
            return $this->sendSmtp($to, $subject, $htmlBody, $fromEmail, $fromName);
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";

        return @mail($to, $subject, $htmlBody, $headers);
    }

    private function smtpConfigured(): bool
    {
        return getenv('MAIL_SMTP_HOST') && getenv('MAIL_SMTP_USER') && getenv('MAIL_SMTP_PASS');
    }

    private function sendSmtp(string $to, string $subject, string $body, string $fromEmail, string $fromName): bool
    {
        $host = getenv('MAIL_SMTP_HOST');
        $port = (int) (getenv('MAIL_SMTP_PORT') ?: 587);
        $username = getenv('MAIL_SMTP_USER');
        $password = getenv('MAIL_SMTP_PASS');
        $encryption = strtolower(getenv('MAIL_SMTP_ENCRYPTION') ?: 'tls');
        $timeout = 30;
        $remote = $host;
        $transport = 'tcp';

        if ($encryption === 'ssl') {
            $remote = "ssl://{$host}";
            $port = $port ?: 465;
        }

        $socket = @stream_socket_client("{$remote}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            return false;
        }

        $this->smtpRead($socket);
        $this->smtpWrite($socket, "EHLO localhost");
        if ($encryption === 'tls') {
            $this->smtpWrite($socket, "STARTTLS");
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return false;
            }
            $this->smtpWrite($socket, "EHLO localhost");
        }

        $this->smtpWrite($socket, "AUTH LOGIN");
        $this->smtpWrite($socket, base64_encode($username));
        $this->smtpWrite($socket, base64_encode($password));
        $this->smtpWrite($socket, "MAIL FROM:<{$fromEmail}>");
        $this->smtpWrite($socket, "RCPT TO:<{$to}>");
        $this->smtpWrite($socket, "DATA");

        $message = "From: {$fromName} <{$fromEmail}>\r\n";
        $message .= "To: {$to}\r\n";
        $message .= "Subject: {$subject}\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-type: text/html; charset=UTF-8\r\n";
        $message .= "\r\n";
        $message .= $body . "\r\n.";

        $this->smtpWrite($socket, $message);
        $this->smtpWrite($socket, "QUIT");
        fclose($socket);

        return true;
    }

    private function smtpRead($socket): string
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    private function smtpWrite($socket, string $command): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->smtpRead($socket);
    }
}
