<?php if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit(); } ?>
<aside style="width:220px;background:#111115;border-right:1px solid rgba(201,168,76,.1);display:flex;flex-direction:column;min-height:100vh;position:sticky;top:0">
  <div style="padding:20px 16px;border-bottom:1px solid rgba(201,168,76,.08)">
    <div style="display:flex;align-items:center;gap:10px">
      <div style="width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,#C9A84C,#8B6914);display:grid;place-items:center;font-size:16px">🍽️</div>
      <div>
        <div style="font-size:1rem;color:#C9A84C;letter-spacing:2px">GRANDEUR</div>
        <div style="font-size:.65rem;color:#5C5A55;letter-spacing:1px">FOOD MANAGEMENT</div>
      </div>
    </div>
  </div>
  <nav style="padding:12px 10px;flex:1">
    <div style="font-size:.65rem;color:#5C5A55;letter-spacing:2px;padding:8px 8px 4px;text-transform:uppercase">Main</div>
    <a href="dashboard.php" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:9px;color:<?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'#C9A84C':'#9B9890' ?>;background:<?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'rgba(201,168,76,.12)':'transparent' ?>;text-decoration:none;font-size:.85rem;margin-bottom:2px">📊 Dashboard</a>
    <a href="new_order.php" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:9px;color:<?= basename($_SERVER['PHP_SELF'])=='new_order.php'?'#C9A84C':'#9B9890' ?>;background:<?= basename($_SERVER['PHP_SELF'])=='new_order.php'?'rgba(201,168,76,.12)':'transparent' ?>;text-decoration:none;font-size:.85rem;margin-bottom:2px">➕ New Order</a>
    <a href="orders.php" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:9px;color:<?= basename($_SERVER['PHP_SELF'])=='orders.php'?'#C9A84C':'#9B9890' ?>;background:<?= basename($_SERVER['PHP_SELF'])=='orders.php'?'rgba(201,168,76,.12)':'transparent' ?>;text-decoration:none;font-size:.85rem;margin-bottom:2px">📋 Orders</a>
    <div style="font-size:.65rem;color:#5C5A55;letter-spacing:2px;padding:8px 8px 4px;text-transform:uppercase;margin-top:8px">Management</div>
    <a href="menu.php" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:9px;color:<?= basename($_SERVER['PHP_SELF'])=='menu.php'?'#C9A84C':'#9B9890' ?>;background:<?= basename($_SERVER['PHP_SELF'])=='menu.php'?'rgba(201,168,76,.12)':'transparent' ?>;text-decoration:none;font-size:.85rem;margin-bottom:2px">🍱 Menu Items</a>
    <a href="tables.php" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:9px;color:<?= basename($_SERVER['PHP_SELF'])=='tables.php'?'#C9A84C':'#9B9890' ?>;background:<?= basename($_SERVER['PHP_SELF'])=='tables.php'?'rgba(201,168,76,.12)':'transparent' ?>;text-decoration:none;font-size:.85rem;margin-bottom:2px">🪑 Tables</a>
    <?php if(isset($_SESSION['role']) && $_SESSION['role']==='admin'): ?>
    <a href="staff.php" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:9px;color:<?= basename($_SERVER['PHP_SELF'])=='staff.php'?'#C9A84C':'#9B9890' ?>;background:<?= basename($_SERVER['PHP_SELF'])=='staff.php'?'rgba(201,168,76,.12)':'transparent' ?>;text-decoration:none;font-size:.85rem;margin-bottom:2px">👨‍🍳 Staff</a>
    <?php endif; ?>
    <div style="font-size:.65rem;color:#5C5A55;letter-spacing:2px;padding:8px 8px 4px;text-transform:uppercase;margin-top:8px">Analytics</div>
    <a href="reports.php" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:9px;color:<?= basename($_SERVER['PHP_SELF'])=='reports.php'?'#C9A84C':'#9B9890' ?>;background:<?= basename($_SERVER['PHP_SELF'])=='reports.php'?'rgba(201,168,76,.12)':'transparent' ?>;text-decoration:none;font-size:.85rem;margin-bottom:2px">📈 Reports</a>
  </nav>
  <div style="padding:16px;border-top:1px solid rgba(201,168,76,.08);display:flex;align-items:center;gap:10px">
    <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#8B6914,#C9A84C);display:grid;place-items:center;font-weight:600;color:#0A0A0B;font-size:13px"><?= strtoupper(substr($_SESSION['full_name'],0,1)) ?></div>
    <div style="flex:1">
      <div style="font-size:.82rem;color:#F0EDE8"><?= $_SESSION['full_name'] ?></div>
      <div style="font-size:.7rem;color:#5C5A55"><?= ucfirst($_SESSION['role']) ?></div>
    </div>
    <a href="logout.php" style="color:#5C5A55;text-decoration:none;font-size:18px" title="Logout">⇦</a>
  </div>
</aside>