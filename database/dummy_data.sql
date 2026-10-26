USE swizy;

-- Insert Dummy Users (passwords are 'password123' -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi is 'password' in laravel, let's use it or just any hash, wait the prompt says `password: admin123 -> hashed` and uses that same hash. I'll use the same hash for all users for simplicity.)
INSERT INTO users (role, name, email, phone, password, profile_image) VALUES 
('customer', 'John Doe', 'john@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png'),
('chef', 'Gordon Ramsay', 'gordon@swizy.com', '9876543211', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png'),
('server', 'Alice Smith', 'alice@swizy.com', '9876543212', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png'),
('site_manager', 'Bob Builder', 'bob@swizy.com', '9876543213', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png');

-- Insert Menu Packages
INSERT INTO menu_packages (package_name, package_type, category, price, guest_limit, image, menu_items) VALUES
('Classic Delight', 'normal', 'both', 500.00, 100, 'tasty.mp4', '["Chicken Curry", "Paneer Butter Masala", "Naan", "Rice", "Gulab Jamun"]'),
('Royal Feast', 'premium', 'non-veg', 1000.00, 50, 'tasty.mp4', '["Mutton Biryani", "Fish Fry", "Chicken Tikka", "Ice Cream"]');

-- Insert Bookings (assuming John Doe has id=2)
INSERT INTO bookings (customer_id, event_type, guest_count, event_date, event_days, location, package_id, total_amount, advance_amount, payment_status, booking_status) VALUES
(2, 'Wedding Reception', 150, DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 'Grand Palace Hall', 1, 75000.00, 20000.00, 'advance_paid', 'approved'),
(2, 'Birthday Party', 50, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 'Sunny Garden', 2, 50000.00, 0.00, 'pending', 'pending');

-- Insert Booking Staff
-- Booking 1 (id=1): Gordon (id=3) as chef, Alice (id=4) as server, Bob (id=5) as site_manager
INSERT INTO booking_staff (booking_id, user_id, assigned_role, assignment_status) VALUES
(1, 3, 'chef', 'assigned'),
(1, 4, 'server', 'assigned'),
(1, 5, 'site_manager', 'assigned');

-- Insert Gallery / Media Data
INSERT INTO media (media_type, media_path, title, section) VALUES
('image', 'gallery_1.png', 'Delicious Event Setup', 'gallery'),
('image', 'gallery_2.png', 'Luxurious Banquet Hall', 'gallery'),
('image', 'gallery_3.png', 'Professional Kitchen Prep', 'gallery');


-- Insert Feedbacks / Reviews
INSERT INTO reviews (user_id, rating, review, status) VALUES
(2, 5, 'Absolutely fantastic service! The food was delicious and the staff were very professional.', 1),
(2, 4, 'Great experience overall. The booking process was smooth and easy.', 1);

-- Insert Dummy Items
INSERT INTO items (name, category, price_per_head, description, image, status) VALUES
('Paneer Butter Masala', 'veg', 150.00, 'Rich and creamy curry made with paneer, spices, onions, tomatoes, cashews, and butter.', 'paneer_butter_masala.png', 1),
('Vegetable Spring Rolls', 'veg', 80.00, 'Crispy deep-fried pastry wrapper filled with a savory mixture of stir-fried vegetables.', 'veg_spring_rolls.png', 1),
('Chicken Tikka Masala', 'non-veg', 220.00, 'Roasted marinated chicken chunks in a spiced sauce, creamy and orange-coloured.', 'chicken_tikka.png', 1),
('Butter Chicken', 'non-veg', 240.00, 'Tender chicken pieces cooked in a smooth, rich, and creamy butter-tomato sauce.', 'butter_chicken.png', 1),
('Assorted Indian Breads', 'both', 50.00, 'A combination of freshly baked hot Butter Naan, Garlic Naan, and Tandoori Roti.', 'naan_roti.png', 1),
('Gulab Jamun with Ice Cream', 'both', 90.00, 'Warm golden milk-solid dumplings dipped in cardamom sugar syrup, served with a scoop of vanilla ice cream.', 'gulab_jamun_ice_cream.png', 1);
