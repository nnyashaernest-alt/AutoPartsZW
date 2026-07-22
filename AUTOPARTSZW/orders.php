<?php
// AutoPartsZW - My Orders | Nyasha Ernest Nyakamhanda | B242508B | NWE214
session_start();
require 'connect.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0;

// Get all orders for this customer
$orders = [];
$res = $conn->query("SELECT * FROM orders WHERE user_id=$user_id ORDER BY created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Get order items for each order
$orderItems = [];
foreach ($orders as $order) {
    $oid = (int)$order['id'];
    $items = [];
    $res2 = $conn->query("SELECT * FROM order_items WHERE order_id=$oid");
    if ($res2) {
        while ($row = $res2->fetch_assoc()) {
            $items[] = $row;
        }
    }
    $orderItems[$oid] = $items;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Orders - AutoPartsZW</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:Arial,sans-serif;background:#f7fafc;color:#111827}
    a{text-decoration:none;color:inherit}
    .pw{width:min(1000px,calc(100% - 30px));margin:0 auto}
    .navbar{background:#fff;display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid #e6e9ee;position:sticky;top:0;z-index:100}
    .logo{font-size:1.3rem;font-weight:700}.logo span{color:#1f6feb}
    .nav-links{display:flex;gap:16px}.nav-links a{color:#374151;font-size:0.9rem}
    .cart-btn{background:#1f6feb;color:#fff;padding:8px 14px;border-radius:6px;font-size:0.9rem;display:flex;align-items:center;gap:6px}
    .cart-count{background:#fff;color:#1f6feb;border-radius:50%;font-size:11px;font-weight:700;padding:1px 6px;min-width:18px;text-align:center}
    .page-header{background:#fff;padding:22px 0;border-bottom:1px solid #e6e9ee;margin-bottom:24px}
    .page-header h1{font-size:1.4rem;color:#0f172a}.page-header h1 span{color:#1f6feb}
    .page-header p{font-size:0.88rem;color:#6b7280;margin-top:4px}
    .order-card{background:#fff;border:1px solid #e6e9ee;border-radius:8px;margin-bottom:18px;overflow:hidden}
    .order-head{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:1px solid #f1f5f9;flex-wrap:wrap;gap:10px}
    .order-num{font-weight:700;font-size:0.95rem;color:#0f172a}
    .order-date{font-size:0.8rem;color:#6b7280}
    .status{display:inline-block;padding:4px 12px;border-radius:999px;font-size:0.78rem;font-weight:700}
    .status.pending{background:#fef9c3;color:#854d0e}
    .status.confirmed{background:#dbeafe;color:#1e40af}
    .status.delivered{background:#dcfce7;color:#166534}
    .status.cancelled{background:#fee2e2;color:#991b1b}
    .order-body{padding:16px 18px}
    .order-items{margin-bottom:14px}
    .order-item{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:0.88rem}
    .order-item:last-child{border-bottom:none}
    .item-name{color:#374151;flex:1}
    .item-qty{color:#6b7280;margin:0 14px}
    .item-price{font-weight:700;color:#0f172a}
    .order-footer{display:flex;justify-content:space-between;align-items:center;padding-top:12px;border-top:1px solid #f1f5f9;flex-wrap:wrap;gap:8px}
    .order-total{font-weight:700;font-size:1rem;color:#0f172a}
    .order-total span{color:#1f6feb}
    .order-payment{font-size:0.82rem;color:#6b7280}
    .order-delivery{font-size:0.82rem;color:#374151;margin-top:6px}
    .status-track{display:flex;align-items:center;gap:0;margin:14px 0 6px}
    .track-step{display:flex;flex-direction:column;align-items:center;flex:1;position:relative}
    .track-step:not(:last-child)::after{content:'';position:absolute;top:14px;left:60%;width:80%;height:2px;background:#e6e9ee;z-index:0}
    .track-dot{width:28px;height:28px;border-radius:50%;border:2px solid #e6e9ee;background:#fff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:#9ca3af;z-index:1;position:relative}
    .track-dot.done{background:#1f6feb;border-color:#1f6feb;color:#fff}
    .track-dot.active{background:#fff;border-color:#1f6feb;color:#1f6feb}
    .track-label{font-size:0.7rem;color:#6b7280;margin-top:4px;text-align:center}
    .track-label.done{color:#1f6feb;font-weight:700}
    .empty-orders{text-align:center;padding:60px 20px;background:#fff;border:1px solid #e6e9ee;border-radius:8px}
    .empty-orders .e-icon{font-size:48px;margin-bottom:14px}
    .empty-orders h3{font-size:1.1rem;margin-bottom:8px}
    .empty-orders p{font-size:0.88rem;color:#6b7280;margin-bottom:18px}
    .btn-shop{display:inline-block;padding:10px 22px;background:#1f6feb;color:#fff;border-radius:6px;font-size:0.9rem;font-weight:700}
    footer{padding:16px 0;text-align:center;color:#6b7280;border-top:1px solid #e6e9ee;font-size:0.82rem;margin-top:20px}
    footer span{color:#1f6feb}
    @media(max-width:600px){.nav-links{display:none}.order-head{flex-direction:column;align-items:flex-start}}
  </style>
</head>
<body>
<div class="pw">

  <nav class="navbar">
    <div class="logo">Auto<span>Parts</span>ZW</div>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="catalog.php">Catalog</a>
      <a href="orders.php" style="color:#1f6feb;font-weight:700">My Orders</a>
      <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['first_name']) ?>)</a>
    </div>
    <a href="cart.php" class="cart-btn">🛒 Cart <span class="cart-count"><?= $cartCount ?></span></a>
  </nav>

  <div class="page-header">
    <h1>My <span>Orders</span></h1>
    <p>Hello <?= htmlspecialchars($_SESSION['first_name']) ?> — here are all your orders and their current status.</p>
  </div>

  <?php if (empty($orders)): ?>
    <div class="empty-orders">
      <div class="e-icon">📦</div>
      <h3>No orders yet</h3>
      <p>You have not placed any orders yet. Browse our catalog to get started!</p>
      <a href="catalog.php" class="btn-shop">Browse Catalog</a>
    </div>

  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <?php
        $status = $order['status'];
        $steps  = ['pending', 'confirmed', 'delivered'];
        $stepLabels = ['Order Placed', 'Confirmed', 'Delivered'];
        $stepIcons  = ['📋', '✅', '📦'];
        $currentStep = array_search($status, $steps);
        if ($status === 'cancelled') $currentStep = -1;
      ?>
      <div class="order-card">

        <!-- ORDER HEADER -->
        <div class="order-head">
          <div>
            <div class="order-num">Order #<?= htmlspecialchars($order['order_number']) ?></div>
            <div class="order-date">Placed on <?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
          </div>
          <span class="status <?= $status ?>">
            <?php
              if($status === 'pending')   echo '🕐 Pending';
              if($status === 'confirmed') echo '✅ Confirmed';
              if($status === 'delivered') echo '📦 Delivered';
              if($status === 'cancelled') echo '❌ Cancelled';
            ?>
          </span>
        </div>

        <div class="order-body">

          <!-- STATUS TRACKER -->
          <?php if ($status !== 'cancelled'): ?>
          <div class="status-track">
            <?php foreach ($steps as $i => $step): ?>
              <div class="track-step">
                <div class="track-dot <?= $currentStep >= $i ? 'done' : ($currentStep === $i-1 ? 'active' : '') ?>">
                  <?= $currentStep >= $i ? '✓' : ($i+1) ?>
                </div>
                <div class="track-label <?= $currentStep >= $i ? 'done' : '' ?>"><?= $stepLabels[$i] ?></div>
              </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
            <div style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:6px;font-size:0.85rem;margin-bottom:12px">
              ❌ This order was cancelled. Please contact us if you have any questions.
            </div>
          <?php endif; ?>

          <!-- ORDER ITEMS -->
          <div class="order-items">
            <?php foreach ($orderItems[$order['id']] as $item): ?>
              <div class="order-item">
                <div class="item-name"><?= htmlspecialchars($item['part_name']) ?></div>
                <div class="item-qty">x<?= $item['quantity'] ?></div>
                <div class="item-price">$<?= number_format($item['subtotal'], 2) ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- ORDER FOOTER -->
          <div class="order-footer">
            <div>
              <div class="order-payment">💳 Payment: <?= ucfirst(htmlspecialchars($order['payment_method'])) ?>
                <?php if($order['payment_number']): ?>
                  (<?= htmlspecialchars($order['payment_number']) ?>)
                <?php endif; ?>
              </div>
              <div class="order-delivery">📍 <?= htmlspecialchars($order['city']) ?> — <?= htmlspecialchars($order['address']) ?></div>
            </div>
            <div class="order-total">
              Total: <span>$<?= number_format($order['total'], 2) ?></span>
            </div>
          </div>

        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <footer>
    <p>&copy; 2025 <span>AutoPartsZW</span> &nbsp;|&nbsp; Nyasha Ernest Nyakamhanda &nbsp;|&nbsp; B242508B &nbsp;|&nbsp; NWE214</p>
  </footer>
</div>
</body>
</html>
