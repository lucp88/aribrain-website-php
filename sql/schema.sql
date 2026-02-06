CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    authors TEXT NOT NULL,
    journal VARCHAR(255),
    year INT,
    pub_date DATE,
    location VARCHAR(255),
    conference VARCHAR(255),
    presentation_type VARCHAR(100),
    description TEXT,
    topics TEXT,
    doi VARCHAR(255),
    url VARCHAR(500),
    github_url VARCHAR(500),
    video_url VARCHAR(500),
    pub_type ENUM('paper', 'talk') DEFAULT 'paper',
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS forum_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(7) DEFAULT '#6bcf8e',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS forum_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(255),
    is_approved BOOLEAN DEFAULT TRUE,
    is_answered BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS forum_question_tags (
    question_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (question_id, tag_id),
    FOREIGN KEY (question_id) REFERENCES forum_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES forum_tags(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS forum_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    content TEXT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(255),
    is_admin_reply BOOLEAN DEFAULT FALSE,
    admin_id INT,
    is_approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES forum_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

INSERT IGNORE INTO forum_tags (name, slug, color) VALUES 
('Installation', 'installation', '#3b82f6'),
('Analysis', 'analysis', '#8b5cf6'),
('Bug Report', 'bug-report', '#ef4444'),
('Feature Request', 'feature-request', '#f59e0b'),
('General', 'general', '#6bcf8e'),
('fMRI', 'fmri', '#ec4899'),
('TDP', 'tdp', '#14b8a6');
