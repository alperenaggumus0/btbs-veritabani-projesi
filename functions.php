<?php
require_once 'config.php';

/**
 * Get popular movies using a stored procedure
 * @param int $limit Number of movies to return
 * @return array Array of popular movies
 */
function getPopularMovies($limit = 10) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("CALL sp_GetPopularMovies(:limit)");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $movies = $stmt->fetchAll();
        $stmt->closeCursor();
        return $movies;
    } catch (PDOException $e) {
        error_log("Get Popular Movies failed (SP): " . $e->getMessage());
        return []; // Hata durumunda boş dizi döndür
    }
}

/**
 * Get popular TV shows using a stored procedure
 * @param int $limit Number of TV shows to return
 * @return array Array of popular TV shows
 */
function getPopularTVShows($limit = 10) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("CALL sp_GetPopularTVShows(:limit)");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $tvShows = $stmt->fetchAll();
        $stmt->closeCursor();
        return $tvShows;
    } catch (PDOException $e) {
        error_log("Get Popular TV Shows failed (SP): " . $e->getMessage());
        return []; // Hata durumunda boş dizi döndür
    }
}

/**
 * Get movie details by ID using a stored procedure
 * @param int $movieId Movie ID
 * @return array|false Movie details or false if not found
 */
function getMovieDetails($movieId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("CALL sp_GetMovieDetails(:movie_id)");
        $stmt->bindValue(':movie_id', $movieId, PDO::PARAM_INT);
        $stmt->execute();
        $movie = $stmt->fetch();
        $stmt->closeCursor();
        return $movie;
    } catch (PDOException $e) {
        error_log("Get Movie Details failed (SP): " . $e->getMessage());
        return false;
    }
}

/**
 * Get TV show details by ID
 * @param int $tvShowId TV Show ID
 * @return array|false TV Show details or false if not found
 */
