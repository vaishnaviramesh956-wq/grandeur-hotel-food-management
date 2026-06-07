<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$today = date('Y-m-d');

$total_orders = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT COUNT(*) as cnt FROM orders WHERE DATE(created_at)='$today'"))['cnt'];

$total_revenue = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT SUM(total_amount) as rev FROM orders WHERE DATE(created_at)='$today' AND status='Served'"))['rev'] ?? 0;

$pending = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT COUNT(*) as cnt FROM orders WHERE status='Pending'"))['cnt'];

$occupied = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT COUNT(*) as cnt FROM hotel_tables WHERE status='occupied'"))['cnt'];

$total_tables = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT COUNT(*) as cnt FROM hotel_tables"))['cnt'];

$recent_orders = mysqli_query($conn,
  "SELECT o.*, t.table_number FROM orders o
   LEFT JOIN hotel_tables t ON o.table_id = t.id
   ORDER BY o.created_at DESC LIMIT 8");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dashboard — Grandeur</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#0A0A0B;color:#F0EDE8;display:flex}
.main-content{flex:1;overflow-y:auto}
.top-bar{padding:18px 28px;border-bottom:1px solid rgba(201,168,76,.08);display:flex;align-items:center;justify-content:space-between;background:#0A0A0B;position:sticky;top:0;z-index:50}
.top-bar h2{font-size:1.5rem;font-weight:300;color:#F0EDE8}
.top-bar span{font-size:.8rem;color:#9B9890}
.content{padding:24px}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat-card{background:#111115;border:1px solid rgba(201,168,76,.1);border-radius:14px;padding:20px}
.stat-icon{font-size:24px;margin-bottom:10px}
.stat-label{font-size:.72rem;color:#5C5A55;letter-spacing:1px;text-transform:uppercase}
.stat-value{font-size:2rem;color:#C9A84C;font-weight:600;margin:6px 0 4px}
.stat-sub{font-size:.75rem;color:#9B9890}
.card{background:#111115;border:1px solid rgba(201,168,76,.1);border-radius:14px;padding:22px}
.card-title{font-size:1.1rem;font-weight:400;margin-bottom:16px;color:#F0EDE8}
table{width:100%;border-collapse:collapse;font-size:.85rem}
thead th{text-align:left;padding:10px 14px;color:#5C5A55;font-size:.75rem;border-bottom:1px solid rgba(201,168,76,.1)}
tbody td{padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.04)}
tbody tr:last-child td{border-bottom:none}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600}
.badge-pending{background:rgba(224,167,82,.15);color:#E0A752;border:1px solid rgba(224,167,82,.3)}
.badge-preparing{background:rgba(82,146,224,.15);color:#5292E0;border:1px solid rgba(82,146,224,.3)}
.badge-ready{background:rgba(201,168,76,.15);color:#C9A84C;border:1px solid rgba(201,168,76,.3)}
.badge-served{background:rgba(82,201,122,.15);color:#52C97A;border:1px solid rgba(82,201,122,.3)}
.badge-cancelled{background:rgba(224,82,82,.15);color:#E05252;border:1px solid rgba(224,82,82,.3)}
</style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
  <div class="top-bar">
    <h2>Dashboard</h2>
    <span><?= date('l, d F Y') ?></span>
  </div>
  <div class="content">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">🛎️</div>
        <div class="stat-label">Today's Orders</div>
        <div class="stat-value"><?= $total_orders ?></div>
        <div class="stat-sub">Total orders today</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-label">Revenue Today</div>
        <div class="stat-value">₹<?= number_format($total_revenue) ?></div>
        <div class="stat-sub">From served orders</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🪑</div>
        <div class="stat-label">Tables Occupied</div>
        <div class="stat-value"><?= $occupied ?>/<?= $total_tables ?></div>
        <div class="stat-sub"><?= $total_tables - $occupied ?> tables free</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-label">Pending Orders</div>
        <div class="stat-value"><?= $pending ?></div>
        <div class="stat-sub">Needs attention</div>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Recent Orders</div>
      <table>
        <thead>
          <tr>
            <th>Order #</th>
            <th>Table</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          <?php if(mysqli_num_rows($recent_orders) > 0): ?>
          <?php while($o = mysqli_fetch_assoc($recent_orders)): ?>
          <tr>
            <td><strong><?= $o['order_number'] ?></strong></td>
            <td><?= $o['table_number'] ?></td>
            <td><?= $o['customer_name'] ?></td>
            <td style="color:#C9A84C">₹<?= number_format($o['total_amount']) ?></td>
            <td><span class="badge badge-<?= strtolower($o['status']) ?>"><?= $o['status'] ?></span></td>
            <td><?= date('h:i A', strtotime($o['created_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
          <?php else: ?>
          <tr><td colspan="6" style="text-align:center;padding:30px;color:#5C5A55">No orders yet today</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>