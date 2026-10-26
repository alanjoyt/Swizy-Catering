CREATE DATABASE IF NOT EXISTS swizy;
USE swizy;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'customer', 'chef', 'server', 'site_manager') NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    availability_status TINYINT(1) DEFAULT 1,
    active_status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menu_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(255) NOT NULL,
    package_type ENUM('normal', 'premium', 'luxury') NOT NULL,
    category ENUM('veg', 'non-veg', 'both') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    guest_limit INT DEFAULT 0,
    image VARCHAR(255) NOT NULL,
    menu_items JSON NOT NULL,
    status TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    guest_count INT NOT NULL,
    event_date DATE NOT NULL,
    event_days INT DEFAULT 1,
    location VARCHAR(255) NOT NULL,
    package_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    advance_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'advance_paid', 'fully_paid', 'refunded') DEFAULT 'pending',
    booking_status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    special_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES menu_packages(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS booking_staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    assigned_role ENUM('chef', 'server', 'site_manager') NOT NULL,
    assignment_status ENUM('assigned', 'completed', 'cancelled') DEFAULT 'assigned',
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    media_type ENUM('image', 'video') NOT NULL,
    media_path VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    section VARCHAR(100) NOT NULL,
    status TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT DEFAULT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK(rating >= 1 AND rating <= 5),
    review TEXT NOT NULL,
    image VARCHAR(255),
    status TINYINT(1) DEFAULT 0,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category ENUM('veg', 'non-veg', 'both') NOT NULL,
    price_per_head DECIMAL(10,2) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS blocked_dates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blocked_date DATE NOT NULL UNIQUE,
    reason VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin user (password: admin123 -> hashed)
INSERT INTO users (role, name, email, phone, password, profile_image) VALUES 
('admin', 'Admin Swizy', 'admin@swizy.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png');

