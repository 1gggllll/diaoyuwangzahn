CREATE DATABASE IF NOT EXISTS phishguard DEFAULT CHARSET utf8mb4;
USE phishguard;

CREATE TABLE phishing_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  account VARCHAR(100),
  password VARCHAR(100),
  ip VARCHAR(50),
  channel VARCHAR(20) DEFAULT 'direct',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50),
  password VARCHAR(50)
);

INSERT INTO admins (username, password) VALUES ('admin', 'admin123');
