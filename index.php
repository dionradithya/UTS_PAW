<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $stmt = $conn->prepare("SELECT image_path FROM Movies WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();

    if ($movie) {
        $delete_stmt = $conn->prepare("DELETE FROM Movies WHERE id = ?");
        $delete_stmt->bind_param("i", $delete_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        if (!empty($movie['image_path']) && $movie['image_path'] !== 'NULL' && file_exists('/Movies/uploads/' . basename($movie['image_path']))) {
            unlink('/Movies/uploads/' . basename($movie['image_path']));
        }

        header("Location: index.php");
        exit();
    } else {
        echo "Movie not found.";
    }
}

$stmt = $conn->prepare("SELECT id, title, genre, image_path FROM Movies ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
$movies = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movie Catalog</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #343131;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2, h3 {
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
        .btn-danger { background-color: #A04747; }
        .btn-success { background-color: #557C56; }
        .btn-info { background-color: #EEDF7A; color: #000 }
        .btn-warning { background-color: #D8A25E; color: #000; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 4px;
            text-align: center ;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .movie-image {
            width: 50px;
            height: 75px;
            object-fit: cover;
            margin-right: 10px;
        }

        .td-title {
            text-align: left ;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome to Movie Catalog, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <a href="logout.php" class="btn btn-danger">Logout</a>
        <a href="add_movie.php" class="btn btn-success">Add New Movie</a>

        <h3>Movie List</h3>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Genre</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movies as $movie): ?>
                    <tr>
                        <td>
                            <?php if (!empty($movie['image_path']) && $movie['image_path'] !== 'NULL'): ?>
                                <img src="/Movies/uploads/<?php echo basename($movie['image_path']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-image">
                            <?php else: ?>
                                <p>No image available</p>
                            <?php endif; ?>
                        </td>
                        <td class="td-title"><?php echo htmlspecialchars($movie['title']); ?></td>
                        <td><?php echo htmlspecialchars($movie['genre']); ?></td>
                        <td>
                            <a href="details.php?id=<?php echo $movie['id']; ?>" class="btn btn-info">Details</a>
                            <a href="edit_movie.php?id=<?php echo $movie['id']; ?>" class="btn btn-warning">Edit</a>
                            <a href="?delete_id=<?php echo $movie['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this movie?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>