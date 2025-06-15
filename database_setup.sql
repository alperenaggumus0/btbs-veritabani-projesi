-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS dizi_film_oneri_sistemi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dizi_film_oneri_sistemi;

-- Movies tablosu
CREATE TABLE IF NOT EXISTS movies (
    movie_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    overview TEXT,
    poster_path VARCHAR(255),
    backdrop_path VARCHAR(255),
    release_date DATE,
    vote_average DECIMAL(3,1),
    popularity DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TV Shows tablosu
CREATE TABLE IF NOT EXISTS tv_shows (
    tv_show_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    overview TEXT,
    poster_path VARCHAR(255),
    backdrop_path VARCHAR(255),
    first_air_date DATE,
    vote_average DECIMAL(3,1),
    popularity DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Genres tablosu
CREATE TABLE IF NOT EXISTS genres (
    genre_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Actors tablosu
CREATE TABLE IF NOT EXISTS actors (
    actor_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    profile_path VARCHAR(255)
);

-- Keywords tablosu
CREATE TABLE IF NOT EXISTS keywords (
    keyword_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Production Companies tablosu
CREATE TABLE IF NOT EXISTS production_companies (
    company_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    logo_path VARCHAR(255)
);

-- Movie Genres tablosu
CREATE TABLE IF NOT EXISTS movie_genres (
    movie_id INT,
    genre_id INT,
    PRIMARY KEY (movie_id, genre_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id),
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id)
);

-- TV Show Genres tablosu
CREATE TABLE IF NOT EXISTS tv_show_genres (
    tv_show_id INT,
    genre_id INT,
    PRIMARY KEY (tv_show_id, genre_id),
    FOREIGN KEY (tv_show_id) REFERENCES tv_shows(tv_show_id),
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id)
);

-- Movie Cast tablosu
CREATE TABLE IF NOT EXISTS movie_cast (
    movie_id INT,
    actor_id INT,
    character_name VARCHAR(100),
    PRIMARY KEY (movie_id, actor_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id),
    FOREIGN KEY (actor_id) REFERENCES actors(actor_id)
);

-- TV Show Cast tablosu
CREATE TABLE IF NOT EXISTS tv_show_cast (
    tv_show_id INT,
    actor_id INT,
    character_name VARCHAR(100),
    PRIMARY KEY (tv_show_id, actor_id),
    FOREIGN KEY (tv_show_id) REFERENCES tv_shows(tv_show_id),
    FOREIGN KEY (actor_id) REFERENCES actors(actor_id)
);

-- Movie Keywords tablosu
CREATE TABLE IF NOT EXISTS movie_keywords (
    movie_id INT,
    keyword_id INT,
    PRIMARY KEY (movie_id, keyword_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id),
    FOREIGN KEY (keyword_id) REFERENCES keywords(keyword_id)
);

-- TV Show Keywords tablosu
CREATE TABLE IF NOT EXISTS tv_show_keywords (
    tv_show_id INT,
    keyword_id INT,
    PRIMARY KEY (tv_show_id, keyword_id),
    FOREIGN KEY (tv_show_id) REFERENCES tv_shows(tv_show_id),
    FOREIGN KEY (keyword_id) REFERENCES keywords(keyword_id)
);

-- Movie Production Companies tablosu
CREATE TABLE IF NOT EXISTS movie_production_companies (
    movie_id INT,
    company_id INT,
    PRIMARY KEY (movie_id, company_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id),
    FOREIGN KEY (company_id) REFERENCES production_companies(company_id)
);

-- TV Show Production Companies tablosu
CREATE TABLE IF NOT EXISTS tv_show_production_companies (
    tv_show_id INT,
    company_id INT,
    PRIMARY KEY (tv_show_id, company_id),
    FOREIGN KEY (tv_show_id) REFERENCES tv_shows(tv_show_id),
    FOREIGN KEY (company_id) REFERENCES production_companies(company_id)
);

-- Users tablosu
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User Movie Ratings tablosu
CREATE TABLE IF NOT EXISTS user_movie_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    rating_type ENUM('not_watched', 'disliked', 'liked', 'loved') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id),
    UNIQUE KEY unique_user_movie (user_id, movie_id)
);

-- User TV Show Ratings tablosu
CREATE TABLE IF NOT EXISTS user_tv_show_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tv_show_id INT NOT NULL,
    rating_type ENUM('not_watched', 'disliked', 'liked', 'loved') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (tv_show_id) REFERENCES tv_shows(tv_show_id),
    UNIQUE KEY unique_user_tv_show (user_id, tv_show_id)
);

-- User Favorite Genres tablosu
CREATE TABLE IF NOT EXISTS user_favorite_genres (
    user_id INT NOT NULL,
    genre_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, genre_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id)
);

-- User Favorite Actors tablosu
CREATE TABLE IF NOT EXISTS user_favorite_actors (
    user_id INT NOT NULL,
    actor_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, actor_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (actor_id) REFERENCES actors(actor_id)
); 