<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$today = date('Y-m-d');

// Today stats
$today_orders = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT COUNT(*) as cnt FROM orders WHERE DATE(created_at)='$today'"))['cnt'];

$today_revenue = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT SUM(total_amount) as rev FROM orders WHERE DATE(created_at)='$today' AND status='Served'"))['rev'] ?? 0;

$total_items_served = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT SUM(quantity) as qty FROM order_items oi
   JOIN orders o ON oi.order_id=o.id
   WHERE DATE(o.created_at)='$today' AND o.status='Served'"))['qty'] ?? 0;

// Weekly revenue (last 7 days)
$weekly = mysqli_query($conn,
  "SELECT DATE(created_at) as day, SUM(total_amount) as rev
   FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
   AND status='Served'
   GROUP BY DATE(created_at) ORDER BY day ASC");

// Top menu items
$top_items = mysqli_query($conn,
  "SELECT item_name, SUM(quantity) as total_qty, SUM(quantity*price) as total_rev
   FROM order_items GROUP BY item_name ORDER BY total_qty DESC LIMIT 6");

// Category revenue
$cat_rev = mysqli_query($conn,
  "SELECT m.category, SUM(oi.quantity*oi.price) as rev
   FROM order_items oi JOIN menu_items m ON oi.menu_item_id=m.id
   GROUP BY m.category ORDER BY rev DESC");

// Recent transactions
$transactions = mysqli_query($conn,
  "SELECT o.*, t.table_number FROM orders o
   LEFT JOIN hotel_tables t ON o.table_id=t.id
   ORDER BY o.created_at DESC LIMIT 8");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Reports — Grandeur</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#0A0A0B;color:#F0EDE8;display:flex}
.main-content{flex:1;overflow-y:auto}
.top-bar{padding:18px 28px;border-bottom:1px solid rgba(201,168,76,.08);background:#0A0A0B;position:sticky;top:0;z-index:50}
.top-bar h2{font-size:1.5rem;font-weight:300}
.content{padding:24px}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat-card{background:#111115;border:1px solid rgba(201,168,76,.1);border-radius:14px;padding:20px}
.stat-icon{font-size:24px;margin-bottom:10px}
.stat-label{font-size:.72rem;color:#5C5A55;letter-spacing:1px;text-transform:uppercase}
.stat-value{font-size:1.8rem;color:#C9A84C;font-weight:600;margin:6px 0 4px}
.reports-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.card{background:#111115;border:1px solid rgba(201,168,76,.1);border-radius:14px;padding:22px}
.card-title{font-size:1rem;font-weight:500;margin-bottom:18px;color:#F0EDE8}
.bar-row{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.bar-label{font-size:.75rem;color:#9B9890;min-width:80px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bar-track{flex:1;height:22px;background:#1A1A20;border-radius:4px;overflow:hidden}
.bar-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,#8B6914,#C9A84C);display:flex;align-items:center;justify-content:flex-end;padding-right:8px;transition:width .8s ease;min-width:30px}
.bar-fill span{font-size:.7rem;font-weight:600;color:#0A0A0B;white-space:nowrap}
table{width:100%;border-collapse:collapse;font-size:.83rem}
thead th{text-align:left;padding:9px 12px;color:#5C5A55;font-size:.73rem;border-bottom:1px solid rgba(201,168,76,.1)}
tbody td{padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.04)}
tbody tr:last-child td{border-bottom:none}
.badge{display:inline-block;padding:3px 8px;border-radius:20px;font-size:.7rem;font-weight:600}
.badge-pending{background:rgba(224,167,82,.15);color:#E0A752;border:1px solid rgba(224,167,82,.3)}
.badge-preparing{background:rgba(82,146,224,.15);color:#5292E0;border:1px solid rgba(82,146,224,.3)}
.badge-ready{background:rgba(201,168,76,.15);color:#C9A84C;border:1px solid rgba(201,168,76,.3)}
.badge-served{background:rgba(82,201,122,.15);color:#52C97A;border:1px solid rgba(82,201,122,.3)}
.badge-cancelled{background:rgba(224,82,82,.15);color:#E05252;border:1px solid rgba(224,82,82,.3)}
.empty-row{text-align:center;padding:20px;color:#5C5A55;font-size:.82rem}
</style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
  <div class="top-bar"><h2>Reports & Analytics</h2></div>
  <div class="content">

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-label">Today's Orders</div>
        <div class="stat-value"><?= $today_orders ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-label">Today's Revenue</div>
        <div class="stat-value">₹<?= number_format($today_revenue) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🍽️</div>
        <div class="stat-label">Items Served Today</div>
        <div class="stat-value"><?= $total_items_served ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">👨‍🍳</div>
        <div class="stat-label">Total Staff</div>
        <div class="stat-value"><?= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as cnt FROM staff"))['cnt'] ?></div>
      </div>
    </div>

    <div class="reports-grid">

      <!-- Weekly Revenue -->
      <div class="card">
        <div class="card-title">Weekly Revenue</div>
        <?php
        $weekly_data = [];
        while($w = mysqli_fetch_assoc($weekly)) $weekly_data[] = $w;
        $max_rev = max(array_column($weekly_data,'rev') ?: [1]);
        if(count($weekly_data) > 0):
          foreach($weekly_data as $w):
            $pct = round($w['rev']/$max_rev*100);
        ?>
        <div class="bar-row">
          <span class="bar-label"><?= date('D d', strtotime($w['day'])) ?></span>
          <div class="bar-track">
            <div class="bar-fill" style="width:<?= $pct ?>%">
              <span>₹<?= number_format($w['rev']) ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; else: ?>
        <p class="empty-row">No revenue data yet</p>
        <?php endif; ?>
      </div>

      <!-- Top Menu Items -->
      <div class="card">
        <div class="card-title">Top Selling Items</div>
        <?php
        $top_data = [];
        while($t = mysqli_fetch_assoc($top_items)) $top_data[] = $t;
        $max_qty = max(array_column($top_data,'total_qty') ?: [1]);
        if(count($top_data) > 0):
          foreach($top_data as $t):
            $pct = round($t['total_qty']/$max_qty*100);
        ?>
        <div class="bar-row">
          <span class="bar-label"><?= $t['item_name'] ?></span>
          <div class="bar-track">
            <div class="bar-fill" style="width:<?= $pct ?>%">
              <span><?= $t['total_qty'] ?> orders</span>
            </div>
          </div>
        </div>
        <?php endforeach; else: ?>
        <p class="empty-row">No orders yet</p>
        <?php endif; ?>
      </div>

      <!-- Category Revenue -->
      <div class="card">
        <div class="card-title">Revenue by Category</div>
        <?php
        $cat_data = [];
        while($c = mysqli_fetch_assoc($cat_rev)) $cat_data[] = $c;
        $max_cat = max(array_column($cat_data,'rev') ?: [1]);
        if(count($cat_data) > 0):
          foreach($cat_data as $c):
            $pct = round($c['rev']/$max_cat*100);
        ?>
        <div class="bar-row">
          <span class="bar-label"><?= $c['category'] ?></span>
          <div class="bar-track">
            <div class="bar-fill" style="width:<?= $pct ?>%">
              <span>₹<?= number_format($c['rev']) ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; else: ?>
        <p class="empty-row">No data yet</p>
        <?php endif; ?>
      </div>

      <!-- Recent Transactions -->
      <div class="card">
        <div class="card-title">Recent Transactions</div>
        <table>
          <thead>
            <tr><th>Order</th><th>Table</th><th>Amount</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php
            $has = false;
            while($tr = mysqli_fetch_assoc($transactions)):
              $has = true;
            ?>
            <tr>
              <td><strong><?= $tr['order_number'] ?></strong></td>
              <td><?= $tr['table_number'] ?></td>
              <td style="color:#C9A84C">₹<?= number_format($tr['total_amount']) ?></td>
              <td><span class="badge badge-<?= strtolower($tr['status']) ?>"><?= $tr['status'] ?></span></td>
            </tr>
            <?php endwhile; ?>
            <?php if(!$has): ?>
            <tr><td colspan="4" class="empty-row">No transactions yet</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>
</body>
</html>