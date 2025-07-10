<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: index.php');
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - ElectroShop</title>
    <link href="https://fonts.googleapis.com/css2?family=Asap&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: rgba(244, 91, 105, 1);
            font-family: 'Asap', sans-serif;
        }

        .login {
            overflow: hidden;
            background-color: white;
            padding: 40px 30px 30px 30px;
            border-radius: 10px;
            position: absolute;
            top: 50%;
            left: 50%;
            width: 400px;
            transform: translate(-50%, -50%);
            transition: transform 300ms, box-shadow 300ms;
            box-shadow: 5px 10px 10px rgba(2, 128, 144, 0.2);
        }

        .login::before, .login::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-top-left-radius: 40%;
            border-top-right-radius: 45%;
            border-bottom-left-radius: 35%;
            border-bottom-right-radius: 40%;
            z-index: -1;
        }

        .login::before {
            left: 40%;
            bottom: -130%;
            background-color: rgba(69, 105, 144, 0.15);
            animation: wawes 6s infinite linear;
        }

        .login::after {
            left: 35%;
            bottom: -125%;
            background-color: rgba(2, 128, 144, 0.2);
            animation: wawes 7s infinite linear;
        }

        .login input {
            font-family: 'Asap', sans-serif;
            display: block;
            border-radius: 5px;
            font-size: 16px;
            background: white;
            width: 100%;
            border: 0;
            padding: 10px 10px;
            margin: 15px -10px;
        }

        .login button {
            font-family: 'Asap', sans-serif;
            cursor: pointer;
            color: #fff;
            font-size: 16px;
            text-transform: uppercase;
            width: 80px;
            border: 0;
            padding: 10px 0;
            margin-top: 10px;
            margin-left: -5px;
            border-radius: 5px;
            background-color: rgba(244, 91, 105, 1);
            transition: background-color 300ms;
        }

        .login button:hover {
            background-color: #e65a65; /* 5% darken from redFire */
        }

        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 1rem;
        }

        a {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.6);
            position: absolute;
            right: 10px;
            bottom: 10px;
            font-size: 12px;
        }

        @keyframes wawes {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login">
        <h2 style="text-align:center; color:rgba(69, 105, 144, 1); margin-bottom: 1rem;">Login ElectroShop</h2>
        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <a href="#">Lupa password?</a>
    </div>
</body>
</html>
