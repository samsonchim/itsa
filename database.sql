-- Create the organisations table
CREATE TABLE organisations (
    id INT PRIMARY KEY,
    organisation_name VARCHAR(255) NOT NULL,
    organisation_email VARCHAR(191) NOT NULL UNIQUE,
    organisation_phone VARCHAR(20),
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE system_monitor (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP NOT NULL,
    battery_percent INT NOT NULL,
    battery_plugged_in BOOLEAN NOT NULL,
    running_processes INT NOT NULL,
    manufacturer VARCHAR(100),
    model VARCHAR(100),
    os_version VARCHAR(100),
    os_build_number VARCHAR(100),
    os_update_status VARCHAR(50),
    cpu_type VARCHAR(100),
    cpu_speed VARCHAR(50),
    cpu_cores INT,
    ram_amount VARCHAR(50),
    ram_type VARCHAR(50),
    ram_speed VARCHAR(50),
    storage_type VARCHAR(50),
    capacity VARCHAR(50),
    available_space VARCHAR(50),
    bios_version VARCHAR(100),
    application_count INT,
    ip_address VARCHAR(50),
    subnet_mask VARCHAR(50),
    default_gateway VARCHAR(50),
    dns_servers VARCHAR(100),
    connection_status VARCHAR(50),
    connection_speed VARCHAR(50),
    cpu_usage VARCHAR(50),
    memory_usage VARCHAR(50),
    disk_usage VARCHAR(50),
    network_usage VARCHAR(50),
    temperature VARCHAR(50),
    fan_speed VARCHAR(50),
    voltage_readings VARCHAR(50),
    health_status VARCHAR(50),
    uptime VARCHAR(50) NOT NULL
);

CREATE TABLE request_recived (    
    id INT PRIMARY KEY,
    organisation_id INT NOT NULL,
    description VARCHAR(255) NOT NULL
);

CREATE TABLE ongoing_maintenance (    
    id INT PRIMARY KEY,
    organisation_id INT NOT NULL,
    description VARCHAR(255) NOT NULL
);

CREATE TABLE completed_maintenance (    
    id INT PRIMARY KEY,
    organisation_id INT NOT NULL,
    description VARCHAR(255) NOT NULL
);


CREATE TABLE staffs (    
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    visible_password VARCHAR (255) NOT NULL,
    system_id INT NOT NULL,
    organisation_id INT NOT NULL,
    request_sent VARCHAR(255) NOT NULL,
    ongoing_maintenance VARCHAR(255) NOT NULL,
    completed_maintenance VARCHAR (255) NOT NULL
);

