<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

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

if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM Movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();
    
    if (!$movie) {
        echo "Movie not found.";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $release_year = $_POST['release_year'];
    $director = $_POST['director'];
    $genre = $_POST['genre'];
    $description = $_POST['description'];

    $image_updated = false;
    if (!empty($_FILES["image"]["name"])) {
        $upload = uploadImage($_FILES["image"]);
        if (isset($upload['error'])) {
            $error = $upload['error'];
        } else {
            $image_updated = true;
        }
    }

    if (!$error) {
        if ($image_updated) {
            $sql = "UPDATE Movies SET title = ?, release_year = ?, director = ?, genre = ?, description = ?, image_path = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sissssi", $title, $release_year, $director, $genre, $description, $upload['path'], $movie_id);
        } else {
            $sql = "UPDATE Movies SET title = ?, release_year = ?, director = ?, genre = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisssi", $title, $release_year, $director, $genre, $description, $movie_id);
        }

        if ($stmt->execute()) {
            $success = "Movie updated successfully.";
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
    <title>Edit Movie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #343131;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="file"] {
            margin-top: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #557C56;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .message {
            text-align: center;
            margin-top: 10px;
        }
        .message p {
            padding: 10px;
            border-radius: 5px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        img {
            display: block;
            max-width: 100%;
            margin: 10px 0;
            border-radius: 5px;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #A04747;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Movie</h2>

        <?php if ($error): ?>
            <div class="message error">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success">
                <p><?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <label>Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>

            <label>Release Year:</label>
            <input type="number" name="release_year" value="<?php echo htmlspecialchars($movie['release_year']); ?>" required>

            <label>Director:</label>
            <input type="text" name="director" value="<?php echo htmlspecialchars($movie['director']); ?>" required>

            <label>Genre:</label>
            <input type="text" name="genre" value="<?php echo htmlspecialchars($movie['genre']); ?>" required>

            <label>Description:</label>
            <textarea name="description" rows="3" required><?php echo htmlspecialchars($movie['description']); ?></textarea>

            <label>Movie Poster:</label>
            <input type="file" name="image">
            <?php if (!empty($movie['image_path'])): ?>
                <img src="/Movies/uploads/<?php echo basename($movie['image_path']); ?>" alt="Movie Poster">
            <?php endif; ?>

            <button type="submit">Update Movie</button>
        </form>

        <a href="index.php">Back to Movie List</a>
    </div>
</body>
</html>
