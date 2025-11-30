<?php

// ====== UBAH SESUAI KONFIGURASI ANDA ======
$host = 'localhost';
$port = '5432';
$dbname = 'cantabu';     // atau nama DB yang kamu pakai (mis: 'labdb')
$user = 'postgres';       // username PostgreSQL
$password = '7541305';
// ==========================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn_str = "host=$host port=$port dbname=$dbname user=$user password=$password";
$conn = pg_connect($conn_str);
