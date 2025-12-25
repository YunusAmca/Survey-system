-- Database Schema for Survey System

-- Drop tables if they exist to start fresh
DROP TABLE IF EXISTS answers CASCADE;
DROP TABLE IF EXISTS submissions CASCADE;
DROP TABLE IF EXISTS questions CASCADE;
DROP TABLE IF EXISTS surveys CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Users Table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user', -- 'admin' or 'user'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Surveys Table
CREATE TABLE surveys (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    creator_id INT REFERENCES users(id) ON DELETE CASCADE,
    unique_link VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Questions Table
CREATE TABLE questions (
    id SERIAL PRIMARY KEY,
    survey_id INT REFERENCES surveys(id) ON DELETE CASCADE,
    question_text TEXT NOT NULL,
    question_type VARCHAR(50) NOT NULL, -- 'choice', 'yes_no', 'range', 'text'
    options JSONB, -- For 'choice' (e.g. ["A", "B"]) or 'range' (min, max)
    order_num INT DEFAULT 0
);

-- Submissions (Session/Attempt)
CREATE TABLE submissions (
    id SERIAL PRIMARY KEY,
    survey_id INT REFERENCES surveys(id) ON DELETE CASCADE,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Answers
CREATE TABLE answers (
    id SERIAL PRIMARY KEY,
    submission_id INT REFERENCES submissions(id) ON DELETE CASCADE,
    question_id INT REFERENCES questions(id) ON DELETE CASCADE,
    answer_text TEXT, -- For text/choice/yes_no
    answer_value INT -- For range
);
