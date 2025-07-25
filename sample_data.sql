-- Sample data for DesignHub platform
-- Run this after schema.sql to populate the database with demo data

-- Admin user
INSERT INTO users (username, email, password, role, last_login, created_at, status) VALUES 
('admin', 'admin@designhub.com', 'password', 'admin', 
 '2024-01-21 15:30:00', '2023-12-01 10:00:00', 'active');

-- Vendor users
INSERT INTO users (username, email, password, role, last_login, created_at, status) VALUES 
('sarah_designs', 'sarah@designhub.com', 'password', 'vendor',
 '2024-01-20 14:25:00', '2023-12-05 11:30:00', 'active'),
('ahmed_creative', 'ahmed@designhub.com', 'password', 'vendor',
 '2024-01-21 09:15:00', '2023-12-10 13:45:00', 'active'),
('nour_graphics', 'nour@designhub.com', 'password', 'vendor',
 '2024-01-19 16:40:00', '2023-12-15 09:20:00', 'active');

-- Client users
INSERT INTO users (username, email, password, role, last_login, created_at, status) VALUES 
('client1', 'client1@example.com', 'password', 'client',
 '2024-01-21 11:20:00', '2023-12-20 14:15:00', 'active'),
('client2', 'client2@example.com', 'password', 'client',
 '2024-01-20 17:45:00', '2023-12-25 10:30:00', 'active');

-- Note: All passwords are 'password' using bcrypt hash

-- Vendor profiles
INSERT INTO vendors (user_id, display_name, bio, created_at) VALUES 
((SELECT id FROM users WHERE email = 'sarah@designhub.com'), 
 'Sarah Designs', 
 'Professional logo and brand identity designer with 5+ years experience.',
 '2023-12-05 11:35:00'),
((SELECT id FROM users WHERE email = 'ahmed@designhub.com'), 
 'Ahmed Creative Studio', 
 'Specializing in modern web design and UI/UX solutions.',
 '2023-12-10 13:50:00'),
((SELECT id FROM users WHERE email = 'nour@designhub.com'), 
 'Nour Graphics', 
 'Expert in motion graphics and video editing.',
 '2023-12-15 09:25:00');

-- Services
INSERT INTO services (vendor_id, title, description, price, category, created_at, status) VALUES 
((SELECT id FROM vendors WHERE display_name = 'Sarah Designs'), 
 'Professional Logo Design', 
 'Custom logo design with unlimited revisions and all file formats included.', 
 299.99, 'Logo Design', '2023-12-06 10:00:00', 'active'),
((SELECT id FROM vendors WHERE display_name = 'Sarah Designs'), 
 'Brand Identity Package', 
 'Complete brand identity including logo, business cards, letterhead, and brand guidelines.', 
 799.99, 'Branding', '2023-12-07 11:30:00', 'active'),
((SELECT id FROM vendors WHERE display_name = 'Ahmed Creative Studio'), 
 'Website Design', 
 'Modern, responsive website design with up to 5 pages.', 
 899.99, 'Web Design', '2023-12-11 09:15:00', 'active'),
((SELECT id FROM vendors WHERE display_name = 'Ahmed Creative Studio'), 
 'UI/UX Design', 
 'User interface and experience design for web and mobile applications.', 
 1299.99, 'UI/UX', '2023-12-12 14:20:00', 'active'),
((SELECT id FROM vendors WHERE display_name = 'Nour Graphics'), 
 'Promotional Video', 
 '60-second promotional video with professional editing and motion graphics.', 
 599.99, 'Video', '2023-12-16 10:45:00', 'active'),
((SELECT id FROM vendors WHERE display_name = 'Nour Graphics'), 
 'Social Media Package', 
 'Monthly package of 20 social media graphics and 2 animated posts.', 
 399.99, 'Social Media', '2023-12-17 13:30:00', 'active');

-- Sample bookings
INSERT INTO bookings (service_id, client_id, vendor_id, requirements, status, created_at) VALUES 
((SELECT id FROM services WHERE title = 'Professional Logo Design'), 
 (SELECT id FROM users WHERE email = 'client1@example.com'),
 (SELECT vendor_id FROM services WHERE title = 'Professional Logo Design'),
 'Looking for a modern, minimalist logo for my tech startup. Main colors should be blue and white.',
 'in_progress',
 '2024-01-10 09:30:00'),

((SELECT id FROM services WHERE title = 'Website Design'),
 (SELECT id FROM users WHERE email = 'client2@example.com'),
 (SELECT vendor_id FROM services WHERE title = 'Website Design'),
 'Need a responsive website for my restaurant. Should include menu, about us, and contact pages.',
 'pending',
 '2024-01-15 14:20:00'),

((SELECT id FROM services WHERE title = 'Social Media Package'),
 (SELECT id FROM users WHERE email = 'client1@example.com'),
 (SELECT vendor_id FROM services WHERE title = 'Social Media Package'),
 'Monthly social media content for my fitness brand. Focus on workout tips and motivation.',
 'delivered',
 '2024-01-05 11:45:00');

-- Sample order status history
INSERT INTO order_status (booking_id, status, changed_at) VALUES 
(1, 'pending', '2024-01-10 09:30:00'),
(1, 'in_progress', '2024-01-11 10:15:00'),
(2, 'pending', '2024-01-15 14:20:00'),
(3, 'pending', '2024-01-05 11:45:00'),
(3, 'in_progress', '2024-01-06 09:30:00'),
(3, 'delivered', '2024-01-08 16:20:00'); 