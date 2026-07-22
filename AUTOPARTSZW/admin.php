<?php
// AutoPartsZW - Admin | Nyasha Ernest Nyakamhanda | B242508B | NWE214
session_start();
require 'connect.php';

// Protect admin page
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Add Part
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_part'])) {
    $name       = mysqli_real_escape_string($conn, trim($_POST['name']));
    $category   = mysqli_real_escape_string($conn, $_POST['category']);
    $make       = mysqli_real_escape_string($conn, trim($_POST['make']));
    $model      = mysqli_real_escape_string($conn, trim($_POST['model']));
    $year_range = mysqli_real_escape_string($conn, trim($_POST['year_range']));
    $price      = (float)$_POST['price'];
    $stock      = (int)$_POST['stock'];
    $icon       = mysqli_real_escape_string($conn, trim($_POST['icon']) ?: '🔧');
    $conn->query("INSERT INTO parts (name,category,make,model,year_range,price,stock,icon) VALUES ('$name','$category','$make','$model','$year_range',$price,$stock,'$icon')");
    header("Location: admin.php?panel=parts&msg=added");
    exit();
}

// Handle Edit Part
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_part'])) {
    $id         = (int)$_POST['id'];
    $name       = mysqli_real_escape_string($conn, trim($_POST['name']));
    $category   = mysqli_real_escape_string($conn, $_POST['category']);
    $make       = mysqli_real_escape_string($conn, trim($_POST['make']));
    $model      = mysqli_real_escape_string($conn, trim($_POST['model']));
    $year_range = mysqli_real_escape_string($conn, trim($_POST['year_range']));
    $price      = (float)$_POST['price'];
    $stock      = (int)$_POST['stock'];
    $icon       = mysqli_real_escape_string($conn, trim($_POST['icon']) ?: '🔧');
    $conn->query("UPDATE parts SET name='$name',category='$category',make='$make',model='$model',year_range='$year_range',price=$price,stock=$stock,icon='$icon' WHERE id=$id");
    header("Location: admin.php?panel=parts&msg=updated");
    exit();
}

// Handle Delete Part
if (isset($_GET['delete_part'])) {
    $id = (int)$_GET['delete_part'];
    $conn->query("DELETE FROM parts WHERE id=$id");
    header("Location: admin.php?panel=parts&msg=deleted");
    exit();
}

// Handle Update Order Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $id     = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $conn->query("UPDATE orders SET status='$status' WHERE id=$id");
    header("Location: admin.php?panel=orders&msg=updated");
    exit();
}

// Handle Update Stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id    = (int)$_POST['part_id'];
    $stock = (int)$_POST['stock'];
    $conn->query("UPDATE parts SET stock=$stock WHERE id=$id");
    header("Location: admin.php?panel=stock&msg=updated");
    exit();
}


// Handle Create Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $fn    = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $ln    = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $pwd   = $_POST['password'];
    $type  = $_POST['account_type'];

    if ($fn && $ln && $email && $phone && $pwd) {
        $hashed = password_hash($pwd, PASSWORD_BCRYPT);
        $check  = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $user_msg_error = "Email already exists.";
        } else {
            $conn->query("INSERT INTO users (first_name,last_name,email,phone,password,account_type) VALUES ('$fn','$ln','$email','$phone','$hashed','$type')");
            header("Location: admin.php?panel=users&msg=created");
            exit();
        }
    } else {
        $user_msg_error = "Please fill in all fields.";
    }
}

// Handle Delete User
if (isset($_GET['delete_user'])) {
    $uid = (int)$_GET['delete_user'];
    if ($uid !== (int)$_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id=$uid");
    }
    header("Location: admin.php?panel=users&msg=deleted");
    exit();
}

