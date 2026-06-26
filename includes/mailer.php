<?php
require_once __DIR__ . '/functions.php';

class SmtpMailer
{
    private $sock = null;
    private $lastError = '';

    public function getLastError(): string
    {
        return $this->lastError;
    }

    public function deliver(array $cfg, string $to, string $toName, string $subject, string $html): bool
    {
        if (!$this->connect($cfg['host'], (int)$cfg['port'], $cfg['encryption'])) return false;
        if (!$this->auth($cfg['username'], $cfg['password']))  { $this->quit(); return false; }
        $ok = $this->sendMessage($cfg['from_email'], $cfg['from_name'], $to, $toName, $subject, $html);
        $this->quit();
        return $ok;
    }

    private function connect(string $host, int $port, string $enc): bool
    {
        $addr = ($enc === 'ssl' ? 'ssl://' : '') . $host;
        $this->sock = @fsockopen($addr, $port, $errno, $errstr, 15);
        if (!$this->sock) {
            $this->lastError = trim("Connection failed: {$errno} {$errstr}");
            return false;
        }

        $greeting = $this->read();
        if (!str_starts_with($greeting, '220')) {
            $this->lastError = 'SMTP greeting failed: ' . trim($greeting);
            return false;
        }

        $ehlo = $this->cmd('EHLO ' . (gethostname() ?: 'localhost'));
        if (!str_starts_with($ehlo, '250')) {
            $this->lastError = 'EHLO failed: ' . trim($ehlo);
            return false;
        }

        if ($enc === 'tls') {
            $tls = $this->cmd('STARTTLS');
            if (!str_starts_with($tls, '220')) {
                $this->lastError = 'STARTTLS failed: ' . trim($tls);
                return false;
            }

            if (!stream_socket_enable_crypto($this->sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $this->lastError = 'Unable to enable TLS encryption.';
                return false;
            }

            $ehloTls = $this->cmd('EHLO ' . (gethostname() ?: 'localhost'));
            if (!str_starts_with($ehloTls, '250')) {
                $this->lastError = 'EHLO after STARTTLS failed: ' . trim($ehloTls);
                return false;
            }
        }
        return true;
    }

    private function auth(string $u, string $p): bool
    {
        $auth = $this->cmd('AUTH LOGIN');
        if (!str_starts_with($auth, '334')) {
            $this->lastError = 'AUTH LOGIN failed: ' . trim($auth);
            return false;
        }

        $userResp = $this->cmd(base64_encode($u));
        if (!str_starts_with($userResp, '334')) {
            $this->lastError = 'SMTP username rejected: ' . trim($userResp);
            return false;
        }

        $r = $this->cmd(base64_encode($p));
        if (!str_starts_with($r, '235')) {
            $this->lastError = 'SMTP password rejected: ' . trim($r);
        }
        return str_starts_with($r, '235');
    }

    private function sendMessage(
        string $from, string $fromName,
        string $to,   string $toName,
        string $subject, string $html
    ): bool {
        $mailFrom = $this->cmd("MAIL FROM:<$from>");
        if (!str_starts_with($mailFrom, '250')) {
            $this->lastError = 'MAIL FROM failed: ' . trim($mailFrom);
            return false;
        }

        $rcptTo = $this->cmd("RCPT TO:<$to>");
        if (!str_starts_with($rcptTo, '250') && !str_starts_with($rcptTo, '251')) {
            $this->lastError = 'RCPT TO failed: ' . trim($rcptTo);
            return false;
        }

        $data = $this->cmd('DATA');
        if (!str_starts_with($data, '354')) {
            $this->lastError = 'DATA command failed: ' . trim($data);
            return false;
        }

        $boundary = uniqid('gnx_', true);
        $plain    = wordwrap(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $html)), 76, "\n", true);

        $msg  = 'From: =?UTF-8?B?' . base64_encode($fromName) . "?= <$from>\r\n";
        $msg .= 'To: =?UTF-8?B?' . base64_encode($toName ?: $to) . "?= <$to>\r\n";
        $msg .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
        $msg .= 'Date: ' . date('r') . "\r\n";
        $msg .= "X-Mailer: GADGET HUBMailer/1.0\r\n\r\n";

        $msg .= "--$boundary\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $msg .= chunk_split(base64_encode($plain)) . "\r\n";

        $msg .= "--$boundary\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $msg .= chunk_split(base64_encode($html)) . "\r\n";

        $msg .= "--$boundary--\r\n.";

        $r = $this->cmd($msg);
        if (!str_starts_with($r, '250')) {
            $this->lastError = 'SMTP message rejected: ' . trim($r);
        }
        return str_starts_with($r, '250');
    }

    private function quit(): void
    {
        if ($this->sock) {
            @fwrite($this->sock, "QUIT\r\n");
            fclose($this->sock);
            $this->sock = null;
        }
    }

    private function cmd(string $c): string
    {
        fwrite($this->sock, $c . "\r\n");
        return $this->read();
    }

    private function read(): string
    {
        $data = '';
        while ($line = fgets($this->sock, 515)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    }
}

// Public helper 
function sendMail(string $to, string $toName, string $subject, string $html): bool
{
    $cfg = [
        'host'       => getSetting('smtp_host'),
        'port'       => getSetting('smtp_port', '587'),
        'encryption' => getSetting('smtp_encryption', 'tls'),
        'username'   => getSetting('smtp_username'),
        'password'   => getSetting('smtp_password'),
        'from_email' => getSetting('smtp_from_email'),
        'from_name'  => getSetting('smtp_from_name', getSetting('store_name', 'GADGET HUB Store')),
    ];

    if (!$cfg['host'] || !$cfg['username'] || !$cfg['from_email']) {
        error_log('sendMail skipped: SMTP settings are incomplete.');
        return false;
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log('sendMail skipped: invalid recipient email "' . $to . '".');
        return false;
    }

    try {
        $mailer = new SmtpMailer();
        $ok = $mailer->deliver($cfg, $to, $toName, $subject, $html);
        if (!$ok) {
            error_log('sendMail failed for ' . $to . ': ' . $mailer->getLastError());
        }
        return $ok;
    } catch (Throwable $e) {
        error_log('sendMail exception for ' . $to . ': ' . $e->getMessage());
        return false;
    }
}
