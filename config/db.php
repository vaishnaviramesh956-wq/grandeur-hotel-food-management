<?php
define('DB_HOST', 'sql208.byetcluster.com');
define('DB_USER', 'b31_42119499');
define('DB_PASS', 'hotel1@2026');
define('DB_NAME', 'b31_42119499_hotel');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>