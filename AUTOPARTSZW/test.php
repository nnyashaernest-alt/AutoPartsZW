<?php
session_start();
require 'connect.php';

$email = 'test@test.com';
$password = 'admin123';

$stmt = $conn->prepare("SELECT id, first_name, password, account_type FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

echo "User found: " . ($user ? 'YES' : 'NO') . "<br>";
if($user){
    echo "Password verify: " . (password_verify($password, $user['password']) ? 'YES' : 'NO') . "<br>";
    echo "Account type: " . $user['account_type'] . "<br>";
}
?>