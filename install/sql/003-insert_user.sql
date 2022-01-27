USE mellivora;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

INSERT INTO users (email, passhash, download_key, username, class, enabled, country_id, added) VALUES 
('admin@admin.com', '$2y$10$QgutLsaCOGvkvlCWZvZrXe1hOYNMPguDxmYsfPBZwn2WBx6o2jLfe', '59344858ede21a6cdb7a1a1718042faed710cbec5addbb3bdedb9c3e23f415ea', 'admin', 100, 1, 200, 1);

