DROP DATABASE IF EXISTS sympa;
CREATE DATABASE sympa;
DROP USER IF EXISTS 'sympa'@'localhost';
CREATE USER 'sympa'@'localhost' IDENTIFIED BY 'sympa';
GRANT ALL ON sympa.* TO 'sympa'@'localhost';
USE sympa;

