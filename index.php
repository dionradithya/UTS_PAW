<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, title, release_year FROM Movies ORDER BY release_year DESC");
$stmt->execute();
$result = $stmt->get_result();
$movies = $result->fetch_all(MYSQLI_ASSOC);

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM Movies WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie_details = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Welcome to Movie Catalog, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <a href="logout.php" class="btn btn-danger mb-3">Logout</a>

        <h3>Movie List</h3>
        <ul class="list-group">
            <?php foreach ($movies as $movie): ?>
                <li class="list-group-item">
                    <a href="?id=<?php echo $movie['id']; ?>">
                        <?php echo htmlspecialchars($movie['title']) . " (" . $movie['release_year'] . ")"; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (isset($movie_details)): ?>
            <h3 class="mt-4">Movie Details</h3>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($movie_details['title']); ?></h5>
                    <p class="card-text">Release Year: <?php echo $movie_details['release_year']; ?></p>
                    <p class="card-text">Director: <?php echo htmlspecialchars($movie_details['director']); ?></p>
                    <p class="card-text">Genre: <?php echo htmlspecialchars($movie_details['genre']); ?></p>
                    <p class="card-text">Description: <?php echo htmlspecialchars($movie_details['description']); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>