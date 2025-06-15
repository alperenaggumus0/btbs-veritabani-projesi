<?php
require_once 'config.php';
require_once 'functions.php';

// 1. URL'den filtre parametrelerini al ve temizle
$genreIdsArray = isset($_GET['genre_ids']) && is_array($_GET['genre_ids']) ? $_GET['genre_ids'] : [];
$genreIds = implode(',', array_map('intval', $genreIdsArray)); // Yordam için virgülle ayrılmış string'e çevir

$yearRange = isset($_GET['year_range']) ? sanitizeInput($_GET['year_range']) : null;
$ratingRange = isset($_GET['rating_range']) ? sanitizeInput($_GET['rating_range']) : null;
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'popularity_desc';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;

// 2. Saklı yordamı çağır
try {
    // Yordamı çağırırken hem IN hem de OUT parametrelerini kullanıyoruz
    $stmt = $pdo->prepare("CALL sp_FilterMovies(:genre_ids, :year_range, :rating_range, :sort_by, :page_num, :page_size, @total_records)");
    
    // IN parametrelerini bağla
    $stmt->bindValue(':genre_ids', !empty($genreIds) ? $genreIds : null, PDO::PARAM_STR);
    $stmt->bindValue(':year_range', $yearRange, PDO::PARAM_STR);
    $stmt->bindValue(':rating_range', $ratingRange, PDO::PARAM_STR);
    $stmt->bindValue(':sort_by', $sort, PDO::PARAM_STR);
    $stmt->bindValue(':page_num', $page, PDO::PARAM_INT);
    $stmt->bindValue(':page_size', $perPage, PDO::PARAM_INT);
    
    // Yordamı çalıştır
    $stmt->execute();
    
    // Sonuçları (filmleri) al
    $movies = $stmt->fetchAll();
    
    // Bir sonraki sonuç setine geç (OUT parametresini okumak için gerekli)
    $stmt->nextRowset();
    $stmt->closeCursor();

    // OUT parametresinin değerini (toplam kayıt sayısı) al
    $totalMoviesResult = $pdo->query("SELECT @total_records AS total")->fetch(PDO::FETCH_ASSOC);
    $totalMovies = $totalMoviesResult ? (int)$totalMoviesResult['total'] : 0;
    
    $totalPages = ceil($totalMovies / $perPage);

} catch (PDOException $e) {
    // Hata durumunda boş değerler ata
    $movies = [];
    $totalMovies = 0;
    $totalPages = 0;
    error_log("Movie filtering failed (SP): " . $e->getMessage());
    // İstersen kullanıcıya bir hata mesajı da gösterebilirsin.
}

// 3. Filtreleme formları için gerekli verileri (tüm türler vb.) getirelim
// Bu yordamları daha önce oluşturmuştuk.
$stmt = $pdo->prepare("CALL sp_GetAllGenres()");
$stmt->execute();
$genres = $stmt->fetchAll();
$stmt->closeCursor();

