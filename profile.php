<?php
require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle favorite genre/actor additions and deletions using stored procedures
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['genre_id'])) {
            $genreId = (int)$_POST['genre_id'];
            if (isset($_POST['delete'])) {
                // Favori Tür Silme
                $stmt = $pdo->prepare("CALL sp_DeleteFavoriteGenre(?, ?)");
                $success = $stmt->execute([$userId, $genreId]);
                $message = $success ? "Favori tür silindi!" : "Bir hata oluştu.";
            } else {
                // Favori Tür Ekleme (fonksiyon aracılığıyla)
                $success = addFavoriteGenre($userId, $genreId);
                $message = $success ? "Favori tür eklendi!" : "Bir hata oluştu.";
            }
        } elseif (isset($_POST['actor_id'])) {
            $actorId = (int)$_POST['actor_id'];
            if (isset($_POST['delete'])) {
                // Favori Oyuncu Silme
                $stmt = $pdo->prepare("CALL sp_DeleteFavoriteActor(?, ?)");
                $success = $stmt->execute([$userId, $actorId]);
                $message = $success ? "Favori oyuncu silindi!" : "Bir hata oluştu.";
            } else {
                // Favori Oyuncu Ekleme (fonksiyon aracılığıyla)
                $success = addFavoriteActor($userId, $actorId);
                $message = $success ? "Favori oyuncu eklendi!" : "Bir hata oluştu.";
            }
        }
    } catch (PDOException $e) {
        $error = "İşlem sırasında bir veritabanı hatası oluştu.";
        error_log("Profile page action failed (SP): " . $e->getMessage());
    }
}

