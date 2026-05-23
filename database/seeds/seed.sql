-- Prism SMM Panel - Seed Data
USE `prism_smm`;

-- Default admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`, `balance`, `api_key`, `email_verified`) VALUES
('admin', 'admin@prismsmm.com', '$2y$12$LJ3m4ys3GZxbkHKvz0qnRO3fT5WvZz7gBIh2F5fXqkGhJd5tQhYiO', 'admin', 'active', 1000.0000, NULL, 1);

-- Default settings
INSERT INTO `settings` (`key_name`, `value`, `group_name`) VALUES
('site_name', 'Prism SMM Panel', 'general'),
('site_description', 'Premium Social Media Marketing Services', 'general'),
('site_keywords', 'smm panel, social media marketing, instagram followers, youtube views', 'general'),
('maintenance_mode', '0', 'general'),
('registration_enabled', '1', 'general'),
('min_deposit', '5', 'payment'),
('max_deposit', '10000', 'payment'),
('default_currency', 'USD', 'payment'),
('currency_symbol', '$', 'payment'),
('terms_page', '', 'pages'),
('privacy_page', '', 'pages'),
('announcement', '', 'general'),
('support_email', 'support@prismsmm.com', 'general'),
('api_enabled', '1', 'api'),
('api_rate_limit', '60', 'api');

-- Sample categories
INSERT INTO `categories` (`name`, `slug`, `description`, `sort_order`, `status`) VALUES
('Instagram', 'instagram', 'Instagram growth services', 1, 'active'),
('YouTube', 'youtube', 'YouTube marketing services', 2, 'active'),
('TikTok', 'tiktok', 'TikTok growth services', 3, 'active'),
('Twitter / X', 'twitter', 'Twitter engagement services', 4, 'active'),
('Facebook', 'facebook', 'Facebook marketing services', 5, 'active'),
('Telegram', 'telegram', 'Telegram channel services', 6, 'active'),
('Spotify', 'spotify', 'Spotify streaming services', 7, 'active');

-- Sample services
INSERT INTO `services` (`category_id`, `name`, `description`, `type`, `price_per_1000`, `min_quantity`, `max_quantity`, `drip_feed`, `refill`, `cancel`, `status`, `sort_order`) VALUES
(1, 'Instagram Followers - Premium Quality', 'High quality followers with profile pictures and posts. Gradual delivery.', 'default', 2.5000, 100, 50000, 1, 1, 1, 'active', 1),
(1, 'Instagram Likes - Instant', 'Fast delivery likes from real-looking accounts.', 'default', 1.2000, 50, 100000, 0, 0, 1, 'active', 2),
(1, 'Instagram Views - Real', 'Real Instagram video/reel views.', 'default', 0.5000, 100, 1000000, 1, 0, 0, 'active', 3),
(1, 'Instagram Comments - Custom', 'Custom comments from real accounts.', 'custom_comments', 15.0000, 10, 5000, 0, 0, 1, 'active', 4),
(2, 'YouTube Views - High Retention', 'High retention YouTube views (70-90%).', 'default', 3.0000, 500, 1000000, 1, 0, 0, 'active', 1),
(2, 'YouTube Subscribers - Real', 'Real YouTube subscribers with activity.', 'default', 8.0000, 100, 50000, 1, 1, 0, 'active', 2),
(2, 'YouTube Likes', 'YouTube video likes.', 'default', 4.0000, 50, 100000, 0, 1, 1, 'active', 3),
(3, 'TikTok Followers', 'TikTok followers - real looking profiles.', 'default', 3.5000, 100, 100000, 1, 1, 0, 'active', 1),
(3, 'TikTok Views', 'TikTok video views - fast delivery.', 'default', 0.3000, 500, 10000000, 0, 0, 0, 'active', 2),
(3, 'TikTok Likes', 'TikTok likes from real-looking accounts.', 'default', 1.5000, 50, 500000, 0, 0, 1, 'active', 3),
(4, 'Twitter Followers', 'Twitter/X followers with profile pictures.', 'default', 5.0000, 100, 50000, 1, 1, 0, 'active', 1),
(4, 'Twitter Likes', 'Twitter/X post likes.', 'default', 3.0000, 50, 100000, 0, 0, 1, 'active', 2),
(5, 'Facebook Page Likes', 'Facebook page likes from worldwide.', 'default', 6.0000, 100, 100000, 1, 1, 0, 'active', 1),
(6, 'Telegram Channel Members', 'Telegram channel members.', 'default', 4.0000, 500, 100000, 0, 0, 0, 'active', 1),
(7, 'Spotify Plays', 'Spotify track plays.', 'default', 2.0000, 1000, 1000000, 1, 0, 0, 'active', 1);

-- Sample pages
INSERT INTO `pages` (`title`, `slug`, `content`, `meta_title`, `meta_description`, `status`) VALUES
('Terms of Service', 'terms', '<h2>Terms of Service</h2><p>By using Prism SMM Panel, you agree to the following terms and conditions...</p>', 'Terms of Service - Prism SMM', 'Read our terms of service', 'active'),
('Privacy Policy', 'privacy', '<h2>Privacy Policy</h2><p>Your privacy is important to us. This policy explains how we collect, use, and protect your data...</p>', 'Privacy Policy - Prism SMM', 'Read our privacy policy', 'active'),
('FAQ', 'faq', '<h2>Frequently Asked Questions</h2><p><strong>What is an SMM Panel?</strong></p><p>An SMM Panel is a platform that provides social media marketing services...</p>', 'FAQ - Prism SMM', 'Frequently asked questions', 'active');
