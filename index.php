<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db.php';
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login — Grandeur Hotel</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'Segoe UI', sans-serif;
    background: #0A0A0B;
    min-height: 100vh;
    display: grid;
    place-items: center;
}
.login-wrap { width: 100%; max-width: 400px; padding: 24px; }
.login-brand { text-align: center; margin-bottom: 32px; }
.crest {
    width: 64px; height: 64px; border-radius: 50%;
    background: linear-gradient(135deg, #C9A84C, #8B6914);
    display: grid; place-items: center;
    margin: 0 auto 14px; font-size: 28px;
}
h1 {
    font-size: 2rem; color: #C9A84C;
    letter-spacing: 4px; font-weight: 300;
}
.login-brand p { color: #9B9890; font-size: .8rem; letter-spacing: 2px; margin-top: 4px; }
.login-card {
    background: #111115;
    border: 1px solid rgba(201,168,76,.2);
    border-radius: 16px; padding: 32px;
}
.form-group { margin-bottom: 18px; }
label { display: block; font-size: .78rem; color: #9B9890; margin-bottom: 7px; letter-spacing: .5px; }
input {
    width: 100%; padding: 11px 14px;
    background: #1A1A20; border: 1px solid #32323F;
    border-radius: 9px; color: #F0EDE8;
    font-size: .9rem; transition: border-color .2s;
}
input:focus { outline: none; border-color: #C9A84C; }
.btn {
    width: 100%; padding: 12px;
    background: #C9A84C; color: #0A0A0B;
    border: none; border-radius: 9px;
    font-size: .95rem; font-weight: 600;
    cursor: pointer; margin-top: 8px;
    transition: background .2s;
}
.btn:hover { background: #E8C97A; }
.error {
    background: rgba(224,82,82,.15);
    border: 1px solid rgba(224,82,82,.3);
    color: #E05252; padding: 10px 14px;
    border-radius: 8px; font-size: .83rem;
    margin-bottom: 16px;
}
.hint { text-align: center; color: #5C5A55; font-size: .72rem; margin-top: 14px; }
</style>
</head>
<body>
<div class="login-wrap">
    <div class="login-brand">
        <div class="crest">🏨</div>
        <h1>GRANDEUR</h1>
        <p>HOTEL FOOD MANAGEMENT</p>
    </div>
    <div class="login-card">
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>USERNAME</label>
                <input type="text" name="username" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <label>PASSWORD</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn">Sign In →</button>
        </form>
        <p class="hint">Demo: admin / password</p>
    </div>
</div>
</body>
</html>