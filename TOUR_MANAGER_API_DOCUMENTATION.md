# Tour Manager & Tour Access API Documentation

## Base URL
```
https://dev.proppik.in/api
```

---

## Table of Contents
1. [Tour Manager Authentication](#tour-manager-authentication)
2. [Tour Manager APIs](#tour-manager-apis)
3. [Tour Access APIs](#tour-access-apis)

---

## Tour Manager Authentication

### Login Endpoint

**Endpoint:** `POST /api/tour-manager/login`

**Description:** Authenticate a tour manager and receive an access token.

**Authentication:** Not required

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "email": "demo@gmail.com",
    "password": "your_password"
}
```

**cURL Example:**
```bash
curl -X POST "https://dev.proppik.in/api/tour-manager/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "demo@gmail.com",
    "password": "your_password"
  }'
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 7,
        "name": "demo tour manager",
        "email": "demo@gmail.com",
        "role": "Tour Manager",
        "token": "LMRKnQ8sz4VmoKiAJgs6GHt0gVD2m5gxfQqHeFGna73ae560"
    }
}
```

**Error Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

**Note:** Save the `token` from the response. You'll need it for all protected Tour Manager API endpoints.

---

## Tour Manager APIs

All Tour Manager APIs require authentication using the token received from the login endpoint.

### Authentication Header
```
Authorization: Bearer {token}
```

**Example:**
```
Authorization: Bearer LMRKnQ8sz4VmoKiAJgs6GHt0gVD2m5gxfQqHeFGna73ae560
```

**Unauthenticated Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Unauthenticated.",
    "code": "UNAUTHENTICATED"
}
```

---

### 1. Get All Customers

**Endpoint:** `GET /api/tour-manager/customers`

**Description:** Retrieve all users with the 'customer' role.

**Authentication:** Required (Bearer Token)

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**cURL Example:**
```bash
curl -X GET "https://dev.proppik.in/api/tour-manager/customers" \
  -H "Authorization: Bearer LMRKnQ8sz4VmoKiAJgs6GHt0gVD2m5gxfQqHeFGna73ae560" \
  -H "Accept: application/json"
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "customers": [
        {
            "id": 1,
            "firstname": "John",
            "lastname": "Doe",
            "email": "john.doe@example.com",
            "mobile": "1234567890"
        },
        {
            "id": 2,
            "firstname": "Jane",
            "lastname": "Smith",
            "email": "jane.smith@example.com",
            "mobile": "0987654321"
        }
    ]
}
```

---

### 2. Get Tours by Customer

**Endpoint:** `GET /api/tour-manager/tours-by-customer`

**Description:** Retrieve all tours for a specific customer (identified by user_id).

**Authentication:** Required (Bearer Token)

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| user_id | integer | Yes | The ID of the customer/user |

**cURL Example:**
```bash
curl -X GET "https://dev.proppik.in/api/tour-manager/tours-by-customer?user_id=1" \
  -H "Authorization: Bearer LMRKnQ8sz4VmoKiAJgs6GHt0gVD2m5gxfQqHeFGna73ae560" \
  -H "Accept: application/json"
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "tours": [
        {
            "id": 1,
            "qr_code": "z2bhT1M5",
            "tour_code": "z2bhT1M5",
            "qr_link": "https://qr.proppik.com/z2bhT1M5",
            "s3_link": "https://creartimages.s3.ap-south-1.amazonaws.com/tours/z2bhT1M5/",
            "footer_brand_logo": "https://creartimages.s3.ap-south-1.amazonaws.com/logo.png",
            "sidebar_logo": "https://creartimages.s3.ap-south-1.amazonaws.com/sidebar.png",
            "top_image": "https://creartimages.s3.ap-south-1.amazonaws.com/top.png",
            "top_number": "+1234567890",
            "top_title": "Tour Title",
            "top_email": "tour@example.com",
            "top_sub_title": "Subtitle",
            "top_description": "Description",
            "is_hosted": false,
            "hosted_link": null,
            "api_link": "https://dev.proppik.in/api/",
            "custom_logo_sidebar_url": "https://creartimages.s3.ap-south-1.amazonaws.com/custom-sidebar.png",
            "custom_logo_footer_url": "https://creartimages.s3.ap-south-1.amazonaws.com/custom-footer.png"
        }
    ]
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "user_id": [
            "The user id field is required."
        ]
    }
}
```

---

### 3. Get Tour Details

**Endpoint:** `GET /api/tour-manager/tour/{tour_code}`

**Description:** Retrieve detailed information for a specific tour by tour_code.

**Authentication:** Required (Bearer Token)

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| tour_code | string | Yes | The tour code identifier |

