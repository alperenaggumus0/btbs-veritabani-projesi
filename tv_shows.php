<?php
require_once 'config.php';
require_once 'functions.php';

// Get filter parameters
$genreId = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$minRating = isset($_GET['min_rating']) ? (float)$_GET['min_rating'] : 0;
$maxRating = isset($_GET['max_rating']) ? (float)$_GET['max_rating'] : 10;
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'popularity_desc';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;

// Build query
$query = "SELECT t.*, GROUP_CONCAT(DISTINCT g.name) as genres
          FROM tv_shows t
          LEFT JOIN tv_show_genres tg ON t.tv_show_id = tg.tv_show_id
          LEFT JOIN genres g ON tg.genre_id = g.genre_id
          WHERE 1=1";
$params = [];

if ($genreId) {
    $query .= " AND EXISTS (SELECT 1 FROM tv_show_genres tg2 WHERE tg2.tv_show_id = t.tv_show_id AND tg2.genre_id = :genre_id)";
    $params[':genre_id'] = $genreId;
}

if ($year) {
    $query .= " AND YEAR(t.first_air_date) = :year";
    $params[':year'] = $year;
}

if ($minRating > 0) {
    $query .= " AND t.vote_average >= :min_rating";
    $params[':min_rating'] = $minRating;
}

if ($maxRating < 10) {
    $query .= " AND t.vote_average <= :max_rating";
    $params[':max_rating'] = $maxRating;
}

$query .= " GROUP BY t.tv_show_id";

// Add sorting
switch ($sort) {
    case 'rating_desc':
        $query .= " ORDER BY t.vote_average DESC";
        break;
    case 'rating_asc':
        $query .= " ORDER BY t.vote_average ASC";
        break;
    case 'date_desc':
        $query .= " ORDER BY t.first_air_date DESC";
        break;
    case 'date_asc':
        $query .= " ORDER BY t.first_air_date ASC";
        break;
    default: // popularity_desc
        $query .= " ORDER BY t.popularity DESC";
}

// Get total count for pagination
$countQuery = str_replace("SELECT t.*, GROUP_CONCAT(DISTINCT g.name) as genres", "SELECT COUNT(DISTINCT t.tv_show_id) as total", $query);
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalShows = $stmt->fetch()['total'];
$totalPages = ceil($totalShows / $perPage);

// Add pagination
$query .= " LIMIT :offset, :limit";
$params[':offset'] = ($page - 1) * $perPage;
$params[':limit'] = $perPage;

// Get TV shows
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$tvShows = $stmt->fetchAll();

// Get all genres for filter
$stmt = $pdo->query("SELECT * FROM genres ORDER BY name");
$genres = $stmt->fetchAll();

// Get years for filter
$stmt = $pdo->query("SELECT DISTINCT YEAR(first_air_date) as year FROM tv_shows ORDER BY year DESC");
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diziler - Film Öner</title>
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
                        <a class="nav-link active" href="tv_shows.php">Diziler</a>
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

    <!-- Filters -->
    <div class="container my-4">
        <div class="card bg-dark border-secondary">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tür</label>
                        <select name="genre_id" class="form-select bg-dark text-light">
                            <option value="">Tümü</option>
                            <?php foreach ($genres as $g): ?>
                                <option value="<?php echo $g['genre_id']; ?>" <?php echo $genreId == $g['genre_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($g['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Yıl</label>
                        <select name="year" class="form-select bg-dark text-light">
                            <option value="">Tümü</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Min Puan</label>
                        <input type="number" name="min_rating" class="form-control bg-dark text-light" 
                               min="0" max="10" step="0.1" value="<?php echo $minRating; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Max Puan</label>
                        <input type="number" name="max_rating" class="form-control bg-dark text-light" 
                               min="0" max="10" step="0.1" value="<?php echo $maxRating; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sıralama</label>
                        <select name="sort" class="form-select bg-dark text-light">
                            <option value="popularity_desc" <?php echo $sort == 'popularity_desc' ? 'selected' : ''; ?>>Popülerlik (Yüksek-Düşük)</option>
                            <option value="rating_desc" <?php echo $sort == 'rating_desc' ? 'selected' : ''; ?>>Puan (Yüksek-Düşük)</option>
                            <option value="rating_asc" <?php echo $sort == 'rating_asc' ? 'selected' : ''; ?>>Puan (Düşük-Yüksek)</option>
                            <option value="date_desc" <?php echo $sort == 'date_desc' ? 'selected' : ''; ?>>Tarih (Yeni-Eski)</option>
                            <option value="date_asc" <?php echo $sort == 'date_asc' ? 'selected' : ''; ?>>Tarih (Eski-Yeni)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Filtrele</button>
                        <a href="tv_shows.php" class="btn btn-secondary">Sıfırla</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TV Shows Grid -->
    <div class="container my-4">
        <h2 class="mb-4">Diziler</h2>
        
        <?php if (empty($tvShows)): ?>
            <div class="alert alert-info">
                Seçilen kriterlere uygun dizi bulunamadı.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($tvShows as $show): ?>
                <div class="col">
                    <div class="card bg-dark text-light show-card">
                        <div class="position-relative">
                            <img src="https://image.tmdb.org/t/p/w500<?php echo $show['poster_path']; ?>" 
                                 class="card-img-top poster-img" alt="<?php echo htmlspecialchars($show['name']); ?>">
                            <span class="rating-badge">
                                <i class="bi bi-star-fill text-warning"></i>
                                <?php echo number_format($show['vote_average'], 1); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($show['name']); ?></h5>
                            <p class="card-text text-truncate"><?php echo htmlspecialchars($show['overview']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?php echo date('Y', strtotime($show['first_air_date'])); ?> • 
                                    <?php echo $show['number_of_seasons']; ?> Sezon
                                </small>
                                <a href="tv_show_detail.php?id=<?php echo $show['tv_show_id']; ?>" class="btn btn-primary">Detaylar</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link bg-dark text-light" href="?page=<?php echo $page-1; ?>&genre_id=<?php echo $genreId; ?>&year=<?php echo $year; ?>&min_rating=<?php echo $minRating; ?>&max_rating=<?php echo $maxRating; ?>&sort=<?php echo $sort; ?>">
                                Önceki
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link bg-dark text-light" href="?page=<?php echo $i; ?>&genre_id=<?php echo $genreId; ?>&year=<?php echo $year; ?>&min_rating=<?php echo $minRating; ?>&max_rating=<?php echo $maxRating; ?>&sort=<?php echo $sort; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link bg-dark text-light" href="?page=<?php echo $page+1; ?>&genre_id=<?php echo $genreId; ?>&year=<?php echo $year; ?>&min_rating=<?php echo $minRating; ?>&max_rating=<?php echo $maxRating; ?>&sort=<?php echo $sort; ?>">
                                Sonraki
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
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