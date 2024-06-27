-- Create the organisations table
CREATE TABLE organisations (
    id INT PRIMARY KEY,
    organisation_name VARCHAR(255) NOT NULL,
    organisation_email VARCHAR(191) NOT NULL UNIQUE,
    organisation_phone VARCHAR(20),
    ip_address VARCHAR((255),)
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE request_recieved (    
    id INT PRIMARY KEY AUTO_INCREMENT,
    technician_id INT NOT NULL,
    organisation_id INT NOT NULL,
    staff_email VARCHAR(255) NOT NULL,
    request_id INT NOT NULL,
    recieved_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ongoing_maintenance (    
    id INT PRIMARY KEY,
    organisation_id INT NOT NULL,
    staff_id INT NOT NULL,
    request_id INT NOT NULL,
    technician_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE completed_maintenance (    
    id INT PRIMARY KEY,
    organisation_id INT NOT NULL,
    description VARCHAR(255) NOT NULL
);

CREATE TABLE request_sent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    staff_ip VARCHAR (255) NOT NULL,
    organisation_id INT NOT NULL,
    subject_issue VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    notice_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staffs(id)
);



CREATE TABLE staffs (    
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR (255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    plank VARCHAR (255) NOT NULL,
    system_id INT NOT NULL,
    organisation_id INT NOT NULL,
    request_sent VARCHAR(255) NOT NULL,
    ongoing_maintenance VARCHAR(255) NOT NULL,
    completed_maintenance VARCHAR (255) NOT NULL
);


CREATE TABLE technicians (    
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR (255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    plank VARCHAR (255) NOT NULL
);


CREATE TABLE p_assignment (    
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisation_id VARCHAR(255) NOT NULL,
    technician_id INT NOT NULL
);
