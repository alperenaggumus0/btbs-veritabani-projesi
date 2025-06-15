-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User movie ratings table
CREATE TABLE user_movie_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    rating_type ENUM('not_watched', 'disliked', 'liked', 'loved') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id),
    UNIQUE KEY unique_user_movie (user_id, movie_id)
);

-- User TV show ratings table
CREATE TABLE user_tv_show_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tv_show_id INT NOT NULL,
    rating_type ENUM('not_watched', 'disliked', 'liked', 'loved') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (tv_show_id) REFERENCES tv_shows(tv_show_id),
    UNIQUE KEY unique_user_tv_show (user_id, tv_show_id)
);

-- User favorite genres table
CREATE TABLE user_favorite_genres (
    user_id INT NOT NULL,
    genre_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, genre_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id)
);

-- User favorite actors table
CREATE TABLE user_favorite_actors (
    user_id INT NOT NULL,
    actor_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, actor_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (actor_id) REFERENCES actors(actor_id)
); 