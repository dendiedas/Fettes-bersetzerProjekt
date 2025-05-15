-- Datenbank erstellen
CREATE DATABASE IF NOT EXISTS vocabulary_trainer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vocabulary_trainer;

-- Tabelle für Benutzer
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Tabelle für Sprachen
CREATE TABLE languages (
    language_id INT AUTO_INCREMENT PRIMARY KEY,
    language_code VARCHAR(5) NOT NULL UNIQUE COMMENT 'z.B. de, en, fr, es',
    language_name VARCHAR(50) NOT NULL UNIQUE COMMENT 'z.B. Deutsch, Englisch, Französisch, Spanisch'
);

-- Tabelle für Kategorien
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL,
    description TEXT NULL,
    parent_category_id INT NULL,
    FOREIGN KEY (parent_category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- Tabelle für Vokabeln
CREATE TABLE vocabulary (
    vocab_id INT AUTO_INCREMENT PRIMARY KEY,
    source_language_id INT NOT NULL,
    target_language_id INT NOT NULL,
    source_word VARCHAR(255) NOT NULL,
    target_word VARCHAR(255) NOT NULL,
    example_sentence TEXT NULL,
    translation_example TEXT NULL,
    difficulty_level TINYINT DEFAULT 1 COMMENT '1-5, wobei 5 am schwierigsten ist',
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (source_language_id) REFERENCES languages(language_id),
    FOREIGN KEY (target_language_id) REFERENCES languages(language_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Tabelle für Lernfortschritt
CREATE TABLE user_progress (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vocab_id INT NOT NULL,
    mastery_level TINYINT DEFAULT 0 COMMENT '0-5, wobei 5 vollständig gelernt bedeutet',
    last_practiced TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    next_review TIMESTAMP NULL COMMENT 'Spaced Repetition Zeitpunkt',
    times_practiced INT DEFAULT 0,
    times_correct INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (vocab_id) REFERENCES vocabulary(vocab_id) ON DELETE CASCADE,
    UNIQUE KEY user_vocab_unique (user_id, vocab_id) COMMENT 'Ein Benutzer hat nur einen Fortschrittseintrag pro Vokabel'
);

-- Tabelle für Lernsets (z.B. "Meine Reisevokabeln")
CREATE TABLE learning_sets (
    set_id INT AUTO_INCREMENT PRIMARY KEY,
    set_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_by INT NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Verknüpfungstabelle zwischen Lernsets und Vokabeln
CREATE TABLE set_vocabulary (
    set_id INT NOT NULL,
    vocab_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (set_id, vocab_id),
    FOREIGN KEY (set_id) REFERENCES learning_sets(set_id) ON DELETE CASCADE,
    FOREIGN KEY (vocab_id) REFERENCES vocabulary(vocab_id) ON DELETE CASCADE
);

-- Beispieldaten für Sprachen einfügen
INSERT INTO languages (language_code, language_name) VALUES
('de', 'Deutsch'),
('en', 'Englisch'),
('fr', 'Französisch'),
('es', 'Spanisch'),
('it', 'Italienisch');

-- Beispieldaten für Kategorien einfügen
INSERT INTO categories (category_name, description) VALUES
('Alltag', 'Vokabeln für alltägliche Situationen'),
('Reisen', 'Vokabeln rund ums Reisen'),
('Geschäftsleben', 'Vokabeln für den beruflichen Kontext'),
('Essen & Trinken', 'Vokabeln zu Lebensmitteln und Gastronomie'),
('Natur', 'Vokabeln über Tiere, Pflanzen und Umwelt');

-- Unterkategorien beispielhaft anlegen
INSERT INTO categories (category_name, description, parent_category_id) VALUES
('Im Hotel', 'Vokabeln für den Hotelaufenthalt', 2),
('Transportmittel', 'Vokabeln zu verschiedenen Transportmitteln', 2),
('Bewerbungsgespräch', 'Vokabeln für Vorstellungsgespräche', 3);