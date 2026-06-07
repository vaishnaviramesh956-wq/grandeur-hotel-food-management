<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
if ($_SESSION['role'] !== 'admin') { header("Location: dashboard.php"); exit(); }

// Add staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = $_POST['role'];
    $shift = mysqli_real_escape_string($conn, $_POST['shift']);
    mysqli_query($conn, "INSERT INTO staff (name, role, shift) VALUES ('$name', '$role', '$shift')");
    header("Location: staff.php"); exit();
}

// Delete staff
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM staff WHERE id=$id");
    header("Location: staff.php"); exit();
}

$staff = mysqli_query($conn, "SELECT * FROM staff ORDER BY role, name");
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM staff"))['cnt'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Staff — Grandeur</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#0A0A0B;color:#F0EDE8;display:flex}
.main-content{flex:1;overflow-y:auto}
.top-bar{padding:18px 28px;border-bottom:1px solid rgba(201,168,76,.08);display:flex;align-items:center;justify-content:space-between;background:#0A0A0B;position:sticky;top:0;z-index:50}
.top-bar h2{font-size:1.5rem;font-weight:300}
.btn{padding:10px 18px;border-radius:9px;border:none;cursor:pointer;font-size:.85rem;font-weight:500;transition:all .2s}
.btn-gold{background:#C9A84C;color:#0A0A0B}
.btn-gold:hover{background:#E8C97A}
.content{padding:24px}
.total-badge{background:#111115;border:1px solid rgba(201,168,76,.1);border-radius:12px;padding:14px 20px;display:inline-flex;align-items:center;gap:10px;margin-bottom:22px}
.total-badge .val{font-size:1.4rem;color:#C9A84C;font-weight:600}
.total-badge .label{font-size:.78rem;color:#9B9890}
.staff-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px}
.staff-card{background:#111115;border:1px solid rgba(201,168,76,.1);border-radius:14px;padding:22px;text-align:center;transition:border-color .25s}
.staff-card:hover{border-color:rgba(201,168,76,.3)}
.staff-avatar{width:58px;height:58px;border-radius:50%;background:linear-gradient(135deg,#8B6914,#C9A84C);display:grid;place-items:center;font-size:22px;font-weight:600;color:#0A0A0B;margin:0 auto 14px}
.staff-name{font-size:.95rem;font-weight:500;margin-bottom:4px}
.staff-role{font-size:.75rem;color:#9B9890;margin-bottom:4px}
.staff-shift{font-size:.72rem;color:#5C5A55;margin-bottom:14px}
.staff-stats{display:flex;border-top:1px solid rgba(255,255,255,.05);padding-top:12px;margin-bottom:14px}
.staff-stat{flex:1}
.staff-stat .val{font-size:.95rem;font-weight:600;color:#C9A84C}
.staff-stat .key{font-size:.68rem;color:#5C5A55}
.staff-stat+.staff-stat{border-left:1px solid rgba(255,255,255,.05)}
.btn-del{background:rgba(224,82,82,.15);color:#E05252;border:1px solid rgba(224,82,82,.3);padding:6px 14px;border-radius:7px;font-size:.75rem;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-block}
.btn-del:hover{background:rgba(224,82,82,.25)}
/* Modal */
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:200;place-items:center}
.modal-backdrop.open{display:grid}
.modal{background:#111115;border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:28px;width:90%;max-width:400px}
.modal h3{font-size:1.3rem;font-weight:400;margin-bottom:20px}
.form-group{margin-bottom:16px}
label{display:block;font-size:.73rem;color:#9B9890;margin-bottom:5px}
input,select{width:100%;padding:10px 12px;background:#1A1A20;border:1px solid #32323F;border-radius:8px;color:#F0EDE8;font-size:.88rem}
input:focus,select:focus{outline:none;border-color:#C9A84C}
select option{background:#1A1A20}
.modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:20px}
.btn-outline{background:transparent;border:1px solid #32323F;color:#9B9890;padding:9px 18px;border-radius:8px;cursor:pointer;font-size:.85rem}
.empty{text-align:center;padding:60px;color:#5C5A55;grid-column:1/-1}
.empty div{font-size:40px;margin-bottom:10px}
</style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
  <div class="top-bar">
    <h2>Staff Management</h2>
    <button class="btn btn-gold" onclick="document.getElementById('add-modal').classList.add('open')">+ Add Staff</button>
  </div>
  <div class="content">
    <div class="total-badge">
      <div class="val"><?= $total ?></div>
      <div class="label">Total Staff Members</div>
    </div>
    <div class="staff-grid">
      <?php if(mysqli_num_rows($staff) > 0): ?>
      <?php while($s = mysqli_fetch_assoc($staff)): ?>
      <div class="staff-card">
        <div class="staff-avatar"><?= strtoupper(substr($s['name'],0,1)) ?></div>
        <div class="staff-name"><?= $s['name'] ?></div>
        <div class="staff-role"><?= $s['role'] ?></div>
        <div class="staff-shift">⏰ <?= $s['shift'] ?></div>
        <div class="staff-stats">
          <div class="staff-stat">
            <div class="val"><?= $s['orders_handled'] ?></div>
            <div class="key">Orders</div>
          </div>
          <div class="staff-stat">
            <div class="val">⭐ <?= number_format($s['rating'],1) ?></div>
            <div class="key">Rating</div>
          </div>
        </div>
        <a href="staff.php?delete=<?= $s['id'] ?>" class="btn-del" onclick="return confirm('Remove <?= $s['name'] ?>?')">Remove</a>
      </div>
      <?php endwhile; ?>
      <?php else: ?>
      <div class="empty"><div>👨‍🍳</div><p>No staff added yet</p></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Add Staff Modal -->
<div class="modal-backdrop" id="add-modal">
  <div class="modal">
    <h3>Add Staff Member</h3>
    <form method="POST">
      <div class="form-group">
        <label>FULL NAME</label>
        <input type="text" name="name" placeholder="Enter name" required>
      </div>
      <div class="form-group">
        <label>ROLE</label>
        <select name="role">
          <option>Waiter</option>
          <option>Chef</option>
          <option>Cashier</option>
          <option>Manager</option>
          <option>Kitchen Helper</option>
        </select>
      </div>
      <div class="form-group">
        <label>SHIFT</label>
        <select name="shift">
          <option>Morning (6AM-2PM)</option>
          <option>Afternoon (2PM-10PM)</option>
          <option>Night (10PM-6AM)</option>
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-outline" onclick="document.getElementById('add-modal').classList.remove('open')">Cancel</button>
        <button type="submit" name="add_staff" class="btn btn-gold">Add Staff</button>
      </div>
    </form>
  </div>
</div>
<script>
document.getElementById('add-modal').addEventListener('click',function(e){
  if(e.target===this)this.classList.remove('open');
});
</script>
</body>
</html>