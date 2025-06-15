DROP PROCEDURE IF EXISTS sp_GetAllGenres;
DROP PROCEDURE IF EXISTS sp_GetAllMovieYears;
DROP PROCEDURE IF EXISTS sp_FilterMovies;
DROP PROCEDURE IF EXISTS sp_GetUserRatedMovies;
DROP PROCEDURE IF EXISTS sp_GetUserRatedTVShows;
DROP PROCEDURE IF EXISTS sp_GetUserFavoriteGenres;
DROP PROCEDURE IF EXISTS sp_GetUserFavoriteActors;
DROP PROCEDURE IF EXISTS sp_GetAllActors;

DELIMITER //

-- Tüm türleri getiren stored procedure
CREATE PROCEDURE sp_GetAllGenres()
BEGIN
    SELECT * FROM genres ORDER BY name;
END //

-- Tüm film yıllarını getiren stored procedure
CREATE PROCEDURE sp_GetAllMovieYears()
BEGIN
    SELECT DISTINCT YEAR(release_date) as year 
    FROM movies 
    WHERE release_date IS NOT NULL 
    ORDER BY year DESC;
END //

-- Filmleri filtreleyen stored procedure
CREATE PROCEDURE sp_FilterMovies(
    IN p_genre_ids VARCHAR(255),
    IN p_year_range VARCHAR(50),
    IN p_rating_range VARCHAR(50),
    IN p_sort_by VARCHAR(50),
    IN p_page_num INT,
    IN p_page_size INT,
    OUT p_total_records INT
)
BEGIN
    DECLARE v_offset INT;
    SET v_offset = (p_page_num - 1) * p_page_size;
    
    -- Temel sorgu
    SET @sql = CONCAT('
        SELECT SQL_CALC_FOUND_ROWS m.*, 
            GROUP_CONCAT(DISTINCT g.name) as genres
        FROM movies m
        LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
        LEFT JOIN genres g ON mg.genre_id = g.genre_id
        WHERE 1=1
    ');
    
    -- Tür filtresi
    IF p_genre_ids IS NOT NULL AND p_genre_ids != '' THEN
        SET @sql = CONCAT(@sql, ' AND m.movie_id IN (
            SELECT movie_id 
            FROM movie_genres 
            WHERE genre_id IN (', p_genre_ids, ')
            GROUP BY movie_id
            HAVING COUNT(DISTINCT genre_id) = ', 
            (LENGTH(p_genre_ids) - LENGTH(REPLACE(p_genre_ids, ',', '')) + 1), 
        ')');
    END IF;
    
    -- Yıl aralığı filtresi
    IF p_year_range IS NOT NULL AND p_year_range != '' THEN
        CASE p_year_range
            WHEN 'before_2000' THEN
                SET @sql = CONCAT(@sql, ' AND YEAR(m.release_date) < 2000');
            WHEN '2000_2010' THEN
                SET @sql = CONCAT(@sql, ' AND YEAR(m.release_date) BETWEEN 2000 AND 2010');
            WHEN '2010_2020' THEN
                SET @sql = CONCAT(@sql, ' AND YEAR(m.release_date) BETWEEN 2010 AND 2020');
            WHEN 'after_2020' THEN
                SET @sql = CONCAT(@sql, ' AND YEAR(m.release_date) > 2020');
        END CASE;
    END IF;
    
    -- Puan aralığı filtresi
    IF p_rating_range IS NOT NULL AND p_rating_range != '' THEN
        CASE p_rating_range
            WHEN '0_2.5' THEN
                SET @sql = CONCAT(@sql, ' AND m.vote_average BETWEEN 0 AND 2.5');
            WHEN '2.5_5' THEN
                SET @sql = CONCAT(@sql, ' AND m.vote_average BETWEEN 2.5 AND 5');
            WHEN '5_7.5' THEN
                SET @sql = CONCAT(@sql, ' AND m.vote_average BETWEEN 5 AND 7.5');
            WHEN '7.5_10' THEN
                SET @sql = CONCAT(@sql, ' AND m.vote_average BETWEEN 7.5 AND 10');
        END CASE;
    END IF;
    
    -- Gruplama
    SET @sql = CONCAT(@sql, ' GROUP BY m.movie_id');
    
    -- Sıralama
    CASE p_sort_by
        WHEN 'popularity_desc' THEN
            SET @sql = CONCAT(@sql, ' ORDER BY m.popularity DESC');
        WHEN 'rating_desc' THEN
            SET @sql = CONCAT(@sql, ' ORDER BY m.vote_average DESC');
        WHEN 'rating_asc' THEN
            SET @sql = CONCAT(@sql, ' ORDER BY m.vote_average ASC');
        WHEN 'date_desc' THEN
            SET @sql = CONCAT(@sql, ' ORDER BY m.release_date DESC');
        WHEN 'date_asc' THEN
            SET @sql = CONCAT(@sql, ' ORDER BY m.release_date ASC');
        ELSE
            SET @sql = CONCAT(@sql, ' ORDER BY m.popularity DESC');
    END CASE;
    
    -- Sayfalama
    SET @sql = CONCAT(@sql, ' LIMIT ', v_offset, ', ', p_page_size);
    
    -- Sorguyu hazırla ve çalıştır
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    
    -- Toplam kayıt sayısını al
    SELECT FOUND_ROWS() INTO p_total_records;
END //

-- Kullanıcının puanladığı filmleri getirir
CREATE PROCEDURE sp_GetUserRatedMovies(IN p_user_id INT, IN p_limit INT)
BEGIN
    SELECT m.*, r.rating_type, r.created_at as rated_at
    FROM movies m
    INNER JOIN user_movie_ratings r ON m.movie_id = r.movie_id
    WHERE r.user_id = p_user_id
    ORDER BY r.created_at DESC
    LIMIT p_limit;
END //

-- Kullanıcının puanladığı dizileri getirir
CREATE PROCEDURE sp_GetUserRatedTVShows(IN p_user_id INT, IN p_limit INT)
BEGIN
    SELECT t.*, r.rating_type, r.created_at as rated_at
    FROM tv_shows t
    INNER JOIN user_tv_show_ratings r ON t.tv_show_id = r.tv_show_id
    WHERE r.user_id = p_user_id
    ORDER BY r.created_at DESC
    LIMIT p_limit;
END //

-- Kullanıcının favori türlerini getirir
CREATE PROCEDURE sp_GetUserFavoriteGenres(IN p_user_id INT)
BEGIN
    SELECT g.*
    FROM genres g
    INNER JOIN user_favorite_genres ufg ON g.genre_id = ufg.genre_id
    WHERE ufg.user_id = p_user_id;
END //

-- Kullanıcının favori oyuncularını getirir
CREATE PROCEDURE sp_GetUserFavoriteActors(IN p_user_id INT)
BEGIN
    SELECT a.*
    FROM actors a
    INNER JOIN user_favorite_actors ufa ON a.actor_id = ufa.actor_id
    WHERE ufa.user_id = p_user_id;
END //

-- Tüm oyuncuları getirir
CREATE PROCEDURE sp_GetAllActors()
BEGIN
    SELECT * FROM actors ORDER BY name;
END //

DELIMITER ; 