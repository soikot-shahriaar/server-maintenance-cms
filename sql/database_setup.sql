-- Server Maintenance Log CMS Database Setup
-- Create database and tables for the maintenance log system

CREATE DATABASE IF NOT EXISTS server_maintenance_cms;
USE server_maintenance_cms;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Maintenance logs table
CREATE TABLE IF NOT EXISTS maintenance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_name VARCHAR(100) NOT NULL,
    maintenance_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    description TEXT NOT NULL,
    maintenance_type ENUM('routine', 'emergency', 'upgrade', 'repair', 'security') NOT NULL,
    status ENUM('scheduled', 'in_progress', 'completed', 'failed', 'cancelled') DEFAULT 'scheduled',
    outcome TEXT,
    performed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- Create indexes for better performance
CREATE INDEX idx_server_name ON maintenance_logs(server_name);
CREATE INDEX idx_maintenance_date ON maintenance_logs(maintenance_date);
CREATE INDEX idx_status ON maintenance_logs(status);
CREATE INDEX idx_performed_by ON maintenance_logs(performed_by);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('admin', 'admin@company.com', '$2y$10$VCf0FM1NFOdKroJRcM1X7uGAhHLtschl4e65SEiNGGSEqmw6E1sRu', 'System Administrator', 'admin');

-- Insert sample maintenance logs
INSERT INTO maintenance_logs (server_name, maintenance_date, start_time, end_time, description, maintenance_type, status, outcome, performed_by) VALUES
('WEB-SERVER-01', '2024-08-01', '02:00:00', '04:00:00', 'Monthly security patches and system updates', 'routine', 'completed', 'All patches applied successfully. Server rebooted without issues.', 1),
('DB-SERVER-01', '2024-08-05', '01:00:00', '03:30:00', 'Database optimization and index rebuilding', 'routine', 'completed', 'Database performance improved by 15%. All indexes rebuilt successfully.', 1),
('APP-SERVER-02', '2024-08-10', '14:00:00', NULL, 'Emergency fix for memory leak issue', 'emergency', 'in_progress', NULL, 1);

