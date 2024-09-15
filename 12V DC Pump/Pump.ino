
const int relay = 4;

void setup() {
  Serial.begin(115200);
  pinMode(relay, OUTPUT);
}

void loop() {
  digitalWrite(relay, HIGH);
  Serial.println("Pump is ON");
  delay(5000);
  digitalWrite(relay, LOW);
  Serial.println("Pump is OFF");
  delay(5000);
}