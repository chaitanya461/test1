-- Create users table
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create quizzes table
CREATE TABLE quizzes (
    quiz_id SERIAL PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    created_by INT REFERENCES users(user_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Create questions table
CREATE TABLE questions (
    question_id SERIAL PRIMARY KEY,
    quiz_id INT REFERENCES quizzes(quiz_id),
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_answer CHAR(1) NOT NULL CHECK (correct_answer IN ('a', 'b', 'c', 'd')),
    points INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create user responses table
CREATE TABLE user_responses (
    response_id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(user_id),
    question_id INT REFERENCES questions(question_id),
    selected_answer CHAR(1) CHECK (selected_answer IN ('a', 'b', 'c', 'd')),
    is_correct BOOLEAN,
    responded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create quiz results table
CREATE TABLE quiz_results (
    result_id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(user_id),
    quiz_id INT REFERENCES quizzes(quiz_id),
    total_questions INT NOT NULL,
    correct_answers INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create an admin user (password: admin123 - change this in production)
INSERT INTO users (username, email, password_hash, is_admin)
VALUES ('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);
