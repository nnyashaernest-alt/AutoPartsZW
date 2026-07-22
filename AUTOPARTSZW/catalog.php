<?php
// AutoPartsZW - Catalog | Nyasha Ernest Nyakamhanda | B242508B | NWE214
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require 'connect.php';

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['part_id'])) {
    $part_id = (int)$_POST['part_id'];
    $res  = $conn->query("SELECT * FROM parts WHERE id=$part_id AND stock > 0");
    $part = $res ? $res->fetch_assoc() : null;
    if ($part) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$part_id])) {
            $_SESSION['cart'][$part_id]['qty']++;
        } else {
            $_SESSION['cart'][$part_id] = [
                'id'    => $part['id'],
                'name'  => $part['name'],
                'price' => $part['price'],
                'icon'  => $part['icon'] ?? '🔧',
                'image' => $part['image'] ?? '',
                'fit'   => $part['make'].' '.$part['model'].' '.$part['year_range'],
                'qty'   => 1
            ];
        }
    }
    header("Location: cart.php");
    exit();
}

// Load parts from DB
$dbParts = [];
$res = $conn->query("SELECT * FROM parts ORDER BY name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $dbParts[] = $row;
    }
}

// Load makes from DB
$makes = [];
$res2 = $conn->query("SELECT DISTINCT make FROM parts ORDER BY make ASC");
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        $makes[] = $row['make'];
    }
}

// Load models from DB
$modelsData = [];
$res3 = $conn->query("SELECT DISTINCT make, model FROM parts ORDER BY make, model ASC");
if ($res3) {
    while ($row = $res3->fetch_assoc()) {
        $modelsData[$row['make']][] = $row['model'];
    }
}

