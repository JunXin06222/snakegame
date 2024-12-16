<?php
 include"sql.php";
 $userName = $_POST['userName'];
 $score = $_POST['score'];
 $sql = "SELECT id, score FROM sorce WHERE id = '$userName' ";
// 執行查詢
$result = $conn->query($sql);

$row = $result->fetch_assoc();
// 檢查是否有查詢結果
if ($result->num_rows > 0)
 {
    if($score > $row['score']){
        $sql = "UPDATE sorce SET score='$score' WHERE id='$userName'";
    if ($conn->query($sql) === TRUE) {
        echo "<script>
        location.href = '/snake/snake.php'
        </script>";
    } else {
        echo "<script>
        alert('新增分數失敗')
        location.href = '/snake/snake.php'
        </script>";
    }
    }
    else{
        echo "<script>
        location.href = '/snake/snake.php'
        </script>";
    }
} else {
    $sql = "INSERT INTO sorce (id, score) VALUES ('$userName', '$score')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>
        location.href = '/snake/snake.php'
        </script>";
    } else {
        echo "<script>
        alert('新增分數失敗')
        location.href = '/snake/snake.php'
        </script>";
    }
    
}