<?php
// AutoPartsZW - Checkout | Nyasha Ernest Nyakamhanda | B242508B | NWE214
session_start();
require 'connect.php';

// Redirect to cart if empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$cart      = $_SESSION['cart'];
$cartCount = array_sum(array_column($cart, 'qty'));
$subtotal  = array_sum(array_map(function($i){ return $i['price'] * $i['qty']; }, $cart));
$delivery  = 5.00;
$total     = $subtotal + $delivery;

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $first_name     = trim($_POST['first_name']);
    $last_name      = trim($_POST['last_name']);
    $phone          = trim($_POST['phone']);
    $city           = trim($_POST['city']);
    $address        = trim($_POST['address']);
    $notes          = trim($_POST['notes']);
    $payment_method = $_POST['payment_method'];
    $payment_number = trim($_POST['payment_number'] ?? '');

    if ($first_name && $last_name && $phone && $city && $address) {
        $order_number = 'APZ-' . mt_rand(1000, 9999);
        $user_id      = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        // Use simple query to avoid bind_param issues
        $on  = mysqli_real_escape_string($conn, $order_number);
        $fn  = mysqli_real_escape_string($conn, $first_name);
        $ln  = mysqli_real_escape_string($conn, $last_name);
        $ph  = mysqli_real_escape_string($conn, $phone);
        $ct  = mysqli_real_escape_string($conn, $city);
        $ad  = mysqli_real_escape_string($conn, $address);
        $nt  = mysqli_real_escape_string($conn, $notes);
        $pm  = mysqli_real_escape_string($conn, $payment_method);
        $pn  = mysqli_real_escape_string($conn, $payment_number);
        $uid = $user_id ? $user_id : 'NULL';

        $conn->query("INSERT INTO orders (order_number,user_id,first_name,last_name,phone,city,address,notes,payment_method,payment_number,subtotal,delivery_fee,total,status)
            VALUES ('$on',$uid,'$fn','$ln','$ph','$ct','$ad','$nt','$pm','$pn',$subtotal,$delivery,$total,'pending')");

        $order_id = $conn->insert_id;

        // Save order items
        foreach ($cart as $item) {
            $iname = mysqli_real_escape_string($conn, $item['name']);
            $iprice = (float)$item['price'];
            $iqty   = (int)$item['qty'];
            $isub   = $iprice * $iqty;
            $iid    = (int)$item['id'];
            $conn->query("INSERT INTO order_items (order_id,part_id,part_name,price,quantity,subtotal)
                VALUES ($order_id,$iid,'$iname',$iprice,$iqty,$isub)");
        }

        // Clear cart
        $_SESSION['cart'] = [];
        $order_placed = $order_number;
    } else {
        $form_error = "Please fill in all required fields.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Checkout - AutoPartsZW</title>
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
    .steps{display:flex;justify-content:center;align-items:center;gap:8px;padding:14px;background:#fff;border-bottom:1px solid #e6e9ee;font-size:0.85rem}
    .step{display:flex;align-items:center;gap:6px;color:#9ca3af}
    .step.done{color:#16a34a}.step.active{color:#1f6feb;font-weight:700}
    .step-num{width:22px;height:22px;border-radius:50%;border:2px solid currentColor;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700}
    .step-line{width:36px;height:2px;background:#e6e9ee}
    .layout{display:flex;gap:22px;max-width:1000px;margin:28px auto;padding:0 20px;align-items:flex-start}
    .forms{flex:1}
    .section{background:#fff;border:1px solid #e6e9ee;border-radius:8px;padding:22px;margin-bottom:18px}
    .section h2{font-size:0.95rem;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #e6e9ee}
    .form-group{margin-bottom:13px}
    .form-group label{display:block;font-size:0.82rem;font-weight:700;margin-bottom:4px;color:#374151}
    .form-group input,.form-group select,.form-group textarea{width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:0.9rem;background:#fff}
    .form-group input:focus,.form-group select:focus{outline:none;border-color:#1f6feb}
    .form-group textarea{resize:vertical;height:65px}
    .row2{display:flex;gap:12px}.row2 .form-group{flex:1}
    .pay-option{border:2px solid #e6e9ee;border-radius:8px;padding:12px 14px;cursor:pointer;display:flex;align-items:center;gap:12px;margin-bottom:10px;transition:border-color 0.2s}
    .pay-option:hover,.pay-option.selected{border-color:#1f6feb;background:#f0f7ff}
    .pay-option input{accent-color:#1f6feb;flex-shrink:0}
    .pay-icon{font-size:24px;flex-shrink:0}
    .pay-label strong{font-size:0.9rem;display:block}
    .pay-label span{font-size:0.8rem;color:#6b7280}
    .pay-field{display:none;margin-top:10px;padding-top:10px;border-top:1px solid #e6e9ee}
    .pay-field.show{display:block}
    .summary{width:280px;flex-shrink:0}
    .summary-box{background:#fff;border:1px solid #e6e9ee;border-radius:8px;padding:20px;position:sticky;top:80px}
    .summary-box h2{font-size:0.95rem;font-weight:700;margin-bottom:14px;color:#0f172a}
    .s-item{display:flex;align-items:center;gap:8px;margin-bottom:10px;font-size:0.85rem}
    .s-icon{font-size:20px;background:#f7fafc;border-radius:4px;padding:4px 6px;flex-shrink:0}
    .s-name{flex:1;color:#374151}.s-qty{color:#9ca3af;font-size:0.8rem}
    .s-price{font-weight:700}
    hr{border:none;border-top:1px solid #e6e9ee;margin:12px 0}
    .s-row{display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:8px;color:#6b7280}
    .s-row.total{font-size:0.95rem;font-weight:700;color:#0f172a;border-top:1px solid #e6e9ee;padding-top:10px;margin-top:4px}
    .s-row.total span:last-child{color:#1f6feb}
    .btn-order{width:100%;padding:11px;background:#1f6feb;color:#fff;border:none;border-radius:6px;font-size:0.95rem;font-weight:700;cursor:pointer;margin-top:14px}
    .btn-order:hover{background:#1e40af}
    .secure{text-align:center;font-size:0.75rem;color:#9ca3af;margin-top:8px}
    .msg-error{background:#fdecea;color:#b71c1c;border:1px solid #f5c6c6;padding:10px 14px;border-radius:6px;font-size:0.85rem;margin-bottom:14px}
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;align-items:center;justify-content:center}
    .modal-overlay.show{display:flex}
    .modal{background:#fff;border-radius:10px;padding:36px 30px;max-width:400px;width:90%;text-align:center}
    .modal .ok-icon{font-size:52px;margin-bottom:14px}
    .modal h2{font-size:1.3rem;margin-bottom:8px;color:#0f172a}
    .modal p{font-size:0.85rem;color:#6b7280;margin-bottom:6px}
    .modal .onum{font-size:1.1rem;font-weight:700;color:#1f6feb;margin:12px 0}
    .modal-btns{display:flex;gap:10px;margin-top:20px}
    .modal-btns a{flex:1;padding:10px;border-radius:6px;font-size:0.9rem;font-weight:700;text-align:center}
    .btn-home{border:1px solid #e6e9ee;color:#374151}
    .btn-catalog{background:#1f6feb;color:#fff}
    footer{background:#0f172a;color:#94a3b8;text-align:center;padding:16px;font-size:0.8rem;margin-top:40px}
    footer span{color:#1f6feb}
    @media(max-width:700px){nav{display:none}.layout{flex-direction:column}.summary{width:100%}.row2{flex-direction:column}}
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
    <a href="cart.php" class="cart-btn">🛒 Cart</a>
  </nav>

  <div class="page-header">
    <h1>Secure <span>Checkout</span></h1>
  </div>

  <div class="steps">
    <div class="step done"><div class="step-num">✓</div><span>Cart</span></div>
    <div class="step-line"></div>
    <div class="step active"><div class="step-num">2</div><span>Checkout</span></div>
    <div class="step-line"></div>
    <div class="step"><div class="step-num">3</div><span>Confirmation</span></div>
  </div>

  <div class="layout">
    <div class="forms">
      <?php if (!empty($form_error)): ?>
        <div class="msg-error">❌ <?= htmlspecialchars($form_error) ?></div>
      <?php endif; ?>

      <form method="POST" action="checkout.php" id="checkoutForm">
        <div class="section">
          <h2>📦 Delivery Details</h2>
          <div class="row2">
            <div class="form-group">
              <label>First Name *</label>
              <input type="text" name="first_name" placeholder="Nyasha" required value="<?= htmlspecialchars($_SESSION['first_name'] ?? '') ?>"/>
            </div>
            <div class="form-group">
              <label>Last Name *</label>
              <input type="text" name="last_name" placeholder="Nyakamhanda" required value="<?= htmlspecialchars($_SESSION['last_name'] ?? '') ?>"/>
            </div>
          </div>
          <div class="form-group">
            <label>Phone Number *</label>
            <input type="tel" name="phone" placeholder="e.g. 0771234567" required/>
          </div>
          <div class="form-group">
            <label>City / Town *</label>
            <select name="city" required>
              <option value="">Select city...</option>
              <?php foreach(['Harare','Bulawayo','Mutare','Gweru','Masvingo','Kwekwe','Kadoma','Chinhoyi'] as $c): ?>
                <option><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Delivery Address *</label>
            <input type="text" name="address" placeholder="e.g. 12 Samora Machel Ave" required/>
          </div>
          <div class="form-group">
            <label>Order Notes (optional)</label>
            <textarea name="notes" placeholder="Any special instructions..."></textarea>
          </div>
        </div>

        <div class="section">
          <h2>💳 Payment Method</h2>
          <div class="pay-option selected" id="opt-ecocash" onclick="selectPay('ecocash')">
            <input type="radio" name="payment_method" value="ecocash" checked/>
            <div class="pay-icon">📱</div>
            <div class="pay-label"><strong>EcoCash</strong><span>Pay via EcoCash (Sandbox demo)</span></div>
          </div>
          <div class="pay-field show" id="field-ecocash">
            <div class="form-group" style="margin:0">
              <label>EcoCash Number</label>
              <input type="tel" name="payment_number" placeholder="e.g. 0771234567"/>
            </div>
          </div>

          <div class="pay-option" id="opt-onemoney" onclick="selectPay('onemoney')">
            <input type="radio" name="payment_method" value="onemoney"/>
            <div class="pay-icon">💚</div>
            <div class="pay-label"><strong>OneMoney</strong><span>Pay via NetOne OneMoney (Sandbox demo)</span></div>
          </div>
          <div class="pay-field" id="field-onemoney">
            <div class="form-group" style="margin:0">
              <label>OneMoney Number</label>
              <input type="tel" name="payment_number" placeholder="e.g. 0711234567"/>
            </div>
          </div>

          <div class="pay-option" id="opt-cod" onclick="selectPay('cod')">
            <input type="radio" name="payment_method" value="cod"/>
            <div class="pay-icon">💵</div>
            <div class="pay-label"><strong>Cash on Delivery</strong><span>Pay when your order arrives</span></div>
          </div>
        </div>

        <input type="hidden" name="place_order" value="1"/>
      </form>
    </div>

    <div class="summary">
      <div class="summary-box">
        <h2>Order Summary</h2>
        <?php foreach ($cart as $item): ?>
          <div class="s-item">
            <div class="s-icon"><?= htmlspecialchars($item['icon']) ?></div>
            <div class="s-name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="s-qty">x<?= $item['qty'] ?></div>
            <div class="s-price">$<?= number_format($item['price']*$item['qty'],2) ?></div>
          </div>
        <?php endforeach; ?>
        <hr/>
        <div class="s-row"><span>Subtotal</span><span>$<?= number_format($subtotal,2) ?></span></div>
        <div class="s-row"><span>Delivery</span><span>$<?= number_format($delivery,2) ?></span></div>
        <div class="s-row total"><span>Total</span><span>$<?= number_format($total,2) ?></span></div>
        <button class="btn-order" onclick="document.getElementById('checkoutForm').submit()">Place Order</button>
        <p class="secure">🔒 Your payment details are secure</p>
      </div>
    </div>
  </div>

  <!-- SUCCESS MODAL -->
  <div class="modal-overlay <?= !empty($order_placed)?'show':'' ?>" id="successModal">
    <div class="modal">
      <div class="ok-icon">✅</div>
      <h2>Order Placed!</h2>
      <p>Thank you! Your parts are being prepared.</p>
      <div class="onum">Order #<?= !empty($order_placed)?htmlspecialchars($order_placed):'APZ-0000' ?></div>
      <p>A confirmation will be sent to your phone number.</p>
      <div class="modal-btns">
        <a href="catalog.php" class="btn-catalog">Continue Shopping</a>
        <a href="index.html" class="btn-home">Back to Home</a>
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 <span>AutoPartsZW</span> &nbsp;|&nbsp; Nyasha Ernest Nyakamhanda &nbsp;|&nbsp; B242508B &nbsp;|&nbsp; NWE214</p>
  </footer>

  <script>
    function selectPay(method){
      ['ecocash','onemoney','cod'].forEach(m=>{
        document.getElementById('opt-'+m).classList.remove('selected');
        const f = document.getElementById('field-'+m);
        if(f) f.classList.remove('show');
      });
      document.getElementById('opt-'+method).classList.add('selected');
      const field = document.getElementById('field-'+method);
      if(field) field.classList.add('show');
      document.querySelector('#opt-'+method+' input[type=radio]').checked=true;
    }
  </script>
</body>
</html>
