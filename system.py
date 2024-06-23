import subprocess
import sys
import platform
import os
import psutil
import mysql.connector
from datetime import datetime
import socket
import time

# Function to install a package using pip
def install_package(package):
    subprocess.check_call([sys.executable, "-m", "pip", "install", package])

# List of required packages
required_packages = [
    "psutil",
    "mysql-connector-python"
]

# Check and install required packages
for package in required_packages:
    try:
        __import__(package)
    except ImportError:
        print(f"{package} not found. Installing...")
        install_package(package)

# Function to connect to MySQL database
def connect_to_database():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="itsa"
    )

# Function to get battery information
def get_battery_info():
    battery = psutil.sensors_battery()
    return {
        'percent': battery.percent,
        'plugged_in': battery.power_plugged
    }

# Function to get number of running processes
def get_running_processes():
    return len(psutil.pids())

# Function to get system uptime
def get_system_info():
    return {
        'uptime': time.time() - psutil.boot_time()
    }

# Function to get network information
def get_network_info():
    net_if_addrs = psutil.net_if_addrs()
    net_if_stats = psutil.net_if_stats()

    ip_address = 'N/A'
    subnet_mask = 'N/A'
    default_gateway = 'N/A'
    dns_servers = 'N/A'
    connection_status = 'Disconnected'
    connection_speed = 'N/A'

    for interface in net_if_addrs:
        if interface == 'lo':
            continue
        for snic in net_if_addrs[interface]:
            if snic.family == socket.AF_INET:
                ip_address = snic.address
                subnet_mask = snic.netmask
                break
        if interface in net_if_stats:
            connection_status = 'Connected' if net_if_stats[interface].isup else 'Disconnected'
            connection_speed = net_if_stats[interface].speed
            break

    return {
        'ip_address': ip_address,
        'subnet_mask': subnet_mask,
        'default_gateway': default_gateway,
        'dns_servers': dns_servers,
        'connection_status': connection_status,
        'connection_speed': connection_speed
    }

# Function to get additional system information
def get_additional_info():
    # Manufacturer and Model
    manufacturer = platform.uname().system
    model = platform.uname().node

    # Processor (CPU)
    cpu_type = platform.processor()
    cpu_speed = psutil.cpu_freq().current if psutil.cpu_freq() else 'N/A'
    cpu_cores = psutil.cpu_count(logical=True)

    # Memory (RAM)
    virtual_mem = psutil.virtual_memory()
    ram_amount = virtual_mem.total

    # Storage Devices
    storage_info = psutil.disk_partitions()
    storage_details = []
    for partition in storage_info:
        try:
            usage = psutil.disk_usage(partition.mountpoint)
            storage_details.append({
                'type': partition.fstype,
                'capacity': usage.total,
                'available_space': usage.free
            })
        except PermissionError:
            continue

    # Operating System
    os_version = platform.version()
    os_build_number = platform.release()

    # BIOS version
    bios_version = 'N/A'  # Placeholder, requires specific methods to fetch

    # Performance Data
    cpu_usage = psutil.cpu_percent(interval=1)
    memory_usage = virtual_mem.percent
    disk_usage = psutil.disk_usage('/').percent
    network_usage = psutil.net_io_counters().bytes_sent + psutil.net_io_counters().bytes_recv

    # Placeholder for additional metrics
    temperature = 'N/A'
    fan_speed = 'N/A'
    voltage_readings = 'N/A'
    health_status = 'N/A'

    return {
        'manufacturer': manufacturer,
        'model': model,
        'cpu_type': cpu_type,
        'cpu_speed': cpu_speed,
        'cpu_cores': cpu_cores,
        'ram_amount': ram_amount,
        'storage_details': storage_details,
        'os_version': os_version,
        'os_build_number': os_build_number,
        'bios_version': bios_version,
        'cpu_usage': cpu_usage,
        'memory_usage': memory_usage,
        'disk_usage': disk_usage,
        'network_usage': network_usage,
        'temperature': temperature,
        'fan_speed': fan_speed,
        'voltage_readings': voltage_readings,
        'health_status': health_status
    }

