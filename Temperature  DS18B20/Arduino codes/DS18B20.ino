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
