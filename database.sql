-- Create database
CREATE DATABASE IF NOT EXISTS project2;
USE project2;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Stories table
CREATE TABLE IF NOT EXISTS stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255),
    age_group VARCHAR(10),
    reading_level VARCHAR(20),
    genre VARCHAR(50),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User progress table
CREATE TABLE IF NOT EXISTS user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    story_id INT,
    progress INT DEFAULT 0,
    last_read TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (story_id) REFERENCES stories(id)
);

-- Insert sample stories
INSERT INTO stories (title, description, age_group, reading_level, genre) VALUES
('The Magic Forest', 'Join Timmy on his magical adventure through the enchanted forest!', '3-5', 'beginner', 'fantasy'),
('Space Adventure', 'Blast off into space with Captain Luna and her crew!', '6-8', 'intermediate', 'adventure'),
('Underwater World', 'Dive deep into the ocean with Sammy the Seahorse!', '9-12', 'advanced', 'educational'); 