<?php
require_once 'config.php';
require_once 'functions.php';

// Get movie ID from URL
$movieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get movie details
$movie = getMovieDetails($movieId);

// If movie not found, redirect to home page
if (!$movie) {
    header('Location: index.php');
    exit;
}

// Get similar movies
$similarMovies = getSimilarMovies($movieId);

// Split comma-separated values into arrays
$genres = explode(',', $movie['genres']);
$actors = explode(',', $movie['actors']);
$keywords = explode(',', $movie['keywords']);
$productionCompanies = explode(',', $movie['production_companies']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Film Detayı - Film Öner</title>
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

    <!-- Movie Backdrop -->
    <div class="backdrop" style="background-image: url('https://image.tmdb.org/t/p/original<?php echo $movie['backdrop_path']; ?>')">
        <div class="container movie-info py-5">
            <div class="row">
                <div class="col-md-4">
                    <img src="https://image.tmdb.org/t/p/w500<?php echo $movie['poster_path']; ?>" 
                         class="img-fluid rounded shadow" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                </div>
                <div class="col-md-8">
                    <h1 class="display-4 mb-3"><?php echo htmlspecialchars($movie['title']); ?></h1>
                    <?php if ($movie['original_title'] !== $movie['title']): ?>
                        <h4 class="text-muted mb-3"><?php echo htmlspecialchars($movie['original_title']); ?></h4>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <span class="badge bg-warning text-dark me-2">
                            <i class="bi bi-star-fill"></i> <?php echo number_format($movie['vote_average'], 1); ?>
                        </span>
                        <span class="badge bg-secondary me-2">
                            <?php echo date('Y', strtotime($movie['release_date'])); ?>
                        </span>
                        <span class="badge bg-secondary me-2">
                            <?php echo $movie['runtime']; ?> dakika
                        </span>
                        <span class="badge bg-secondary">
                            <?php echo strtoupper($movie['original_language']); ?>
                        </span>
                    </div>

                    <p class="lead mb-4"><?php echo htmlspecialchars($movie['overview']); ?></p>

                    <div class="mb-4">
                        <h5>Türler</h5>
                        <?php foreach ($genres as $genre): ?>
                            <span class="badge bg-primary me-2"><?php echo htmlspecialchars($genre); ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-4">
                        <h5>Oyuncular</h5>
                        <div class="row">
                            <?php foreach (array_slice($actors, 0, 5) as $actor): ?>
                                <div class="col-auto">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($actor); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Yapım Şirketleri</h5>
                        <?php foreach ($productionCompanies as $company): ?>
                            <span class="badge bg-info text-dark me-2"><?php echo htmlspecialchars($company); ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-4">
                        <h5>Anahtar Kelimeler</h5>
                        <?php foreach ($keywords as $keyword): ?>
                            <span class="badge bg-secondary me-2"><?php echo htmlspecialchars($keyword); ?></span>
                        <?php endforeach; ?>
                    </div>

                    <a href="<?php echo $movie['tmdb_url']; ?>" target="_blank" class="btn btn-primary">
                        <i class="bi bi-box-arrow-up-right"></i> TMDB'de Görüntüle
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Similar Movies Section -->
    <div class="container my-5">
        <h2 class="mb-4">Benzer Filmler</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($similarMovies as $similarMovie): ?>
            <div class="col">
                <div class="card bg-dark text-light movie-card">
                    <img src="https://image.tmdb.org/t/p/w500<?php echo $similarMovie['poster_path']; ?>" 
                         class="card-img-top poster-img" alt="<?php echo htmlspecialchars($similarMovie['title']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($similarMovie['title']); ?></h5>
                        <p class="card-text text-truncate"><?php echo htmlspecialchars($similarMovie['overview']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-star-fill"></i> <?php echo number_format($similarMovie['vote_average'], 1); ?>
                            </span>
                            <a href="movie_detail.php?id=<?php echo $similarMovie['movie_id']; ?>" class="btn btn-primary">Detaylar</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
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