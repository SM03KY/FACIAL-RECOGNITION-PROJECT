#include <Arduino.h>
#include "esp_camera.h"
#include <WiFi.h>
#include <HTTPClient.h> 

// Select your camera model in board_config.h
#include "board_config.h"

//connection need to connect

//const char* ssid = "SMOEKY";
//const char* password = "123456789";
const char* ssid = "HUAWEI-9bpe";
const char* password = "X239Rq5j";

String serverUrl = "http://192.168.18.42/techpass/php/upload_photo.php"; // change when wifi ip change check ipconfig 

// Near the top of testcam.ino
void startCameraServer();
void setupLedFlash(); // Add this line if it is missing

void setup() {
  Serial.begin(115200);
  Serial.setDebugOutput(true);
  Serial.println();

  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = 5;
  config.pin_d1 = 18;
  config.pin_d2 = 19;
  config.pin_d3 = 21;
  config.pin_d4 = 36;
  config.pin_d5 = 39;
  config.pin_d6 = 34;
  config.pin_d7 = 35;
  config.pin_xclk = 0;
  config.pin_pclk = 22;
  config.pin_vsync = 25;
  config.pin_href = 23;
  config.pin_sscb_sda = 26;
  config.pin_sscb_scl = 27;
  config.pin_pwdn = 32;
  config.pin_reset = -1; // Some models use 15, but -1 is safer for AI Thinker
  config.xclk_freq_hz = 20000000;
  config.pixel_format = PIXFORMAT_JPEG;

  // ==========================================
  // LATENCY FIX: Lower Resolution & Higher Compression
  // ==========================================
  if(psramFound()){
    config.frame_size = FRAMESIZE_VGA; // Dropped from UXGA to VGA for speed
    config.jpeg_quality = 15;          // Increased from 10 to 15 for faster Wi-Fi transfer
    config.fb_count = 2;
  } else {
    config.frame_size = FRAMESIZE_QVGA; // Dropped from SVGA to QVGA
    config.jpeg_quality = 15;
    config.fb_count = 1;
  }

  // Camera init
  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed with error 0x%x", err);
    return;
  }

  // Connect to Wi-Fi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected");

  setupLedFlash();
  startCameraServer();

  Serial.print("Camera Ready! Use IP: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  delay(10000);
}