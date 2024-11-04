<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'mongoDB.php';
$connection = DatabaseConnection::getConnection();
$collection = $connection->auth_db->users;
$user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])]);

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['user_id']);
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .logout {
            background-color: #ff4444;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div>
        <p>Привет, <?php echo htmlspecialchars($user->username); ?></p>
    </div>

    <a href="?action=logout" class="logout">Выход</a>
</body>
</html>