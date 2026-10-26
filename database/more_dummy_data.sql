USE swizy;

-- Insert 3 new customers
INSERT INTO users (role, name, email, phone, password, profile_image) VALUES 
('customer', 'Emma Watson', 'emma@example.com', '9876543220', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png'),
('customer', 'Robert Downey', 'robert@example.com', '9876543221', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png'),
('customer', 'Scarlett Johansson', 'scarlett@example.com', '9876543222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png');

-- Set variables to the new user IDs
SET @emma_id = (SELECT id FROM users WHERE email = 'emma@example.com' LIMIT 1);
SET @robert_id = (SELECT id FROM users WHERE email = 'robert@example.com' LIMIT 1);
SET @scarlett_id = (SELECT id FROM users WHERE email = 'scarlett@example.com' LIMIT 1);

-- Insert new bookings
INSERT INTO bookings (customer_id, event_type, guest_count, event_date, event_days, location, package_id, total_amount, advance_amount, payment_status, booking_status) VALUES
(@emma_id, 'Anniversary Celebration', 80, DATE_ADD(CURDATE(), INTERVAL 20 DAY), 1, 'Sea View Hotel', 2, 80000.00, 20000.00, 'advance_paid', 'approved'),
(@robert_id, 'Corporate Event', 300, DATE_ADD(CURDATE(), INTERVAL 45 DAY), 2, 'Tech Park Hall', 1, 150000.00, 50000.00, 'advance_paid', 'approved'),
(@robert_id, 'Private Dinner', 20, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 1, 'Villa Rose', 2, 20000.00, 0.00, 'pending', 'pending'),
(@scarlett_id, 'Baby Shower', 40, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1, 'Community Hall', 1, 20000.00, 5000.00, 'advance_paid', 'approved');

-- Set variables to the new booking IDs
SET @booking_emma = (SELECT id FROM bookings WHERE customer_id = @emma_id LIMIT 1);
SET @booking_robert1 = (SELECT id FROM bookings WHERE customer_id = @robert_id ORDER BY id ASC LIMIT 1);
SET @booking_scarlett = (SELECT id FROM bookings WHERE customer_id = @scarlett_id LIMIT 1);

-- Assign Staff to these approved bookings
-- Emma's Booking (Chef 3, Server 12)
INSERT INTO booking_staff (booking_id, user_id, assigned_role, assignment_status) VALUES
(@booking_emma, 3, 'chef', 'assigned'),
(@booking_emma, 12, 'server', 'assigned');

-- Robert's Corporate Event (Chef 4, Server 13, Manager 28)
INSERT INTO booking_staff (booking_id, user_id, assigned_role, assignment_status) VALUES
(@booking_robert1, 4, 'chef', 'assigned'),
(@booking_robert1, 13, 'server', 'assigned'),
(@booking_robert1, 28, 'site_manager', 'assigned');

-- Scarlett's Baby Shower (Chef 5, Server 14)
INSERT INTO booking_staff (booking_id, user_id, assigned_role, assignment_status) VALUES
(@booking_scarlett, 5, 'chef', 'assigned'),
(@booking_scarlett, 14, 'server', 'assigned');

-- Insert Feedbacks / Reviews
INSERT INTO reviews (user_id, rating, review, status) VALUES
(@emma_id, 5, 'The Anniversary Celebration was perfect! The food was extraordinary and the presentation was top-notch.', 1),
(@robert_id, 5, 'Swizy handled our corporate event flawlessly. All 300 guests were impressed by the Royal Feast menu.', 1),
(@scarlett_id, 4, 'Really good service and tasty food for my event. The staff was very attentive.', 1);
