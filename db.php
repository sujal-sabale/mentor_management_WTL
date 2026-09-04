<?php
// ===================================================
// Database Connection Configuration (db.php)
// Smart auto-detection for Localhost & InfinityFree
// ===================================================

$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (strpos($httpHost, 'localhost') !== false || strpos($httpHost, '127.0.0.1') !== false) {
    // --- Localhost (XAMPP) Configuration ---
    $host     = 'localhost';
    $dbname   = 'mentor_management';
    $username = 'root';
    $password = '';
} else {
    // --- InfinityFree Live Hosting Configuration ---
    $host     = 'sql108.infinityfree.com';
    $dbname   = 'if0_42830499_mentors';
    $username = 'if0_42830499';
    // Enter your InfinityFree hosting account password here if different:
    $password = 'sujal4518'; 
}

try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unable to connect to database. Please verify your database credentials in db.php.'
    ]);
    exit;
}
?>
