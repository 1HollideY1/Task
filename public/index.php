<?php
require '../vendor/autoload.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'mongoDB.php';

    if (isBlocked()) {
        $remainingTime = $_SESSION['blocked_until'] - time();
        $error = "Слишком много попыток входа. Попробуйте снова через {$remainingTime} секунд.";
    } else {
        $login = $_POST['login'];
        $password = $_POST['password'];
        
        try {
            $connection = DatabaseConnection::getConnection();
            $collection = $connection->auth_db->users;

            $user = $collection->findOne([
                'login' => $login,
                'password' => $password
            ]);

            if ($user) {
                $_SESSION['login_attempts'] = [];
                $_SESSION['user_id'] = (string)$user->_id;
                header('Location: home.php');
                exit;
            } else {
                if (registerFailedAttempt()) {
                    $error = "Слишком много попыток входа. Вы заблокированы на 20 секунд.";
                } else {
                    $remainingAttempts = 3 - count($_SESSION['login_attempts']);

                    $error = "Неверный пароль или логин. Осталось {$remainingAttempts} попыток.";
                }
            }
        } catch (Exception $e) {
            $error = "Упс, ошибочка: " . $e->getMessage();
        }
    }
}

function isBlocked() {
    if (isset($_SESSION['blocked_until']) && time() < $_SESSION['blocked_until']) {
        return true;
    }
    return false;
}

function registerFailedAttempt() {
    $currentTime = time();
    
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    
    $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function($timestamp) use ($currentTime) {
        return $currentTime - $timestamp <= 30;
    });
    
    $_SESSION['login_attempts'][] = $currentTime;
    
    if (count($_SESSION['login_attempts']) >= 3) {
        $_SESSION['blocked_until'] = $currentTime + 20;
        $_SESSION['login_attempts'] = [];
        return true;
    }
    
    return false;
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