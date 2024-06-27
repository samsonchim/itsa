

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
