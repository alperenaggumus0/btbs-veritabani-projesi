-- Dizi & Film Öneri Sistemi - Full Veritabanı Scripti
-- ---------------------------------------------------------

-- Veritabanı Oluşturma
CREATE DATABASE IF NOT EXISTS dizi_film_oneri_sistemi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dizi_film_oneri_sistemi;

-- ----------------------------
-- Tablo Yapıları
-- ----------------------------

-- movies tablosu
CREATE TABLE IF NOT EXISTS movies (
    movie_id INT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    original_title VARCHAR(255),
    overview TEXT,
    poster_path VARCHAR(255),
    backdrop_path VARCHAR(255),
    original_language VARCHAR(10),
    release_date DATE,
    runtime INT,
    vote_average DECIMAL(3,1),
    popularity DECIMAL(10,3),
    tmdb_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- tv_shows tablosu
CREATE TABLE IF NOT EXISTS tv_shows (
    tv_show_id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    overview TEXT,
    poster_path VARCHAR(255),
    backdrop_path VARCHAR(255),
    first_air_date DATE,
    vote_average DECIMAL(3,1),
    popularity DECIMAL(10,3),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- genres tablosu
CREATE TABLE IF NOT EXISTS genres (
    genre_id INT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- actors tablosu
CREATE TABLE IF NOT EXISTS actors (
    actor_id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    profile_path VARCHAR(255)
);

-- keywords tablosu
CREATE TABLE IF NOT EXISTS keywords (
    keyword_id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- production_companies tablosu
CREATE TABLE IF NOT EXISTS production_companies (
    company_id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    logo_path VARCHAR(255)
);

-- movie_genres ilişki tablosu
CREATE TABLE IF NOT EXISTS movie_genres (
    movie_id INT,
    genre_id INT,
    PRIMARY KEY (movie_id, genre_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id) ON DELETE CASCADE
);

-- tv_show_genres ilişki tablosu
CREATE TABLE IF NOT EXISTS tv_show_genres (
    tv_show_id INT,
    genre_id INT,
    PRIMARY KEY (tv_show_id, genre_id),
    FOREIGN KEY (tv_show_id) REFERENCES tv_shows(tv_show_id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id) ON DELETE CASCADE
);

-- movie_cast ilişki tablosu
CREATE TABLE IF NOT EXISTS movie_cast (
    movie_id INT,
    actor_id INT,
    character_name VARCHAR(255),
    PRIMARY KEY (movie_id, actor_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES actors(actor_id) ON DELETE CASCADE
);

-- tv_show_cast ilişki tablosu
CREATE TABLE IF NOT EXISTS tv_show_cast (
    tv_show_id INT,
    actor_id INT,
    character_name VARCHAR(255),
    PRIMARY KEY (tv_show_id, actor_id),
    FOREIGN KEY (tv_show_id) REFERENCES tv_shows(tv_show_id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES actors(actor_id) ON DELETE CASCADE
);

-- movie_keywords ilişki tablosu
CREATE TABLE IF NOT EXISTS movie_keywords (
    movie_id INT,
    keyword_id INT,
    PRIMARY KEY (movie_id, keyword_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (keyword_id) REFERENCES keywords(keyword_id) ON DELETE CASCADE
);

-- tv_show_keywords ilişki tablosu
CREATE TABLE IF NOT EXISTS tv_show_keywords (
    tv_show_id INT,
    keyword_id INT,
    PRIMARY KEY (tv_show_id, keyword_id),
    FOREIGN KEY (tv_show_id) REFERENCES tv_shows(tv_show_id) ON DELETE CASCADE,
    FOREIGN KEY (keyword_id) REFERENCES keywords(keyword_id) ON DELETE CASCADE
);

-- movie_production_companies ilişki tablosu
CREATE TABLE IF NOT EXISTS movie_production_companies (
    movie_id INT,
    company_id INT,
    PRIMARY KEY (movie_id, company_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES production_companies(company_id) ON DELETE CASCADE
);

-- tv_show_production_companies ilişki tablosu
CREATE TABLE IF NOT EXISTS tv_show_production_companies (
    tv_show_id INT,
    company_id INT,
    PRIMARY KEY (tv_show_id, company_id),
    FOREIGN KEY (tv_show_id) REFERENCES tv_shows(tv_show_id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES production_companies(company_id) ON DELETE CASCADE
);

-- users tablosu
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- user_movie_ratings tablosu
CREATE TABLE IF NOT EXISTS user_movie_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    rating_type ENUM('not_watched', 'disliked', 'liked', 'loved') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie (user_id, movie_id)
);

-- user_tv_show_ratings tablosu
CREATE TABLE IF NOT EXISTS user_tv_show_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tv_show_id INT NOT NULL,
    rating_type ENUM('not_watched', 'disliked', 'liked', 'loved') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (tv_show_id) REFERENCES tv_shows(tv_show_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_tv_show (user_id, tv_show_id)
);

-- user_favorite_genres tablosu
CREATE TABLE IF NOT EXISTS user_favorite_genres (
    user_id INT NOT NULL,
    genre_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, genre_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id) ON DELETE CASCADE
);

-- user_favorite_actors tablosu
CREATE TABLE IF NOT EXISTS user_favorite_actors (
    user_id INT NOT NULL,
    actor_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, actor_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES actors(actor_id) ON DELETE CASCADE
);

-- ratings_log tablosu (Trigger için)
CREATE TABLE IF NOT EXISTS ratings_log (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    content_id INT,
    content_type VARCHAR(10),
    old_rating VARCHAR(20),
    new_rating VARCHAR(20),
    action_type VARCHAR(10),
    action_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ----------------------------
-- Saklı Yordamlar (Stored Procedures)
-- ----------------------------
DELIMITER //

-- 1. Kullanıcı Kayıt
CREATE PROCEDURE sp_RegisterUser(
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    OUT p_result_code INT
)
BEGIN
    DECLARE user_count INT;
    DECLARE email_count INT;
    SELECT COUNT(*) INTO user_count FROM users WHERE username = p_username;
    SELECT COUNT(*) INTO email_count FROM users WHERE email = p_email;
    IF user_count > 0 THEN
        SET p_result_code = 1; -- Kullanıcı adı mevcut
    ELSEIF email_count > 0 THEN
        SET p_result_code = 2; -- E-posta mevcut
    ELSE
        INSERT INTO users (username, email, password) VALUES (p_username, p_email, p_password_hash);
        SET p_result_code = 0; -- Başarılı
    END IF;
END //

-- 2. Kullanıcı Giriş
CREATE PROCEDURE sp_LoginUser(IN p_username VARCHAR(50))
BEGIN
    SELECT user_id, username, password, email, created_at FROM users WHERE username = p_username;
END //

-- 3. Film Detayları
CREATE PROCEDURE sp_GetMovieDetails(IN p_movie_id INT)
BEGIN
    SELECT m.*, 
           GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres,
           GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ') AS actors,
           GROUP_CONCAT(DISTINCT k.name ORDER BY k.name SEPARATOR ', ') AS keywords,
           GROUP_CONCAT(DISTINCT pc.name ORDER BY pc.name SEPARATOR ', ') AS production_companies
    FROM movies m
    LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.genre_id
    LEFT JOIN movie_cast mc ON m.movie_id = mc.movie_id
    LEFT JOIN actors a ON mc.actor_id = a.actor_id
    LEFT JOIN movie_keywords mk ON m.movie_id = mk.movie_id
    LEFT JOIN keywords k ON mk.keyword_id = k.keyword_id
    LEFT JOIN movie_production_companies mpc ON m.movie_id = mpc.movie_id
    LEFT JOIN production_companies pc ON mpc.company_id = pc.company_id
    WHERE m.movie_id = p_movie_id
    GROUP BY m.movie_id;
END //

-- 4. Popüler Filmler
CREATE PROCEDURE sp_GetPopularMovies(IN p_limit INT)
BEGIN
    SELECT * FROM movies ORDER BY popularity DESC LIMIT p_limit;
END //

-- 5. Popüler Diziler
CREATE PROCEDURE sp_GetPopularTVShows(IN p_limit INT)
BEGIN
    SELECT * FROM tv_shows ORDER BY popularity DESC LIMIT p_limit;
END //

-- 6. Film Puanlama
CREATE PROCEDURE sp_RateMovie(IN p_user_id INT, IN p_movie_id INT, IN p_rating_type ENUM('not_watched', 'disliked', 'liked', 'loved'))
BEGIN
    INSERT INTO user_movie_ratings (user_id, movie_id, rating_type)
    VALUES (p_user_id, p_movie_id, p_rating_type)
    ON DUPLICATE KEY UPDATE rating_type = p_rating_type;
END //

-- 7. Dizi Puanlama
CREATE PROCEDURE sp_RateTVShow(IN p_user_id INT, IN p_tv_show_id INT, IN p_rating_type ENUM('not_watched', 'disliked', 'liked', 'loved'))
BEGIN
    INSERT INTO user_tv_show_ratings (user_id, tv_show_id, rating_type)
    VALUES (p_user_id, p_tv_show_id, p_rating_type)
    ON DUPLICATE KEY UPDATE rating_type = p_rating_type;
END //

-- 8. İçerik Arama
CREATE PROCEDURE sp_SearchContent(IN p_search_term VARCHAR(255))
BEGIN
    (SELECT 'movie' AS type, movie_id AS id, title AS name, overview, poster_path, vote_average
     FROM movies WHERE title LIKE p_search_term OR overview LIKE p_search_term)
    UNION ALL
    (SELECT 'tv_show' AS type, tv_show_id AS id, name, overview, poster_path, vote_average
     FROM tv_shows WHERE name LIKE p_search_term OR overview LIKE p_search_term)
    ORDER BY vote_average DESC;
END //

-- 9. Favori Tür Ekleme
CREATE PROCEDURE sp_AddFavoriteGenre(IN p_user_id INT, IN p_genre_id INT)
BEGIN
    INSERT IGNORE INTO user_favorite_genres (user_id, genre_id) VALUES (p_user_id, p_genre_id);
END //

-- 10. Favori Tür Silme
CREATE PROCEDURE sp_DeleteFavoriteGenre(IN p_user_id INT, IN p_genre_id INT)
BEGIN
    DELETE FROM user_favorite_genres WHERE user_id = p_user_id AND genre_id = p_genre_id;
END //

-- 11. Favori Oyuncu Ekleme
CREATE PROCEDURE sp_AddFavoriteActor(IN p_user_id INT, IN p_actor_id INT)
BEGIN
    INSERT IGNORE INTO user_favorite_actors (user_id, actor_id) VALUES (p_user_id, p_actor_id);
END //

-- 12. Favori Oyuncu Silme
CREATE PROCEDURE sp_DeleteFavoriteActor(IN p_user_id INT, IN p_actor_id INT)
BEGIN
    DELETE FROM user_favorite_actors WHERE user_id = p_user_id AND actor_id = p_actor_id;
END //

-- 13. Kullanıcının Favori Türlerini Getirme
CREATE PROCEDURE sp_GetUserFavoriteGenres(IN p_user_id INT)
BEGIN
    SELECT g.genre_id, g.name FROM user_favorite_genres ufg JOIN genres g ON ufg.genre_id = g.genre_id WHERE ufg.user_id = p_user_id ORDER BY g.name;
END //

-- 14. Kullanıcının Favori Oyuncularını Getirme
CREATE PROCEDURE sp_GetUserFavoriteActors(IN p_user_id INT)
BEGIN
    SELECT a.actor_id, a.name, a.profile_path FROM user_favorite_actors ufa JOIN actors a ON ufa.actor_id = a.actor_id WHERE ufa.user_id = p_user_id ORDER BY a.name;
END //

-- 15. Kullanıcının Puanladığı Filmleri Getirme
CREATE PROCEDURE sp_GetUserRatedMovies(IN p_user_id INT, IN p_limit INT)
BEGIN
    SELECT m.*, umr.rating_type FROM user_movie_ratings umr JOIN movies m ON umr.movie_id = m.movie_id WHERE umr.user_id = p_user_id ORDER BY umr.created_at DESC LIMIT p_limit;
END //

-- 16. Kullanıcının Puanladığı Dizileri Getirme
CREATE PROCEDURE sp_GetUserRatedTVShows(IN p_user_id INT, IN p_limit INT)
BEGIN
    SELECT t.*, utr.rating_type FROM user_tv_show_ratings utr JOIN tv_shows t ON utr.tv_show_id = t.tv_show_id WHERE utr.user_id = p_user_id ORDER BY utr.created_at DESC LIMIT p_limit;
END //

-- 17. Tüm Türleri Getirme
CREATE PROCEDURE sp_GetAllGenres()
BEGIN
    SELECT * FROM genres ORDER BY name;
END //

-- 18. Tüm Oyuncuları Getirme
CREATE PROCEDURE sp_GetAllActors()
BEGIN
    SELECT * FROM actors ORDER BY name;
END //

-- 19. Tüm Film Yıllarını Getirme
CREATE PROCEDURE sp_GetAllMovieYears()
BEGIN
    SELECT DISTINCT YEAR(release_date) as year FROM movies WHERE release_date IS NOT NULL ORDER BY year DESC;
END //

-- 20. Kişiselleştirilmiş Film Önerileri
CREATE PROCEDURE sp_GetPersonalizedMovieRecommendations(IN p_user_id INT, IN p_limit INT)
BEGIN
    WITH user_preferences AS (
        SELECT g.genre_id, 1 AS genre_weight FROM user_favorite_genres ufg JOIN genres g ON ufg.genre_id = g.genre_id WHERE ufg.user_id = p_user_id
        UNION ALL
        SELECT mg.genre_id, CASE WHEN umr.rating_type = 'loved' THEN 3 ELSE 2 END AS genre_weight FROM user_movie_ratings umr JOIN movie_genres mg ON umr.movie_id = mg.movie_id WHERE umr.user_id = p_user_id AND umr.rating_type IN ('liked', 'loved')
    ), aggregated_preferences AS (
        SELECT genre_id, SUM(genre_weight) as total_weight FROM user_preferences GROUP BY genre_id
    ), movie_scores AS (
        SELECT m.movie_id, SUM(ap.total_weight) AS preference_score FROM movies m JOIN movie_genres mg ON m.movie_id = mg.movie_id JOIN aggregated_preferences ap ON mg.genre_id = ap.genre_id WHERE m.movie_id NOT IN (SELECT movie_id FROM user_movie_ratings WHERE user_id = p_user_id) GROUP BY m.movie_id
    )
    SELECT m.*, (ms.preference_score + (m.vote_average * 0.5) + (m.popularity * 0.1)) AS total_score
    FROM movie_scores ms JOIN movies m ON ms.movie_id = m.movie_id ORDER BY total_score DESC LIMIT p_limit;
END //

-- 21. Kişiselleştirilmiş Dizi Önerileri
CREATE PROCEDURE sp_GetPersonalizedTVShowRecommendations(IN p_user_id INT, IN p_limit INT)
BEGIN
    WITH user_preferences AS (
        SELECT g.genre_id, 1 AS genre_weight FROM user_favorite_genres ufg JOIN genres g ON ufg.genre_id = g.genre_id WHERE ufg.user_id = p_user_id
        UNION ALL
        SELECT tg.genre_id, CASE WHEN utr.rating_type = 'loved' THEN 3 ELSE 2 END AS genre_weight FROM user_tv_show_ratings utr JOIN tv_show_genres tg ON utr.tv_show_id = tg.tv_show_id WHERE utr.user_id = p_user_id AND utr.rating_type IN ('liked', 'loved')
    ), aggregated_preferences AS (
        SELECT genre_id, SUM(genre_weight) as total_weight FROM user_preferences GROUP BY genre_id
    ), tv_show_scores AS (
        SELECT t.tv_show_id, SUM(ap.total_weight) AS preference_score FROM tv_shows t JOIN tv_show_genres tg ON t.tv_show_id = tg.tv_show_id JOIN aggregated_preferences ap ON tg.genre_id = ap.genre_id WHERE t.tv_show_id NOT IN (SELECT tv_show_id FROM user_tv_show_ratings WHERE user_id = p_user_id) GROUP BY t.tv_show_id
    )
    SELECT t.*, (tss.preference_score + (t.vote_average * 0.5) + (t.popularity * 0.1)) AS total_score
    FROM tv_show_scores tss JOIN tv_shows t ON tss.tv_show_id = t.tv_show_id ORDER BY total_score DESC LIMIT p_limit;
END //

-- 22. Puanlanacak Rastgele Film Getirme
CREATE PROCEDURE sp_GetRandomMovieToRate(IN p_user_id INT)
BEGIN
    SELECT m.* FROM movies m LEFT JOIN user_movie_ratings umr ON m.movie_id = umr.movie_id AND umr.user_id = p_user_id WHERE umr.rating_id IS NULL ORDER BY RAND() LIMIT 1;
END //

-- 23. Puanlanacak Rastgele Dizi Getirme
CREATE PROCEDURE sp_GetRandomTVShowToRate(IN p_user_id INT)
BEGIN
    SELECT t.* FROM tv_shows t LEFT JOIN user_tv_show_ratings utr ON t.tv_show_id = utr.tv_show_id AND utr.user_id = p_user_id WHERE utr.rating_id IS NULL ORDER BY RAND() LIMIT 1;
END //

-- 24. Filmleri Dinamik Filtreleme
CREATE PROCEDURE sp_FilterMovies(
    IN p_genre_ids TEXT, 
    IN p_year_range VARCHAR(20), 
    IN p_rating_range VARCHAR(10), 
    IN p_sort_by VARCHAR(20), 
    IN p_page_number INT, 
    IN p_page_size INT, 
    OUT p_total_records INT
)
BEGIN
    DECLARE v_query_body TEXT;
    DECLARE v_where_clause TEXT DEFAULT ' WHERE 1=1';
    DECLARE v_order_by_clause VARCHAR(100);
    DECLARE v_limit_clause VARCHAR(50);
    DECLARE v_offset INT;
    IF p_genre_ids IS NOT NULL AND p_genre_ids != '' THEN
        SET v_where_clause = CONCAT(v_where_clause, ' AND m.movie_id IN (SELECT mg.movie_id FROM movie_genres mg WHERE FIND_IN_SET(mg.genre_id, p_genre_ids) GROUP BY mg.movie_id HAVING COUNT(DISTINCT mg.genre_id) = (LENGTH(p_genre_ids) - LENGTH(REPLACE(p_genre_ids, ',', '')) + 1))');
    END IF;
    IF p_year_range IS NOT NULL AND p_year_range != '' THEN
        CASE p_year_range
            WHEN 'before_2000' THEN SET v_where_clause = CONCAT(v_where_clause, ' AND YEAR(m.release_date) < 2000');
            WHEN '2000_2010'   THEN SET v_where_clause = CONCAT(v_where_clause, ' AND YEAR(m.release_date) BETWEEN 2000 AND 2010');
            WHEN '2010_2020'   THEN SET v_where_clause = CONCAT(v_where_clause, ' AND YEAR(m.release_date) BETWEEN 2010 AND 2020');
            WHEN 'after_2020'  THEN SET v_where_clause = CONCAT(v_where_clause, ' AND YEAR(m.release_date) > 2020');
            ELSE BEGIN END;
        END CASE;
    END IF;
    IF p_rating_range IS NOT NULL AND p_rating_range != '' THEN
        CASE p_rating_range
            WHEN '0_2.5'   THEN SET v_where_clause = CONCAT(v_where_clause, ' AND m.vote_average BETWEEN 0 AND 2.5');
            WHEN '2.5_5'   THEN SET v_where_clause = CONCAT(v_where_clause, ' AND m.vote_average BETWEEN 2.5 AND 5.0');
            WHEN '5_7.5'   THEN SET v_where_clause = CONCAT(v_where_clause, ' AND m.vote_average BETWEEN 5.0 AND 7.5');
            WHEN '7.5_10'  THEN SET v_where_clause = CONCAT(v_where_clause, ' AND m.vote_average BETWEEN 7.5 AND 10.0');
            ELSE BEGIN END;
        END CASE;
    END IF;
    SET v_order_by_clause = CASE p_sort_by WHEN 'rating_desc' THEN ' ORDER BY m.vote_average DESC' WHEN 'rating_asc' THEN ' ORDER BY m.vote_average ASC' WHEN 'date_desc' THEN ' ORDER BY m.release_date DESC' WHEN 'date_asc' THEN ' ORDER BY m.release_date ASC' ELSE ' ORDER BY m.popularity DESC' END;
    SET v_offset = (p_page_number - 1) * p_page_size;
    SET v_limit_clause = CONCAT(' LIMIT ', v_offset, ', ', p_page_size);
    SET v_query_body = 'FROM movies m';
    SET @count_sql = CONCAT('SELECT COUNT(*) INTO @total_records ', v_query_body, v_where_clause);
    PREPARE count_stmt FROM @count_sql;
    EXECUTE count_stmt;
    DEALLOCATE PREPARE count_stmt;
    SET p_total_records = @total_records;
    SET @sql = CONCAT('SELECT m.*, GROUP_CONCAT(DISTINCT g.name SEPARATOR ", ") AS genres FROM movies m LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id LEFT JOIN genres g ON mg.genre_id = g.genre_id ', v_where_clause, ' GROUP BY m.movie_id', v_order_by_clause, v_limit_clause);
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //

DELIMITER ;


-- ----------------------------
-- Tetikleyiciler (Triggers)
-- ----------------------------
DELIMITER //

-- Kullanıcı silindiğinde ilişkili verileri temizle
CREATE TRIGGER trg_AfterUserDelete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    DELETE FROM user_movie_ratings WHERE user_id = OLD.user_id;
    DELETE FROM user_tv_show_ratings WHERE user_id = OLD.user_id;
    DELETE FROM user_favorite_genres WHERE user_id = OLD.user_id;
    DELETE FROM user_favorite_actors WHERE user_id = OLD.user_id;
END //

-- Film puanlaması eklendiğinde logla
CREATE TRIGGER trg_AfterMovieRatingInsert
AFTER INSERT ON user_movie_ratings
FOR EACH ROW
BEGIN
    INSERT INTO ratings_log (user_id, content_id, content_type, old_rating, new_rating, action_type)
    VALUES (NEW.user_id, NEW.movie_id, 'movie', NULL, NEW.rating_type, 'INSERT');
END //

-- Film puanlaması güncellendiğinde logla
CREATE TRIGGER trg_AfterMovieRatingUpdate
AFTER UPDATE ON user_movie_ratings
FOR EACH ROW
BEGIN
    IF OLD.rating_type != NEW.rating_type THEN
        INSERT INTO ratings_log (user_id, content_id, content_type, old_rating, new_rating, action_type)
        VALUES (NEW.user_id, NEW.movie_id, 'movie', OLD.rating_type, NEW.rating_type, 'UPDATE');
    END IF;
END //

DELIMITER ;

-- ----------------------------
-- Kullanıcı Tanımlı Fonksiyon (Function)
-- ----------------------------
DELIMITER //

-- Bir filmin olumlu puan sayısını döndürür
CREATE FUNCTION fn_GetMoviePositiveRatingCount(
    p_movie_id INT
)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_rating_count INT;
    SELECT COUNT(*) INTO v_rating_count
    FROM user_movie_ratings
    WHERE movie_id = p_movie_id AND rating_type IN ('liked', 'loved');
    RETURN v_rating_count;
END //

DELIMITER ;