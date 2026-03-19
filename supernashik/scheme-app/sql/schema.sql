CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','officer') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  application_id VARCHAR(20) NOT NULL UNIQUE,
  user_id INT NOT NULL,
  status ENUM('draft','submitted','approved','rejected','sent_back') NOT NULL DEFAULT 'draft',
  full_name VARCHAR(150) NOT NULL,
  dob DATE NOT NULL,
  gender ENUM('Male','Female','Transgender','Prefer not to say') NOT NULL,
  mobile VARCHAR(20) NOT NULL,
  alt_mobile VARCHAR(20),
  email VARCHAR(150) NOT NULL,
  address TEXT NOT NULL,
  village VARCHAR(120) NOT NULL,
  taluka VARCHAR(50) NOT NULL,
  pin_code VARCHAR(10) NOT NULL,
  school_name VARCHAR(200) NOT NULL,
  school_address TEXT NOT NULL,
  appearing_10th ENUM('Yes','No') NOT NULL,
  category ENUM('SC','ST','Other') NOT NULL,
  disability ENUM('Yes','No') NOT NULL,
  udid_number VARCHAR(30),
  family_income_lt2l ENUM('Yes','No') NOT NULL,
  aadhaar VARCHAR(20) NOT NULL,
  course_interest ENUM('NEET','JEE','Both') NOT NULL,
  exam_city VARCHAR(50) NOT NULL,
  declaration_confirm TINYINT(1) NOT NULL DEFAULT 0,
  pdf_path VARCHAR(255),
  submitted_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  application_id INT NOT NULL,
  doc_type ENUM('photo','aadhaar','income','domicile','other') NOT NULL DEFAULT 'other',
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  file_type VARCHAR(50) NOT NULL,
  file_size INT NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (application_id) REFERENCES applications(id)
);

CREATE TABLE status_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  application_id INT NOT NULL,
  status ENUM('draft','submitted','approved','rejected','sent_back') NOT NULL,
  remarks TEXT,
  changed_by INT NOT NULL,
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (application_id) REFERENCES applications(id),
  FOREIGN KEY (changed_by) REFERENCES users(id)
);