function getTVShowDetails($tvShowId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT t.*, 
               GROUP_CONCAT(DISTINCT g.name) as genres,
               GROUP_CONCAT(DISTINCT a.name) as actors,
               GROUP_CONCAT(DISTINCT k.name) as keywords,
               GROUP_CONCAT(DISTINCT pc.name) as production_companies
        FROM tv_shows t
        LEFT JOIN tv_show_genres tg ON t.tv_show_id = tg.tv_show_id
        LEFT JOIN genres g ON tg.genre_id = g.genre_id
        LEFT JOIN tv_show_cast tc ON t.tv_show_id = tc.tv_show_id
        LEFT JOIN actors a ON tc.actor_id = a.actor_id
        LEFT JOIN tv_show_keywords tk ON t.tv_show_id = tk.tv_show_id
        LEFT JOIN keywords k ON tk.keyword_id = k.keyword_id
        LEFT JOIN tv_show_production_companies tpc ON t.tv_show_id = tpc.tv_show_id
        LEFT JOIN production_companies pc ON tpc.company_id = pc.company_id
        WHERE t.tv_show_id = :tv_show_id
        GROUP BY t.tv_show_id
    ");
    $stmt->bindValue(':tv_show_id', $tvShowId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Get similar movies based on genres and keywords
 * @param int $movieId Movie ID
 * @param int $limit Number of similar movies to return
 * @return array Array of similar movies
 */
function getSimilarMovies($movieId, $limit = 6) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT DISTINCT m.*, 
            COUNT(DISTINCT mg2.genre_id) as common_genres,
            COUNT(DISTINCT mk2.keyword_id) as common_keywords
        FROM movies m
        JOIN movie_genres mg1 ON mg1.movie_id = :movie_id1
        JOIN movie_genres mg2 ON mg2.genre_id = mg1.genre_id AND mg2.movie_id != :movie_id2
        LEFT JOIN movie_keywords mk1 ON mk1.movie_id = :movie_id3
        LEFT JOIN movie_keywords mk2 ON mk2.keyword_id = mk1.keyword_id AND mk2.movie_id = m.movie_id
        WHERE m.movie_id != :movie_id4
        GROUP BY m.movie_id
        ORDER BY common_genres DESC, common_keywords DESC, m.popularity DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':movie_id1', $movieId, PDO::PARAM_INT);
    $stmt->bindValue(':movie_id2', $movieId, PDO::PARAM_INT);
    $stmt->bindValue(':movie_id3', $movieId, PDO::PARAM_INT);
    $stmt->bindValue(':movie_id4', $movieId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get similar TV shows based on genres and keywords
 * @param int $tvShowId TV Show ID
 * @param int $limit Number of similar TV shows to return
 * @return array Array of similar TV shows
 */
function getSimilarTVShows($tvShowId, $limit = 6) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT DISTINCT t.*, 
            COUNT(DISTINCT tg2.genre_id) as common_genres,
            COUNT(DISTINCT tk2.keyword_id) as common_keywords
        FROM tv_shows t
        JOIN tv_show_genres tg1 ON tg1.tv_show_id = :tv_show_id1
        JOIN tv_show_genres tg2 ON tg2.genre_id = tg1.genre_id AND tg2.tv_show_id != :tv_show_id2
        LEFT JOIN tv_show_keywords tk1 ON tk1.tv_show_id = :tv_show_id3
        LEFT JOIN tv_show_keywords tk2 ON tk2.keyword_id = tk1.keyword_id AND tk2.tv_show_id = t.tv_show_id
        WHERE t.tv_show_id != :tv_show_id4
        GROUP BY t.tv_show_id
        ORDER BY common_genres DESC, common_keywords DESC, t.popularity DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':tv_show_id1', $tvShowId, PDO::PARAM_INT);
    $stmt->bindValue(':tv_show_id2', $tvShowId, PDO::PARAM_INT);
    $stmt->bindValue(':tv_show_id3', $tvShowId, PDO::PARAM_INT);
    $stmt->bindValue(':tv_show_id4', $tvShowId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Rate a movie using a stored procedure
 * @param int $userId User ID
 * @param int $movieId Movie ID
 * @param string $ratingType Rating type
 * @return bool|string True on success, error message on failure
 */
function rateMovie($userId, $movieId, $ratingType) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("CALL sp_RateMovie(:user_id, :movie_id, :rating_type)");
        $stmt->execute([
            ':user_id' => $userId,
            ':movie_id' => $movieId,
            ':rating_type' => $ratingType
        ]);
        $stmt->closeCursor();
        return true;
    } catch (PDOException $e) {
        error_log("Movie rating error (SP): " . $e->getMessage());
        return "Veritabanı hatası: " . $e->getMessage();
    }
}

/**
 * Rate a TV show using a stored procedure
 * @param int $userId User ID
 * @param int $tvShowId TV Show ID
 * @param string $ratingType Rating type
 * @return bool|string True on success, error message on failure
 */
function rateTVShow($userId, $tvShowId, $ratingType) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("CALL sp_RateTVShow(:user_id, :tv_show_id, :rating_type)");
        $stmt->execute([
            ':user_id' => $userId,
            ':tv_show_id' => $tvShowId,
            ':rating_type' => $ratingType
        ]);
        $stmt->closeCursor();
        return true;
    } catch (PDOException $e) {
        error_log("TV Show rating error (SP): " . $e->getMessage());
        return "Veritabanı hatası: " . $e->getMessage();
    }
}

/**
 * Search movies and TV shows using a stored procedure
 * @param string $query Search query
 * @return array Array of search results
 */