// Yıllar için veritabanını sorgulamak yerine sabit bir liste kullanabiliriz, bu daha performanslı.
// Ancak dinamik kalması için yordamla devam edelim.
$stmt = $pdo->prepare("CALL sp_GetAllMovieYears()");
$stmt->execute();
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);
$stmt->closeCursor();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filmler - Film Öner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.css">
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
                        <a class="nav-link active" href="movies.php">Filmler</a>
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

    <!-- Filters -->
    <div class="container my-4">
        <div class="card bg-dark border-secondary">
            <div class="card-body">
                <form method="GET">
                    <div class="filters-container">
                        <div class="filter-box">
                            <div class="filter-title">Türler</div>
                            <div class="genre-select-container">
                                <div class="custom-select" id="genreSelect">
                                    <?php foreach ($genres as $g): ?>
                                        <div class="select-option" data-value="<?php echo $g['genre_id']; ?>" <?php echo in_array($g['genre_id'], $genreIdsArray) ? 'data-selected="true"' : ''; ?>>
                                            <?php echo htmlspecialchars($g['name']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="genre_ids[]" id="selectedGenresInput">
                            </div>
                        </div>
                        <div class="filter-box">
                            <div class="filter-title">Yıl Aralığı</div>
                            <div class="custom-select year-select" id="yearRangeSelect">
                                <div class="select-option<?php if($yearRange === '') echo ' selected'; ?>" data-value="">Tüm Yıllar</div>
                                <div class="select-option<?php if($yearRange === 'before_2000') echo ' selected'; ?>" data-value="before_2000">2000 Öncesi</div>
                                <div class="select-option<?php if($yearRange === '2000_2010') echo ' selected'; ?>" data-value="2000_2010">2000-2010</div>
                                <div class="select-option<?php if($yearRange === '2010_2020') echo ' selected'; ?>" data-value="2010_2020">2010-2020</div>
                                <div class="select-option<?php if($yearRange === 'after_2020') echo ' selected'; ?>" data-value="after_2020">2020 Sonrası</div>
                            </div>
                            <input type="hidden" name="year_range" id="selectedYearRange" value="<?php echo $yearRange; ?>">
                        </div>
                        <div class="filter-box">
                            <div class="filter-title">IMDB Puanı</div>
                            <div class="custom-select rating-select" id="ratingRangeSelect">
                                <div class="select-option<?php if($ratingRange === '') echo ' selected'; ?>" data-value="">Tümü</div>
                                <div class="select-option<?php if($ratingRange === '0_2.5') echo ' selected'; ?>" data-value="0_2.5">0 - 2,5</div>
                                <div class="select-option<?php if($ratingRange === '2.5_5') echo ' selected'; ?>" data-value="2.5_5">2,5 - 5</div>
                                <div class="select-option<?php if($ratingRange === '5_7.5') echo ' selected'; ?>" data-value="5_7.5">5 - 7,5</div>
                                <div class="select-option<?php if($ratingRange === '7.5_10') echo ' selected'; ?>" data-value="7.5_10">7,5 - 10</div>
                            </div>
                            <input type="hidden" name="rating_range" id="selectedRatingRange" value="<?php echo $ratingRange; ?>">
                        </div>
                        <div class="filter-box">
                            <div class="filter-title">Sıralama</div>
                            <div class="custom-select sort-select" id="sortSelect">
                                <div class="select-option<?php if($sort == 'popularity_desc') echo ' selected'; ?>" data-value="popularity_desc">Popülerlik (Yüksek-Düşük)</div>
                                <div class="select-option<?php if($sort == 'rating_desc') echo ' selected'; ?>" data-value="rating_desc">Puan (Yüksek-Düşük)</div>
                                <div class="select-option<?php if($sort == 'rating_asc') echo ' selected'; ?>" data-value="rating_asc">Puan (Düşük-Yüksek)</div>
                                <div class="select-option<?php if($sort == 'date_desc') echo ' selected'; ?>" data-value="date_desc">Tarih (Yeni-Eski)</div>
                                <div class="select-option<?php if($sort == 'date_asc') echo ' selected'; ?>" data-value="date_asc">Tarih (Eski-Yeni)</div>
                            </div>
                            <input type="hidden" name="sort" id="selectedSort" value="<?php echo $sort; ?>">
                        </div>
                    </div>
                    <div class="filter-buttons mt-3">
                        <button type="submit" class="btn btn-primary">Filtrele</button>
                        <a href="movies.php" class="btn btn-secondary">Sıfırla</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Movies Grid -->
    <div class="container my-4">
        <h2 class="mb-4">Filmler</h2>
        
        <?php if (empty($movies)): ?>
            <div class="alert alert-info">
                Seçilen kriterlere uygun film bulunamadı.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($movies as $movie): ?>
                <div class="col">
                    <div class="card bg-dark text-light movie-card">
                        <div class="position-relative">
                            <img src="https://image.tmdb.org/t/p/w500<?php echo $movie['poster_path']; ?>" 
                                 class="card-img-top movie-poster" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            <span class="rating-badge">
                                <i class="bi bi-star-fill text-warning"></i>
                                <?php echo number_format($movie['vote_average'], 1); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                            <p class="card-text text-truncate"><?php echo htmlspecialchars($movie['overview']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?php echo date('Y', strtotime($movie['release_date'])); ?></small>
                                <a href="movie_detail.php?id=<?php echo $movie['movie_id']; ?>" class="btn btn-primary">Detaylar</a>
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
                            <a class="page-link bg-dark text-light" href="?page=<?php echo $page-1; ?>&<?php echo http_build_query(array_merge($_GET, ['page' => $page-1])); ?>">
                                Önceki
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link bg-dark text-light" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link bg-dark text-light" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page+1])); ?>">
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
    <script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.js"></script>
    <script>
        // Custom genre select
        const genreSelect = document.getElementById('genreSelect');
        const selectedGenresInput = document.getElementById('selectedGenresInput');
        const options = genreSelect.querySelectorAll('.select-option');
        let selectedGenres = selectedGenresInput.value ? selectedGenresInput.value.split(',').map(Number) : [];

        // Initialize selected options
        options.forEach(option => {
            if (option.dataset.selected === 'true') {
                option.classList.add('selected');
            }
        });

        // Handle option clicks
        options.forEach(option => {
            option.addEventListener('click', () => {
                const value = parseInt(option.dataset.value);
                const index = selectedGenres.indexOf(value);

                if (index === -1) {
                    // Add to selection
                    selectedGenres.push(value);
                    option.classList.add('selected');
                } else {
                    // Remove from selection
                    selectedGenres.splice(index, 1);
                    option.classList.remove('selected');
                }

                // Update hidden input
                selectedGenresInput.value = selectedGenres.join(',');
            });
        });

        // Yıl aralığı custom select
        const yearRangeSelect = document.getElementById('yearRangeSelect');
        const selectedYearRangeInput = document.getElementById('selectedYearRange');
        if (yearRangeSelect && selectedYearRangeInput) {
            const yearOptions = yearRangeSelect.querySelectorAll('.select-option');
            yearOptions.forEach(option => {
                option.addEventListener('click', () => {
                    yearOptions.forEach(o => o.classList.remove('selected'));
                    option.classList.add('selected');
                    selectedYearRangeInput.value = option.dataset.value;
                });
            });
        }

        // IMDB puanı custom select
        const ratingRangeSelect = document.getElementById('ratingRangeSelect');
        const selectedRatingRangeInput = document.getElementById('selectedRatingRange');
        if (ratingRangeSelect && selectedRatingRangeInput) {
            const ratingOptions = ratingRangeSelect.querySelectorAll('.select-option');
            ratingOptions.forEach(option => {
                option.addEventListener('click', () => {
                    ratingOptions.forEach(o => o.classList.remove('selected'));
                    option.classList.add('selected');
                    selectedRatingRangeInput.value = option.dataset.value;
                });
            });
        }

        // Sıralama custom select
        const sortSelect = document.getElementById('sortSelect');
        const selectedSortInput = document.getElementById('selectedSort');
        if (sortSelect && selectedSortInput) {
            const sortOptions = sortSelect.querySelectorAll('.select-option');
            sortOptions.forEach(option => {
                option.addEventListener('click', () => {
                    sortOptions.forEach(o => o.classList.remove('selected'));
                    option.classList.add('selected');
                    selectedSortInput.value = option.dataset.value;
                });
            });
        }
    </script>
</body>
</html> 