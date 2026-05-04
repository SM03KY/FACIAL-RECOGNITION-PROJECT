#include "Arduino.h"
#include "esp_http_server.h"
#include "esp_timer.h"
#include "esp_camera.h"
#include "img_converters.h"
#include "fb_gfx.h"
#include "esp32-hal-ledc.h"
#include "sdkconfig.h"
#include "camera_index.h"
#include "board_config.h"
#include <WiFi.h>
#include <HTTPClient.h>

extern String serverUrl; 

#if defined(ARDUINO_ARCH_ESP32) && defined(CONFIG_ARDUHAL_ESP_LOG)
#include "esp32-hal-log.h"
#endif

#if defined(LED_GPIO_NUM)
#define CONFIG_LED_MAX_INTENSITY 255
int led_duty = 0;
#endif

#define PART_BOUNDARY "123456789000000000000987654321"
static const char *_STREAM_CONTENT_TYPE = "multipart/x-mixed-replace;boundary=" PART_BOUNDARY;
static const char *_STREAM_BOUNDARY = "\r\n--" PART_BOUNDARY "\r\n";
static const char *_STREAM_PART = "Content-Type: image/jpeg\r\nContent-Length: %u\r\nX-Timestamp: %d.%06d\r\n\r\n";

httpd_handle_t camera_httpd = NULL;

#if defined(LED_GPIO_NUM)
void enable_led(bool en) {
  ledcWrite(LED_GPIO_NUM, en ? 255 : 0);
}
#endif

// CAPTURE HANDLER (Sends to XAMPP)
static esp_err_t capture_handler(httpd_req_t *req) {
  camera_fb_t *fb = NULL;
  esp_err_t res = ESP_OK;

#if defined(LED_GPIO_NUM)
  enable_led(true);
  vTaskDelay(150 / portTICK_PERIOD_MS);
  fb = esp_camera_fb_get();
  enable_led(false);
#else
  fb = esp_camera_fb_get();
#endif

  if (!fb) {
    httpd_resp_send_500(req);
    return ESP_FAIL;
  }

  if(WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverUrl); 
    http.addHeader("Content-Type", "image/jpeg");
    int httpResponseCode = http.POST(fb->buf, fb->len);
    Serial.printf("XAMPP Upload: %d\n", httpResponseCode);
    http.end();
  }

  httpd_resp_set_type(req, "image/jpeg");
  httpd_resp_set_hdr(req, "Content-Disposition", "inline; filename=capture.jpg");
  httpd_resp_set_hdr(req, "Access-Control-Allow-Origin", "*"); // FIXES BLACK SCREEN

  res = httpd_resp_send(req, (const char *)fb->buf, fb->len);
  esp_camera_fb_return(fb);
  return res;
}

// STREAM HANDLER (The Live Preview)
static esp_err_t stream_handler(httpd_req_t *req) {
  camera_fb_t *fb = NULL;
  esp_err_t res = ESP_OK;
  size_t _jpg_buf_len = 0;
  uint8_t *_jpg_buf = NULL;
  char *part_buf[128];

  // ADDED CORS HEADER TO FIX PREVIEW
  httpd_resp_set_hdr(req, "Access-Control-Allow-Origin", "*");
  res = httpd_resp_set_type(req, _STREAM_CONTENT_TYPE);
  if (res != ESP_OK) return res;

  while (true) {
    fb = esp_camera_fb_get();
    if (!fb) {
      res = ESP_FAIL;
    } else {
      _jpg_buf_len = fb->len;
      _jpg_buf = fb->buf;
    }
    
    if (res == ESP_OK) {
      res = httpd_resp_send_chunk(req, _STREAM_BOUNDARY, strlen(_STREAM_BOUNDARY));
    }
    if (res == ESP_OK) {
      size_t hlen = snprintf((char *)part_buf, 128, _STREAM_PART, _jpg_buf_len, 0, 0);
      res = httpd_resp_send_chunk(req, (const char *)part_buf, hlen);
    }
    if (res == ESP_OK) {
      res = httpd_resp_send_chunk(req, (const char *)_jpg_buf, _jpg_buf_len);
    }
    
    if (fb) {
      esp_camera_fb_return(fb);
      fb = NULL;
    }
    if (res != ESP_OK) break;
  }
  return res;
}

void startCameraServer() {
  httpd_config_t config = HTTPD_DEFAULT_CONFIG();
  config.server_port = 80;

  httpd_uri_t capture_uri = { .uri = "/capture", .method = HTTP_GET, .handler = capture_handler, .user_ctx = NULL };
  httpd_uri_t stream_uri = { .uri = "/stream", .method = HTTP_GET, .handler = stream_handler, .user_ctx = NULL };

  if (httpd_start(&camera_httpd, &config) == ESP_OK) {
    httpd_register_uri_handler(camera_httpd, &capture_uri);
    httpd_register_uri_handler(camera_httpd, &stream_uri);
  }
}

void setupLedFlash() {
#if defined(LED_GPIO_NUM)
  ledcAttach(LED_GPIO_NUM, 5000, 8);
#endif
}