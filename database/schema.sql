

USE dbms;

CREATE TABLE medical_records (
    record_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    diagnosis TEXT NOT NULL,
    treatment TEXT NOT NULL,
    prescriptions TEXT,
    date_of_record TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    report_generated BOOLEAN DEFAULT FALSE,  -- Flag to check if report is generated
    FOREIGN KEY (patient_id) REFERENCES patient_details(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctor_details(doctor_id) ON DELETE CASCADE
);
CREATE TABLE health_vitals (
    vital_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    recorded_date DATE NOT NULL,
    recorded_time TIME NOT NULL,
    blood_pressure VARCHAR(20), -- e.g., "120/80"
    heart_rate INT, -- beats per minute
    blood_sugar_level DECIMAL(5,2), -- mg/dL
    temperature DECIMAL(4,1), -- body temperature in °C
    notification_sent BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (patient_id) REFERENCES patient_details(patient_id) ON DELETE CASCADE
);
CREATE TABLE appointments (
    appointment_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    reason_for_visit TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_details(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctor_details(doctor_id) ON DELETE CASCADE
);
CREATE TABLE medications (
    medication_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    medication_name VARCHAR(255) NOT NULL,
    dosage VARCHAR(100),
    frequency VARCHAR(100), -- e.g., "twice a day"
    start_date DATE NOT NULL,
    end_date DATE,
    FOREIGN KEY (patient_id) REFERENCES patient_details(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctor_details(doctor_id) ON DELETE CASCADE
);
CREATE TABLE emergency_contacts (
    contact_id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    contact_relationship VARCHAR(50), -- e.g., "Mother", "Friend"
    contact_phone VARCHAR(20) NOT NULL,
    contact_email VARCHAR(100),
    FOREIGN KEY (patient_id) REFERENCES patient_details(patient_id) ON DELETE CASCADE
);
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    age INT,
    phone_number VARCHAR(20),
    role ENUM('patient', 'doctor') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE patient_details (
    patient_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
CREATE TABLE doctor_details (
    doctor_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    license_number VARCHAR(50),
    consultation_fee DECIMAL(10,2),
    available_days SET('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
    consultation_hours_start TIME,
    consultation_hours_end TIME,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
