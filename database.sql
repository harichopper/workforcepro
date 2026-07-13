-- ============================================================
-- WorkForce Pro - Complete Database Schema
-- Version: 1.0.0
-- ============================================================

CREATE DATABASE IF NOT EXISTS workforcepro
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE workforcepro;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS leave_requests;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS salaries;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS designations;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- Users (Admin/HR accounts)
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','hr','manager') NOT NULL DEFAULT 'hr',
    avatar VARCHAR(255) NULL,
    phone VARCHAR(30) NULL,
    bio TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active)
) ENGINE=InnoDB;

-- Departments
CREATE TABLE departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    code VARCHAR(20) NOT NULL UNIQUE,
    manager_name VARCHAR(120) NULL,
    description TEXT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dept_status (status)
) ENGINE=InnoDB;

-- Designations
CREATE TABLE designations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_id INT UNSIGNED NOT NULL,
    title VARCHAR(120) NOT NULL,
    level ENUM('junior','mid','senior','lead','manager','director','c-level') NOT NULL DEFAULT 'mid',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_desig_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_desig_dept (department_id),
    INDEX idx_desig_status (status)
) ENGINE=InnoDB;

-- Employees
CREATE TABLE employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    emp_code VARCHAR(30) NOT NULL UNIQUE,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    dob DATE NULL,
    gender ENUM('male','female','other') NOT NULL DEFAULT 'male',
    department_id INT UNSIGNED NOT NULL,
    designation_id INT UNSIGNED NOT NULL,
    hire_date DATE NOT NULL,
    address TEXT NULL,
    avatar VARCHAR(255) NULL,
    status ENUM('active','inactive','terminated') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_emp_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_emp_desig FOREIGN KEY (designation_id) REFERENCES designations(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_emp_dept (department_id),
    INDEX idx_emp_desig (designation_id),
    INDEX idx_emp_status (status),
    INDEX idx_emp_name (first_name, last_name)
) ENGINE=InnoDB;

-- Salaries
CREATE TABLE salaries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    basic_salary DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    allowances DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    deductions DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    net_salary DECIMAL(12,2) GENERATED ALWAYS AS (basic_salary + allowances - deductions) STORED,
    pay_month DATE NOT NULL,
    status ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
    paid_at DATETIME NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_salary_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY uq_salary_month (employee_id, pay_month),
    INDEX idx_salary_status (status),
    INDEX idx_salary_month (pay_month)
) ENGINE=InnoDB;

-- Attendance
CREATE TABLE attendance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    check_in TIME NULL,
    check_out TIME NULL,
    status ENUM('present','absent','half-day','leave','holiday') NOT NULL DEFAULT 'present',
    remarks VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_att_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY uq_att_daily (employee_id, date),
    INDEX idx_att_date (date),
    INDEX idx_att_status (status)
) ENGINE=InnoDB;

-- Leave Requests
CREATE TABLE leave_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    leave_type ENUM('annual','sick','casual','maternity','paternity','unpaid') NOT NULL DEFAULT 'annual',
    from_date DATE NOT NULL,
    to_date DATE NOT NULL,
    days INT UNSIGNED GENERATED ALWAYS AS (DATEDIFF(to_date, from_date) + 1) STORED,
    reason TEXT NULL,
    status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
    reviewed_by INT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_leave_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_leave_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_leave_emp (employee_id),
    INDEX idx_leave_status (status),
    INDEX idx_leave_dates (from_date, to_date)
) ENGINE=InnoDB;

-- Notifications
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(140) NOT NULL,
    message VARCHAR(255) NOT NULL,
    type ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
    read_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_notif_user (user_id, read_at)
) ENGINE=InnoDB;

-- Audit Logs
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity VARCHAR(80) NOT NULL,
    entity_id INT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_audit_entity (entity, entity_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_created (created_at)
) ENGINE=InnoDB;

-- Settings
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin user (password: password)
INSERT INTO users (name, email, password, role, phone, bio) VALUES
('Admin User', 'admin@workforce.test', '$2y$10$Hc5Xo2r5MLsZvzEUbgUAWuJSQKes77IPtl68cug9SQWDEgFLRfbFO', 'admin', '+91 90000 00001', 'Platform administrator for WorkForce Pro.');