**cURL Example:**
```bash
curl -X GET "https://dev.proppik.in/api/tour-manager/tour/z2bhT1M5" \
  -H "Authorization: Bearer LMRKnQ8sz4VmoKiAJgs6GHt0gVD2m5gxfQqHeFGna73ae560" \
  -H "Accept: application/json"
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "tour": {
        "id": 1,
        "qr_code": "z2bhT1M5",
        "qr_link": "https://qr.proppik.com/z2bhT1M5",
        "s3_link": "https://creartimages.s3.ap-south-1.amazonaws.com/tours/z2bhT1M5/",
        "footer_brand_logo": "https://creartimages.s3.ap-south-1.amazonaws.com/logo.png",
        "sidebar_logo": "https://creartimages.s3.ap-south-1.amazonaws.com/sidebar.png",
        "top_image": "https://creartimages.s3.ap-south-1.amazonaws.com/top.png",
        "top_number": "+1234567890",
        "top_title": "Tour Title",
        "top_email": "tour@example.com",
        "top_sub_title": "Subtitle",
        "top_description": "Description",
        "is_hosted": false,
        "hosted_link": null,
        "api_link": "https://dev.proppik.in/api/",
        "custom_logo_sidebar_url": "https://creartimages.s3.ap-south-1.amazonaws.com/custom-sidebar.png",
        "custom_logo_footer_url": "https://creartimages.s3.ap-south-1.amazonaws.com/custom-footer.png"
    }
}
```

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Tour code not found"
}
```

---

### 4. Update Working JSON

**Endpoint:** `PUT /api/tour-manager/working_json/{tour_code}`

**Description:** Update the working_json field for a specific tour.

**Authentication:** Required (Bearer Token)

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| tour_code | string | Yes | The tour code identifier |

**Request Body:**
```json
{
    "working_json": {
        "key1": "value1",
        "key2": "value2"
    },
    "working_json_last_update_user": 7
}
```

**cURL Example:**
```bash
curl -X PUT "https://dev.proppik.in/api/tour-manager/working_json/z2bhT1M5" \
  -H "Authorization: Bearer LMRKnQ8sz4VmoKiAJgs6GHt0gVD2m5gxfQqHeFGna73ae560" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "working_json": {
        "key1": "value1",
        "key2": "value2"
    },
    "working_json_last_update_user": 7
  }'
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Working JSON updated successfully",
    "tour": {
        "id": 1,
        "working_json": {
            "key1": "value1",
            "key2": "value2"
        },
        "working_json_last_update_user": 7
    }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "working_json": [
            "The working json field is required."
        ],
        "working_json_last_update_user": [
            "The working json last update user field is required."
        ]
    }
}
```

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Tour code not found"
}
```

---

## Tour Access APIs

All Tour Access APIs require a dynamic token based on the tour code.

### Authentication Method

**Token Generation Formula:**
```
Token String = "proppik" + tour_code + created_at_formatted
```

**Date Format:** `Ymd\THis000000\Z` (e.g., `20251229T125148000000Z`)

**Example:**
- Tour Code: `z2bhT1M5`
- Created At: `2025-12-29 12:51:48`
- Formatted Date: `20251229T125148000000Z`
- Token String: `proppikz2bhT1M520251229T125148000000Z`
- **MD5 Hash:** `470ec0ba5310221aaa057c5bc6484a27`

**Request Header:**
```
X-Tour-Token: {md5_hash}
```

**Example:**
```
X-Tour-Token: 470ec0ba5310221aaa057c5bc6484a27
```

---

### 1. Check Tour Active Status

**Endpoint:** `GET /api/tour/is_active/{tour_code}`

**Description:** Check if a tour is active based on tour_code.

**Authentication:** Required (X-Tour-Token header)

**Request Headers:**
```
X-Tour-Token: {md5_hash}
Accept: application/json
```

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| tour_code | string | Yes | The tour code identifier |

**cURL Example:**
```bash
curl -X GET "https://dev.proppik.in/api/tour/is_active/z2bhT1M5" \
  -H "X-Tour-Token: 470ec0ba5310221aaa057c5bc6484a27" \
  -H "Accept: application/json"
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "is_active": true
}
```

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Tour code not found",
    "is_active": false
}
```

**Error Response (403 Forbidden):**
```json
{
    "success": false,
    "message": "Unauthorized access. Invalid or missing X-Tour-Token."
}
```

---

### 2. Check Tour Credentials Required

**Endpoint:** `GET /api/tour/tour_credentials/{tour_code}`

**Description:** Check if credentials are required for a tour.

**Authentication:** Required (X-Tour-Token header)

**Request Headers:**
```
X-Tour-Token: {md5_hash}
Accept: application/json
```

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| tour_code | string | Yes | The tour code identifier |

**cURL Example:**
```bash
curl -X GET "https://dev.proppik.in/api/tour/tour_credentials/z2bhT1M5" \
  -H "X-Tour-Token: 470ec0ba5310221aaa057c5bc6484a27" \
  -H "Accept: application/json"
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "is_credentials": true
}
```

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Tour code not found",
    "is_credentials": false
}
```

---

### 3. Tour Login

**Endpoint:** `POST /api/tour/login`

**Description:** Authenticate with tour credentials.

**Authentication:** Required (X-Tour-Token header)

