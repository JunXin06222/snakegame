<?php
include"sql.php";

// 從 POST 請求中獲取用戶名和密碼
$username = $_POST['username'];
$password = $_POST['password'];

// 構建 SQL 查詢
$sql = "SELECT * FROM login WHERE id='$username' AND pwd='$password'";

// 執行查詢
$result = $conn->query($sql);

// 檢查是否有查詢結果
if ($result->num_rows > 0) 
{
    $_SESSION['id'] = $username;
    echo "
    <script>
    alert('登入成功')
    location.href = '/snake/snake.php'
    </script>";

} else {
    echo "
    <script>
    alert('登入失敗')
    location.href = '/snake/'
    </script>";
}

// 關閉連接
$conn->close();
?>
