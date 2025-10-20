<?php
function db(): PDO
{
    static $pdo;
    
    if (!$pdo) {
        try {
            $pdo = new PDO(
                "sqlite:" . __DIR__ . "/../../config/EBiletDB.db",
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}