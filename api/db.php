<?php
declare(strict_types=1);

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $cfg = require __DIR__ . '/config.php';
    $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['db']};charset={$cfg['charset']}";
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function jsonResponse($data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requestJson(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) jsonResponse(['ok'=>false,'error'=>'JSON inválido'], 400);
    return $data;
}

function allowCors(): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
}

function validateLifecycle(PDO $pdo, string $etapa, string $estatus): bool {
    $st = $pdo->prepare('SELECT 1 FROM ciclo_vida_estatus WHERE etapa=? AND estatus=? AND activo=1');
    $st->execute([$etapa, $estatus]);
    return (bool)$st->fetchColumn();
}
