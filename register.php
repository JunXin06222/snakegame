<?php
include"sql.php";

$new_username = $_POST['new_username'];
$new_password = $_POST['new_password'];

$sql = "INSERT INTO login (id, pwd) VALUES ('$new_username', '$new_password')";

// 執行查詢
$result = $conn->query($sql);

// 檢查是否有查詢結果
if ($result > 0) {
    echo "
    <script>
    alert('註冊成功')
    location.href = '/snake/'
    </script>";
} else {
    echo "<script>
    alert('註冊失敗')
    location.href = '/snake/'
    </script>";
}

// 關閉連接
$conn->close();
?>

