# SG Cart Customer Auth API Documentation

This API provides stateless customer authentication for mobile applications using JWT (JSON Web Tokens).

---

## Authentication Overview

* **Guard Name:** `customer-api` (using `jwt` driver)
* **Token Format:** Bearer token (`Authorization: Bearer <your_jwt_token>`)
* **Base URL Prefix:** `/api`

---

## Route Summary

| Endpoint | HTTP Method | Auth Required | Description |
| :--- | :--- | :--- | :--- |
| [`/api/customer/register`](#1-customer-registration) | `POST` | No | Registers a new pending customer and returns a verification code. |
| [`/api/customer/verify-otp`](#2-verify-otp--verification-code) | `POST` | No | Verifies the OTP/code, creates the customer, and returns the JWT. |
| [`/api/customer/resend-otp`](#3-resend-otp--verification-code) | `POST` | No | Generates and returns a new OTP/code (respects resend cooldown). |
| [`/api/customer/login`](#4-customer-login) | `POST` | No | Authenticates customer credentials and returns JWT. |
| [`/api/customer/me`](#5-get-profile-details) | `GET` | **Yes** | Retrieves authenticated customer profile details. |
| [`/api/customer/logout`](#6-customer-logout) | `POST` | **Yes** | Invalidates the JWT access token and logs out the customer. |
| [`/api/customer/forgot-password`](#7-request-forgot-password) | `POST` | No | Initiates forgot password flow by generating/sending reset OTP. |
| [`/api/customer/forgot-password/verify`](#8-verify-forgot-password-otp) | `POST` | No | Verifies forgot password OTP and authorizes password reset. |
| [`/api/customer/forgot-password/resend`](#9-resend-forgot-password-otp) | `POST` | No | Generates and resends a new forgot password OTP. |
| [`/api/customer/forgot-password/reset`](#10-reset-password) | `POST` | No | Sets a new password for the customer after successful verification. |

---

## Endpoint Details

### 1. Customer Registration

Registers a customer and holds their details in a temporary cache for 15 minutes until OTP verification is completed. No unverified account is written to the database.

* **URL:** `/api/customer/register`
* **Method:** `POST`
* **Headers:**
  * `Accept: application/json`
  * `Content-Type: application/json`

#### Request Parameters

| Parameter | Type | Required | Rules & Description |
| :--- | :--- | :--- | :--- |
| `name` | `string` | **Yes** | Max length 255. |
| `email_or_phone` | `string` | **Yes** | Must be unique in `customers` table.<br>• If email: must be a valid email format.<br>• If phone: must start with `+` and contain only digits after `+` (7 to 15 digits total). |
| `password` | `string` | **Yes** | Minimum 8 characters. Must match `password_confirmation`. |
| `password_confirmation` | `string` | **Yes** | Must match `password` exactly. |

#### Example Request Payload
```json
{
  "name": "John Doe",
  "email_or_phone": "john.doe@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Example Responses

* **Success (200 OK):**
  ```json
  {
    "success": true,
    "message": "Verification code generated.",
    "email_or_phone": "john.doe@example.com",
    "is_email": true,
    "verification_code": "336846",
    "otp": "336846"
  }
  ```

* **Validation Failure (422 Unprocessable Content):**
  ```json
  {
    "message": "The email address has already been taken. (and 1 more error)",
    "errors": {
      "email_or_phone": [
        "The email address has already been taken."
      ],
      "password": [
        "The password field confirmation does not match."
      ]
    }
  }
  ```

---

### 2. Verify OTP / Verification Code

Verifies the OTP to complete either a **pending registration** or **unverified login**. Accepts either `verification_code` or `otp` in the payload.

* **URL:** `/api/customer/verify-otp`
* **Method:** `POST`
* **Headers:**
  * `Accept: application/json`
  * `Content-Type: application/json`

#### Request Parameters

| Parameter | Type | Required | Rules & Description |
| :--- | :--- | :--- | :--- |
| `email_or_phone` | `string` | **Yes** | The email or phone number associated with the verification. |
| `otp` | `string` | **Semi** | Exact length of 6 digits. *Required if `verification_code` is missing.* |
| `verification_code` | `string` | **Semi** | Exact length of 6 digits. *Required if `otp` is missing.* |

#### Example Request Payload
```json
{
  "email_or_phone": "john.doe@example.com",
  "verification_code": "336846"
}
```

#### Example Responses

* **Success (200 OK):** Returns the JWT token and the authenticated user payload.
  ```json
  {
    "success": true,
    "message": "Account created and verified successfully.",
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 12,
      "ulid": "01J1VE2A9P3F4G5H6J7K8L9M0N",
      "name": "John Doe",
      "email": "john.doe@example.com",
      "phone_no": null,
      "profile_picture": null
    }
  }
  ```

* **Incorrect OTP (422 Unprocessable Content):**
  ```json
  {
    "message": "The entered verification code is incorrect or has expired.",
    "errors": {
      "verification_code": [
        "The entered verification code is incorrect or has expired."
      ],
      "otp": [
        "The entered OTP is incorrect or has expired."
      ]
    }
  }
  ```

---

### 3. Resend OTP / Verification Code

Generates a new verification code. This endpoint enforces the same 5-minute resend cooldown defined in the web storefront.

* **URL:** `/api/customer/resend-otp`
* **Method:** `POST`
* **Headers:**
  * `Accept: application/json`
  * `Content-Type: application/json`

#### Request Parameters

| Parameter | Type | Required | Rules & Description |
| :--- | :--- | :--- | :--- |
| `email_or_phone` | `string` | **Yes** | The email or phone number associated with the verification. |

#### Example Request Payload
```json
{
  "email_or_phone": "john.doe@example.com"
}
```

#### Example Responses

* **Success (200 OK):**
  ```json
  {
    "success": true,
    "message": "A new OTP has been generated.",
    "verification_code": "847392",
    "otp": "847392"
  }
  ```

* **Rate Limited / Cooldown Active (429 Too Many Requests):**
  ```json
  {
    "success": false,
    "message": "Please wait 5 minute(s) before requesting a new OTP.",
    "cooldown_remaining_seconds": 298
  }
  ```

---

### 4. Customer Login

Authenticates customer credentials. Handles unverified account detection by outputting verification codes directly.

* **URL:** `/api/customer/login`
* **Method:** `POST`
* **Headers:**
  * `Accept: application/json`
  * `Content-Type: application/json`

#### Request Parameters

| Parameter | Type | Required | Rules & Description |
| :--- | :--- | :--- | :--- |
| `email_or_phone` | `string` | **Yes** | Registered email address or phone number. |
| `password` | `string` | **Yes** | Plain text password. |

#### Example Request Payload
```json
{
  "email_or_phone": "john.doe@example.com",
  "password": "password123"
}
```

#### Example Responses

* **Success - Account is Verified (200 OK):**
  ```json
  {
    "success": true,
    "message": "Logged in successfully!",
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 12,
      "ulid": "01J1VE2A9P3F4G5H6J7K8L9M0N",
      "name": "John Doe",
      "email": "john.doe@example.com",
      "phone_no": null,
      "profile_picture": null
    }
  }
  ```

* **Error - Account is Unverified (403 Forbidden):**
  ```json
  {
    "success": false,
    "code": "VERIFICATION_REQUIRED",
    "message": "Your account is not verified. A verification code has been generated.",
    "email_or_phone": "john.doe@example.com",
    "is_email": true,
    "verification_code": "294810",
    "otp": "294810"
  }
  ```
  *(App clients should intercept this code and show the OTP verification screen).*

* **Error - Account Not Found (404 Not Found):**
  ```json
  {
    "success": false,
    "code": "USER_NOT_FOUND",
    "message": "No account found. Please register to continue.",
    "email_or_phone": "unknown@example.com"
  }
  ```

* **Error - Incorrect Password (422 Unprocessable Content):**
  ```json
  {
    "message": "These credentials do not match our records.",
    "errors": {
      "email_or_phone": [
        "These credentials do not match our records."
      ]
    }
  }
  ```

---

### 5. Get Profile Details

Retrieves the currently authenticated customer's profile parameters.

* **URL:** `/api/customer/me`
* **Method:** `GET`
* **Headers:**
  * `Accept: application/json`
  * `Authorization: Bearer <your_jwt_token>`

#### Request Parameters
None.

#### Example Responses

* **Success (200 OK):**
  ```json
  {
    "success": true,
    "user": {
      "id": 12,
      "ulid": "01J1VE2A9P3F4G5H6J7K8L9M0N",
      "name": "John Doe",
      "email": "john.doe@example.com",
      "phone_no": null,
      "profile_picture": null,
      "email_verified_at": "2026-07-03T12:00:00.000000Z",
      "phone_verified_at": null
    }
  }
  ```

* **Error - Missing/Invalid Token (401 Unauthorized):**
  ```json
  {
    "message": "Unauthenticated."
  }
  ```

---

### 6. Customer Logout

Invalidates the JWT access token and logs out the session.

* **URL:** `/api/customer/logout`
* **Method:** `POST`
* **Headers:**
  * `Accept: application/json`
  * `Authorization: Bearer <your_jwt_token>`

#### Request Parameters
None.

#### Example Responses

* **Success (200 OK):**
  ```json
  {
    "success": true,
    "message": "Logged out successfully!"
  }
  ```

---

### 7. Request Forgot Password

Initiates the forgot password flow by validating the identifier (email or phone) and generating/sending a verification OTP.

* **URL:** `/api/customer/forgot-password`
* **Method:** `POST`
* **Headers:**
  * `Accept: application/json`
  * `Content-Type: application/json`

#### Request Parameters

| Parameter | Type | Required | Rules & Description |
| :--- | :--- | :--- | :--- |
| `email_or_phone` | `string` | **Yes** | Registered email or phone number. |

#### Example Request Payload
```json
{
  "email_or_phone": "john.reset@example.com"
}
```

#### Example Responses

* **Success (200 OK):**
  ```json
  {
    "success": true,
    "message": "A verification code has been sent to your email.",
    "data": {
      "email_or_phone": "john.reset@example.com",
      "is_email": true,
      "verification_code": "583920",
      "otp": "583920"
    }
  }
  ```

* **Validation Failure (422 Unprocessable Content):**
  ```json
  {
    "success": false,
    "message": "No account found with this email or phone number.",
    "errors": {
      "email_or_phone": [
        "No account found with this email or phone number."
      ]
    }
  }
  ```

---

### 8. Verify Forgot Password OTP

Verifies the OTP submitted for the customer and authorizes the password reset step.

* **URL:** `/api/customer/forgot-password/verify`
* **Method:** `POST`
* **Headers:**
  * `Accept: application/json`
  * `Content-Type: application/json`

#### Request Parameters

| Parameter | Type | Required | Rules & Description |
| :--- | :--- | :--- | :--- |
| `email_or_phone` | `string` | **Yes** | Registered email or phone number. |
| `otp` | `string` | **Semi** | Exact length of 6 digits. *Required if `verification_code` is missing.* |
| `verification_code` | `string` | **Semi** | Exact length of 6 digits. *Required if `otp` is missing.* |

#### Example Request Payload
```json
{
  "email_or_phone": "john.reset@example.com",
  "otp": "583920"
}
```

#### Example Responses

* **Success (200 OK):**
  ```json
  {
    "success": true,
    "message": "Email verified successfully! You can now choose a new password.",
    "data": {
      "email_or_phone": "john.reset@example.com"
    }
  }
  ```

* **Incorrect OTP (422 Unprocessable Content):**
  ```json
  {
    "success": false,
    "message": "The entered OTP is incorrect or has expired.",
    "errors": {
      "otp": [
        "The entered OTP is incorrect or has expired."
      ],
      "verification_code": [
        "The entered verification code is incorrect or has expired."
      ]
    }
  }
  ```

---

### 9. Resend Forgot Password OTP

Generates and resends a new OTP. Follows the standard 5-minute resend cooldown.

* **URL:** `/api/customer/forgot-password/resend`
* **Method:** `POST`
* **Headers:**
  * `Accept: application/json`
  * `Content-Type: application/json`

#### Request Parameters

| Parameter | Type | Required | Rules & Description |
| :--- | :--- | :--- | :--- |
| `email_or_phone` | `string` | **Yes** | Registered email or phone number. |

#### Example Request Payload
```json
{
  "email_or_phone": "john.reset@example.com"
}
```

#### Example Responses

* **Success (200 OK):**
  ```json
  {
    "success": true,
    "message": "A new OTP has been sent to your email.",
    "data": {
      "email_or_phone": "john.reset@example.com",
      "is_email": true,
      "verification_code": "830291",
      "otp": "830291"
    }
  }
  ```

* **Rate Limited / Cooldown Active (429 Too Many Requests):**
  ```json
  {
    "success": false,
    "message": "Please wait 5 minute(s) before requesting a new OTP.",
    "cooldown_remaining_seconds": 298
  }
  ```

---

### 10. Reset Password

Resets the customer's password with the new provided password. Requires the OTP verification step to be completed first.

* **URL:** `/api/customer/forgot-password/reset`
* **Method:** `POST`
* **Headers:**
  * `Accept: application/json`
  * `Content-Type: application/json`

#### Request Parameters

| Parameter | Type | Required | Rules & Description |
| :--- | :--- | :--- | :--- |
| `email_or_phone` | `string` | **Yes** | Registered email or phone number. |
| `password` | `string` | **Yes** | Minimum 8 characters. Must match `password_confirmation`. |
| `password_confirmation` | `string` | **Yes** | Must match `password` exactly. |

#### Example Request Payload
```json
{
  "email_or_phone": "john.reset@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

#### Example Responses

* **Success (200 OK):**
  ```json
  {
    "success": true,
    "message": "Your password has been reset successfully!",
    "data": null
  }
  ```

* **Error - Unauthorized Reset (403 Forbidden):**
  ```json
  {
    "success": false,
    "message": "Please verify your OTP code first."
  }
  ```

---

## Development & Testing

You can run the PHPUnit test suites validating all the routes, inputs, limits, and authentication states:
```bash
php artisan test --filter=CustomerApiAuthTest
php artisan test --filter=CustomerForgotPasswordApiTest
```
