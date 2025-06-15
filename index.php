<?php
require_once 'config.php';
require_once 'functions.php';

// Cache key oluştur
$cacheKey = 'homepage_content';

// Cache'den veriyi al
$cachedData = getCachedData($cacheKey);

if ($cachedData) {
    $popularMovies = $cachedData['movies'];
    $popularTVShows = $cachedData['shows'];
} else {
    // Get popular content
    $popularMovies = getPopularMovies(6);
    $popularTVShows = getPopularTVShows(6);
    
    // Cache'e kaydet (1 saat)
    cacheData($cacheKey, [
        'movies' => $popularMovies,
        'shows' => $popularTVShows
    ], 3600);
}

// Kullanıcı oturum durumunu kontrol et
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FilmOner.com - Film ve Dizi Öneri Platformu</title>
    <meta name="description" content="En popüler film ve dizileri keşfedin, size özel öneriler alın.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading" id="loading">
        <div class="loading-spinner"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">FilmOner.com</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="movies.php">Filmler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tv_shows.php">Diziler</a>
                    </li>
                    <?php if ($isLoggedIn): ?>
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
                    <?php if ($isLoggedIn): ?>
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-background">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <div class="container">
                    <h1 class="hero-title" data-aos="fade-up">FilmOner.com</h1>
                    <p class="hero-description" data-aos="fade-up" data-aos-delay="200">
                        En popüler film ve dizileri keşfedin, size özel öneriler alın.
                    </p>
                    <?php if (!$isLoggedIn): ?>
                    <a href="register.php" class="btn btn-primary" data-aos="fade-up" data-aos-delay="400">
                        <i class="fas fa-user-plus me-2"></i>Hemen Başla
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Movies Section -->
    <section class="content-section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-right">Popüler Filmler</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($popularMovies as $index => $movie): ?>
                <div class="col" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="movie-card">
                        <div class="position-relative overflow-hidden">
                            <?php
                            $posterUrl = !empty($movie['poster_path']) 
                                ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path']
                                : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAwIiBoZWlnaHQ9IjUwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNTAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iIzFGMUYxRiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM2NjY2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5GSUxNT05FUi5DT00gTG9nbzwvdGV4dD48L3N2Zz4=';
                            ?>
                            <img src="<?php echo $posterUrl; ?>" 
                                 class="poster-img" 
                                 alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAwIiBoZWlnaHQ9IjUwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNTAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iIzFGMUYxRiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM2NjY2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5GSUxNT05FUi5DT00gTG9nbzwvdGV4dD48L3N2Zz4=';">
                            <span class="rating-badge">
                                <i class="bi bi-star-fill text-warning"></i>
                                <?php echo number_format($movie['vote_average'], 1); ?>
                            </span>
                        </div>
                        <div class="card-content">
                            <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                            <p class="card-text text-truncate"><?php echo htmlspecialchars($movie['overview']); ?></p>
                            <a href="movie_detail.php?id=<?php echo $movie['movie_id']; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-info-circle me-2"></i>Detaylar
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="movies.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-film me-2"></i>Tüm Filmleri Gör
                </a>
            </div>
        </div>
    </section>

    <!-- Popular TV Shows Section -->
    <section class="content-section bg-dark">
        <div class="container">
            <h2 class="section-title" data-aos="fade-right">Popüler Diziler</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($popularTVShows as $index => $show): ?>
                <div class="col" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="movie-card">
                        <div class="position-relative overflow-hidden">
                            <?php
                            $posterUrl = !empty($show['poster_path']) 
                                ? 'https://image.tmdb.org/t/p/w500' . $show['poster_path']
                                : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAwIiBoZWlnaHQ9IjUwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNTAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iIzFGMUYxRiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM2NjY2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5GSUxNT05FUi5DT00gTG9nbzwvdGV4dD48L3N2Zz4=';
                            ?>
                            <img src="<?php echo $posterUrl; ?>" 
                                 class="poster-img" 
                                 alt="<?php echo htmlspecialchars($show['name']); ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAwIiBoZWlnaHQ9IjUwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNTAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iIzFGMUYxRiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM2NjY2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5GSUxNT05FUi5DT00gTG9nbzwvdGV4dD48L3N2Zz4=';">
                            <span class="rating-badge">
                                <i class="bi bi-star-fill text-warning"></i>
                                <?php echo number_format($show['vote_average'], 1); ?>
                            </span>
                        </div>
                        <div class="card-content">
                            <h5 class="card-title"><?php echo htmlspecialchars($show['name']); ?></h5>
                            <p class="card-text text-truncate"><?php echo htmlspecialchars($show['overview']); ?></p>
                            <a href="tv_show_detail.php?id=<?php echo $show['tv_show_id']; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-info-circle me-2"></i>Detaylar
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="tv_shows.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-tv me-2"></i>Tüm Dizileri Gör
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-4">FilmOner.com</h5>
                    <p class="mb-4">En iyi film ve dizi önerileri için doğru adres.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-4">&copy; <?php echo date('Y'); ?> FilmOner.com. Tüm hakları saklıdır.</p>
                    <div class="footer-links">
                        <a href="about.php" class="text-light me-3">Hakkımızda</a>
                        <a href="contact.php" class="text-light me-3">İletişim</a>
                        <a href="privacy.php" class="text-light">Gizlilik Politikası</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js" crossorigin="anonymous"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Hide loading spinner when page is ready
        document.addEventListener('DOMContentLoaded', function() {
            const loading = document.getElementById('loading');
            if (loading) {
                loading.style.display = 'none';
            }
        });

        // Lazy loading
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                    }
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    </script>
</body>
</html> 