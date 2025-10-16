<?php
// Carregar variáveis de ambiente se existir arquivo .env
if (file_exists(__DIR__ . '/../../.env')) {
    $env = parse_ini_file(__DIR__ . '/../../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

// Configurações do banco de dados
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'compras');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Configurações de segurança
define('SECURE_SESSION', true);
define('SESSION_TIMEOUT', 1800); // 30 minutos
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutos
define('PASSWORD_MIN_LENGTH', 8);
define('REQUIRE_STRONG_PASSWORD', true);
define('CSRF_PROTECTION', true);

// Configurações de log
define('LOG_ERRORS', true);
define('LOG_QUERIES', true);
define('LOG_PATH', __DIR__ . '/../../logs');
define('LOG_LEVEL', 'DEBUG'); // DEBUG, INFO, WARNING, ERROR

// Configurações de e-mail
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM', getenv('SMTP_FROM') ?: 'noreply@prefeitura.sp.gov.br');
define('SMTP_NAME', getenv('SMTP_NAME') ?: 'Sistema de Compras');

// Configurações de upload
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/png',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);
define('UPLOAD_PATH', __DIR__ . '/../../uploads');

// Configurações de cache
define('ENABLE_CACHE', true);
define('CACHE_TIME', 3600); // 1 hora
define('CACHE_PATH', __DIR__ . '/../../cache');

// URLs e paths
define('BASE_URL', 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST']);
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// API de Machine Learning
define('ML_API_URL', getenv('ML_API_URL') ?: 'http://127.0.0.1:5001');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Verificar requisitos mínimos
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('Este sistema requer PHP 7.4 ou superior.');
}

// Verificar extensões necessárias
$required_extensions = ['mysqli', 'mbstring', 'json', 'openssl'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        die("A extensão PHP '$ext' é necessária.");
    }
}

// Criar diretórios necessários
$directories = [LOG_PATH, UPLOAD_PATH, CACHE_PATH];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Configurar handlers de erro
if (LOG_ERRORS) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_PATH . '/php_errors.log');
}

// Configurar sessão
if (SECURE_SESSION) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_trans_sid', 0);
}
