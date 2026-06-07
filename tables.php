<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// Update table status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_table'])) {
    $id = $_POST['table_id'];
    $status = $_POST['status'];
    $reserved_by = mysqli_real_escape_string($conn, $_POST['reserved_by'] ?? '');
    mysqli_query($conn, "UPDATE hotel_tables SET status='$status', reserved_by='$reserved_by' WHERE id=$id");
    header("Location: tables.php"); exit();
}

$tables = mysqli_query($conn, "SELECT * FROM hotel_tables ORDER BY table_number");
$available = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM hotel_tables WHERE status='available'"))['cnt'];
$occupied = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM hotel_tables WHERE status='occupied'"))['cnt'];
$reserved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM hotel_tables WHERE status='reserved'"))['cnt'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Tables — Grandeur</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#0A0A0B;color:#F0EDE8;display:flex}
.main-content{flex:1;overflow-y:auto}
.top-bar{padding:18px 28px;border-bottom:1px solid rgba(201,168,76,.08);background:#0A0A0B;position:sticky;top:0;z-index:50}
.top-bar h2{font-size:1.5rem;font-weight:300}
.content{padding:24px}
.stats-row{display:flex;gap:14px;margin-bottom:24px;flex-wrap:wrap}
.stat-mini{background:#111115;border:1px solid rgba(201,168,76,.1);border-radius:12px;padding:16px 22px;display:flex;align-items:center;gap:12px}
.stat-mini .dot{width:12px;height:12px;border-radius:50%;flex-shrink:0}
.dot-green{background:#52C97A;box-shadow:0 0 8px #52C97A}
.dot-red{background:#E05252}
.dot-gold{background:#C9A84C}
.stat-mini .label{font-size:.78rem;color:#9B9890}
.stat-mini .val{font-size:1.4rem;color:#F0EDE8;font-weight:600}
.tables-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px}
.table-card{background:#111115;border:2px solid transparent;border-radius:14px;padding:20px;text-align:center;cursor:pointer;transition:all .25s}
.table-card:hover{transform:translateY(-2px)}
.table-card .t-icon{font-size:32px;margin-bottom:10px}
.table-card .t-num{font-size:1.4rem;font-weight:600;color:#F0EDE8}
.table-card .t-cap{font-size:.72rem;color:#5C5A55;margin-top:3px}
.table-card .t-reserve{font-size:.72rem;color:#9B9890;margin-top:4px}
.table-card .t-status{font-size:.75rem;margin-top:8px;font-weight:500}
.table-available{border-color:rgba(82,201,122,.25)}
.table-available .t-status{color:#52C97A}
.table-occupied{border-color:rgba(224,82,82,.3);background:rgba(224,82,82,.04)}
.table-occupied .t-status{color:#E05252}
.table-reserved{border-color:rgba(201,168,76,.3);background:rgba(201,168,76,.04)}
.table-reserved .t-status{color:#C9A84C}
/* Modal */
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:200;place-items:center}
.modal-backdrop.open{display:grid}
.modal{background:#111115;border:1px solid rgba(201,168,76,.2);border-radius:16px;padding:28px;width:90%;max-width:400px}
.modal h3{font-size:1.3rem;font-weight:400;margin-bottom:20px}
.form-group{margin-bottom:16px}
label{display:block;font-size:.73rem;color:#9B9890;margin-bottom:5px}
select,input{width:100%;padding:10px 12px;background:#1A1A20;border:1px solid #32323F;border-radius:8px;color:#F0EDE8;font-size:.88rem}
select:focus,input:focus{outline:none;border-color:#C9A84C}
select option{background:#1A1A20}
.modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:20px}
.btn{padding:9px 18px;border-radius:8px;cursor:pointer;font-size:.85rem;font-weight:500;border:none;transition:all .2s}
.btn-gold{background:#C9A84C;color:#0A0A0B}
.btn-gold:hover{background:#E8C97A}
.btn-outline{background:transparent;border:1px solid #32323F;color:#9B9890}
</style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
  <div class="top-bar"><h2>Table Management</h2></div>
  <div class="content">

    <!-- Stats Row -->
    <div class="stats-row">
      <div class="stat-mini">
        <div class="dot dot-green"></div>
        <div><div class="label">Available</div><div class="val"><?= $available ?></div></div>
      </div>
      <div class="stat-mini">
        <div class="dot dot-red"></div>
        <div><div class="label">Occupied</div><div class="val"><?= $occupied ?></div></div>
      </div>
      <div class="stat-mini">
        <div class="dot dot-gold"></div>
        <div><div class="label">Reserved</div><div class="val"><?= $reserved ?></div></div>
      </div>
    </div>

    <!-- Tables Grid -->
    <div class="tables-grid">
      <?php
      $icons = ['available'=>'🟢','occupied'=>'🔴','reserved'=>'🟡'];
      $labels = ['available'=>'Available','occupied'=>'Occupied','reserved'=>'Reserved'];
      while($t = mysqli_fetch_assoc($tables)):
      ?>
      <div class="table-card table-<?= $t['status'] ?>"
           onclick="openModal(<?= $t['id'] ?>,'<?= $t['table_number'] ?>','<?= $t['status'] ?>','<?= $t['reserved_by'] ?>')">
        <div class="t-icon">🪑</div>
        <div class="t-num"><?= $t['table_number'] ?></div>
        <div class="t-cap"><?= $t['capacity'] ?> seats</div>
        <?php if($t['reserved_by']): ?>
        <div class="t-reserve">👤 <?= $t['reserved_by'] ?></div>
        <?php endif; ?>
        <div class="t-status"><?= $icons[$t['status']] ?> <?= $labels[$t['status']] ?></div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal-backdrop" id="table-modal">
  <div class="modal">
    <h3 id="modal-title">Table</h3>
    <form method="POST">
      <input type="hidden" name="table_id" id="modal-table-id">
      <input type="hidden" name="update_table" value="1">
      <div class="form-group">
        <label>STATUS</label>
        <select name="status" id="modal-status" onchange="toggleReserve(this.value)">
          <option value="available">Available</option>
          <option value="occupied">Occupied</option>
          <option value="reserved">Reserved</option>
        </select>
      </div>
      <div class="form-group" id="reserve-group" style="display:none">
        <label>RESERVED BY</label>
        <input type="text" name="reserved_by" id="modal-reserve" placeholder="Guest name">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('table-modal').classList.remove('open')">Cancel</button>
        <button type="submit" class="btn btn-gold">Update</button>
      </div>
    </form>
  </div>
</div>
<script>
function openModal(id, num, status, reserve) {
  document.getElementById('modal-title').textContent = 'Table ' + num;
  document.getElementById('modal-table-id').value = id;
  document.getElementById('modal-status').value = status;
  document.getElementById('modal-reserve').value = reserve;
  toggleReserve(status);
  document.getElementById('table-modal').classList.add('open');
}
function toggleReserve(val) {
  document.getElementById('reserve-group').style.display = val === 'reserved' ? 'block' : 'none';
}
document.getElementById('table-modal').addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('open');
});
</script>
</body>
</html>