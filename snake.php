<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>貪食蛇遊戲</title>
    <style>
        body {
            text-align: center;
            background-color: #f0f0f0;
            font-family: 'Roboto', sans-serif;
            background-image: url('bg.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #fff;
        }

        h1, h2 {
            font-family: 'Pacifico', cursive;
        }

        canvas {
            border: 2px solid #4CAF50;
            display: block;
            margin: 20px auto;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
        }

        button {
            font-size: 18px;
            padding: 10px 20px;
            margin: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
        }

        button:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }

        p {
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: bold;
            background: rgba(0, 0, 0, 0.5);
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
        }

        .sidebar {
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            border: 2px solid #4CAF50;
            border-radius: 10px;
            width: 60%;
            box-shadow: 0px 0px 10px #000;
        }

        select {
            font-size: 16px;
            padding: 5px;
            border: none;
            border-radius: 5px;
            margin: 10px;
        }

        label {
            font-size: 18px;
            margin-right: 10px;
        }

        form {
            display: none;
        }
    </style>
</head>

<body>
    <h1>貪食蛇遊戲</h1>
    <canvas id="gameCanvas" width="400" height="400"></canvas>
    <div class="sidebar">
        <h2>遊戲資訊</h2>
        <p id="score">分數：0</p>
        <p id="hp">HP：100</p>
        <p>最高分：<span id="highestScore">
        <?php
            include "sql.php";
            $userId = $_SESSION['id'];

            $sql = "SELECT score FROM sorce WHERE id ='$userId' ";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo $row['score'];
            } else {
                echo "0";  // 默認最高分為0
            }
        ?>
        </span></p>
    </div>
    <br>
    <button onclick="startGame()" id="startButton">開始遊戲</button>
    <button onclick="pauseGame()" id="pauseButton" style="display: none;">暫停遊戲</button>
    <button onclick="exitGame()">結束遊戲</button>
    <div id="difficultySelection">
        <label for="difficulty">選擇難度：</label>
        <select id="difficulty">
            <option value="easy">簡單</option>
            <option value="medium">中等</option>
            <option value="hard">困難</option>
        </select>
    </div>
    
    <form action="endGame.php" method="post" id="userScore">  
        <input type="text" id="userName" name="userName">
        <input type="text" id="score" name="score">
        <input type="submit">
    </form>

    <script>
        <?php $userName = $_SESSION['id']; echo "let userName = '$userName'; "; ?>
        var canvas = document.getElementById("gameCanvas");
        var ctx = canvas.getContext("2d");
        var snakeSize = 20;
        var snake = [{ x: 200, y: 200 }];
        var food = { x: 0, y: 0 };
        var speed = 100;
        var dx = 0;
        var dy = -1;
        var changingDirection = false;
        var gamePaused = false;
        var interval;
        var score = 0;
        var specialFood = { x: 0, y: 0 };
        var specialFoodActive = false;
        var highScore = localStorage.getItem('highScore') || 0;
        document.getElementById('highestScore').innerText = highScore;
        var obstacles = [];
        var hp = 100;

        function drawSnakePart(snakePart, color) {
            ctx.fillStyle = color;
            ctx.strokeStyle = 'darkgreen';
            ctx.fillRect(snakePart.x, snakePart.y, snakeSize, snakeSize);
            ctx.strokeRect(snakePart.x, snakePart.y, snakeSize, snakeSize);
        }

        function drawFood() {
            ctx.fillStyle = 'red';
            ctx.fillRect(food.x, food.y, snakeSize, snakeSize);
        }

        function drawSnake() {
            snake.forEach((snakePart, index) => {
                const color = index % 2 === 0 ? 'lightgreen' : 'green';
                drawSnakePart(snakePart, color);
            });
        }

        function drawSpecialFood() {
            if (specialFoodActive) {
                ctx.fillStyle = 'orange';
                ctx.fillRect(specialFood.x, specialFood.y, snakeSize, snakeSize);
            }
        }

        function drawObstacles() {
            ctx.fillStyle = 'black';
            obstacles.forEach(obstacle => {
                ctx.fillRect(obstacle.x, obstacle.y, snakeSize, snakeSize);
            });
        }

        function changeDirection(event) {
            const LEFT_KEY = 37;
            const RIGHT_KEY = 39;
            const UP_KEY = 38;
            const DOWN_KEY = 40;

            if (changingDirection) return;
            changingDirection = true;

            const keyPressed = event.keyCode;
            const goingUp = dy === -1;
            const goingDown = dy === 1;
            const goingRight = dx === 1;
            const goingLeft = dx === -1;

            if (keyPressed === LEFT_KEY && !goingRight) {
                dx = -1;
                dy = 0;
            }

            if (keyPressed === UP_KEY && !goingDown) {
                dx = 0;
                dy = -1;
            }

            if (keyPressed === RIGHT_KEY && !goingLeft) {
                dx = 1;
                dy = 0;
            }

            if (keyPressed === DOWN_KEY && !goingUp) {
                dx = 0;
                dy = 1;
            }
        }

        function generateFood() {
            food.x = Math.floor(Math.random() * canvas.width / snakeSize) * snakeSize;
            food.y = Math.floor(Math.random() * canvas.height / snakeSize) * snakeSize;

            if (Math.random() < 0.1) {
                specialFood.x = Math.floor(Math.random() * canvas.width / snakeSize) * snakeSize;
                specialFood.y = Math.floor(Math.random() * canvas.height / snakeSize) * snakeSize;
                specialFoodActive = true;
            } else {
                specialFoodActive = false;
            }

            obstacles = [];
            for (let i = 0; i < 5; i++) {
                let obstacleX = Math.floor(Math.random() * canvas.width / snakeSize) * snakeSize;
                let obstacleY = Math.floor(Math.random() * canvas.height / snakeSize) * snakeSize;
                obstacles.push({ x: obstacleX, y: obstacleY });
            }
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        function moveSnake() {
            const head = { x: snake[0].x + dx * snakeSize, y: snake[0].y + dy * snakeSize };
            snake.unshift(head);
            const hasEatenFood = snake[0].x === food.x && snake[0].y === food.y;
            if (hasEatenFood) {
                generateFood();
                score += 10;
                document.getElementById('score').innerText = '分數：' + score;
            } else {
                snake.pop();
            }

            if (specialFoodActive && snake[0].x === specialFood.x && snake[0].y === specialFood.y) {
                specialFoodActive = false;
                score += 50;
                document.getElementById('score').innerText = '分數：' + score;
            }

            if (score > highScore) {
                highScore = score;
                localStorage.setItem('highScore', highScore);
                document.getElementById('highestScore').innerText = highScore;
            }

            const colorIndex = Math.floor(score / 50) % 2 === 0 ? 0 : 1;
            const snakeColor = colorIndex === 0 ? 'lightgreen' : 'green';
            snake.forEach(snakePart => drawSnakePart(snakePart, snakeColor));

            obstacles.forEach(obstacle => {
                if (head.x === obstacle.x && head.y === obstacle.y) {
                    hp -= 20;
                    document.getElementById('hp').innerText = 'HP：' + hp;
                    if (hp <= 0) {
                        endGame();
                    }
                }
            });
        }

        function didGameEnd() {
            for (let i = 4; i < snake.length; i++) {
                if (snake[i].x === snake[0].x && snake[i].y === snake[0].y) {
                    return true;
                }
            }
            const hitLeftWall = snake[0].x < 0;
            const hitRightWall = snake[0].x >= canvas.width;
            const hitTopWall = snake[0].y < 0;
            const hitBottomWall = snake[0].y >= canvas.height;
            return hitLeftWall || hitRightWall || hitTopWall || hitBottomWall;
        }

        function main() {
            if (didGameEnd()) {
                clearInterval(interval);
                alert("遊戲結束！您的分數是：" + score);
                endGame();
                return;
            }
            changingDirection = false;
            if (!gamePaused) {
                clearCanvas();
                drawFood();
                moveSnake();
                drawSnake();
                drawSpecialFood();
                drawObstacles();
            }
        }

        function startGame() {
            document.getElementById('startButton').style.display = 'none';
            document.getElementById('pauseButton').style.display = 'inline-block';
            document.getElementById('difficultySelection').style.display = 'none';
            var difficulty = document.getElementById('difficulty').value;
            if (difficulty === 'easy') {
                speed = 100;
            } else if (difficulty === 'medium') {
                speed = 75;
            } else if (difficulty === 'hard') {
                speed = 50;
            }
            interval = setInterval(main, speed);
            generateFood();
            document.addEventListener("keydown", changeDirection);
        }

        function pauseGame() {
            gamePaused = !gamePaused;
            var pauseButton = document.getElementById('pauseButton');
            if (gamePaused) {
                clearInterval(interval);
                pauseButton.innerText = '繼續遊戲';
            } else {
                interval = setInterval(main, speed);
                pauseButton.innerText = '暫停遊戲';
            }
        }

        function endGame() {
            clearInterval(interval);
            var form = document.getElementById("userScore");
            var userNameInput = document.getElementById("userName");
            var scoreInput = document.getElementById("score");

            userNameInput.value = userName;
            scoreInput.value = highScore;

            form.submit();
        }

        function exitGame() {
            window.location.href = "index.html";
        }
    </script>
</body>

</html>
