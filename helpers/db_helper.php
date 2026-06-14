<?php
// helpers/db_helper.php

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        
        // PERBAIKAN: Menyisipkan config port ke dalam DSN
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['db']};charset={$config['charset']}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (PDOException $e) {
            if (function_exists('log_critical')) {
                log_critical("Database Connection Failed: " . $e->getMessage());
            }
            // Menampilkan pesan error asli dari MySQL agar gampang dilacak
            die("Koneksi basis data gagal: " . $e->getMessage());
        }
    }

    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}

if (!function_exists('db_query')) {
    function db_query($sql, $params = []) {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}