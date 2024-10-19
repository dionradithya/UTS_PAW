<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

function getEmptyID($conn) {
    $query = "SELECT MIN(t1.id + 1) AS missing_id
              FROM Movies t1
              LEFT JOIN Movies t2 ON t1.id + 1 = t2.id
              WHERE t2.id IS NULL AND t1.id < (SELECT MAX(id) FROM Movies)";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['missing_id'];
    }
    return null;
}

function uploadImage($file) {
    $target_dir = __DIR__ . "/uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($file["tmp_name"]);
    if (!$check) return ["error" => "File is not an image."];

    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) 
        return ["error" => "Only JPG, JPEG, PNG, and GIF files are allowed."];

    if (move_uploaded_file($file["tmp_name"], $target_file)) 
        return ["path" => $target_file];
    
    return ["error" => "Error uploading file."];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $release_year = $_POST['release_year'];
    $director = $_POST['director'];
    $genre = $_POST['genre'];
    $description = $_POST['description'];

    $upload = uploadImage($_FILES["image"]);
    if (isset($upload['error'])) {
        $error = $upload['error'];
    } else {
        $empty_id = getEmptyID($conn);
        $sql = $empty_id 
            ? "INSERT INTO Movies (id, title, release_year, director, genre, description, image_path) 
               VALUES (?, ?, ?, ?, ?, ?, ?)"
            : "INSERT INTO Movies (title, release_year, director, genre, description, image_path) 
               VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $empty_id 
            ? $stmt->bind_param("isissss", $empty_id, $title, $release_year, $director, $genre, $description, $upload['path'])
            : $stmt->bind_param("sissss", $title, $release_year, $director, $genre, $description, $upload['path']);

        if ($stmt->execute()) {
            $success = "Movie added successfully.";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Movie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #343131;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .alert-danger {
            background-color: #e74c3c;
            color: white;
        }
        .alert-success {
            background-color: #2ecc71;
            color: white;
        }
        input[type="text"], input[type="number"], input[type="file"], textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            resize: none;
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
        button:hover {
            background-color: #45a049;
        }
        .back-link {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: #A04747;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Movie</h2>
        <a href="index.php" class="back-link">← Back to Movie List</a>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Movie Title" required>
            <input type="number" name="release_year" placeholder="Release Year" required>
            <input type="text" name="director" placeholder="Director" required>
            <input type="text" name="genre" placeholder="Genre" required>
            <textarea name="description" rows="3" placeholder="Description" required></textarea>
            <input type="file" name="image" required>
            <button type="submit">Add Movie</button>
        </form>
    </div>
</body>
</html>
