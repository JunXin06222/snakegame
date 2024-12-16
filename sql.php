<?php
$servername = "localhost"; // 更改為你的 MariaDB 伺服器名稱
$username = "root"; // 更改為你的使用者名稱
$password = ""; // 更改為你的密碼
$dbname = "snake"; // 更改為你的資料庫名稱

// 建立連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接是否成功
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

session_start();