**Request Headers:**
```
X-Tour-Token: {md5_hash}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "tour_code": "z2bhT1M5",
    "username": "tour_username",
    "password": "tour_password"
}
```

**cURL Example:**
```bash
curl -X POST "https://dev.proppik.in/api/tour/login" \
  -H "X-Tour-Token: 470ec0ba5310221aaa057c5bc6484a27" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "tour_code": "z2bhT1M5",
    "username": "tour_username",
    "password": "tour_password"
  }'
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Login successful"
}
```

**Error Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

---

### 4. Check Mobile Validation Required

**Endpoint:** `GET /api/tour/is_mobile_validation/{tour_code}`

**Description:** Check if mobile validation is required for a tour.

**Authentication:** Required (X-Tour-Token header)

**Request Headers:**
```
X-Tour-Token: {md5_hash}
Accept: application/json
```

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| tour_code | string | Yes | The tour code identifier |

**cURL Example:**
```bash
curl -X GET "https://dev.proppik.in/api/tour/is_mobile_validation/z2bhT1M5" \
  -H "X-Tour-Token: 470ec0ba5310221aaa057c5bc6484a27" \
  -H "Accept: application/json"
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "is_mobile_validation": true
}
```

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Tour code not found",
    "is_mobile_validation": false
}
```

---

### 5. Send OTP

**Endpoint:** `POST /api/tour/mobile/send-otp`

**Description:** Send OTP to a mobile number for tour access.

**Authentication:** Required (X-Tour-Token header)

**Request Headers:**
```
X-Tour-Token: {md5_hash}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "tour_code": "z2bhT1M5",
    "mobile": "1234567890"
}
```

**cURL Example:**
```bash
curl -X POST "https://dev.proppik.in/api/tour/mobile/send-otp" \
  -H "X-Tour-Token: 470ec0ba5310221aaa057c5bc6484a27" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "tour_code": "z2bhT1M5",
    "mobile": "1234567890"
  }'
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "OTP sent successfully"
}
```

**Error Response (400 Bad Request):**
```json
{
    "success": false,
    "message": "Invalid mobile number"
}
```

---

### 6. Verify OTP

**Endpoint:** `POST /api/tour/mobile/verify-otp`

**Description:** Verify OTP for mobile validation.

**Authentication:** Required (X-Tour-Token header)

**Request Headers:**
```
X-Tour-Token: {md5_hash}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "tour_code": "z2bhT1M5",
    "mobile": "1234567890",
    "otp": "123456"
}
```

**cURL Example:**
```bash
curl -X POST "https://dev.proppik.in/api/tour/mobile/verify-otp" \
  -H "X-Tour-Token: 470ec0ba5310221aaa057c5bc6484a27" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "tour_code": "z2bhT1M5",
    "mobile": "1234567890",
    "otp": "123456"
  }'
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "OTP verified successfully"
}
```

**Error Response (400 Bad Request):**
```json
{
    "success": false,
    "message": "Invalid or expired OTP"
}
```

---

### 7. Get Mobile History

**Endpoint:** `GET /api/tour/mobile/history/{tour_code}`

**Description:** Retrieve mobile validation history for a tour.

**Authentication:** Required (X-Tour-Token header)

**Request Headers:**
```
X-Tour-Token: {md5_hash}
Accept: application/json
```

**URL Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| tour_code | string | Yes | The tour code identifier |

**cURL Example:**
```bash
curl -X GET "https://dev.proppik.in/api/tour/mobile/history/z2bhT1M5" \
  -H "X-Tour-Token: 470ec0ba5310221aaa057c5bc6484a27" \
  -H "Accept: application/json"
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "history": [
        {
            "id": 1,
            "mobile": "1234567890",
            "verified_at": "2025-12-29 12:51:48",
            "created_at": "2025-12-29 12:51:48"
        }
    ]
}
```

**Error Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Tour code not found"
}
```

---

## Error Codes

| HTTP Status | Code | Description |
|-------------|------|-------------|
| 200 | OK | Request successful |
| 400 | BAD_REQUEST | Invalid request parameters |
| 401 | UNAUTHENTICATED | Authentication required or invalid credentials |
| 403 | FORBIDDEN | Invalid or missing X-Tour-Token |
| 404 | NOT_FOUND | Resource not found |
| 422 | VALIDATION_ERROR | Validation failed |

---

## Notes

1. **Tour Manager Token**: The token received from login endpoint does not include the ID prefix. Use it directly in the Authorization header.

2. **Tour Access Token**: The token is generated dynamically based on the tour's creation date. Make sure to use the correct tour_code and calculate the MD5 hash correctly.

3. **Token Expiry**: Tour Manager tokens are managed by Laravel Sanctum. Tour Access tokens are validated based on the tour's creation date.

4. **Base URLs**: All URLs in responses (QR links, S3 links, API links) are configurable via settings and may vary.

5. **Rate Limiting**: Consider implementing rate limiting for production use.

---

## Support

For API support or questions, please contact the development team.

**Last Updated:** January 2026

