# API Documentation - Booking & Tour APIs

## Authentication Token

**Token Value:** `f4a04670dc9304e7629d6d0095da1c3a`

**Token Source:** MD5 hash of `proppik_api_secret_2026`

**Usage:** Send token either as:
- Header: `X-API-Token: f4a04670dc9304e7629d6d0095da1c3a`
- Query Parameter: `?token=f4a04670dc9304e7629d6d0095da1c3a`

---

## API Endpoints

### 1. Get All Bookings List

**Endpoint:** `GET /api/bookings/list`

**Description:** Returns all bookings with basic information. tour_code is compulsory (only returns bookings where tour_code is not null).

**cURL Example:**
```bash
curl -X GET "https://dev.proppik.in/api/bookings/list" \
  -H "X-API-Token: f4a04670dc9304e7629d6d0095da1c3a" \
  -H "Accept: application/json"
```

**Alternative (using query parameter):**
```bash
curl -X GET "https://dev.proppik.in/api/bookings/list?token=f4a04670dc9304e7629d6d0095da1c3a" \
  -H "Accept: application/json"
```

**Response Example:**
```json
{
  "success": true,
  "total": 10,
  "bookings": [
    {
      "booking_id": 1,
      "tour_code": "ABC123",
      "status": "confirmed",
      "payment_status": "paid",
      "booking_date": "2024-01-15",
      "user": {
        "id": 1,
        "firstname": "John",
        "lastname": "Doe",
        "email": "john@example.com",
        "mobile": "9876543210"
      },
      "created_at": "2024-01-15 10:30:00",
      "updated_at": "2024-01-15 10:30:00"
    }
  ]
}
```

---

### 2. Get Booking by Tour Code

**Endpoint:** `GET /api/booking/tour-code/{tour_code}`

**Description:** Returns detailed booking information based on tour_code with additional information including property details, tour information, and QR code.

**cURL Example:**
```bash
curl -X GET "https://dev.proppik.in/api/booking/tour-code/ABC123" \
  -H "X-API-Token: f4a04670dc9304e7629d6d0095da1c3a" \
  -H "Accept: application/json"
```

**Alternative (using query parameter):**
```bash
curl -X GET "https://dev.proppik.in/api/booking/tour-code/ABC123?token=f4a04670dc9304e7629d6d0095da1c3a" \
  -H "Accept: application/json"
```

**Response Example:**
```json
{
  "success": true,
  "booking": {
    "booking_id": 1,
    "tour_code": "ABC123",
    "status": "confirmed",
    "payment_status": "paid",
    "base_url": "https://example.com",
    "tour_final_link": "https://example.com/tour",
    "booking_date": "2024-01-15",
    "booking_time": "10:00",
    "booking_notes": "Some notes",
    "created_at": "2024-01-15 10:30:00",
    "updated_at": "2024-01-15 10:30:00",
    "user": {
      "id": 1,
      "firstname": "John",
      "lastname": "Doe",
      "email": "john@example.com",
      "mobile": "9876543210"
    },
    "property": {
      "property_type": "Apartment",
      "property_sub_type": "2BHK",
      "bhk": "2 BHK",
      "area": "1200",
      "price": "5000000",
      "furniture_type": "Furnished",
      "address": {
        "house_no": "101",
        "building": "ABC Tower",
        "society_name": "XYZ Society",
        "address_area": "Downtown",
        "landmark": "Near Park",
        "full_address": "101 ABC Tower, XYZ Society, Downtown",
        "pin_code": "400001",
        "city": "Mumbai",
        "state": "Maharashtra"
      }
    },
    "tour": {
      "tour_id": 1,
      "tour_name": "Property Tour",
      "tour_title": "Beautiful 2BHK Apartment",
      "tour_slug": "beautiful-2bhk-apartment",
      "location": "mumbai",
      "status": "published",
      "is_active": true,
      "is_credentials": false,
      "is_mobile_validation": true,
      "is_hosted": false,
      "hosted_link": null,
      "tour_live_url": "https://example.com/tour/beautiful-2bhk-apartment",
      "created_at": "2024-01-15 10:30:00",
      "updated_at": "2024-01-15 10:30:00"
    },
    "qr_code": "QR123456"
  }
}
```

---

### 3. Get Mobile History

**Endpoint:** `GET /api/tour/mobile/history/{tour_code}`

**Description:** Returns mobile validation history for a specific tour, grouped by mobile number with counts of sent, verified, and failed actions.

**cURL Example:**
```bash
curl -X GET "https://dev.proppik.in/api/tour/mobile/history/ABC123" \
  -H "X-API-Token: f4a04670dc9304e7629d6d0095da1c3a" \
  -H "Accept: application/json"
```

**Alternative (using query parameter):**
```bash
curl -X GET "https://dev.proppik.in/api/tour/mobile/history/ABC123?token=f4a04670dc9304e7629d6d0095da1c3a" \
  -H "Accept: application/json"
```

**Response Example:**
```json
{
  "success": true,
  "tour_id": 1,
  "tour_code": "ABC123",
  "history": [
    {
      "mobile": "9876543210",
      "sent_count": 2,
      "verified_count": 1,
      "failed_count": 1,
      "last_action_at": "2024-01-15 14:30:00"
    },
    {
      "mobile": "9876543211",
      "sent_count": 1,
      "verified_count": 1,
      "failed_count": 0,
      "last_action_at": "2024-01-15 12:00:00"
    }
  ]
}
```

---

## Error Responses

### Unauthorized (403)
```json
{
  "success": false,
  "message": "Unauthorized access. Invalid token."
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Tour code not found"
}
```

---

## Production URLs

Replace `https://dev.proppik.in` with your production domain:
- `https://yourdomain.com/api/bookings/list`
- `https://yourdomain.com/api/booking/tour-code/{tour_code}`
- `https://yourdomain.com/api/tour/mobile/history/{tour_code}`

---

## PHP cURL Examples (for CRM Integration)

### 1. Get All Bookings List (PHP)
```php
<?php
$token = 'f4a04670dc9304e7629d6d0095da1c3a';
$url = 'https://dev.proppik.in/api/bookings/list';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Token: ' . $token,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
?>
```

### 2. Get Booking by Tour Code (PHP)
```php
<?php
$token = 'f4a04670dc9304e7629d6d0095da1c3a';
$tourCode = 'ABC123';
$url = "https://dev.proppik.in/api/booking/tour-code/{$tourCode}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Token: ' . $token,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
?>
```

### 3. Get Mobile History (PHP)
```php
<?php
$token = 'f4a04670dc9304e7629d6d0095da1c3a';
$tourCode = 'ABC123';
$url = "https://dev.proppik.in/api/tour/mobile/history/{$tourCode}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Token: ' . $token,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
?>
```

---

## Notes

- All APIs require authentication via token
- Token can be sent as header (`X-API-Token`) or query parameter (`token`)
- All responses are in JSON format
- Replace `localhost/brokerx` with your actual domain in production
- Ensure your CRM has network access to the API endpoints

