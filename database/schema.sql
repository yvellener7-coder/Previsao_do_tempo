-- =========================================================
-- Sistema de Previsao do Tempo (Trabalho Escolar)
-- Base de dados: MySQL
-- =========================================================

DROP DATABASE IF EXISTS tempo_escolar;
CREATE DATABASE tempo_escolar
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tempo_escolar;

-- ---------------------------------------------------------
-- Tabela de utilizadores (autenticacao)
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    language_preference ENUM('pt', 'en') NOT NULL DEFAULT 'pt',
    theme_preference ENUM('light', 'dark') NOT NULL DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- Tokens de recuperacao de senha
-- ---------------------------------------------------------
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE INDEX idx_password_resets_user_id ON password_resets(user_id);
CREATE INDEX idx_password_resets_expires_at ON password_resets(expires_at);

-- ---------------------------------------------------------
-- Cidades monitorizadas
-- ---------------------------------------------------------
CREATE TABLE cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    country_code CHAR(2) NOT NULL,
    lat DECIMAL(9,6) NOT NULL,
    lon DECIMAL(9,6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_city_country (name, country_code)
);

-- ---------------------------------------------------------
-- Registos de previsao por utilizador (CRUD principal)
-- ---------------------------------------------------------
CREATE TABLE weather_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    city_id INT NOT NULL,
    temperature_c DECIMAL(5,2) NOT NULL,
    weather_main VARCHAR(80) NOT NULL,
    weather_description VARCHAR(255) NOT NULL,
    humidity INT NOT NULL,
    wind_speed DECIMAL(6,2) NOT NULL,
    forecast_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_weather_records_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_weather_records_city
        FOREIGN KEY (city_id) REFERENCES cities(id)
        ON DELETE RESTRICT
);

CREATE INDEX idx_weather_records_user_id ON weather_records(user_id);
CREATE INDEX idx_weather_records_city_id ON weather_records(city_id);
CREATE INDEX idx_weather_records_forecast_time ON weather_records(forecast_time);

-- ---------------------------------------------------------
