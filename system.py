import subprocess
import sys
import platform
import os
import psutil
import mysql.connector
import time
from datetime import datetime
import socket

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

def connect_to_database():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="itsa"
    )

def get_battery_info():
    battery = psutil.sensors_battery()
    return {
        'percent': battery.percent,
        'plugged_in': battery.power_plugged
    }

def get_running_processes():
    processes = [p.info for p in psutil.process_iter(attrs=['pid', 'name'])]
    return len(processes)

def get_system_info():
    return {
        'uptime': time.time() - psutil.boot_time()
    }

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
    ram_type = 'N/A'  # Placeholder, actual type fetching is more complex
    ram_speed = 'N/A'  # Placeholder

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
    os_update_status = 'N/A'  # Placeholder

    # BIOS version
    bios_version = 'N/A'  # Placeholder, requires specific methods to fetch

    # Performance Data
    cpu_usage = psutil.cpu_percent(interval=1)
    memory_usage = virtual_mem.percent
    disk_usage = psutil.disk_usage('/').percent
    network_usage = psutil.net_io_counters().bytes_sent + psutil.net_io_counters().bytes_recv

    # Temperature and Fan Speeds
    temperature = 'N/A'  # Placeholder, requires specific methods to fetch
    fan_speed = 'N/A'  # Placeholder

    # Voltage Readings and Health Status
    voltage_readings = 'N/A'  # Placeholder
    health_status = 'N/A'  # Placeholder

    network_info = get_network_info()

    return {
        'manufacturer': manufacturer,
        'model': model,
        'cpu_type': cpu_type,
        'cpu_speed': cpu_speed,
        'cpu_cores': cpu_cores,
        'ram_amount': ram_amount,
        'ram_type': ram_type,
        'ram_speed': ram_speed,
        'storage_details': storage_details,
        'os_version': os_version,
        'os_build_number': os_build_number,
        'os_update_status': os_update_status,
        'bios_version': bios_version,
        'application_count': get_running_processes(),
        'ip_address': network_info['ip_address'],
        'subnet_mask': network_info['subnet_mask'],
        'default_gateway': network_info['default_gateway'],
        'dns_servers': network_info['dns_servers'],
        'connection_status': network_info['connection_status'],
        'connection_speed': network_info['connection_speed'],
        'cpu_usage': cpu_usage,
        'memory_usage': memory_usage,
        'disk_usage': disk_usage,
        'network_usage': network_usage,
        'temperature': temperature,
        'fan_speed': fan_speed,
        'voltage_readings': voltage_readings,
        'health_status': health_status
    }