# Function to insert data into MySQL database
def insert_data(db_connection, id, data):
    cursor = db_connection.cursor()
    query = """
    INSERT INTO system_monitor (
        id, timestamp, battery_percent, battery_plugged_in, running_processes,
        manufacturer, model, os_version, os_build_number, bios_version,
        cpu_type, cpu_speed, cpu_cores, ram_amount,
        storage_type, capacity, available_space,
        ip_address, subnet_mask, default_gateway, dns_servers,
        connection_status, connection_speed,
        cpu_usage, memory_usage, disk_usage, network_usage,
        temperature, fan_speed, voltage_readings, health_status,
        uptime
    ) VALUES (
        %s, %s, %s, %s, %s,
        %s, %s, %s, %s, %s,
        %s, %s, %s, %s,
        %s, %s, %s,
        %s, %s, %s, %s,
        %s, %s,
        %s, %s, %s, %s,
        %s, %s, %s, %s,
        %s
    )
    """
    storage = data['storage_details'][0] if data['storage_details'] else {'type': 'N/A', 'capacity': 'N/A', 'available_space': 'N/A'}
    values = (
        id, data['timestamp'], data['battery']['percent'], data['battery']['plugged_in'], data['processes'],
        data['manufacturer'], data['model'], data['os_version'], data['os_build_number'], data['bios_version'],
        data['cpu_type'], data['cpu_speed'], data['cpu_cores'], data['ram_amount'],
        storage['type'], storage['capacity'], storage['available_space'],
        data['network']['ip_address'], data['network']['subnet_mask'], data['network']['default_gateway'], data['network']['dns_servers'],
        data['network']['connection_status'], data['network']['connection_speed'],
        data['cpu_usage'], data['memory_usage'], data['disk_usage'], data['network_usage'],
        data['temperature'], data['fan_speed'], data['voltage_readings'], data['health_status'],
        data['uptime']
    )
    cursor.execute(query, values)
    db_connection.commit()
    cursor.close()

# Function to check if a record exists in MySQL database
def record_exists(db_connection, id):
    cursor = db_connection.cursor()
    query = "SELECT 1 FROM system_monitor WHERE id=%s"
    cursor.execute(query, (id,))
    result = cursor.fetchone()
    cursor.close()
    return result is not None

# Main function to gather data and insert into MySQL database
def main():
    try:
        id = input("Enter the ID: ")
        db_connection = connect_to_database()

        while True:
            data = {
                'id': id,
                'timestamp': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
                'battery': get_battery_info(),
                'processes': get_running_processes(),
                'network': get_network_info(),
                'manufacturer': platform.uname().system,
                'model': platform.uname().node,
                'os_version': platform.version(),
                'os_build_number': platform.release(),
                'bios_version': 'N/A',  # Placeholder
                'cpu_type': platform.processor(),
                'cpu_speed': psutil.cpu_freq().current if psutil.cpu_freq() else 'N/A',
                'cpu_cores': psutil.cpu_count(logical=True),
                'ram_amount': psutil.virtual_memory().total,
                'storage_details': [{'type': 'N/A', 'capacity': 'N/A', 'available_space': 'N/A'}],
                'cpu_usage': psutil.cpu_percent(interval=1),
                'memory_usage': psutil.virtual_memory().percent,
                'disk_usage': psutil.disk_usage('/').percent,
                'network_usage': psutil.net_io_counters().bytes_sent + psutil.net_io_counters().bytes_recv,
                'temperature': 'N/A',  # Placeholder
                'fan_speed': 'N/A',  # Placeholder
                'voltage_readings': 'N/A',  # Placeholder
                'health_status': 'N/A',  # Placeholder
                'uptime': time.time() - psutil.boot_time()
            }

            if record_exists(db_connection, id):
                print(f"Updating data for ID: {id}")
                update_data(db_connection, id, data)
            else:
                print(f"Inserting data for ID: {id}")
                insert_data(db_connection, id, data)

            # Wait for 60 seconds before collecting data again
            time.sleep(60)

    except mysql.connector.Error as e:
        print(f"Error connecting to MySQL database: {e}")
    finally:
        if db_connection:
            db_connection.close()

if __name__ == "__main__":
    main()