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

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentId = (int)$_POST['content_id'];
    $contentType = $_POST['content_type'];
    $ratingType = $_POST['rating_type'];
    
    if ($contentType === 'movie') {
        $success = rateMovie($userId, $contentId, $ratingType);
    } else {
        $success = rateTVShow($userId, $contentId, $ratingType);
    }
    
    if ($success === true) {
        $message = "Değerlendirmeniz kaydedildi!";
    } else {
        // Display the detailed error message from the function
        $message = "Bir hata oluştu: " . $success . ". Lütfen tekrar deneyin.";
    }
}

// Get random content to rate
$movie = getRandomMovieToRate($userId);
$tvShow = getRandomTVShowToRate($userId);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İçerik Değerlendir - Film Öner</title>
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

    <div class="container my-5 d-flex justify-content-center align-items-center" style="min-height:70vh;">
        <div class="w-100" style="max-width: 420px;">
            <h2 class="mb-4 text-center">İçerik Değerlendir</h2>
            <?php if ($message): ?>
                <div class="alert alert-info text-center"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($movie || $tvShow): ?>
                <?php $content = $movie ?: $tvShow; $isMovie = !!$movie; ?>
                <div class="card bg-dark text-light shadow-lg border-0 rounded-4">
                    <div class="position-relative rounded-top-4 overflow-hidden">
                        <img src="https://image.tmdb.org/t/p/w500<?php echo $content[$isMovie ? 'poster_path' : 'poster_path']; ?>" alt="<?php echo htmlspecialchars($isMovie ? $content['title'] : $content['name']); ?>" class="card-img-top" style="object-fit:cover; height:420px; border-top-left-radius:1.5rem; border-top-right-radius:1.5rem;">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title text-center mb-3"><?php echo htmlspecialchars($isMovie ? $content['title'] : $content['name']); ?></h4>
                        <p class="card-text text-center text-truncate mb-4" title="<?php echo htmlspecialchars($content['overview']); ?>"><?php echo htmlspecialchars(mb_strimwidth($content['overview'], 0, 150, '...')); ?></p>
                        <form method="POST" action="" class="mt-auto">
                            <input type="hidden" name="content_id" value="<?php echo $isMovie ? $content['movie_id'] : $content['tv_show_id']; ?>">
                            <input type="hidden" name="content_type" value="<?php echo $isMovie ? 'movie' : 'tv_show'; ?>">
                            <div class="d-flex flex-column gap-2">
                                <button type="submit" name="rating_type" value="not_watched" class="btn btn-outline-secondary rounded-pill py-2 fw-semibold">İzlemedim</button>
                                <button type="submit" name="rating_type" value="disliked" class="btn btn-outline-danger rounded-pill py-2 fw-semibold">Beğenmedim</button>
                                <button type="submit" name="rating_type" value="liked" class="btn btn-outline-success rounded-pill py-2 fw-semibold">Beğendim</button>
                                <button type="submit" name="rating_type" value="loved" class="btn btn-primary rounded-pill py-2 fw-semibold">Çok Beğendim</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    Değerlendirebileceğiniz tüm içerikleri değerlendirdiniz! <a href="index.php" class="alert-link">Ana sayfaya dön</a> ve size özel önerileri görüntüleyin.
                </div>
            <?php endif; ?>
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