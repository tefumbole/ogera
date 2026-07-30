-- Runs once, when the ogera_mysql_data volume is first created.
--
-- This server is Ogera-only. The `ogera` user is granted rights on these two schemas
-- and nothing else, so even a typo'd database name cannot escape the project.
CREATE DATABASE IF NOT EXISTS `ogera`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS `ogera_laravel`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'ogera'@'%' IDENTIFIED BY 'ogera_local';
GRANT ALL PRIVILEGES ON `ogera`.* TO 'ogera'@'%';
GRANT ALL PRIVILEGES ON `ogera_laravel`.* TO 'ogera'@'%';
FLUSH PRIVILEGES;
