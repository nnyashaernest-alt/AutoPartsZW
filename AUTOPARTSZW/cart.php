<?php
// AutoPartsZW - Cart | Nyasha Ernest Nyakamhanda | B242508B | NWE214
session_start();
require 'connect.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $part_id = (int)$_POST['part_id'];

    if ($_POST['action'] === 'remove') {
        unset($_SESSION['cart'][$part_id]);
    } elseif ($_POST['action'] === 'update' && isset($_POST['qty'])) {
        $qty = (int)$_POST['qty'];
        if ($qty <= 0) unset($_SESSION['cart'][$part_id]);
        else $_SESSION['cart'][$part_id]['qty'] = $qty;
    }
    header("Location: cart.php");
    exit();
}

$cart      = $_SESSION['cart'];
$cartCount = array_sum(array_column($cart, 'qty'));
$subtotal  = array_sum(array_map(function($i){ return $i['price'] * $i['qty']; }, $cart));
$delivery  = count($cart) > 0 ? 5.00 : 0.00;
$total     = $subtotal + $delivery;
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cart - AutoPartsZW</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:Arial,Helvetica,sans-serif;background:#f7fafc;color:#111827}
    a{text-decoration:none;color:inherit}
    .navbar{background:#fff;padding:14px 30px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100;border-bottom:1px solid #e6e9ee}
    .logo{font-size:1.2rem;font-weight:700}.logo span{color:#1f6feb}
    nav{display:flex;gap:16px}nav a{color:#374151;font-size:0.95rem}
    .cart-btn{background:#1f6feb;color:#fff;padding:8px 14px;border-radius:6px;font-size:0.9rem;display:flex;align-items:center;gap:6px}
    .cart-count{background:#fff;color:#1f6feb;border-radius:50%;font-size:11px;font-weight:700;padding:1px 6px;min-width:18px;text-align:center}
    .page-header{background:#fff;padding:22px 30px;text-align:center;border-bottom:1px solid #e6e9ee}
    .page-header h1{font-size:1.5rem;color:#0f172a}.page-header h1 span{color:#1f6feb}
    .cart-layout{display:flex;gap:24px;max-width:1000px;margin:30px auto;padding:0 20px;align-items:flex-start}
    .cart-items{flex:1}
    .cart-items h2{font-size:1rem;margin-bottom:14px;color:#0f172a}
    .cart-item{background:#fff;border:1px solid #e6e9ee;border-radius:8px;padding:14px;display:flex;align-items:center;gap:14px;margin-bottom:12px}
    .item-icon{font-size:36px;background:#f7fafc;border-radius:6px;padding:8px 12px;flex-shrink:0}
    .item-details{flex:1}
    .item-details h3{font-size:0.95rem;margin-bottom:2px}
    .item-details p{font-size:0.8rem;color:#6b7280;margin-bottom:4px}
    .item-price{font-size:0.95rem;font-weight:700}
    .qty-form{display:flex;align-items:center;gap:6px;flex-shrink:0}
    .qty-form input{width:50px;padding:5px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:0.9rem;text-align:center}
    .item-subtotal{font-size:0.95rem;font-weight:700;color:#1f6feb;min-width:56px;text-align:right;flex-shrink:0}
    .remove-btn{background:none;border:none;color:#9ca3af;font-size:1.1rem;cursor:pointer;flex-shrink:0}
    .remove-btn:hover{color:#ef4444}
    .continue-link{display:inline-block;margin-top:8px;font-size:0.85rem;color:#1f6feb}
    .empty-cart{text-align:center;padding:50px 20px;background:#fff;border:1px solid #e6e9ee;border-radius:8px}
    .empty-cart .e-icon{font-size:48px;margin-bottom:12px}
    .empty-cart h3{font-size:1.1rem;margin-bottom:6px}
    .empty-cart p{font-size:0.85rem;color:#6b7280;margin-bottom:18px}
    .btn-shop{display:inline-block;padding:10px 22px;background:#1f6feb;color:#fff;border-radius:6px;font-size:0.9rem;font-weight:700}
    .order-summary{width:290px;flex-shrink:0}
    .summary-box{background:#fff;border:1px solid #e6e9ee;border-radius:8px;padding:20px}
    .summary-box h2{font-size:1rem;margin-bottom:16px;color:#0f172a}
    .summary-row{display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:10px;color:#6b7280}
    .summary-row.total{font-size:1rem;font-weight:700;color:#0f172a;border-top:1px solid #e6e9ee;padding-top:12px;margin-top:4px}
    .summary-row.total span:last-child{color:#1f6feb}
    .btn-checkout{width:100%;padding:11px;background:#1f6feb;color:#fff;border:none;border-radius:6px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:14px}
    .btn-checkout:hover{background:#1e40af}
    .btn-checkout:disabled{background:#d1d5db;cursor:not-allowed}
    .pay-note{text-align:center;font-size:0.75rem;color:#9ca3af;margin-top:8px}
    footer{background:#0f172a;color:#94a3b8;text-align:center;padding:16px;font-size:0.8rem;margin-top:40px}
    footer span{color:#1f6feb}
    @media(max-width:700px){nav{display:none}.cart-layout{flex-direction:column}.order-summary{width:100%}}
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo">Auto<span>Parts</span>ZW</div>
    <nav>
      <a href="index.html">Home</a>
      <a href="catalog.php">Catalog</a>
      <a href="<?= isset($_SESSION['user_id']) ? 'logout.php' : 'login.php' ?>">
        <?= isset($_SESSION['user_id']) ? 'Logout' : 'Login' ?>
      </a>
    </nav>
    <a href="cart.php" class="cart-btn">🛒 Cart <span class="cart-count"><?= $cartCount ?></span></a>
  </nav>

  <div class="page-header">
    <h1>Your <span>Cart</span></h1>
  </div>

  <div class="cart-layout">
    <div class="cart-items">
      <h2>Items in Your Cart</h2>

      <?php if (empty($cart)): ?>
        <div class="empty-cart">
          <div class="e-icon">🛒</div>
          <h3>Your cart is empty</h3>
          <p>You have not added any parts yet.</p>
          <a href="catalog.php" class="btn-shop">Browse Catalog</a>
        </div>
      <?php else: ?>
        <?php foreach ($cart as $item): ?>
          <div class="cart-item">
            <div class="item-icon"><?= htmlspecialchars($item['icon']) ?></div>
            <div class="item-details">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <p><?= htmlspecialchars($item['fit']) ?></p>
              <div class="item-price">$<?= number_format($item['price'],2) ?> each</div>
            </div>
            <form method="POST" action="cart.php" class="qty-form">
              <input type="hidden" name="action" value="update"/>
              <input type="hidden" name="part_id" value="<?= $item['id'] ?>"/>
              <input type="number" name="qty" value="<?= $item['qty'] ?>" min="0" max="99" onchange="this.form.submit()"/>
            </form>
            <div class="item-subtotal">$<?= number_format($item['price']*$item['qty'],2) ?></div>
            <form method="POST" action="cart.php">
              <input type="hidden" name="action" value="remove"/>
              <input type="hidden" name="part_id" value="<?= $item['id'] ?>"/>
              <button type="submit" class="remove-btn" title="Remove">✕</button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <a href="catalog.php" class="continue-link">← Continue Shopping</a>
    </div>

    <div class="order-summary">
      <div class="summary-box">
        <h2>Order Summary</h2>
        <div class="summary-row">
          <span>Subtotal (<?= $cartCount ?> items)</span>
          <span>$<?= number_format($subtotal,2) ?></span>
        </div>
        <div class="summary-row">
          <span>Delivery</span>
          <span>$<?= number_format($delivery,2) ?></span>
        </div>
        <div class="summary-row total">
          <span>Total</span>
          <span>$<?= number_format($total,2) ?></span>
        </div>
        <a href="<?= empty($cart)?'#':'checkout.php' ?>">
          <button class="btn-checkout" <?= empty($cart)?'disabled':'' ?>>Proceed to Checkout</button>
        </a>
        <p class="pay-note">📱 Pay with EcoCash or OneMoney at checkout</p>
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 <span>AutoPartsZW</span> &nbsp;|&nbsp; Nyasha Ernest Nyakamhanda &nbsp;|&nbsp; B242508B &nbsp;|&nbsp; NWE214</p>
  </footer>
</body>
</html>
