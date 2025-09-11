-- Database: university_management
CREATE DATABASE IF NOT EXISTS university_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE university_management;

-- Bảng thông tin trường đại học
CREATE TABLE universities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    province VARCHAR(100) NOT NULL,
    address TEXT,
    website VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(100),
    description TEXT,
    logo VARCHAR(255),
    established_year YEAR,
    university_type ENUM('Công lập', 'Dân lập', 'Tư thục') DEFAULT 'Công lập',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng ngành đào tạo
CREATE TABLE majors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university_id INT NOT NULL,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    training_level ENUM('Đại học', 'Cao đẳng', 'Thạc sĩ', 'Tiến sĩ') DEFAULT 'Đại học',
    duration_years INT DEFAULT 4,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
);

-- Bảng điểm chuẩn theo năm
CREATE TABLE admission_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    major_id INT NOT NULL,
    year YEAR NOT NULL,
    block VARCHAR(10) NOT NULL, -- A, A1, B, C, D1, etc.
    min_score DECIMAL(4,2) NOT NULL,
    quota INT DEFAULT 0,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (major_id) REFERENCES majors(id) ON DELETE CASCADE,
    UNIQUE KEY unique_major_year_block (major_id, year, block)
);

-- Bảng admin users
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    full_name VARCHAR(100),
    role ENUM('admin', 'moderator') DEFAULT 'moderator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin mặc định
INSERT INTO admin_users (username, password, email, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@university.com', 'Administrator', 'admin');
-- Password: password
