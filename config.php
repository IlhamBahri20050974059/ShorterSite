<?php
session_start();

// ⚙️ Sesuaikan dengan detail database InfinityFree / Localhost kamu
$host = "sql105.infinityfree.com";
$dbname = "if0_41872080_shorter";
$username = "if0_41872080"; // isi username DB InfinityFree
$password = "q8ql0kPP9zX2a";     // isi password DB InfinityFree

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

// Ganti dengan domain kamu
define('BASE_URL', 'https://shorter.site.je'); 
?>