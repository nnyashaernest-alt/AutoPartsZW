<?php
$servername = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'autopartszw';
$port = 3307;

$conn = new mysqli($servername, $username, $password, $database, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
