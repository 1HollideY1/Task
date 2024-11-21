<?php
require '../vendor/autoload.php';
require_once 'mongoDB.php';

session_start();

try {
    $connection = DatabaseConnection::getConnection();

    $loginAttemptsCollection = $connection->auth_db->login_attempts;
    $usersCollection = $connection->auth_db->users;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $currentTime = time();
        $clientIP = $_SERVER['REMOTE_ADDR'];

        $blockAttempt = $loginAttemptsCollection->findOne([
            'ip' => $clientIP,
            'blocked_until' => ['$gt' => $currentTime]
        ]);

        if ($blockAttempt) {
            $remainingTime = $blockAttempt['blocked_until'] - $currentTime;
            $error = "Слишком много попыток входа. Попробуйте снова через {$remainingTime} секунд.";
        }
        else {
            $user = $usersCollection->findOne([
                'login' => $login,
                'password' => $password 
            ]);

            if ($user) {
                $loginAttemptsCollection->deleteMany([
                    'ip' => $clientIP,
                    'timestamp' => ['$lt' => $currentTime - 30]
                ]);

                $_SESSION['user_id'] = (string)$user->_id;

                header('Location: home.php');
                exit;
            }
            else {
                $recentAttempts = $loginAttemptsCollection->count([
                    'ip' => $clientIP,
                    'timestamp' => ['$gt' => $currentTime - 30]
                ]);

                if ($recentAttempts >= 2) {
                    $loginAttemptsCollection->insertOne([
                        'ip' => $clientIP,
                        'timestamp' => $currentTime,
                        'blocked_until' => $currentTime + 40
                    ]);

                    $error = "Слишком много попыток входа. Вы заблокированы на 40 секунд.";
                } else {
                    $loginAttemptsCollection->insertOne([
                        'ip' => $clientIP,
                        'timestamp' => $currentTime,
                    ]);

                    $remainingAttempts = 3 - ($recentAttempts + 1);
                    $error = "Неверный пароль или логин. Осталось {$remainingAttempts} попыток.";
                }
            }
        }
    }
} catch (Exception $e) {
    $error = "Упс, ошибочка: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        button:hover:not(:disabled) {
            background-color: #45a049;
        }
        .attempts {
            margin-top: 10px;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <div class="form-group">
            <label for="login">Логин:</label>
            <input type="text" id="login" name="login" required>
        </div>

        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Вход</button>
    </form>
</body>
</html>