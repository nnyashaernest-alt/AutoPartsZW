<?php
// AutoPartsZW - Login | Nyasha Ernest Nyakamhanda | B242508B | NWE214
session_start();
require 'connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        error_reporting(E_ALL); ini_set('display_errors', 1);
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password, account_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']      = $user['id'];
                $_SESSION['first_name']   = $user['first_name'];
                $_SESSION['last_name']    = $user['last_name'];
                $_SESSION['account_type'] = $user['account_type'];
                header("Location: " . ($user['account_type'] === 'admin' ? 'admin.php' : 'index.html'));
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - AutoPartsZW</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:Arial,Helvetica,sans-serif;background:#f7fafc;color:#111827}
    a{text-decoration:none;color:inherit}
    .navbar{background:#fff;padding:14px 30px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100;border-bottom:1px solid #e6e9ee}
    .logo{font-size:1.2rem;font-weight:700}.logo span{color:#1f6feb}
    nav{display:flex;gap:16px}nav a{color:#374151;font-size:0.95rem}
    .cart-btn{background:#1f6feb;color:#fff;padding:8px 14px;border-radius:6px;font-size:0.9rem}
    .page-header{background:#fff;padding:22px 30px;text-align:center;border-bottom:1px solid #e6e9ee}
    .page-header h1{font-size:1.5rem;color:#0f172a}.page-header h1 span{color:#1f6feb}
    .form-wrap{max-width:440px;margin:36px auto;background:#fff;border:1px solid #e6e9ee;border-radius:8px;padding:32px 28px}
    .form-wrap h2{font-size:1.1rem;margin-bottom:4px;color:#0f172a}
    .form-wrap p{font-size:0.85rem;color:#6b7280;margin-bottom:22px}
    .form-group{margin-bottom:14px}
    .form-group label{display:block;font-size:0.85rem;font-weight:700;margin-bottom:5px;color:#374151}
    .form-group input{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.95rem}
    .form-group input:focus{outline:none;border-color:#1f6feb}
    .form-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;font-size:0.85rem}
    .form-row a{color:#1f6feb}
    .btn{width:100%;padding:11px;background:#1f6feb;color:#fff;border:none;border-radius:6px;font-size:1rem;font-weight:700;cursor:pointer}
    .btn:hover{background:#1e40af}
    .msg{padding:10px 14px;border-radius:6px;font-size:0.85rem;margin-bottom:16px}
    .msg.error{background:#fdecea;color:#b71c1c;border:1px solid #f5c6c6}
    .link-row{text-align:center;font-size:0.85rem;color:#6b7280;margin-top:16px}
    .link-row a{color:#1f6feb;font-weight:700}
    footer{background:#0f172a;color:#94a3b8;text-align:center;padding:16px;font-size:0.8rem;margin-top:40px}
    footer span{color:#1f6feb}
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo">Auto<span>Parts</span>ZW</div>
    <nav>
      <a href="index.html">Home</a>
      <a href="catalog.php">Catalog</a>
      <a href="login.php">Login</a>
    </nav>
    <a href="cart.php" class="cart-btn">🛒 Cart</a>
  </nav>

  <div class="page-header">
    <h1>Customer <span>Login</span></h1>
  </div>

  <div class="form-wrap">
    <h2>Welcome Back</h2>
    <p>Login to view your orders and checkout faster.</p>

    <?php if (!empty($error)): ?>
      <div class="msg error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="e.g. nyasha@gmail.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required/>
      </div>
      <div class="form-row">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
          <input type="checkbox" name="remember" style="accent-color:#1f6feb;width:auto"/> Remember me
        </label>
        <a href="#">Forgot Password?</a>
      </div>
      <button type="submit" class="btn">Login</button>
    </form>

    <div class="link-row">
      Don't have an account? <a href="register.php">Register here</a>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 <span>AutoPartsZW</span> &nbsp;|&nbsp; Nyasha Ernest Nyakamhanda &nbsp;|&nbsp; B242508B &nbsp;|&nbsp; NWE214</p>
  </footer>
</body>
</html>