-- Departments
INSERT INTO departments (name, code, manager_name, description, status) VALUES
('Engineering',       'ENG',  'Ravi Kumar',    'Software development and technology.',  'active'),
('Human Resources',   'HR',   'Priya Nair',    'Recruitment, payroll, and culture.',    'active'),
('Marketing',         'MKT',  'Arjun Sharma',  'Brand and growth marketing.',           'active'),
('Finance',           'FIN',  'Meera Pillai',  'Accounts, budgets and financial ops.',  'active'),
('Operations',        'OPS',  'Vikram Das',    'Logistics and business operations.',    'active');

-- Designations
INSERT INTO designations (department_id, title, level) VALUES
(1,'Software Engineer','junior'), (1,'Senior Engineer','senior'), (1,'Engineering Lead','lead'),
(2,'HR Executive','junior'),      (2,'HR Manager','manager'),
(3,'Marketing Analyst','junior'), (3,'Marketing Lead','lead'),
(4,'Accountant','mid'),           (4,'Finance Manager','manager'),
(5,'Operations Analyst','mid'),   (5,'Operations Head','director');

-- Employees
INSERT INTO employees (emp_code,first_name,last_name,email,phone,dob,gender,department_id,designation_id,hire_date,status) VALUES
('EMP-001','Aanya','Krishnan','aanya@workforce.test','+91 88001 00001','2000-04-12','female',1,1,DATE_SUB(CURDATE(),INTERVAL 14 MONTH),'active'),
('EMP-002','Rohan','Das','rohan@workforce.test','+91 88001 00002','1998-11-08','male',1,2,DATE_SUB(CURDATE(),INTERVAL 20 MONTH),'active'),
('EMP-003','Sara','Khan','sara@workforce.test','+91 88001 00003','1999-02-20','female',2,4,DATE_SUB(CURDATE(),INTERVAL 10 MONTH),'active'),
('EMP-004','Kabir','Nair','kabir@workforce.test','+91 88001 00004','1997-09-03','male',3,6,DATE_SUB(CURDATE(),INTERVAL 18 MONTH),'active'),
('EMP-005','Isha','Patel','isha@workforce.test','+91 88001 00005','2001-07-14','female',4,8,DATE_SUB(CURDATE(),INTERVAL 8 MONTH),'active'),
('EMP-006','Dev','Sharma','dev@workforce.test','+91 88001 00006','1996-10-25','male',5,10,DATE_SUB(CURDATE(),INTERVAL 24 MONTH),'active'),
('EMP-007','Neha','Verma','neha@workforce.test','+91 88001 00007','2000-03-16','female',1,3,DATE_SUB(CURDATE(),INTERVAL 6 MONTH),'active'),
('EMP-008','Aditya','Singh','aditya@workforce.test','+91 88001 00008','1998-05-30','male',2,5,DATE_SUB(CURDATE(),INTERVAL 30 MONTH),'active'),
('EMP-009','Tara','Joseph','tara@workforce.test','+91 88001 00009','2001-01-05','female',3,7,DATE_SUB(CURDATE(),INTERVAL 12 MONTH),'active'),
('EMP-010','Yash','Gupta','yash@workforce.test','+91 88001 00010','1999-08-17','male',4,9,DATE_SUB(CURDATE(),INTERVAL 4 MONTH),'active');

-- Salaries
INSERT INTO salaries (employee_id,basic_salary,allowances,deductions,pay_month,status,paid_at) VALUES
(1,45000,5000,2000,DATE_FORMAT(CURDATE(),'%Y-%m-01'),'paid',NOW()),
(2,75000,8000,4000,DATE_FORMAT(CURDATE(),'%Y-%m-01'),'paid',NOW()),
(3,40000,4000,1500,DATE_FORMAT(CURDATE(),'%Y-%m-01'),'paid',NOW()),
(4,50000,6000,2500,DATE_FORMAT(CURDATE(),'%Y-%m-01'),'pending',NULL),
(5,55000,7000,3000,DATE_FORMAT(CURDATE(),'%Y-%m-01'),'pending',NULL),
(6,60000,7500,3500,DATE_FORMAT(CURDATE(),'%Y-%m-01'),'paid',NOW()),
(7,90000,10000,5000,DATE_FORMAT(CURDATE(),'%Y-%m-01'),'paid',NOW()),
(8,48000,5500,2200,DATE_FORMAT(CURDATE(),'%Y-%m-01'),'pending',NULL),
(9,52000,6000,2600,DATE_FORMAT(CURDATE(),'%Y-%m-01'),'paid',NOW()),
(10,58000,7000,3000,DATE_FORMAT(CURDATE(),'%Y-%m-01'),'pending',NULL);