try {
    // Get user's rated movies
    $stmt = $pdo->prepare("CALL sp_GetUserRatedMovies(:user_id, 10)");
    $stmt->execute([':user_id' => $userId]);
    $ratedMovies = $stmt->fetchAll();
    $stmt->closeCursor();

    // Get user's rated TV shows
    $stmt = $pdo->prepare("CALL sp_GetUserRatedTVShows(:user_id, 10)");
    $stmt->execute([':user_id' => $userId]);
    $ratedTVShows = $stmt->fetchAll();
    $stmt->closeCursor();

    // Get user's favorite genres using the new stored procedure
    $stmt = $pdo->prepare("CALL sp_GetUserFavoriteGenres(:user_id)");
    $stmt->execute([':user_id' => $userId]);
    $favoriteGenres = $stmt->fetchAll();
    $stmt->closeCursor();

    // Get user's favorite actors using the new stored procedure
    $stmt = $pdo->prepare("CALL sp_GetUserFavoriteActors(:user_id)");
    $stmt->execute([':user_id' => $userId]);
    $favoriteActors = $stmt->fetchAll();
    $stmt->closeCursor();

    // Get all genres for the dropdown
    $stmt = $pdo->prepare("CALL sp_GetAllGenres()");
    $stmt->execute();
    $allGenres = $stmt->fetchAll();
    $stmt->closeCursor();

    // Get all actors for the dropdown
    $stmt = $pdo->prepare("CALL sp_GetAllActors()");
    $stmt->execute();
    $allActors = $stmt->fetchAll();
    $stmt->closeCursor();
} catch (PDOException $e) {
    $error = "Veritabanı işlemleri sırasında bir hata oluştu.";
    error_log("Profile page data fetch failed (SP): " . $e->getMessage());
    // Varsayılan boş değerler
    $ratedMovies = [];
    $ratedTVShows = [];
    $favoriteGenres = [];
    $favoriteActors = [];
    $allGenres = [];
    $allActors = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Film Öner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Film Öner</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="movies.php">Filmler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tv_shows.php">Diziler</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="rate_content.php">Değerlendir</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="recommendations.php">Öneriler</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profil</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <form class="d-flex" action="search.php" method="get">
                    <input class="form-control me-2" type="search" placeholder="Ara" name="query" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">Ara</button>
                </form>
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Çıkış Yap</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Giriş Yap</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Kayıt Ol</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Profil</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Favori Türler -->
            <div class="col-md-6">
                <div class="profile-card">
                    <h3 class="section-title">Favori Türler</h3>
                    <form method="POST" class="mb-3">
                        <div class="input-group">
                            <select name="genre_id" class="form-select">
                                <option value="">Tür seçin...</option>
                                <?php foreach ($allGenres as $genre): ?>
                                    <option value="<?php echo $genre['genre_id']; ?>">
                                        <?php echo $genre['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary">Ekle</button>
                        </div>
                    </form>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($favoriteGenres as $genre): ?>
                            <div class="favorite-item">
                                <span class="badge bg-primary"><?php echo $genre['name']; ?></span>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="genre_id" value="<?php echo $genre['genre_id']; ?>">
                                    <input type="hidden" name="delete" value="1">
                                    <button type="submit" class="btn btn-link delete-btn p-0">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Favori Oyuncular -->
            <div class="col-md-6">
                <div class="profile-card">
                    <h3 class="section-title">Favori Oyuncular</h3>
                    <form method="POST" class="mb-3">
                        <div class="input-group">
                            <select name="actor_id" class="form-select">
                                <option value="">Oyuncu seçin...</option>
                                <?php foreach ($allActors as $actor): ?>
                                    <option value="<?php echo $actor['actor_id']; ?>">
                                        <?php echo $actor['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary">Ekle</button>
                        </div>
                    </form>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($favoriteActors as $actor): ?>
                            <div class="favorite-item">
                                <span class="badge bg-primary"><?php echo $actor['name']; ?></span>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="actor_id" value="<?php echo $actor['actor_id']; ?>">
                                    <input type="hidden" name="delete" value="1">
                                    <button type="submit" class="btn btn-link delete-btn p-0">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Değerlendirilen Filmler -->
        <h3 class="section-title">Değerlendirilen Filmler</h3>
        <div class="row">
            <?php foreach ($ratedMovies as $movie): ?>
                <div class="col-md-4 col-lg-2">
                    <div class="content-card">
                        <div class="position-relative">
                            <img src="<?php echo $movie['poster_path']; ?>" 
                                 alt="<?php echo $movie['title']; ?>" 
                                 class="content-poster">
                            <div class="rating-badge">
                                <?php echo number_format($movie['vote_average'], 1); ?>
                            </div>
                            <div class="rating-type <?php echo $movie['rating_type']; ?>">
                                <?php
                                switch ($movie['rating_type']) {
                                    case 'not_watched': echo 'İzlemedim'; break;
                                    case 'disliked': echo 'Beğenmedim'; break;
                                    case 'liked': echo 'Beğendim'; break;
                                    case 'loved': echo 'Çok Beğendim'; break;
                                }
                                ?>
                            </div>
                        </div>
                        <div class="p-3">
                            <h5 class="mb-2"><?php echo $movie['title']; ?></h5>
                            <a href="movie_detail.php?id=<?php echo $movie['movie_id']; ?>" 
                               class="btn btn-primary btn-sm w-100">
                                Detayları Gör
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Değerlendirilen Diziler -->
        <h3 class="section-title">Değerlendirilen Diziler</h3>
        <div class="row">
            <?php foreach ($ratedTVShows as $tvShow): ?>
                <div class="col-md-4 col-lg-2">
                    <div class="content-card">
                        <div class="position-relative">
                            <img src="<?php echo $tvShow['poster_path']; ?>" 
                                 alt="<?php echo $tvShow['name']; ?>" 
                                 class="content-poster">
                            <div class="rating-badge">
                                <?php echo number_format($tvShow['vote_average'], 1); ?>
                            </div>
                            <div class="rating-type <?php echo $tvShow['rating_type']; ?>">
                                <?php
                                switch ($tvShow['rating_type']) {
                                    case 'not_watched': echo 'İzlemedim'; break;
                                    case 'disliked': echo 'Beğenmedim'; break;
                                    case 'liked': echo 'Beğendim'; break;
                                    case 'loved': echo 'Çok Beğendim'; break;
                                }
                                ?>
                            </div>
                        </div>
                        <div class="p-3">
                            <h5 class="mb-2"><?php echo $tvShow['name']; ?></h5>
                            <a href="tv_show_detail.php?id=<?php echo $tvShow['tv_show_id']; ?>" 
                               class="btn btn-primary btn-sm w-100">
                                Detayları Gör
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-4">Film Öner</h5>
                    <p class="mb-4">En iyi film ve dizi önerileri için doğru adres.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-4">&copy; <?php echo date('Y'); ?> Film Öner. Tüm hakları saklıdır.</p>
                    <div class="footer-links">
                        <a href="about.php" class="text-light me-3">Hakkımızda</a>
                        <a href="contact.php" class="text-light me-3">İletişim</a>
                        <a href="privacy.php" class="text-light">Gizlilik Politikası</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 