// Fetch data
$parts  = $conn->query("SELECT * FROM parts ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$total_parts  = count($parts);
$total_orders = count($orders);
$low_stock    = count(array_filter($parts, fn($p) => $p['stock'] <= 3));
$revenue      = array_sum(array_column($orders, 'total'));
$panel        = $_GET['panel'] ?? 'dashboard';
$users  = $conn->query("SELECT id, first_name, last_name, email, phone, account_type, created_at FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Fetch single part for edit
$edit_part = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM parts WHERE id=$eid");
    if ($res) $edit_part = $res->fetch_assoc();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin - AutoPartsZW</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:Arial,Helvetica,sans-serif;background:#f7fafc;color:#111827}
    a{text-decoration:none;color:inherit}
    .navbar{background:#0f172a;padding:13px 24px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100}
    .logo{font-size:1.1rem;font-weight:700;color:#fff}.logo span{color:#1f6feb}
    .logo small{font-size:0.75rem;color:#94a3b8;margin-left:8px}
    .nav-right{display:flex;align-items:center;gap:14px;font-size:0.85rem;color:#94a3b8}
    .btn-logout{background:#ef4444;color:#fff;padding:7px 14px;border-radius:6px;font-size:0.85rem;cursor:pointer;border:none}
    .layout{display:flex;min-height:calc(100vh - 50px)}
    .sidebar{width:190px;background:#1e293b;flex-shrink:0;padding:16px 0}
    .s-item{padding:11px 20px;font-size:0.875rem;color:#94a3b8;cursor:pointer;display:flex;align-items:center;gap:8px;transition:background 0.15s}
    .s-item:hover{background:#334155;color:#fff}
    .s-item.active{background:#1f6feb;color:#fff}
    .main{flex:1;padding:24px;overflow-x:auto}
    .panel{display:none}.panel.active{display:block}
    .stats{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:24px}
    .stat{background:#fff;border:1px solid #e6e9ee;border-radius:8px;padding:18px 20px;flex:1;min-width:140px}
    .stat .lbl{font-size:0.75rem;color:#6b7280;margin-bottom:4px}
    .stat .val{font-size:1.6rem;font-weight:700;color:#0f172a}
    .stat .sub{font-size:0.75rem;color:#16a34a;margin-top:3px}
    .sec-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
    .sec-header h2{font-size:1rem;font-weight:700;color:#0f172a}
    .btn-add{background:#1f6feb;color:#fff;border:none;padding:8px 16px;border-radius:6px;font-size:0.85rem;cursor:pointer}
    .btn-add:hover{background:#1e40af}
    .tbl-wrap{background:#fff;border:1px solid #e6e9ee;border-radius:8px;overflow:hidden}
    table{width:100%;border-collapse:collapse;font-size:0.82rem}
    thead{background:#0f172a;color:#fff}
    thead th{padding:11px 13px;text-align:left}
    tbody tr{border-bottom:1px solid #f1f5f9}
    tbody tr:last-child{border-bottom:none}
    tbody tr:hover{background:#f8fafc}
    tbody td{padding:10px 13px;vertical-align:middle}
    .badge{display:inline-block;padding:3px 9px;border-radius:999px;font-size:0.75rem;font-weight:700}
    .in{background:#dcfce7;color:#166534}
    .low{background:#fef9c3;color:#854d0e}
    .out{background:#fee2e2;color:#991b1b}
    .pending{background:#fef9c3;color:#854d0e}
    .confirmed{background:#dbeafe;color:#1e40af}
    .delivered{background:#dcfce7;color:#166534}
    .cancelled{background:#fee2e2;color:#991b1b}
    .act{display:flex;gap:6px}
    .btn-e{padding:4px 10px;border:none;border-radius:4px;font-size:0.78rem;cursor:pointer}
    .btn-e.edit{background:#1f6feb;color:#fff}
    .btn-e.del{background:#ef4444;color:#fff}
    .btn-e.view{background:#6b7280;color:#fff}
    .toolbar{display:flex;gap:10px;margin-bottom:13px;flex-wrap:wrap}
    .toolbar input,.toolbar select{padding:7px 11px;border:1px solid #d1d5db;border-radius:6px;font-size:0.85rem;background:#fff}
    .form-sec{background:#fff;border:1px solid #e6e9ee;border-radius:8px;padding:22px;margin-bottom:20px}
    .form-sec h2{font-size:0.95rem;font-weight:700;margin-bottom:16px;color:#0f172a}
    .fg{margin-bottom:12px}
    .fg label{display:block;font-size:0.82rem;font-weight:700;margin-bottom:4px;color:#374151}
    .fg input,.fg select{width:100%;padding:8px 11px;border:1px solid #d1d5db;border-radius:6px;font-size:0.88rem;background:#fff}
    .fg input:focus,.fg select:focus{outline:none;border-color:#1f6feb}
    .r2{display:flex;gap:12px}.r2 .fg{flex:1}
    .btn-save{background:#1f6feb;color:#fff;border:none;padding:9px 22px;border-radius:6px;font-size:0.9rem;cursor:pointer;font-weight:700}
    .btn-save:hover{background:#1e40af}
    .btn-cancel{background:#f1f5f9;color:#374151;border:1px solid #d1d5db;padding:9px 18px;border-radius:6px;font-size:0.9rem;cursor:pointer}
    .msg-ok{background:#dcfce7;color:#166534;border:1px solid #bbf7d0;padding:9px 13px;border-radius:6px;font-size:0.85rem;margin-bottom:14px}
    footer{background:#0f172a;color:#94a3b8;text-align:center;padding:14px;font-size:0.78rem;margin-top:20px}
    footer span{color:#1f6feb}
    @media(max-width:650px){.sidebar{display:none}.stats{flex-direction:column}}
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo">Auto<span>Parts</span>ZW <small>Admin Panel</small></div>
    <div class="nav-right">
      <span>👤 <?= htmlspecialchars($_SESSION['first_name']) ?></span>
      <form method="POST" action="logout.php" style="display:inline">
        <button type="submit" class="btn-logout">Logout</button>
      </form>
    </div>
  </nav>

  <div class="layout">
    <div class="sidebar">
      <a href="admin.php?panel=dashboard" class="s-item <?= $panel==='dashboard'?'active':'' ?>">📊 Dashboard</a>
      <a href="admin.php?panel=parts"     class="s-item <?= $panel==='parts'?'active':'' ?>">🔧 Manage Parts</a>
      <a href="admin.php?panel=orders"    class="s-item <?= $panel==='orders'?'active':'' ?>">📦 Orders</a>
      <a href="admin.php?panel=stock"     class="s-item <?= $panel==='stock'?'active':'' ?>">📋 Stock Levels</a>
      <a href="admin.php?panel=users"     class="s-item <?= $panel==='users'?'active':'' ?>">👥 Users</a>
    </div>

    <div class="main">

      <!-- DASHBOARD -->
      <div class="panel <?= $panel==='dashboard'?'active':'' ?>" id="panel-dashboard">
        <h2 style="margin-bottom:18px;font-size:1.1rem">Welcome back, <?= htmlspecialchars($_SESSION['first_name']) ?> 👋</h2>
        <div class="stats">
          <div class="stat"><div class="lbl">Total Parts</div><div class="val"><?= $total_parts ?></div><div class="sub">In catalog</div></div>
          <div class="stat"><div class="lbl">Total Orders</div><div class="val"><?= $total_orders ?></div><div class="sub">All time</div></div>
          <div class="stat"><div class="lbl">Revenue</div><div class="val">$<?= number_format($revenue,2) ?></div><div class="sub">All orders</div></div>
          <div class="stat"><div class="lbl">Low Stock</div><div class="val"><?= $low_stock ?></div><div class="sub" style="color:#b45309">Needs restocking</div></div>
        </div>
        <div class="sec-header"><h2>Recent Orders</h2><a href="admin.php?panel=orders" class="btn-add">View All</a></div>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach(array_slice($orders,0,5) as $o): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($o['order_number']) ?></strong></td>
                  <td><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></td>
                  <td>$<?= number_format($o['total'],2) ?></td>
                  <td><?= htmlspecialchars($o['payment_method']) ?></td>
                  <td><span class="badge <?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- MANAGE PARTS -->
      <div class="panel <?= $panel==='parts'?'active':'' ?>" id="panel-parts">
        <?php if(isset($_GET['msg'])): ?>
          <div class="msg-ok">✅ Part <?= htmlspecialchars($_GET['msg']) ?> successfully!</div>
        <?php endif; ?>

        <?php if ($edit_part): ?>
          <!-- EDIT FORM -->
          <div class="form-sec">
            <h2>Edit Part</h2>
            <form method="POST" action="admin.php?panel=parts">
              <input type="hidden" name="id" value="<?= $edit_part['id'] ?>"/>
              <div class="r2">
                <div class="fg"><label>Part Name</label><input type="text" name="name" required value="<?= htmlspecialchars($edit_part['name']) ?>"/></div>
                <div class="fg"><label>Category</label>
                  <select name="category">
                    <?php foreach(['Brakes','Engine','Suspension','Electrical','Filters','Body Parts'] as $cat): ?>
                      <option <?= $edit_part['category']===$cat?'selected':'' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="r2">
                <div class="fg"><label>Make</label><input type="text" name="make" required value="<?= htmlspecialchars($edit_part['make']) ?>"/></div>
                <div class="fg"><label>Model</label><input type="text" name="model" required value="<?= htmlspecialchars($edit_part['model']) ?>"/></div>
              </div>
              <div class="r2">
                <div class="fg"><label>Year Range</label><input type="text" name="year_range" value="<?= htmlspecialchars($edit_part['year_range']) ?>"/></div>
                <div class="fg"><label>Price ($)</label><input type="number" name="price" step="0.01" min="0" value="<?= $edit_part['price'] ?>"/></div>
              </div>
              <div class="r2">
                <div class="fg"><label>Stock Qty</label><input type="number" name="stock" min="0" value="<?= $edit_part['stock'] ?>"/></div>
                <div class="fg"><label>Icon (emoji)</label><input type="text" name="icon" maxlength="4" value="<?= htmlspecialchars($edit_part['icon']) ?>"/></div>
              </div>
              <div style="display:flex;gap:10px;margin-top:8px">
                <button type="submit" name="edit_part" class="btn-save">Save Changes</button>
                <a href="admin.php?panel=parts" class="btn-cancel">Cancel</a>
              </div>
            </form>
          </div>
        <?php else: ?>
          <!-- ADD FORM -->
          <div class="form-sec">
            <h2>+ Add New Part</h2>
            <form method="POST" action="admin.php?panel=parts">
              <div class="r2">
                <div class="fg"><label>Part Name</label><input type="text" name="name" placeholder="e.g. Front Brake Pads" required/></div>
                <div class="fg"><label>Category</label>
                  <select name="category">
                    <?php foreach(['Brakes','Engine','Suspension','Electrical','Filters','Body Parts'] as $cat): ?>
                      <option><?= $cat ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="r2">
                <div class="fg"><label>Make</label><input type="text" name="make" placeholder="e.g. Toyota" required/></div>
                <div class="fg"><label>Model</label><input type="text" name="model" placeholder="e.g. Hilux" required/></div>
              </div>
              <div class="r2">
                <div class="fg"><label>Year Range</label><input type="text" name="year_range" placeholder="e.g. 2015-2022"/></div>
                <div class="fg"><label>Price ($)</label><input type="number" name="price" step="0.01" min="0" placeholder="0.00" required/></div>
              </div>
              <div class="r2">
                <div class="fg"><label>Stock Qty</label><input type="number" name="stock" min="0" placeholder="0" required/></div>
                <div class="fg"><label>Icon (emoji)</label><input type="text" name="icon" maxlength="4" placeholder="🔧"/></div>
              </div>
              <button type="submit" name="add_part" class="btn-save" style="margin-top:8px">Add Part</button>
            </form>
          </div>
        <?php endif; ?>

        <!-- PARTS TABLE -->
        <div class="sec-header"><h2>All Parts (<?= count($parts) ?>)</h2></div>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>#</th><th>Name</th><th>Category</th><th>Fits</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($parts as $i=>$p): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= htmlspecialchars($p['icon']).' '.htmlspecialchars($p['name']) ?></td>
                  <td><?= htmlspecialchars($p['category']) ?></td>
                  <td><?= htmlspecialchars($p['make'].' '.$p['model'].' '.$p['year_range']) ?></td>
                  <td>$<?= number_format($p['price'],2) ?></td>
                  <td>
                    <?php if($p['stock']==0): ?><span class="badge out">Out</span>
                    <?php elseif($p['stock']<=3): ?><span class="badge low">Low (<?= $p['stock'] ?>)</span>
                    <?php else: ?><span class="badge in">In (<?= $p['stock'] ?>)</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="act">
                      <a href="admin.php?panel=parts&edit=<?= $p['id'] ?>" class="btn-e edit">Edit</a>
                      <a href="admin.php?delete_part=<?= $p['id'] ?>" class="btn-e del" onclick="return confirm('Delete this part?')">Delete</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ORDERS -->
      <div class="panel <?= $panel==='orders'?'active':'' ?>" id="panel-orders">
        <?php if(isset($_GET['msg'])): ?>
          <div class="msg-ok">✅ Order status <?= htmlspecialchars($_GET['msg']) ?>!</div>
        <?php endif; ?>
        <div class="sec-header"><h2>Customer Orders (<?= count($orders) ?>)</h2></div>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>Order #</th><th>Customer</th><th>Phone</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach($orders as $o): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($o['order_number']) ?></strong></td>
                  <td><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></td>
                  <td><?= htmlspecialchars($o['phone']) ?></td>
                  <td>$<?= number_format($o['total'],2) ?></td>
                  <td><?= htmlspecialchars($o['payment_method']) ?></td>
                  <td><span class="badge <?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                  <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                  <td>
                    <form method="POST" action="admin.php?panel=orders" style="display:flex;gap:4px">
                      <input type="hidden" name="order_id" value="<?= $o['id'] ?>"/>
                      <select name="status" style="padding:3px 6px;border:1px solid #d1d5db;border-radius:4px;font-size:0.78rem">
                        <?php foreach(['pending','confirmed','delivered','cancelled'] as $s): ?>
                          <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" name="update_order" class="btn-e view">Update</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- STOCK -->
      <div class="panel <?= $panel==='stock'?'active':'' ?>" id="panel-stock">
        <?php if(isset($_GET['msg'])): ?>
          <div class="msg-ok">✅ Stock <?= htmlspecialchars($_GET['msg']) ?>!</div>
        <?php endif; ?>
        <div class="sec-header"><h2>Stock Levels</h2></div>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>Part Name</th><th>Category</th><th>Current Stock</th><th>Status</th><th>Update</th></tr></thead>
            <tbody>
              <?php foreach($parts as $p): ?>
                <tr>
                  <td><?= htmlspecialchars($p['icon']).' '.htmlspecialchars($p['name']) ?></td>
                  <td><?= htmlspecialchars($p['category']) ?></td>
                  <td><strong><?= $p['stock'] ?></strong></td>
                  <td>
                    <?php if($p['stock']==0): ?><span class="badge out">Out of Stock</span>
                    <?php elseif($p['stock']<=3): ?><span class="badge low">Low Stock</span>
                    <?php else: ?><span class="badge in">In Stock</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <form method="POST" action="admin.php?panel=stock" style="display:flex;gap:6px;align-items:center">
                      <input type="hidden" name="part_id" value="<?= $p['id'] ?>"/>
                      <input type="number" name="stock" value="<?= $p['stock'] ?>" min="0" style="width:65px;padding:4px 7px;border:1px solid #d1d5db;border-radius:4px;font-size:0.85rem"/>
                      <button type="submit" name="update_stock" class="btn-e edit">Update</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>


      <!-- USERS -->
      <div class="panel <?= $panel==='users'?'active':'' ?>" id="panel-users">

        <?php if(isset($_GET['msg'])): ?>
          <div class="msg-ok">✅ User <?= htmlspecialchars($_GET['msg']) ?> successfully!</div>
        <?php endif; ?>
        <?php if(!empty($user_msg_error)): ?>
          <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:9px 13px;border-radius:6px;font-size:0.85rem;margin-bottom:14px">❌ <?= htmlspecialchars($user_msg_error) ?></div>
        <?php endif; ?>

        <!-- CREATE USER FORM -->
        <div class="form-sec">
          <h2>+ Add New User / Admin</h2>
          <form method="POST" action="admin.php?panel=users">
            <div class="r2">
              <div class="fg"><label>First Name</label><input type="text" name="first_name" placeholder="Nyasha" required/></div>
              <div class="fg"><label>Last Name</label><input type="text" name="last_name" placeholder="Nyakamhanda" required/></div>
            </div>
            <div class="r2">
              <div class="fg"><label>Email</label><input type="email" name="email" placeholder="nyasha@gmail.com" required/></div>
              <div class="fg"><label>Phone</label><input type="tel" name="phone" placeholder="0771234567" required/></div>
            </div>
            <div class="r2">
              <div class="fg"><label>Password</label><input type="password" name="password" placeholder="Min 6 characters" required/></div>
              <div class="fg"><label>Account Type</label>
                <select name="account_type">
                  <option value="customer">Customer</option>
                  <option value="mechanic">Mechanic</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
            </div>
            <button type="submit" name="create_admin" class="btn-save" style="margin-top:8px">Create User</button>
          </form>
        </div>

        <!-- USERS TABLE -->
        <div class="sec-header"><h2>All Users (<?= count($users) ?>)</h2></div>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Type</th><th>Registered</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach($users as $i=>$u): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><?= htmlspecialchars($u['phone']) ?></td>
                  <td><span class="badge <?= $u['account_type']==="admin"?'confirmed':'in' ?>"><?= ucfirst($u['account_type']) ?></span></td>
                  <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                  <td>
                    <?php if($u['id'] !== (int)$_SESSION['user_id']): ?>
                      <a href="admin.php?delete_user=<?= $u['id'] ?>&panel=users" class="btn-e del" onclick="return confirm('Delete this user?')">Delete</a>
                    <?php else: ?>
                      <span style="font-size:0.75rem;color:#9ca3af">You</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <footer>
    <p>&copy; 2025 <span>AutoPartsZW</span> &nbsp;|&nbsp; Nyasha Ernest Nyakamhanda &nbsp;|&nbsp; B242508B &nbsp;|&nbsp; NWE214</p>
  </footer>
</body>
</html>
