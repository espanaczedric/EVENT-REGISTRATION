CREATE DATABASE IF NOT EXISTS university_events;

USE university_events;

-- =========================================
-- USERS
-- =========================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    course VARCHAR(100),
    year_level VARCHAR(50),
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================
-- EVENTS
-- =========================================

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    registration_deadline DATETIME,
    capacity INT DEFAULT 0,
    cover_image VARCHAR(255),
    status ENUM(
        'upcoming',
        'ongoing',
        'completed',
        'cancelled'
    ) DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================
-- EVENT SCHEDULE
-- =========================================

CREATE TABLE event_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    schedule_time VARCHAR(100),
    description TEXT,

    FOREIGN KEY (event_id)
        REFERENCES events(id)
        ON DELETE CASCADE
);


-- =========================================
-- REGISTRATIONS
-- =========================================

CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,
    event_id INT NOT NULL,

    registration_code VARCHAR(100)
        UNIQUE NOT NULL,

    status ENUM(
        'registered',
        'cancelled',
        'attended'
    ) DEFAULT 'registered',

    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (event_id)
        REFERENCES events(id)
        ON DELETE CASCADE,

    UNIQUE(user_id, event_id)
);


-- =========================================
-- ATTENDANCE
-- =========================================

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,

    registration_id INT NOT NULL,

    time_in DATETIME NOT NULL,
    time_out DATETIME NULL,

    FOREIGN KEY (registration_id)
        REFERENCES registrations(id)
        ON DELETE CASCADE
);


-- =========================================
-- EVENT HIGHLIGHTS
-- =========================================

CREATE TABLE event_highlights (
    id INT AUTO_INCREMENT PRIMARY KEY,

    event_id INT NOT NULL,

    title VARCHAR(255),
    description TEXT,

    media_url VARCHAR(255),

    media_type ENUM(
        'image',
        'video'
    ) DEFAULT 'image',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (event_id)
        REFERENCES events(id)
        ON DELETE CASCADE
);


-- =========================================
-- SAMPLE EVENTS
-- =========================================

INSERT INTO events
(
    title,
    description,
    location,
    start_date,
    end_date,
    registration_deadline,
    capacity,
    cover_image,
    status
)
VALUES
(
    'Organization Fair 2026',
    'A celebration of student organizations, creativity, leadership, and community.',
    'PHINMA-Araullo University South Campus',
    '2026-10-15 08:00:00',
    '2026-10-17 17:00:00',
    '2026-10-13 23:59:00',
    500,
    '/uploads/events/organization-fair.jpg',
    'upcoming'
);