function searchContent($query) {
    global $pdo;
    try {
        $searchTerm = "%{$query}%";
        $stmt = $pdo->prepare("CALL sp_SearchContent(:query)");
        $stmt->bindValue(':query', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $stmt->closeCursor();
        return $results;
    } catch (PDOException $e) {
        error_log("Search failed (SP): " . $e->getMessage());
        return [];
    }
}

/**
 * Sanitize user input
 * @param string $input User input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 * @param string $email Email address to validate
 * @return bool True if email is valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return bool True if password is strong enough
 */
function validatePassword($password) {
    // En az 8 karakter, 1 büyük harf, 1 küçük harf, 1 sayı ve 1 özel karakter
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password) &&
           preg_match('/[^A-Za-z0-9]/', $password);
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if token is valid
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Cache data
 * @param string $key Cache key
 * @param mixed $data Data to cache
 * @param int $ttl Time to live in seconds
 */
function cacheData($key, $data, $ttl = 3600) {
    $cacheDir = __DIR__ . '/cache';
    if (!file_exists($cacheDir)) {
        if (!mkdir($cacheDir, 0777, true)) {
            error_log("Failed to create cache directory: " . $cacheDir);
            return false;
        }
    }
    
    $cacheFile = $cacheDir . '/' . md5($key) . '.cache';
    $cacheData = [
        'data' => $data,
        'expires' => time() + $ttl
    ];
    
    try {
        file_put_contents($cacheFile, serialize($cacheData));
        return true;
    } catch (Exception $e) {
        error_log("Failed to write cache file: " . $e->getMessage());
        return false;
    }
}

/**
 * Get cached data
 * @param string $key Cache key
 * @return mixed|null Cached data or null if expired/not found
 */
function getCachedData($key) {
    $cacheFile = __DIR__ . '/cache/' . md5($key) . '.cache';
    if (file_exists($cacheFile)) {
        try {
            $cacheData = unserialize(file_get_contents($cacheFile));
            if ($cacheData && isset($cacheData['expires']) && $cacheData['expires'] > time()) {
                return $cacheData['data'];
            }
            unlink($cacheFile);
        } catch (Exception $e) {
            error_log("Failed to read cache file: " . $e->getMessage());
        }
    }
    return null;
}

/**
 * Paginate results
 * @param array $data Data to paginate
 * @param int $page Current page
 * @param int $perPage Items per page
 * @return array Paginated data and pagination info
 */
function paginateResults($data, $page = 1, $perPage = 10) {
    $total = count($data);
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    return [
        'data' => array_slice($data, $offset, $perPage),
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $total,
            'per_page' => $perPage
        ]
    ];
}

/**
 * Register a new user using a stored procedure
 * @param string $username Username
 * @param string $email Email
 * @param string $password Password
 * @return bool|string True if successful, error message if failed
 */
function registerUser($username, $email, $password) {
    global $pdo;
    try {
        // Şifreyi hash'le
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Saklı yordamı çağır ve OUT parametresinin sonucunu al
        $stmt = $pdo->prepare("CALL sp_RegisterUser(:username, :email, :password_hash, @result_code)");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password_hash', $hashedPassword, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor(); // Önceki sorgunun cursor'ını kapatmak önemli

        // OUT parametresinin değerini çek
        $result = $pdo->query("SELECT @result_code AS result_code")->fetch(PDO::FETCH_ASSOC);
        $result_code = $result['result_code'];

        // Sonuç koduna göre mesaj döndür
        if ($result_code == 0) {
            return true; // Başarılı
        } elseif ($result_code == 1) {
            return "Kullanıcı adı zaten kullanımda.";
        } elseif ($result_code == 2) {
            return "E-posta adresi zaten kullanımda.";
        } else {
            return "Bilinmeyen bir hata oluştu.";
        }

    } catch (PDOException $e) {
        error_log("Kayıt işlemi başarısız (SP): " . $e->getMessage());
        return "Kayıt işlemi sırasında bir veritabanı hatası oluştu.";
    }
}

/**
 * Login user using a stored procedure
 * @param string $username Username
 * @param string $password Password
 * @return bool|string True if successful, error message if failed
 */
