<?php
require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get personalized recommendations
$movieRecommendations = getPersonalizedMovieRecommendations($userId, 6);
$tvShowRecommendations = getPersonalizedTVShowRecommendations($userId, 6);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öneriler - Film Öner</title>
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
        <h2 class="mb-4">Size Özel Öneriler</h2>

        <?php if (empty($movieRecommendations) && empty($tvShowRecommendations)): ?>
            <div class="alert alert-info">
                Henüz yeterli değerlendirme yapmadınız. 
                <a href="rate_content.php" class="alert-link">İçerik değerlendirmeye başlayın</a> ve size özel öneriler alın!
            </div>
        <?php else: ?>
            <?php if (!empty($movieRecommendations)): ?>
                <h3 class="section-title">Film Önerileri</h3>
                <div class="row">
                    <?php foreach ($movieRecommendations as $movie): ?>
                        <div class="col-md-4 col-lg-2">
                            <div class="recommendation-card">
                                <div class="position-relative">
                                    <img src="<?php echo $movie['poster_path']; ?>" 
                                         alt="<?php echo $movie['title']; ?>" 
                                         class="recommendation-poster">
                                    <div class="rating-badge">
                                        <?php echo number_format($movie['vote_average'], 1); ?>
                                    </div>
                                </div>
                                <div class="p-3">
                                    <h5 class="mb-2"><?php echo $movie['title']; ?></h5>
                                    <p class="text-muted small mb-0">
                                        <?php echo substr($movie['overview'], 0, 100) . '...'; ?>
                                    </p>
                                    <a href="movie_detail.php?id=<?php echo $movie['movie_id']; ?>" 
                                       class="btn btn-primary btn-sm mt-2 w-100">
                                        Detayları Gör
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($tvShowRecommendations)): ?>
                <h3 class="section-title">Dizi Önerileri</h3>
                <div class="row">
                    <?php foreach ($tvShowRecommendations as $tvShow): ?>
                        <div class="col-md-4 col-lg-2">
                            <div class="recommendation-card">
                                <div class="position-relative">
                                    <img src="<?php echo $tvShow['poster_path']; ?>" 
                                         alt="<?php echo $tvShow['name']; ?>" 
                                         class="recommendation-poster">
                                    <div class="rating-badge">
                                        <?php echo number_format($tvShow['vote_average'], 1); ?>
                                    </div>
                                </div>
                                <div class="p-3">
                                    <h5 class="mb-2"><?php echo $tvShow['name']; ?></h5>
                                    <p class="text-muted small mb-0">
                                        <?php echo substr($tvShow['overview'], 0, 100) . '...'; ?>
                                    </p>
                                    <a href="tv_show_detail.php?id=<?php echo $tvShow['tv_show_id']; ?>" 
                                       class="btn btn-primary btn-sm mt-2 w-100">
                                        Detayları Gör
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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