<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// Add new item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category = $_POST['category'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $emoji = mysqli_real_escape_string($conn, $_POST['emoji']);
    mysqli_query($conn, "INSERT INTO menu_items (name, category, price, type, emoji) VALUES ('$name', '$category', '$price', '$type', '$emoji')");
}

// Toggle availability
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $current = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_available FROM menu_items WHERE id=$id"))['is_available'];
    $new = $current ? 0 : 1;
    mysqli_query($conn, "UPDATE menu_items SET is_available=$new WHERE id=$id");
    header("Location: menu.php"); exit();
}

// Delete item
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM menu_items WHERE id=$id");
    header("Location: menu.php"); exit();
}

$category_filter = $_GET['cat'] ?? 'All';
$search = $_GET['search'] ?? '';

$where = "WHERE 1=1";
if ($category_filter !== 'All') $where .= " AND category='$category_filter'";
if ($search) $where .= " AND name LIKE '%$search%'";

$items = mysqli_query($conn, "SELECT * FROM menu_items $where ORDER BY category, name");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Menu — Grandeur</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#0A0A0B;color:#F0EDE8;display:flex}
.main-content{flex:1;overflow-y:auto}
.top-bar{padding:18px 28px;border-bottom:1px solid rgba(201,168,76,.08);display:flex;align-items:center;justify-content:space-between;background:#0A0A0B;position:sticky;top:0;z-index:50}
.top-bar h2{font-size:1.5rem;font-weight:300}
.content{padding:24px}
.toolbar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px}
.search-box{position:relative}
.search-box input{padding:10px 14px 10px 36px;background:#111115;border:1px solid #32323F;border-radius:9px;color:#F0EDE8;font-size:.85rem;width:220px}
.search-box input:focus{outline:none;border-color:#C9A84C}
.search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#5C5A55}
.filter-tabs{display:flex;gap:6px;flex-wrap:wrap}
.filter-tab{padding:6px 14px;border-radius:20px;cursor:pointer;font-size:.78rem;font-weight:500;background:#111115;border:1px solid #32323F;color:#9B9890;text-decoration:none;transition:all .2s}
.filter-tab:hover,.filter-tab.active{background:rgba(201,168,76,.15);border-color:rgba(201,168,76,.4);color:#C9A84C}
.btn{padding:10px 18px;border-radius:9px;border:none;cursor:pointer;font-size:.85rem;font-weight:500;transition:all .2s}
.btn-gold{background:#C9A84C;color:#0A0A0B}
.btn-gold:hover{background:#E8C97A}
.menu-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:14px}
.menu-card{background:#111115;border:1px solid rgba(201,168,76,.1);border-radius:14px;overflow:hidden;transition:all .25s}
.menu-card:hover{border-color:rgba(201,168,76,.3);transform:translateY(-2px)}
.menu-card-img{height:120px;background:#1A1A20;display:flex;align-items:center;justify-content:center;font-size:48px;position:relative}
.avail-dot{position:absolute;top:10px;right:10px;width:10px;height:10px;border-radius:50%}
.dot-on{background:#52C97A;box-shadow:0 0 6px #52C97A}
.dot-off{background:#E05252}
.menu-card-body{padding:14px}
.menu-card-name{font-size:.9rem;font-weight:500;margin-bottom:4px}
.menu-card-cat{font-size:.72rem;color:#5C5A55;margin-bottom:10px}
.menu-card-footer{display:flex;align-items:center;justify-content:space-between}
.price{font-size:1.1rem;color:#C9A84C;font-weight:600}
.card-actions{display:flex;gap:6px}
.btn-sm{padding:6px 12px;font-size:.75rem;border-radius:7px;border:none;cursor:pointer;font-weight:500}
.btn-toggle{background:rgba(82,201,122,.15);color:#52C97A;border:1px solid rgba(82,201,122,.3)}
.btn-del{background:rgba(224,82,82,.15);color:#E05252;border:1px solid rgba(224,82,82,.3)}
.badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:600}
.badge-veg{background:rgba(82,201,122,.12);color:#52C97A;border:1px solid rgba(82,201,122,.25)}
.badge-nonveg{background:rgba(224,82,82,.12);color:#E05252;border:1px solid rgba(224,82,82,.25)}
/* Modal */
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:200;place-items:center}
.modal-backdrop.open{display:grid}
.modal{background:#111115;border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:28px;width:90%;max-width:420px}
.modal h3{font-size:1.3rem;font-weight:400;margin-bottom:20px;color:#F0EDE8}
.form-group{margin-bottom:16px}
label{display:block;font-size:.75rem;color:#9B9890;margin-bottom:6px}
input,select{width:100%;padding:10px 13px;background:#1A1A20;border:1px solid #32323F;border-radius:8px;color:#F0EDE8;font-size:.88rem}
input:focus,select:focus{outline:none;border-color:#C9A84C}
select option{background:#1A1A20}
.modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:20px}
.btn-outline{background:transparent;border:1px solid #32323F;color:#9B9890;padding:9px 18px;border-radius:8px;cursor:pointer;font-size:.85rem}
.empty{text-align:center;padding:50px;color:#5C5A55}
.empty div{font-size:40px;margin-bottom:10px}
</style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
  <div class="top-bar">
    <h2>Menu Items</h2>
    <button class="btn btn-gold" onclick="document.getElementById('add-modal').classList.add('open')">+ Add Item</button>
  </div>
  <div class="content">
    <!-- Toolbar -->
    <div class="toolbar">
      <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <div class="search-box">
          <span class="search-icon">🔍</span>
          <input type="text" name="search" placeholder="Search items..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="filter-tabs">
          <?php foreach(['All','Starters','Main Course','Beverages','Desserts'] as $cat): ?>
          <button type="submit" name="cat" value="<?= $cat ?>" class="filter-tab <?= $category_filter===$cat?'active':'' ?>"><?= $cat ?></button>
          <?php endforeach; ?>
        </div>
      </form>
    </div>

    <!-- Menu Grid -->
    <div class="menu-grid">
      <?php if(mysqli_num_rows($items) > 0): ?>
      <?php while($item = mysqli_fetch_assoc($items)): ?>
      <div class="menu-card">
        <div class="menu-card-img">
          <?= $item['emoji'] ?>
          <div class="avail-dot <?= $item['is_available'] ? 'dot-on' : 'dot-off' ?>"></div>
        </div>
        <div class="menu-card-body">
          <div class="menu-card-name"><?= $item['name'] ?></div>
          <div class="menu-card-cat"><?= $item['category'] ?> · <span class="badge badge-<?= $item['type'] ?>"><?= $item['type']==='veg'?'Veg':'Non-Veg' ?></span></div>
          <div class="menu-card-footer">
            <div class="price">₹<?= $item['price'] ?></div>
            <div class="card-actions">
              <a href="menu.php?toggle=<?= $item['id'] ?>&cat=<?= $category_filter ?>" class="btn-sm btn-toggle"><?= $item['is_available']?'Disable':'Enable' ?></a>
              <a href="menu.php?delete=<?= $item['id'] ?>&cat=<?= $category_filter ?>" class="btn-sm btn-del" onclick="return confirm('Delete this item?')">✕</a>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
      <?php else: ?>
      <div class="empty" style="grid-column:1/-1"><div>🍽️</div><p>No items found</p></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Add Item Modal -->
<div class="modal-backdrop" id="add-modal">
  <div class="modal">
    <h3>Add Menu Item</h3>
    <form method="POST">
      <div class="form-group">
        <label>ITEM NAME</label>
        <input type="text" name="name" placeholder="e.g. Butter Chicken" required>
      </div>
      <div class="form-group">
        <label>CATEGORY</label>
        <select name="category">
          <option>Starters</option>
          <option>Main Course</option>
          <option>Beverages</option>
          <option>Desserts</option>
        </select>
      </div>
      <div class="form-group">
        <label>PRICE (₹)</label>
        <input type="number" name="price" placeholder="0" required>
      </div>
      <div class="form-group">
        <label>TYPE</label>
        <select name="type">
          <option value="veg">Vegetarian</option>
          <option value="nonveg">Non-Vegetarian</option>
        </select>
      </div>
      <div class="form-group">
        <label>EMOJI</label>
        <input type="text" name="emoji" placeholder="🍛" maxlength="2" style="width:70px;font-size:1.3rem">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-outline" onclick="document.getElementById('add-modal').classList.remove('open')">Cancel</button>
        <button type="submit" name="add_item" class="btn btn-gold">Add Item</button>
      </div>
    </form>
  </div>
</div>
<script>
document.getElementById('add-modal').addEventListener('click', function(e){
  if(e.target===this) this.classList.remove('open');
});
</script>
</body>
</html>