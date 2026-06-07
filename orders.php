<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// Update order status
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id=$id");
    header("Location: orders.php"); exit();
}

// Cancel order
if (isset($_GET['cancel'])) {
    $id = $_GET['cancel'];
    mysqli_query($conn, "UPDATE orders SET status='Cancelled' WHERE id=$id");
    header("Location: orders.php"); exit();
}

$filter = $_GET['filter'] ?? 'All';
$where = "WHERE 1=1";
if ($filter !== 'All') $where .= " AND o.status='$filter'";

$orders = mysqli_query($conn,
  "SELECT o.*, t.table_number FROM orders o
   LEFT JOIN hotel_tables t ON o.table_id = t.id
   $where ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Orders — Grandeur</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#0A0A0B;color:#F0EDE8;display:flex}
.main-content{flex:1;overflow-y:auto}
.top-bar{padding:18px 28px;border-bottom:1px solid rgba(201,168,76,.08);display:flex;align-items:center;justify-content:space-between;background:#0A0A0B;position:sticky;top:0;z-index:50}
.top-bar h2{font-size:1.5rem;font-weight:300}
.content{padding:24px}
.filter-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px}
.filter-tab{padding:6px 16px;border-radius:20px;font-size:.78rem;font-weight:500;background:#111115;border:1px solid #32323F;color:#9B9890;text-decoration:none;transition:all .2s}
.filter-tab:hover,.filter-tab.active{background:rgba(201,168,76,.15);border-color:rgba(201,168,76,.4);color:#C9A84C}
.orders-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px}
.order-card{background:#111115;border:1px solid rgba(201,168,76,.1);border-radius:14px;padding:18px;transition:border-color .25s}
.order-card:hover{border-color:rgba(201,168,76,.25)}
.order-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.order-num{font-size:1.3rem;color:#C9A84C;font-weight:600}
.order-time{font-size:.75rem;color:#5C5A55}
.order-table{font-size:.78rem;color:#9B9890;margin-bottom:10px}
.order-items{margin-bottom:12px}
.order-item-line{display:flex;justify-content:space-between;font-size:.82rem;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.03)}
.order-item-line:last-child{border-bottom:none}
.order-notes{font-size:.75rem;color:#9B9890;margin-bottom:10px;font-style:italic}
.order-footer{display:flex;justify-content:space-between;align-items:center;padding-top:10px;border-top:1px solid rgba(201,168,76,.1)}
.order-total{font-size:1.2rem;color:#C9A84C;font-weight:600}
.order-actions{display:flex;gap:8px;margin-top:12px}
.order-actions a{flex:1;text-align:center;padding:7px;border-radius:8px;font-size:.78rem;font-weight:500;text-decoration:none;transition:all .2s}
.btn-next{background:rgba(82,201,122,.15);color:#52C97A;border:1px solid rgba(82,201,122,.3)}
.btn-next:hover{background:rgba(82,201,122,.25)}
.btn-cancel{background:rgba(224,82,82,.15);color:#E05252;border:1px solid rgba(224,82,82,.3)}
.btn-cancel:hover{background:rgba(224,82,82,.25)}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600}
.badge-pending{background:rgba(224,167,82,.15);color:#E0A752;border:1px solid rgba(224,167,82,.3)}
.badge-preparing{background:rgba(82,146,224,.15);color:#5292E0;border:1px solid rgba(82,146,224,.3)}
.badge-ready{background:rgba(201,168,76,.15);color:#C9A84C;border:1px solid rgba(201,168,76,.3)}
.badge-served{background:rgba(82,201,122,.15);color:#52C97A;border:1px solid rgba(82,201,122,.3)}
.badge-cancelled{background:rgba(224,82,82,.15);color:#E05252;border:1px solid rgba(224,82,82,.3)}
.empty{text-align:center;padding:60px;color:#5C5A55;grid-column:1/-1}
.empty div{font-size:40px;margin-bottom:10px}
</style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
  <div class="top-bar">
    <h2>Order Management</h2>
  </div>
  <div class="content">
    <!-- Filter Tabs -->
    <div class="filter-tabs">
      <?php foreach(['All','Pending','Preparing','Ready','Served','Cancelled'] as $f): ?>
      <a href="orders.php?filter=<?= $f ?>" class="filter-tab <?= $filter===$f?'active':'' ?>"><?= $f ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Orders Grid -->
    <div class="orders-grid">
      <?php if(mysqli_num_rows($orders) > 0): ?>
      <?php while($o = mysqli_fetch_assoc($orders)):
        $items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id=".$o['id']);
        $next = ['Pending'=>'Preparing','Preparing'=>'Ready','Ready'=>'Served'];
      ?>
      <div class="order-card">
        <div class="order-header">
          <div class="order-num"><?= $o['order_number'] ?></div>
          <div class="order-time"><?= date('h:i A', strtotime($o['created_at'])) ?></div>
        </div>
        <div class="order-table">🪑 <?= $o['table_number'] ?> · 👤 <?= $o['customer_name'] ?></div>
        <div class="order-items">
          <?php while($item = mysqli_fetch_assoc($items)): ?>
          <div class="order-item-line">
            <span><?= $item['item_name'] ?> × <?= $item['quantity'] ?></span>
            <span>₹<?= $item['price'] * $item['quantity'] ?></span>
          </div>
          <?php endwhile; ?>
        </div>
        <?php if($o['notes']): ?>
        <div class="order-notes">📝 <?= $o['notes'] ?></div>
        <?php endif; ?>
        <div class="order-footer">
          <span class="badge badge-<?= strtolower($o['status']) ?>"><?= $o['status'] ?></span>
          <span class="order-total">₹<?= number_format($o['total_amount']) ?></span>
        </div>
        <div class="order-actions">
          <?php if(isset($next[$o['status']])): ?>
          <a href="orders.php?id=<?= $o['id'] ?>&status=<?= $next[$o['status']] ?>&filter=<?= $filter ?>" class="btn-next">→ <?= $next[$o['status']] ?></a>
          <?php endif; ?>
          <?php if($o['status'] !== 'Cancelled' && $o['status'] !== 'Served'): ?>
          <a href="orders.php?cancel=<?= $o['id'] ?>&filter=<?= $filter ?>" class="btn-cancel" onclick="return confirm('Cancel this order?')">Cancel</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; ?>
      <?php else: ?>
      <div class="empty"><div>📋</div><p>No orders found</p></div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>