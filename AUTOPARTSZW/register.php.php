<?php
// AutoPartsZW - Register | Nyasha Ernest Nyakamhanda | B242508B | NWE214
session_start();
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);
    $email        = trim($_POST['email']);
    $phone        = trim($_POST['phone']);
    $password     = $_POST['password'];
    $confirm      = $_POST['confirm_password'];
    $account_type = $_POST['account_type'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, account_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $phone, $hashed, $account_type);
            if ($stmt->execute()) {
                $success = "Account created! You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
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
  <title>Register - AutoPartsZW</title>
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
    .form-wrap{max-width:480px;margin:36px auto;background:#fff;border:1px solid #e6e9ee;border-radius:8px;padding:32px 28px}
    .form-wrap h2{font-size:1.1rem;margin-bottom:4px;color:#0f172a}
    .form-wrap p{font-size:0.85rem;color:#6b7280;margin-bottom:22px}
    .form-group{margin-bottom:14px}
    .form-group label{display:block;font-size:0.85rem;font-weight:700;margin-bottom:5px;color:#374151}
    .form-group input,.form-group select{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.95rem;background:#fff}
    .form-group input:focus,.form-group select:focus{outline:none;border-color:#1f6feb}
    .row2{display:flex;gap:12px}.row2 .form-group{flex:1}
    .btn{width:100%;padding:11px;background:#1f6feb;color:#fff;border:none;border-radius:6px;font-size:1rem;font-weight:700;cursor:pointer}
    .btn:hover{background:#1e40af}
    .msg{padding:10px 14px;border-radius:6px;font-size:0.85rem;margin-bottom:16px}
    .msg.error{background:#fdecea;color:#b71c1c;border:1px solid #f5c6c6}
    .msg.success{background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9}
    .link-row{text-align:center;font-size:0.85rem;color:#6b7280;margin-top:16px}
    .link-row a{color:#1f6feb;font-weight:700}
    footer{background:#0f172a;color:#94a3b8;text-align:center;padding:16px;font-size:0.8rem;margin-top:40px}
    footer span{color:#1f6feb}
    @media(max-width:500px){.row2{flex-direction:column}}
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
    <h1>Create an <span>Account</span></h1>
  </div>

  <div class="form-wrap">
    <h2>Join AutoPartsZW</h2>
    <p>Create an account to order parts and track your orders.</p>

    <?php if (!empty($error)): ?>
      <div class="msg error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="msg success">✅ <?= htmlspecialchars($success) ?> <a href="login.php" style="color:#2e7d32;font-weight:700">Login here</a></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <div class="row2">
        <div class="form-group">
          <label>First Name</label>
          <input type="text" name="first_name" placeholder="Nyasha" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"/>
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="last_name" placeholder="Nyakamhanda" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"/>
        </div>
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="e.g. nyasha@gmail.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <input type="tel" name="phone" placeholder="e.g. 0771234567" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"/>
      </div>
      <div class="form-group">
        <label>Account Type</label>
        <select name="account_type">
          <option value="customer">Car Owner / Customer</option>
          <option value="mechanic">Mechanic / Garage</option>
        </select>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Minimum 6 characters" required/>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Repeat your password" required/>
      </div>
      <button type="submit" class="btn">Create Account</button>
    </form>

    <div class="link-row">
      Already have an account? <a href="login.php">Login here</a>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 <span>AutoPartsZW</span> &nbsp;|&nbsp; Nyasha Ernest Nyakamhanda &nbsp;|&nbsp; B242508B &nbsp;|&nbsp; NWE214</p>
  </footer>
</body>
</html>
