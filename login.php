<?php
session_start(); 
include 'db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php"); 
    exit(); 
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; 
    $password = $_POST['password']; 

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) { 
        $row = $result->fetch_assoc();
        if ($password === $row['password']) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: index.php"); 
            exit(); 
        } else {
            $error = "Username atau password salah."; 
        }
    } else {
        $error = "Username atau password salah."; 
    }
    $conn->close(); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> 
    <title>Login</title> 
    <style>

        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #343131;
        }

        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #557C56;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        p {
            margin-top: 20px;
            color: #555;
            font-size: 14px;
        }

        a {
            color: #557C56;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" action=""> 
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required> 
            <button type="submit">Login</button> 
        </form>
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p> 
    </div>
</body>
</html>