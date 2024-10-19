<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$movie_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM Movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie_details = $result->fetch_assoc();
$stmt->close();

if (!$movie_details) {
    echo "Movie not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($movie_details['title']); ?> - Movie Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
            background-color: #343131;
        }
        .container {
            max-width: 1300px;
            height: 80vh;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            margin: 5px;
            text-decoration: none;
            color: #fff;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-primary { background-color: #557C56; 
        }

        .movie-image {
            width: 300px;
            height: 90%;
            object-fit: cover;
            max-height: 1200px;
            margin-bottom: 15px;
        }
        .movie-info {
            margin : 10px 50px
        }

        .content-wrapper {
            display: flex;
            height: 80%;
        }

        .img-wrapper {
            display: flex;
            max-width:800px;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($movie_details['title']); ?></h2>
        <div class="content-wrapper">
        <div class="img-wrapper">
        <?php if (!empty($movie_details['image_path']) && $movie_details['image_path'] !== 'NULL'): ?>
            <img src="/Movies/uploads/<?php echo basename($movie_details['image_path']); ?>" alt="Movie Image" class="movie-image">
        <?php else: ?>
            <p>No image available</p>
        <?php endif; ?>
        </div>
        <div class="movie-info">
            <p><strong>Release Year:</strong> <?php echo htmlspecialchars($movie_details['release_year']); ?></p>
            <p><strong>Director:</strong> <?php echo htmlspecialchars($movie_details['director']); ?></p>
            <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie_details['genre']); ?></p>
            <p><strong>Description:</strong><br> <?php echo htmlspecialchars($movie_details['description']); ?></p>
        </div>
        </div>
        
        

        <a href="index.php" class="btn btn-primary">Back to Movie List</a>
    </div>
</body>
</html>