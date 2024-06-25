import requests
import json

url = 'https://cf71-2c0f-2a80-5f-c610-dd0e-7ea2-8fe0-f0e3.ngrok-free.app/itsa/receive_data.php'
data = {
    "key": "Test"

}

response = requests.post(url, json=data)

if response.status_code == 200:
    print('Data sent successfully')
else:
    print('Failed to send data')