-- Attendance (last 7 days)
INSERT INTO attendance (employee_id,date,check_in,check_out,status) VALUES
(1,DATE_SUB(CURDATE(),INTERVAL 6 DAY),'09:02:00','18:05:00','present'),
(1,DATE_SUB(CURDATE(),INTERVAL 5 DAY),'09:15:00','18:00:00','present'),
(1,DATE_SUB(CURDATE(),INTERVAL 4 DAY),'09:00:00','13:00:00','half-day'),
(1,DATE_SUB(CURDATE(),INTERVAL 3 DAY),NULL,NULL,'absent'),
(1,DATE_SUB(CURDATE(),INTERVAL 2 DAY),'09:05:00','18:02:00','present'),
(1,DATE_SUB(CURDATE(),INTERVAL 1 DAY),'09:00:00','18:00:00','present'),
(2,DATE_SUB(CURDATE(),INTERVAL 6 DAY),'09:10:00','18:10:00','present'),
(2,DATE_SUB(CURDATE(),INTERVAL 5 DAY),'09:00:00','18:00:00','present'),
(2,DATE_SUB(CURDATE(),INTERVAL 4 DAY),'09:00:00','18:00:00','present'),
(2,DATE_SUB(CURDATE(),INTERVAL 3 DAY),'09:00:00','18:00:00','present'),
(2,DATE_SUB(CURDATE(),INTERVAL 2 DAY),NULL,NULL,'leave'),
(2,DATE_SUB(CURDATE(),INTERVAL 1 DAY),'09:20:00','18:00:00','present'),
(3,DATE_SUB(CURDATE(),INTERVAL 3 DAY),'09:00:00','18:00:00','present'),
(3,DATE_SUB(CURDATE(),INTERVAL 2 DAY),'09:00:00','18:00:00','present'),
(3,DATE_SUB(CURDATE(),INTERVAL 1 DAY),NULL,NULL,'absent');

-- Leave Requests
INSERT INTO leave_requests (employee_id,leave_type,from_date,to_date,reason,status,reviewed_by,reviewed_at) VALUES
(1,'annual',DATE_SUB(CURDATE(),INTERVAL 2 DAY),DATE_SUB(CURDATE(),INTERVAL 1 DAY),'Family vacation','approved',1,NOW()),
(2,'sick',DATE_SUB(CURDATE(),INTERVAL 5 DAY),DATE_SUB(CURDATE(),INTERVAL 4 DAY),'Fever','approved',1,NOW()),
(3,'casual',DATE_ADD(CURDATE(),INTERVAL 3 DAY),DATE_ADD(CURDATE(),INTERVAL 4 DAY),'Personal work','pending',NULL,NULL),
(4,'annual',DATE_ADD(CURDATE(),INTERVAL 7 DAY),DATE_ADD(CURDATE(),INTERVAL 9 DAY),'Wedding','pending',NULL,NULL);

-- Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('company_name', 'WorkForce Pro Inc.'),
('company_email', 'hr@workforce.test'),
('timezone', 'Asia/Kolkata'),
('records_per_page', '10'),
('currency_symbol', '₹'),
('leave_annual_quota', '20'),
('leave_sick_quota', '10'),
('leave_casual_quota', '8');

-- Notifications
INSERT INTO notifications (user_id, title, message, type) VALUES
(1, 'Welcome to WorkForce Pro', 'Your HR management platform is ready with sample data.', 'success'),
(1, 'Pending Leave Requests', '2 leave requests are awaiting your approval.', 'warning'),
(1, 'Payroll Reminder', '4 salary records are still pending payment for this month.', 'info');

-- Audit Log
INSERT INTO audit_logs (user_id, action, entity, ip_address, user_agent, metadata) VALUES
(1, 'seeded', 'database', '127.0.0.1', 'WorkForcePro Seeder', JSON_OBJECT('version', '1.0.0'));
