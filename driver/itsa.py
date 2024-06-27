import tkinter as tk
from tkinter import ttk
from tkinter import messagebox
import json
import threading
import requests
import time
import re
from ttkthemes import ThemedTk
from system_info import gather_system_info
from email_validator import is_valid_email

API_URL = "http://www.samsonchi.tech/receive_data.php"
UPDATE_INTERVAL = 60  # 300 seconds = 5 minutes

stop_event = threading.Event()

def sanitize_email(email):
    """Sanitize the email address to create a valid filename"""
    return re.sub(r'[^a-zA-Z0-9]', '_', email)

def save_data_to_file(system_info, email):
    """Save system information to a JSON file with the email as the key."""
    sanitized_email = sanitize_email(email)
    filename = f"{sanitized_email}.json"
    data_to_save = {
        email: system_info
    }
    with open(filename, 'w') as file:
        json.dump(data_to_save, file, indent=4)

def submit_email():
    email = email_entry.get()
    if not is_valid_email(email):
        messagebox.showerror("Invalid Email", "Please enter a valid email address.")
    else:
        try:
            system_info = gather_system_info(email)
            messagebox.showinfo("Success", "Your system information and health is being gathered and sent to your dashboard every 1 minute")
            print(json.dumps(system_info, indent=4))
            send_data_to_api(system_info, email)
            loading_label.config(text="Collecting data...")
            start_background_task()
        except Exception as e:
            messagebox.showerror("Error", f"An error occurred: {e}")
            print(f"An error occurred: {e}")

def send_data_to_api(system_info, email):
    """Send system information to the API and save it to a file."""
    data_to_send = {
        email: system_info
    }
    try:
        response = requests.post(API_URL, json=data_to_send)
        if response.status_code == 200:
            print("Data sent successfully to API")
        else:
            print(f"Failed to send data to API. Status code: {response.status_code}")
    except requests.RequestException as e:
        print(f"Error sending data to API: {e}")
    finally:
        save_data_to_file(system_info, email)

def collect_and_send_data():
    """Collect and send data in a loop."""
    while not stop_event.is_set():
        email = email_entry.get()
        if is_valid_email(email):
            try:
                system_info = gather_system_info(email)
                send_data_to_api(system_info, email)
                loading_label.config(text="Data collection complete. Waiting for next cycle...")
            except Exception as e:
                print(f"An error occurred: {e}")
        else:
            print("Invalid email address. Cannot collect and send data.")
        
        time.sleep(UPDATE_INTERVAL)
        loading_label.config(text="Collecting data...")

def start_background_task():
    """Start the background data collection task."""
    stop_event.clear()
    background_thread = threading.Thread(target=collect_and_send_data, daemon=True)
    background_thread.start()

def stop_background_task():
    """Stop the background data collection task."""
    stop_event.set()
    loading_label.config(text="Data collection stopped.")

# Create the main application window with the Yaru theme
app = ThemedTk(theme="yaru")
app.title("System Information Collector")

# Label for the application title
title_label = ttk.Label(app, text="Track the health status of your computer", font=("Helvetica", 12))
title_label.pack(pady=(20, 0), padx=20)  # Top margin only

# Label for the email entry
email_label = ttk.Label(app, text="Enter your email address:")
email_label.pack(pady=(10, 0))  # Top margin only

# Entry widget for email input
email_entry = ttk.Entry(app, width=40)
email_entry.pack(pady=(0, 10))  # Bottom margin only

# Button to submit the email
submit_button = ttk.Button(app, text="Submit", command=submit_email)
submit_button.pack(pady=20)

# Loading label to show the status
loading_label = ttk.Label(app, text="", font=("Helvetica", 10))
loading_label.pack(pady=(10, 10))

# Button to stop the background task
stop_button = ttk.Button(app, text="Stop", command=stop_background_task)
stop_button.pack(pady=10)

# Start the main event loop
app.mainloop()