function loginUser($username, $password) {
    global $pdo;
    try {
        // Saklı yordamı çağır
        $stmt = $pdo->prepare("CALL sp_LoginUser(:username)");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        // Saklı yordam bir sonuç seti döndürdüğü için fetch() ile alıyoruz
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Cursor'ı kapatmayı unutma

        // Kullanıcı bulunduysa ve şifre doğruysa
        if ($user && password_verify($password, $user['password'])) {
            // Oturum bilgilerini ayarla
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            return true; // Başarılı giriş
        }
        
        // Kullanıcı bulunamadı veya şifre yanlışsa
        return "Geçersiz kullanıcı adı veya şifre.";

    } catch (PDOException $e) {
        error_log("Giriş işlemi başarısız (SP): " . $e->getMessage());
        return "Giriş işlemi sırasında bir veritabanı hatası oluştu.";
    }
}

/**
 * Get personalized movie recommendations using a stored procedure
 * @param int $userId User ID
 * @param int $limit Number of recommendations
 * @return array Array of recommended movies
 */
function getPersonalizedMovieRecommendations($userId, $limit = 10) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("CALL sp_GetPersonalizedMovieRecommendations(:user_id, :limit)");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $movies = $stmt->fetchAll();
        $stmt->closeCursor();
        return $movies;
    } catch (PDOException $e) {
        error_log("Get Movie Recommendations failed (SP): " . $e->getMessage());
        return [];
    }
}

/**
 * Get personalized TV show recommendations using a stored procedure
 * @param int $userId User ID
 * @param int $limit Number of recommendations
 * @return array Array of recommended TV shows
 */
function getPersonalizedTVShowRecommendations($userId, $limit = 10) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("CALL sp_GetPersonalizedTVShowRecommendations(:user_id, :limit)");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $tvShows = $stmt->fetchAll();
        $stmt->closeCursor();
        return $tvShows;
    } catch (PDOException $e) {
        error_log("Get TV Show Recommendations failed (SP): " . $e->getMessage());
        return [];
    }
}

/**
 * Get a random movie for user to rate using a stored procedure
 * @param int $userId User ID
 * @return array|false Movie details or false if not found
 */
function getRandomMovieToRate($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("CALL sp_GetRandomMovieToRate(:user_id)");
        $stmt->execute([':user_id' => $userId]);
        $movie = $stmt->fetch();
        $stmt->closeCursor();
        return $movie;
    } catch (PDOException $e) {
        error_log("Get Random Movie failed (SP): " . $e->getMessage());
        return false;
    }
}

/**
 * Get a random TV show for user to rate using a stored procedure
 * @param int $userId User ID
 * @return array|false TV show details or false if not found
 */
function getRandomTVShowToRate($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("CALL sp_GetRandomTVShowToRate(:user_id)");
        $stmt->execute([':user_id' => $userId]);
        $tvShow = $stmt->fetch();
        $stmt->closeCursor();
        return $tvShow;
    } catch (PDOException $e) {
        error_log("Get Random TV Show failed (SP): " . $e->getMessage());
        return false;
    }
}

/**
 * Add favorite genre for user using a stored procedure
 * @param int $userId User ID
 * @param int $genreId Genre ID
 * @return bool Success status
 */
function addFavoriteGenre($userId, $genreId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("CALL sp_AddFavoriteGenre(:user_id, :genre_id)");
        $stmt->execute([':user_id' => $userId, ':genre_id' => $genreId]);
        $stmt->closeCursor();
        return true;
    } catch (PDOException $e) {
        error_log("Add Favorite Genre failed (SP): " . $e->getMessage());
        return false;
    }
}

/**
 * Add favorite actor for user using a stored procedure
 * @param int $userId User ID
 * @param int $actorId Actor ID
 * @return bool Success status
 */
function addFavoriteActor($userId, $actorId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("CALL sp_AddFavoriteActor(:user_id, :actor_id)");
        $stmt->execute([':user_id' => $userId, ':actor_id' => $actorId]);
        $stmt->closeCursor();
        return true;
    } catch (PDOException $e) {
        error_log("Add Favorite Actor failed (SP): " . $e->getMessage());
        return false;
    }
} 