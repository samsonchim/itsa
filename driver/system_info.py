import psutil
import platform
import requests
import re
from datetime import datetime, timedelta
import json
import wmi
import pythoncom

def bytes_to_gb(bytes):
    """Convert bytes to gigabytes."""
    return round(bytes / (1024 ** 3), 2)

def seconds_to_time(seconds):
    """Convert seconds to a human-readable time format."""
    return str(timedelta(seconds=seconds))

def get_os_info():
    pythoncom.CoInitialize()  # Initialize COM library for the calling thread
    try:
        c = wmi.WMI()
        computer_system = c.Win32_ComputerSystem()[0]

        os_info = {
            'Operating System': platform.system(),
            'OS Version': platform.version(),
            'Architecture': platform.architecture()[0],
            'Node Name': platform.node(),
            'Machine': platform.machine(),
            'Processor': platform.processor(),
            'System Manufacturer': computer_system.Manufacturer,  # Add system manufacturer
            'System Model': computer_system.Model  # Add system model
        }
    finally:
        pythoncom.CoUninitialize()  # Uninitialize COM library
    return os_info

def get_cpu_info():
    cpu_info = {
        'Total Logical CPUs': psutil.cpu_count(logical=True),
        'Current CPU Frequency (MHz)': round(psutil.cpu_freq().current, 2),
        'CPU Usage per Core (%)': psutil.cpu_percent(interval=1, percpu=True),
        'CPU Stats': psutil.cpu_stats()._asdict(),
        'CPU Times': psutil.cpu_times()._asdict()
    }
    return cpu_info

def get_memory_info():
    memory = psutil.virtual_memory()
    memory_info = {
        'Total Memory (GB)': bytes_to_gb(memory.total),
        'Available Memory (GB)': bytes_to_gb(memory.available),
        'Used Memory (GB)': bytes_to_gb(memory.used),
        'Free Memory (GB)': bytes_to_gb(memory.free),
        'Memory Usage (%)': memory.percent
    }
    return memory_info

def get_storage_data():
    io_counters = psutil.disk_io_counters()
    storage_data = {
        'Total Bytes Read (GB)': bytes_to_gb(io_counters.read_bytes),
        'Total Bytes Written (GB)': bytes_to_gb(io_counters.write_bytes),
        'Total Read Time (seconds)': seconds_to_time(io_counters.read_time // 1000),
        'Total Write Time (seconds)': seconds_to_time(io_counters.write_time // 1000)
    }
    return storage_data

def get_process_info():
    system_processes = []
    application_processes = []
    for proc in psutil.process_iter(['pid', 'name', 'username', 'cpu_percent', 'memory_percent', 'exe']):
        process_info = {
            'PID': proc.pid,
            'Name': proc.info['name'],
            'Username': proc.info['username'],
            'CPU Percent (%)': proc.info['cpu_percent'],
            'Memory Percent (%)': proc.info['memory_percent']
        }
        if proc.info['exe'] and 'System32' in proc.info['exe']:
            system_processes.append(process_info)
        else:
            application_processes.append(process_info)

    process_info = {
        'Total Processes': len(system_processes) + len(application_processes),
        'System Processes': len(system_processes),
        'Application Processes': len(application_processes),
        'System Process Info': system_processes,
        'Application Process Info': application_processes
    }
    return process_info

def get_uptime():
    boot_time = datetime.fromtimestamp(psutil.boot_time())
    uptime = datetime.now() - boot_time
    return str(uptime)

def get_battery_info():
    if hasattr(psutil, 'sensors_battery'):
        battery = psutil.sensors_battery()
        if battery:
            return {
                'Battery Percent (%)': battery.percent,
                'Power Plugged': battery.power_plugged,
                'Battery Time Left': seconds_to_time(battery.secsleft if battery.secsleft != psutil.POWER_TIME_UNLIMITED else 0)
            }
    return 'Battery information not available.'

def get_public_ip():
    try:
        response = requests.get('https://api.ipify.org?format=json')
        ip_info = response.json()
        return ip_info.get('ip', 'IP not found')
    except requests.RequestException:
        return 'IP not found'

def is_valid_email(email):
    """Validate email address using a regular expression."""
    email_pattern = r'^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$'
    return re.match(email_pattern, email) is not None

def check_health(system_info):
    health_status = {
        'CPU Health': [],
        'Memory Health': [],
        # 'Disk Health': [],
        'Battery Health': [],
        'Uptime Health': [],
        'Process Health': []
    }
    
    # CPU usage check
    avg_cpu_usage = sum(system_info['CPU Info']['CPU Usage per Core (%)']) / len(system_info['CPU Info']['CPU Usage per Core (%)'])
    if avg_cpu_usage > 80:
        health_status['CPU Health'].append('High average CPU usage detected. Health: NOT OK')
    else:
        health_status['CPU Health'].append('CPU usage is within normal range. Health: OK')

    if max(system_info['CPU Info']['CPU Usage per Core (%)']) > 90:
        health_status['CPU Health'].append('One or more cores have very high CPU usage. Health: NOT OK')

    # Memory usage check
    if system_info['Memory Info']['Memory Usage (%)'] > 80:
        health_status['Memory Health'].append('High memory usage detected. Health: NOT OK')
    else:
        health_status['Memory Health'].append('Memory usage is within normal range. Health: OK')

    # Disk usage check
    # for disk in system_info['Disk Info']:
    #     if disk['Percent Used (%)'] > 90:
    #         health_status['Disk Health'].append(f"Low disk space on {disk['Mount Point']}. Health: NOT OK")
    #     else:
    #         health_status['Disk Health'].append(f"Disk space on {disk['Mount Point']} is within normal range. Health: OK")

    # Battery health check
    if isinstance(system_info['Battery Info'], dict):
        if system_info['Battery Info']['Battery Percent (%)'] < 20 and not system_info['Battery Info']['Power Plugged']:
            health_status['Battery Health'].append('Low battery and not charging. Health: NOT OK')
        else:
            health_status['Battery Health'].append('Battery level is sufficient or charging. Health: OK')

    # Uptime health check
    uptime_seconds = (datetime.now() - datetime.fromtimestamp(psutil.boot_time())).total_seconds()
    if uptime_seconds > 7 * 24 * 60 * 60:  # more than 7 days
        health_status['Uptime Health'].append('System has been running for more than 7 days. Consider a reboot. Health: NOT OK')
    else:
        health_status['Uptime Health'].append('System uptime is within normal range. Health: OK')

    # Process health check
    for proc in system_info['Process Info']['System Process Info'] + system_info['Process Info']['Application Process Info']:
        if proc['CPU Percent (%)'] > 80:
            health_status['Process Health'].append(f"Process {proc['Name']} (PID: {proc['PID']}) is using high CPU. Health: NOT OK")
        if proc['Memory Percent (%)'] > 80:
            health_status['Process Health'].append(f"Process {proc['Name']} (PID: {proc['PID']}) is using high memory. Health: NOT OK")
    
    return health_status

def gather_system_info(email):
    system_info = {
        'Email': email,
        'OS Info': get_os_info(),
        'CPU Info': get_cpu_info(),
        'Memory Info': get_memory_info(),
        # 'Disk Info': get_disk_info(),
        'Storage Data': get_storage_data(),
        'Process Info': get_process_info(),
        'Uptime': get_uptime(),
        'Battery Info': get_battery_info(),
        'Public IP': get_public_ip()
    }

    health_status = check_health(system_info)
    system_info['Health Status'] = health_status

    return system_info
