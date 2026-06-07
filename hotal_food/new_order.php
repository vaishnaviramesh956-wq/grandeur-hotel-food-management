<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// Place Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $table_id = $_POST['table_id'];
    $customer = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $items = json_decode($_POST['cart_items'], true);
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['qty'];
    }
    $order_number = 'ORD' . strtoupper(uniqid());
    mysqli_query($conn, "INSERT INTO orders (order_number, table_id, customer_name, total_amount, notes, status)
        VALUES ('$order_number', '$table_id', '$customer', '$total', '$notes', 'Pending')");
    $order_id = mysqli_insert_id($conn);
    foreach ($items as $item) {
        $iname = mysqli_real_escape_string($conn, $item['name']);
        $iprice = $item['price'];
        $iqty = $item['qty'];
        $mid = $item['id'];
        mysqli_query($conn, "INSERT INTO order_items (order_id, menu_item_id, item_name, quantity, price)
            VALUES ('$order_id', '$mid', '$iname', '$iqty', '$iprice')");
    }
    // Update table status
    mysqli_query($conn, "UPDATE hotel_tables SET status='occupied' WHERE id='$table_id'");
    header("Location: orders.php");
    exit();
}

$menu_items = mysqli_query($conn, "SELECT * FROM menu_items WHERE is_available=1 ORDER BY category, name");
$tables = mysqli_query($conn, "SELECT * FROM hotel_tables WHERE status='available' ORDER BY table_number");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>New Order — Grandeur</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#0A0A0B;color:#F0EDE8;display:flex}
.main-content{flex:1;overflow-y:auto}
.top-bar{padding:18px 28px;border-bottom:1px solid rgba(201,168,76,.08);background:#0A0A0B;position:sticky;top:0;z-index:50}
.top-bar h2{font-size:1.5rem;font-weight:300}
.content{padding:24px}
.layout{display:grid;grid-template-columns:1.3fr 1fr;gap:20px}
.card{background:#111115;border:1px solid rgba(201,168,76,.1);border-radius:14px;padding:20px}
.card-title{font-size:1rem;font-weight:500;margin-bottom:16px;color:#F0EDE8}
.filter-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.filter-tab{padding:5px 12px;border-radius:20px;font-size:.75rem;font-weight:500;background:#1A1A20;border:1px solid #32323F;color:#9B9890;cursor:pointer;transition:all .2s}
.filter-tab.active{background:rgba(201,168,76,.15);border-color:rgba(201,168,76,.4);color:#C9A84C}
.menu-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;max-height:520px;overflow-y:auto;padding-right:4px}
.menu-item{background:#1A1A20;border:1px solid #32323F;border-radius:10px;padding:12px;cursor:pointer;transition:all .2s;text-align:center}
.menu-item:hover{border-color:#C9A84C;background:rgba(201,168,76,.06)}
.menu-item .emoji{font-size:28px;display:block;margin-bottom:6px}
.menu-item .iname{font-size:.78rem;font-weight:500;margin-bottom:2px}
.menu-item .icat{font-size:.68rem;color:#5C5A55;margin-bottom:6px}
.menu-item .iprice{font-size:.9rem;color:#C9A84C;font-weight:600}
.form-group{margin-bottom:14px}
label{display:block;font-size:.73rem;color:#9B9890;margin-bottom:5px;letter-spacing:.5px}
input,select{width:100%;padding:10px 12px;background:#1A1A20;border:1px solid #32323F;border-radius:8px;color:#F0EDE8;font-size:.85rem}
input:focus,select:focus{outline:none;border-color:#C9A84C}
select option{background:#1A1A20}
.cart-items{min-height:180px;max-height:300px;overflow-y:auto;margin-bottom:14px}
.cart-item{display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05)}
.cart-item:last-child{border-bottom:none}
.ci-name{flex:1;font-size:.82rem}
.qty-ctrl{display:flex;align-items:center;gap:6px}
.qty-btn{width:24px;height:24px;border-radius:6px;border:1px solid #32323F;background:#1A1A20;color:#F0EDE8;cursor:pointer;font-size:14px;display:grid;place-items:center;transition:all .15s}
.qty-btn:hover{border-color:#C9A84C;color:#C9A84C}
.qty-num{font-size:.82rem;min-width:18px;text-align:center}
.ci-price{font-size:.82rem;color:#C9A84C;min-width:55px;text-align:right}
.ci-remove{color:#5C5A55;cursor:pointer;font-size:14px;transition:color .15s}
.ci-remove:hover{color:#E05252}
.cart-total{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-top:1px solid rgba(201,168,76,.15);margin-bottom:14px}
.cart-total .label{font-size:.85rem;color:#9B9890}
.cart-total .val{font-size:1.5rem;color:#C9A84C;font-weight:600}
.btn-gold{width:100%;padding:12px;background:#C9A84C;color:#0A0A0B;border:none;border-radius:9px;font-size:.95rem;font-weight:600;cursor:pointer;transition:background .2s}
.btn-gold:hover{background:#E8C97A}
.empty-cart{text-align:center;padding:30px;color:#5C5A55;font-size:.85rem}
.empty-cart div{font-size:32px;margin-bottom:8px}
</style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
  <div class="top-bar"><h2>New Order</h2></div>
  <div class="content">
    <div class="layout">
      <!-- Menu Side -->
      <div class="card">
        <div class="card-title">Select Items</div>
        <div class="filter-tabs">
          <?php foreach(['All','Starters','Main Course','Beverages','Desserts'] as $cat): ?>
          <div class="filter-tab <?= $cat==='All'?'active':'' ?>" onclick="filterCat('<?= $cat ?>',this)"><?= $cat ?></div>
          <?php endforeach; ?>
        </div>
        <div class="menu-grid" id="menu-grid">
          <?php
          $all_items = [];
          mysqli_data_seek($menu_items, 0);
          while($m = mysqli_fetch_assoc($menu_items)) { $all_items[] = $m; }
          foreach($all_items as $m): ?>
          <div class="menu-item" data-cat="<?= $m['category'] ?>" onclick="addToCart(<?= $m['id'] ?>,'<?= addslashes($m['name']) ?>',<?= $m['price'] ?>)">
            <span class="emoji"><?= $m['emoji'] ?></span>
            <div class="iname"><?= $m['name'] ?></div>
            <div class="icat"><?= $m['category'] ?></div>
            <div class="iprice">₹<?= $m['price'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Order Side -->
      <div class="card">
        <div class="card-title">Order Summary</div>
        <form method="POST" id="order-form">
          <div class="form-group">
            <label>TABLE NUMBER</label>
            <select name="table_id" required>
              <option value="">— Select Table —</option>
              <?php while($t = mysqli_fetch_assoc($tables)): ?>
              <option value="<?= $t['id'] ?>"><?= $t['table_number'] ?> (<?= $t['capacity'] ?> seats)</option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>CUSTOMER NAME</label>
            <input type="text" name="customer_name" placeholder="Guest name...">
          </div>
          <div class="form-group">
            <label>SPECIAL INSTRUCTIONS</label>
            <input type="text" name="notes" placeholder="Allergies, preferences...">
          </div>
          <input type="hidden" name="cart_items" id="cart-data">

          <div class="cart-items" id="cart-list">
            <div class="empty-cart"><div>🛒</div>Click items to add</div>
          </div>
          <div class="cart-total">
            <span class="label">Total Amount</span>
            <span class="val" id="cart-total">₹0</span>
          </div>
          <button type="submit" name="place_order" class="btn-gold" onclick="return prepareCart()">Place Order →</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
let cart = [];

function addToCart(id, name, price) {
  const existing = cart.find(c => c.id === id);
  if (existing) existing.qty++;
  else cart.push({ id, name, price, qty: 1 });
  renderCart();
}

function changeQty(id, delta) {
  const c = cart.find(c => c.id === id);
  if (!c) return;
  c.qty += delta;
  if (c.qty <= 0) cart = cart.filter(c => c.id !== id);
  renderCart();
}

function renderCart() {
  const list = document.getElementById('cart-list');
  if (!cart.length) {
    list.innerHTML = '<div class="empty-cart"><div>🛒</div>Click items to add</div>';
    document.getElementById('cart-total').textContent = '₹0';
    return;
  }
  list.innerHTML = cart.map(c => `
    <div class="cart-item">
      <div class="ci-name">${c.name}</div>
      <div class="qty-ctrl">
        <button type="button" class="qty-btn" onclick="changeQty(${c.id},-1)">−</button>
        <span class="qty-num">${c.qty}</span>
        <button type="button" class="qty-btn" onclick="changeQty(${c.id},1)">+</button>
      </div>
      <div class="ci-price">₹${c.price * c.qty}</div>
      <span class="ci-remove" onclick="changeQty(${c.id},-99)">✕</span>
    </div>
  `).join('');
  const total = cart.reduce((s, c) => s + c.price * c.qty, 0);
  document.getElementById('cart-total').textContent = '₹' + total;
}

function filterCat(cat, el) {
  document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.querySelectorAll('.menu-item').forEach(item => {
    item.style.display = (cat === 'All' || item.dataset.cat === cat) ? 'block' : 'none';
  });
}

function prepareCart() {
  if (!cart.length) { alert('Add items to the order first!'); return false; }
  document.getElementById('cart-data').value = JSON.stringify(cart);
  return true;
}
</script>
</body>
</html>