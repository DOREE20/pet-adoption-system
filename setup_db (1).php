<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wpl_final');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Create DB
$conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db(DB_NAME);

$tables = [];

// ── USERS ─────────────────────────────────────────
$tables['users'] = "
CREATE TABLE IF NOT EXISTS users (
  email_id   VARCHAR(255) PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name  VARCHAR(100) NOT NULL,
  password   VARCHAR(255) NOT NULL,
  flat_no    VARCHAR(50),
  building   VARCHAR(100),
  street     VARCHAR(100),
  area       VARCHAR(100),
  city       VARCHAR(100),
  pincode    CHAR(6),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

// ── ANIMALS ───────────────────────────────────────
$tables['animals'] = "
CREATE TABLE IF NOT EXISTS animals (
  animal_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  type VARCHAR(50),
  breed VARCHAR(100),
  food VARCHAR(100),
  category VARCHAR(100),
  allergies VARCHAR(100),
  adopt_reason TEXT,
  is_hot TINYINT(1) DEFAULT 0,
  img_url TEXT,
  stock INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

// ── EMPLOYEES ─────────────────────────────────────
$tables['employees'] = "
CREATE TABLE IF NOT EXISTS employees (
  emp_id INT AUTO_INCREMENT PRIMARY KEY,
  emp_name VARCHAR(100),
  role ENUM('admin','vet','booking','maintainer'),
  password VARCHAR(100),
  location VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

// ── APPOINTMENTS ──────────────────────────────────
$tables['appointments'] = "
CREATE TABLE IF NOT EXISTS appointments (
  appt_id INT AUTO_INCREMENT PRIMARY KEY,
  email_id VARCHAR(255),
  animal_id INT,
  appt_date DATE,
  purpose VARCHAR(100),
  status ENUM('pending','confirmed','rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (email_id) REFERENCES users(email_id) ON DELETE CASCADE,
  FOREIGN KEY (animal_id) REFERENCES animals(animal_id) ON DELETE SET NULL
) ENGINE=InnoDB;
";

// ── SERVICE REQUESTS ──────────────────────────────
$tables['service_requests'] = "
CREATE TABLE IF NOT EXISTS service_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  email_id VARCHAR(255),
  type ENUM('shopping','playtime'),
  details TEXT,
  status ENUM('pending','confirmed','rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (email_id) REFERENCES users(email_id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// ── PLAYTIME ──────────────────────────────────────
$tables['playtime'] = "
CREATE TABLE IF NOT EXISTS playtime (
  play_id INT AUTO_INCREMENT PRIMARY KEY,
  email_id VARCHAR(255),
  date DATE,
  slot VARCHAR(50),
  pet_type VARCHAR(100),
  people INT,
  FOREIGN KEY (email_id) REFERENCES users(email_id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// ── SHOPPING ──────────────────────────────────────
$tables['shopping'] = "
CREATE TABLE IF NOT EXISTS shopping (
  shop_id INT AUTO_INCREMENT PRIMARY KEY,
  email_id VARCHAR(255),
  pet_name VARCHAR(100),
  date DATE,
  status ENUM('pending','confirmed','completed') DEFAULT 'pending',
  FOREIGN KEY (email_id) REFERENCES users(email_id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// ── ADMIN ─────────────────────────────────────────
$tables['admin'] = "
CREATE TABLE IF NOT EXISTS admin (
  emp_id INT PRIMARY KEY,
  department VARCHAR(100),
  responsibility_level VARCHAR(100),
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// ── VET ───────────────────────────────────────────
$tables['vet'] = "
CREATE TABLE IF NOT EXISTS vet (
  emp_id INT PRIMARY KEY,
  specialization VARCHAR(100),
  experience_years INT,
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// ── BOOKING STAFF ─────────────────────────────────
$tables['booking_staff'] = "
CREATE TABLE IF NOT EXISTS booking_staff (
  emp_id INT PRIMARY KEY,
  assigned_station VARCHAR(100),
  shift_pattern VARCHAR(100),
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// ── MAINTAINER ────────────────────────────────────
$tables['maintainer'] = "
CREATE TABLE IF NOT EXISTS maintainer (
  emp_id INT PRIMARY KEY,
  trade_certifications VARCHAR(100),
  tools_assigned VARCHAR(100),
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// ── CREATE TABLES ─────────────────────────────────
foreach ($tables as $name => $sql) {
    $conn->query($sql);
}


$employees = [
    ['Admin User','admin','Office'],
    ['Vet User','vet','Clinic'],
    ['Booking Staff','booking','Desk'],
    ['Maintainer Guy','maintainer','Maintenance']
];

foreach ($employees as $e) {
    $name = $e[0];
    
    // password = name without spaces + @123
    $pass = strtolower(str_replace(' ', '', $name)) . "@123";

    $stmt = $conn->prepare("INSERT INTO employees(emp_name, role, password, location) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $name, $e[1], $pass, $e[2]);
    $stmt->execute();
}
echo "<h2>✅ Database wpl_final created successfully (11 tables)</h2>";
echo "<a href='home.html'>Go to Home</a>";

$conn->close();
?>