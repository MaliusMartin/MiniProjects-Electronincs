

---

# DS18B20 Temperature Monitoring System

This project demonstrates how to create a web-based interface for monitoring temperature using the DS18B20 sensor, ESP32, and MySQL database. The ESP32 collects temperature data from the DS18B20 sensor and sends it to a MySQL database via WiFi. The data is then fetched from the database and displayed on a web-based interface.

## Table of Contents

- [Features](#features)
- [Hardware Requirements](#hardware-requirements)
- [Software Requirements](#software-requirements)
- [Project Setup](#project-setup)
  - [Arduino Code](#arduino-code)
  - [Backend Scripts](#backend-scripts)
  - [Frontend Interface](#frontend-interface)
- [Usage](#usage)
- [Video Tutorial](#video-tutorial)
- [License](#license)
- [Acknowledgements](#acknowledgements)

## Features

- Real-time temperature monitoring
- Data storage in MySQL database
- Web-based interface to display temperature data
- ESP32 communicates with the database over WiFi

## Hardware Requirements

- DS18B20 Temperature Sensor
- ESP32 Development Board
- Jumper wires
- Breadboard
- USB to Serial Converter (optional, for programming ESP32)
- Power supply

## Software Requirements

- Arduino IDE
- MySQL Database
- PHP Server (e.g., XAMPP, WAMP)
- Web Browser

## Project Setup

### Arduino Code

1. **DS18B20.ino**: This script is uploaded to the ESP32. It reads the temperature from the DS18B20 sensor and sends the data to a MySQL database over WiFi.

```cpp
// DS18B20.ino
#include <OneWire.h>
#include <DallasTemperature.h>
#include <WiFi.h>
#include <HTTPClient.h>

#define ONE_WIRE_BUS 32


const char* ssid = "A043370";
const char* password = "madeofblacq";
const char* host = "192.168.219.212"; 


OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);

void setup(void)
{
  Serial.begin(9600);

  sensors.begin();
  connectWiFi();
}

// function to print the temperature for a device
void printTemperature()
{
  float tempC = sensors.getTempCByIndex(0);
  float tempF = sensors.getTempFByIndex(0);

  if(tempC == DEVICE_DISCONNECTED_C) 
  {
    Serial.println("Error: Could not read temperature data");
    return;
  }

  Serial.print("Temp C: ");
  Serial.print(tempC);
  Serial.print(" Temp F: ");
  Serial.println(tempF); // Makes a second call to getTempC and then converts to Fahrenheit

  sendDataToServer(tempC);
  
}
/*
 * Main function. It will request the tempC from the sensors and display on Serial.
 */
void loop(void)
{ 
  Serial.println("Requesting temperatures...");
  sensors.requestTemperatures(); // Send the command to get temperatures
  Serial.println("DONE");
  
  printTemperature(); // Use a simple function to print out the data
  delay(1000);
}

void connectWiFi() {
  Serial.println();
  Serial.print("[WiFi] Connecting to ");
  Serial.println(ssid);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected.");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
}

void sendDataToServer(float celsius) {
  if (WiFi.status() == WL_CONNECTED) { // Check WiFi connection status
    HTTPClient http;
    String serverPath = "http://" + String(host) + "/DS18B20/Temp.php?celsius=" + String(celsius);
        //http://192.168.219.212/DS18B20/Temp.php?celsius=
    http.begin(serverPath.c_str());

    int httpResponseCode = http.GET();
    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println(httpResponseCode);
      Serial.println(response);
    } else {
      Serial.print("Error on sending GET Request: ");
      Serial.println(httpResponseCode);
    }
    http.end();
  } else {
    Serial.println("WiFi Disconnected");
  }
}

```

### Backend Scripts

1. **fetch_temperature.php**: This script receives the temperature data from the ESP32 and stores it in the MySQL database.

```php
// fetch_temperature.php
<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "bs18d20");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to get the latest temperature value
$query = "SELECT Temp FROM temperature ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$latestTemp = 0;

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $latestTemp = $row['Temp'];
}

mysqli_close($conn);

echo $latestTemp;
?>

```

2. **Temp.php**: This script retrieves the temperature data from the database.

```php
// Temp.php
<?php
$dbname = 'bs18d20';
$dbuser = 'root';
$dbpass = '';
$dbhost = '127.0.0.1';

$connect = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$connect) {
    echo "Error: " . mysqli_connect_error();
    exit();
}

echo "Connection Success!<br><br>";



$celsius = isset($_GET["celsius"]) ? $_GET["celsius"] : "";

$query = "INSERT INTO temperature(Temp) VALUES ('$celsius')";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "Insertion Success!<br>";
} else {
    echo "Error: " . mysqli_error($connect) . "<br>";
}

mysqli_close($connect);

?>
```

### Frontend Interface

1. **userinterface.php**: This script displays the temperature data in a web-based interface.

```php
// userinterface.php
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Temperature </title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>


  <br><br><br><br>

  <div class="container-fluid">
    <div class="circle-container">
      <div class="circle" id="circle1">
        <div class="circle-text">TEMP (°C)</div>
        <span class="circle-number" id="temp-value"></span>
      </div>
      
    </div>
  </div>

  <br><br>

  <div class="container mt-3">
    <h2>Attention</h2>
    <p>The following are guides on how to interpret results</p>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Color</th>
          <th>Interpretation</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="bg-success">Green</td>
          <td>Desired range</td>
        </tr>
        <tr>
          <td class="bg-warning">Yellow</td>
          <td>Tolerable</td>
        </tr>
        <tr>
          <td class="bg-danger">Red</td>
          <td>Not tolerable</td>
        </tr>
      </tbody>
    </table>
  </div>

  <br><br><br><br><br><br>


 

  <script>
    
    const parameterNames = {
      'circle1': 'Temp',
    };

    function setBackgroundColor(element, value, ranges) {
      let color;
      let parameterName = parameterNames[element.id];

      if (value >= ranges.best.min && value <= ranges.best.max) {
        color = 'green';
      } else if ((value >= ranges.tolerable.low.min && value <= ranges.tolerable.low.max) ||
                 (value >= ranges.tolerable.high.min && value <= ranges.tolerable.high.max)) {
        color = 'yellow';
       
      } else {
        color = 'red';
        
      }
      element.style.backgroundColor = color;
      
    }

   

    function updateCircleColors() {
      const ranges = {
        temp: {
          best: { min: 23, max: 29 },
          tolerable: { low: { min: 20, max: 22.9 }, high: { min: 29.1, max: 33 } },
          bad: { min: -Infinity, max: 19, high: { min: 33.1, max: Infinity } }
        }
      };

      setBackgroundColor(document.getElementById('circle1'), parseFloat(document.getElementById('temp-value').textContent), ranges.temp);
      
    }

    function fetchData(url, elementId) {
      fetch(url)
        .then(response => response.text())
        .then(data => {
          document.getElementById(elementId).textContent = data;
          updateCircleColors();
        })
        .catch(error => console.error(`Error fetching ${elementId}:`, error));
    }

    document.addEventListener('DOMContentLoaded', () => {
      updateCircleColors();
      setInterval(() => fetchData('fetch_temperature.php', 'temp-value'), 3000);
      
    });
  </script>
</body>
</html>

```

2. **style.css**: This script provides styling for the web interface.

```css
/* style.css */
body {
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  font-family: Arial, sans-serif;
  background-color: #f4f4f4;
}

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 20px;
  background-color: #ddd;
}

.navbar a {
  color: #333;
  text-decoration: none;
  font-weight: bold;
  margin-right: 20px;
}

.circle-container {
  display: flex;
  flex-wrap: wrap;
  flex-grow: 1;
  width: 50%;
  justify-content: space-between;
  align-items: center;
  margin: 0 auto;
}

.circle {
  width: 200px;
  height: 200px;
  border-radius: 50%;
  margin: 10px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.circle-number, .circle-text {
  color: rgb(0, 0, 0);
  font-weight: bold;
}

.circle-number {
  font-size: 2em;
}

.circle-text {
  font-size: 1em;
}

@media (max-width: 768px) {
  .circle-container {
    width: 80%;
  }

  .circle {
    width: 80px;
    height: 80px;
  }

  .circle-number {
    font-size: 1.5em;
  }

  .circle-text {
    font-size: 0.65em;
  }
}

.bg-info {
  background-color: #17a2b8 !important;
}

.bg-success {
  background-color: green !important;
}

.bg-warning {
  background-color: yellow !important;
}

.bg-danger {
  background-color: red !important;
}

.container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  padding: 20px;
}

.table-container {
  flex: 1 1 21%;
  margin: 10px;
  background-color: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

h2 {
  background-color: #4CAF50;
  color: white;
  padding: 10px;
  margin: 0;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f2f2f2;
}

@media screen and (max-width: 1024px) {
  .table-container {
    flex: 1 1 46%;
  }
}

@media screen and (max-width: 768px) {
  .table-container {
    flex: 1 1 100%;
  }
}

```

## Usage

1. Upload the `DS18B20.ino` code to your ESP32.
2. Set up your PHP server and MySQL database.
3. Configure the backend scripts (`fetch_temperature.php`, `Temp.php`) with your database credentials.
4. Place the frontend scripts (`userinterface.php`, `style.css`) in the appropriate directory on your server.
5. Access the web interface through your browser to monitor the temperature data.

## Video Tutorial

Check out the [video tutorial](link_to_your_video) for a detailed walkthrough of the project.

## License

This project is licensed under the MIT License.

## Acknowledgements

- [ESP32](https://www.espressif.com/en/products/socs/esp32)
- [DS18B20 Temperature Sensor](https://datasheets.maximintegrated.com/en/ds/DS18B20.pdf)
- [Arduino](https://www.arduino.cc/)
- [Xampp](https://www.apachefriends.org/)
- [MySQL](https://www.mysql.com/)
- [PHP](https://www.php.net/)

---

