<?php
require_once dirname(__DIR__) . '/config.php';

function getMysqlSessionTimezoneOffset(): string {
    $tz = new DateTimeZone(date_default_timezone_get());
    $now = new DateTimeImmutable('now', $tz);
    $offsetSeconds = $tz->getOffset($now);

    $sign = $offsetSeconds >= 0 ? '+' : '-';
    $offsetSeconds = abs($offsetSeconds);
    $hours = (int) floor($offsetSeconds / 3600);
    $minutes = (int) floor(($offsetSeconds % 3600) / 60);

    return sprintf('%s%02d:%02d', $sign, $hours, $minutes);
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec("SET time_zone = '" . getMysqlSessionTimezoneOffset() . "'");
    } catch (PDOException $e) {
        http_response_code(500);
        // Show friendly error in admin, JSON error in API
        $isApi = str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');
        if ($isApi) {
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'error' => 'Database connection failed.']));
        }
        die('<h2 style="font-family:sans-serif;color:#c00;padding:40px">
              Database connection failed.<br>
              <small style="font-size:14px;color:#666">Check config.php - DB_PORT should be 3307.</small>
             </h2>');
    }

    return $pdo;
}