$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0;
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Catalog - AutoPartsZW</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:Arial,sans-serif;background:#f7fafc;color:#111827}
    a{text-decoration:none;color:inherit}
    .pw{width:min(1180px,calc(100% - 30px));margin:0 auto}
    .navbar{background:#fff;display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid #e6e9ee;position:sticky;top:0;z-index:100}
    .logo{font-size:1.3rem;font-weight:700}.logo span{color:#1f6feb}
    .nav-links{display:flex;gap:16px}.nav-links a{color:#374151;font-size:0.9rem}
    .cart-btn{background:#1f6feb;color:#fff;padding:8px 14px;border-radius:6px;font-size:0.9rem;display:flex;align-items:center;gap:6px}
    .cart-count{background:#fff;color:#1f6feb;border-radius:50%;font-size:11px;font-weight:700;padding:1px 6px;min-width:18px;text-align:center}
    .hero{background:#fff;border-radius:8px;margin:20px 0;padding:20px;border:1px solid #e6e9ee}
    .hero h1{font-size:1.4rem;margin-bottom:6px;color:#0f172a}
    .hero p{color:#6b7280;font-size:0.9rem;margin-bottom:14px}
    .controls{display:flex;gap:8px;flex-wrap:wrap}
    .controls select,.controls input{padding:9px 12px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#111827;font-size:0.88rem}
    .btn-search{padding:9px 16px;border-radius:6px;border:none;background:#1f6feb;color:#fff;cursor:pointer;font-weight:700;font-size:0.88rem}
    .btn-clear{padding:9px 14px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#6b7280;cursor:pointer;font-size:0.88rem}
    .result-info{font-size:0.82rem;color:#6b7280;margin:16px 0 8px}
    .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;padding-bottom:30px}
    .card{background:#fff;border-radius:8px;overflow:hidden;border:1px solid #e6e9ee;transition:box-shadow 0.2s,transform 0.2s}
    .card:hover{box-shadow:0 4px 14px rgba(0,0,0,0.09);transform:translateY(-2px)}
    .c-img{height:140px;background:#f7fafc;display:flex;align-items:center;justify-content:center;overflow:hidden}
    .c-img img{width:100%;height:140px;object-fit:cover}
    .c-img .icon{font-size:46px}
    .c-info{padding:12px}
    .stk{display:inline-block;padding:3px 9px;border-radius:999px;font-size:0.75rem;font-weight:700;margin-bottom:7px}
    .stk.in{background:#eef6ff;color:#1f6feb}
    .stk.out{background:#fdecea;color:#b71c1c}
    .c-info h3{font-size:0.88rem;margin-bottom:3px;color:#0f172a}
    .c-info .fit{font-size:0.78rem;color:#6b7280;margin-bottom:6px}
    .c-info .price{font-weight:700;font-size:0.95rem;margin-bottom:9px;color:#0f172a}
    .btn-add{width:100%;padding:8px;background:#1f6feb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:0.85rem;font-weight:700}
    .btn-add:hover{background:#1e40af}
    .btn-add:disabled{background:#d1d5db;cursor:not-allowed}
    .empty{text-align:center;padding:50px;color:#6b7280;grid-column:1/-1}
    .empty .e{font-size:2.5rem;margin-bottom:10px}
    footer{padding:16px 0;text-align:center;color:#6b7280;border-top:1px solid #e6e9ee;font-size:0.82rem;margin-top:10px}
    @media(max-width:650px){.nav-links{display:none}.controls{flex-direction:column}}
  </style>
</head>
<body>
<div class="pw">

  <nav class="navbar">
    <div class="logo">Auto<span>Parts</span>ZW</div>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="catalog.php">Catalog</a>
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['first_name']) ?>)</a>
        <?php if($_SESSION['account_type']==='admin'): ?>
          <a href="admin.php" style="color:#1f6feb;font-weight:700">Admin</a>
        <?php endif; ?>
      <?php else: ?>
        <a href="login.php">Login</a>
      <?php endif; ?>
    </div>
    <a href="cart.php" class="cart-btn">🛒 Cart <span class="cart-count"><?= $cartCount ?></span></a>
  </nav>

  <section class="hero">
    <h1>Parts Catalog</h1>
    <p>Browse all parts or filter by make, model and keyword.</p>
    <div class="controls">
      <select id="makeFilter">
        <option value="">All Makes</option>
        <?php foreach($makes as $make): ?>
          <option><?= htmlspecialchars($make) ?></option>
        <?php endforeach; ?>
      </select>
      <select id="modelFilter">
        <option value="">All Models</option>
      </select>
      <input type="text" id="searchQ" placeholder="Search part name..."/>
      <button class="btn-search" onclick="applyFilters()">Search</button>
      <button class="btn-clear" onclick="clearAll()">Clear</button>
    </div>
  </section>

  <p class="result-info" id="info"></p>
  <div class="grid" id="grid"></div>

  <footer>
    <p>&copy; 2025 AutoPartsZW &nbsp;|&nbsp; Nyasha Ernest Nyakamhanda &nbsp;|&nbsp; B242508B &nbsp;|&nbsp; NWE214</p>
  </footer>
</div>

<script>
var parts  = <?= json_encode($dbParts) ?>;
var mdls   = <?= json_encode($modelsData) ?>;

// Make → model dropdown
document.getElementById('makeFilter').addEventListener('change', function(){
  var ms = document.getElementById('modelFilter');
  ms.innerHTML = '<option value="">All Models</option>';
  (mdls[this.value] || []).forEach(function(m){
    var o = document.createElement('option');
    o.value = m; o.textContent = m; ms.appendChild(o);
  });
  applyFilters();
});

document.getElementById('modelFilter').addEventListener('change', applyFilters);
document.getElementById('searchQ').addEventListener('input', applyFilters);

function clearAll(){
  document.getElementById('makeFilter').value = '';
  document.getElementById('modelFilter').innerHTML = '<option value="">All Models</option>';
  document.getElementById('searchQ').value = '';
  applyFilters();
}

function applyFilters(){
  var make  = document.getElementById('makeFilter').value.toLowerCase();
  var model = document.getElementById('modelFilter').value.toLowerCase();
  var q     = document.getElementById('searchQ').value.trim().toLowerCase();
  var cat   = new URLSearchParams(window.location.search).get('category') || '';

  var filtered = parts.filter(function(p){
    if(make  && p.make.toLowerCase()     !== make)  return false;
    if(model && p.model.toLowerCase()    !== model) return false;
    if(cat   && p.category.toLowerCase() !== cat.toLowerCase()) return false;
    if(q){
      var haystack = (p.name+' '+p.make+' '+p.model+' '+p.category+' '+p.year_range).toLowerCase();
      if(!haystack.includes(q)) return false;
    }
    return true;
  });

  document.getElementById('info').textContent = 'Showing ' + filtered.length + ' part' + (filtered.length!==1?'s':'');
  render(filtered);
}

function render(list){
  var grid = document.getElementById('grid');
  grid.innerHTML = '';
  if(!list.length){
    grid.innerHTML = '<div class="empty"><div class="e">🔍</div>No parts found. Try clearing filters.</div>';
    return;
  }
  list.forEach(function(p){
    var card = document.createElement('div'); card.className = 'card';

    var imgDiv = document.createElement('div'); imgDiv.className = 'c-img';
    if(p.image && p.image.trim() !== ''){
      var img = document.createElement('img');
      img.src = encodeURI('images parts/' + p.image);
      img.alt = p.name;
      img.onerror = function(){ imgDiv.innerHTML = '<div class="icon">'+(p.icon||'🔧')+'</div>'; };
      imgDiv.appendChild(img);
    } else {
      imgDiv.innerHTML = '<div class="icon">'+(p.icon||'🔧')+'</div>';
    }

    var info = document.createElement('div'); info.className = 'c-info';

    var stk = document.createElement('span');
    stk.className = p.stock > 0 ? 'stk in' : 'stk out';
    stk.textContent = p.stock > 0 ? 'In Stock' : 'Out of Stock';

    var h3 = document.createElement('h3'); h3.textContent = p.name;
    var fit = document.createElement('div'); fit.className = 'fit';
    fit.textContent = p.make + ' ' + p.model + ' ' + (p.year_range||'');
    var pr = document.createElement('div'); pr.className = 'price';
    pr.textContent = '$' + parseFloat(p.price).toFixed(2);

    var form = document.createElement('form');
    form.method = 'POST'; form.action = 'catalog.php';
    var hi = document.createElement('input');
    hi.type='hidden'; hi.name='part_id'; hi.value=p.id;
    var btn = document.createElement('button');
    btn.type='submit'; btn.className='btn-add';
    btn.textContent = p.stock > 0 ? 'Add to Cart' : 'Out of Stock';
    btn.disabled = p.stock <= 0;
    form.appendChild(hi); form.appendChild(btn);

    info.appendChild(stk); info.appendChild(h3); info.appendChild(fit);
    info.appendChild(pr); info.appendChild(form);
    card.appendChild(imgDiv); card.appendChild(info);
    grid.appendChild(card);
  });
}

// On page load - read URL params
window.addEventListener('load', function(){
  var p = new URLSearchParams(window.location.search);
  var make = p.get('make')||''; var model = p.get('model')||''; var q = p.get('q')||'';
  if(make){
    document.getElementById('makeFilter').value = make;
    var ms = document.getElementById('modelFilter');
    ms.innerHTML = '<option value="">All Models</option>';
    (mdls[make]||[]).forEach(function(m){
      var o=document.createElement('option'); o.value=m; o.textContent=m; ms.appendChild(o);
    });
  }
  if(model) document.getElementById('modelFilter').value = model;
  if(q)     document.getElementById('searchQ').value = q;
  applyFilters();
});
</script>
</body>
</html>