def insert_data(db_connection, id, data):
    cursor = db_connection.cursor()
    query = """
    INSERT INTO system_monitor (id, timestamp, battery_percent, battery_plugged_in, running_processes, 
    uptime, manufacturer, model, os_version, os_build_number, os_update_status, cpu_type, cpu_speed, 
    cpu_cores, ram_amount, ram_type, ram_speed, storage_type, capacity, available_space, bios_version, 
    application_count, ip_address, subnet_mask, default_gateway, dns_servers, connection_status, 
    connection_speed, cpu_usage, memory_usage, disk_usage, network_usage, temperature, fan_speed, 
    voltage_readings, health_status) 
    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    storage = data['additional']['storage_details'][0] if data['additional']['storage_details'] else {'type': 'N/A', 'capacity': 'N/A', 'available_space': 'N/A'}
    values = (
        id, data['timestamp'], data['battery']['percent'], data['battery']['plugged_in'], data['processes'], 
        data['system']['uptime'], data['additional']['manufacturer'], data['additional']['model'], 
        data['additional']['os_version'], data['additional']['os_build_number'], data['additional']['os_update_status'], 
        data['additional']['cpu_type'], data['additional']['cpu_speed'], data['additional']['cpu_cores'], 
        data['additional']['ram_amount'], data['additional']['ram_type'], data['additional']['ram_speed'], 
        storage['type'], storage['capacity'], storage['available_space'], data['additional']['bios_version'], 
        data['additional']['application_count'], data['additional']['ip_address'], data['additional']['subnet_mask'], 
        data['additional']['default_gateway'], data['additional']['dns_servers'], data['additional']['connection_status'], 
        data['additional']['connection_speed'], data['additional']['cpu_usage'], data['additional']['memory_usage'], 
        data['additional']['disk_usage'], data['additional']['network_usage'], data['additional']['temperature'], 
        data['additional']['fan_speed'], data['additional']['voltage_readings'], data['additional']['health_status']
    )
    cursor.execute(query, values)
    db_connection.commit()
    cursor.close()

def update_data(db_connection, id, data):
    cursor = db_connection.cursor()
    query = """
    UPDATE system_monitor 
    SET timestamp=%s, battery_percent=%s, battery_plugged_in=%s, running_processes=%s, uptime=%s, 
    manufacturer=%s, model=%s, os_version=%s, os_build_number=%s, os_update_status=%s, cpu_type=%s, 
    cpu_speed=%s, cpu_cores=%s, ram_amount=%s, ram_type=%s, ram_speed=%s, storage_type=%s, 
    capacity=%s, available_space=%s, bios_version=%s, application_count=%s, ip_address=%s, subnet_mask=%s, 
    default_gateway=%s, dns_servers=%s, connection_status=%s, connection_speed=%s, cpu_usage=%s, memory_usage=%s, 
    disk_usage=%s, network_usage=%s, temperature=%s, fan_speed=%s, voltage_readings=%s, health_status=%s 
    WHERE id=%s
    """
    storage = data['additional']['storage_details'][0] if data['additional']['storage_details'] else {'type': 'N/A', 'capacity': 'N/A', 'available_space': 'N/A'}
    values = (
        data['timestamp'], data['battery']['percent'], data['battery']['plugged_in'], data['processes'], 
        data['system']['uptime'], data['additional']['manufacturer'], data['additional']['model'], 
        data['additional']['os_version'], data['additional']['os_build_number'], data['additional']['os_update_status'], 
        data['additional']['cpu_type'], data['additional']['cpu_speed'], data['additional']['cpu_cores'], 
        data['additional']['ram_amount'], data['additional']['ram_type'], data['additional']['ram_speed'], 
        storage['type'], storage['capacity'], storage['available_space'], data['additional']['bios_version'], 
        data['additional']['application_count'], data['additional']['ip_address'], data['additional']['subnet_mask'], 
        data['additional']['default_gateway'], data['additional']['dns_servers'], data['additional']['connection_status'], 
        data['additional']['connection_speed'], data['additional']['cpu_usage'], data['additional']['memory_usage'], 
        data['additional']['disk_usage'], data['additional']['network_usage'], data['additional']['temperature'], 
        data['additional']['fan_speed'], data['additional']['voltage_readings'], data['additional']['health_status'], id
    )
    cursor.execute(query, values)
    db_connection.commit()
    cursor.close()

def record_exists(db_connection, id):
    cursor = db_connection.cursor()
    query = "SELECT 1 FROM system_monitor WHERE id=%s"
    cursor.execute(query, (id,))
    result = cursor.fetchone()
    cursor.close()
    return result is not None

def main():
    id = input("Enter the ID: ")
    while True:
        try:
            db_connection = connect_to_database()
            data = {
                'timestamp': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
                'battery': get_battery_info(),
                'processes': get_running_processes(),
                'system': get_system_info(),
                'additional': get_additional_info()
            }
            if record_exists(db_connection, id):
                update_data(db_connection, id, data)
            else:
                insert_data(db_connection, id, data)
            db_connection.close()
        except mysql.connector.Error as err:
            print(f"Error: {err}")
        time.sleep(60)

if __name__ == "__main__":
    main()
