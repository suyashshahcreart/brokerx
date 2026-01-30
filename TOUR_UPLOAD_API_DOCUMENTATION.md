# Tour Upload API Documentation

## Overview
This document provides comprehensive technical documentation for the Tour Upload APIs. These APIs allow developers to retrieve available tour locations and upload tour ZIP files for processing.

**Base URL:** `https://dev.proppik.in/api` (or your local development URL)

**Authentication:** All endpoints require `auth:sanctum` token (except login endpoint)

---

## Table of Contents
1. [Get Tour Locations API](#1-get-tour-locations-api)
2. [Upload Tour File API](#2-upload-tour-file-api)
3. [Error Handling](#error-handling)
4. [Implementation Examples](#implementation-examples)
5. [Best Practices](#best-practices)

---

## 1. Get Tour Locations API

### Endpoint
```
GET /api/tour-manager/locations
```

### Description
Retrieves a list of all active tour locations available in the system. These locations are used when uploading tour files to specify where the tour should be hosted.

### Authentication
**Required:** Yes (Bearer Token)

**Header:**
```
Authorization: Bearer {your_access_token}
```

### Request Parameters
None

### Response Format

#### Success Response (200 OK)
```json
{
    "success": true,
    "locations": [
        {
            "id": 1,
            "category_name": "mumbai",
            "display_name": "Mumbai",
            "main_url": "https://mumbai.proppik.com"
        },
        {
            "id": 2,
            "category_name": "delhi",
            "display_name": "Delhi",
            "main_url": "https://delhi.proppik.com"
        }
    ]
}
```

#### Response Fields
| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Indicates if the request was successful |
| `locations` | array | List of available tour locations |
| `locations[].id` | integer | Unique identifier for the location |
| `locations[].category_name` | string | Location code/category name (use this in upload API) |
| `locations[].display_name` | string | Human-readable location name |
| `locations[].main_url` | string | Base URL for the location |

### cURL Example
```bash
curl -X GET "https://dev.proppik.in/api/tour-manager/locations" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### JavaScript/Fetch Example
```javascript
async function getTourLocations() {
    try {
        const response = await fetch('https://dev.proppik.in/api/tour-manager/locations', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${accessToken}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Available locations:', data.locations);
            return data.locations;
        } else {
            throw new Error('Failed to fetch locations');
        }
    } catch (error) {
        console.error('Error fetching locations:', error);
        throw error;
    }
}
```

### React Example
```jsx
import { useState, useEffect } from 'react';

function TourLocationSelector() {
    const [locations, setLocations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        async function fetchLocations() {
            try {
                const response = await fetch('/api/tour-manager/locations', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    setLocations(data.locations);
                } else {
                    setError('Failed to load locations');
                }
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        }
        
        fetchLocations();
    }, []);

    if (loading) return <div>Loading locations...</div>;
    if (error) return <div>Error: {error}</div>;

    return (
        <select name="location">
            {locations.map(location => (
                <option key={location.id} value={location.category_name}>
                    {location.display_name}
                </option>
            ))}
        </select>
    );
}
```

---

## 2. Upload Tour File API

### Endpoint
```
POST /api/tour-manager/upload-file
```

### Description
Uploads a ZIP file containing tour assets. The API automatically handles both small files (< 100MB) and large files (>= 100MB) using internal chunking. The file is processed asynchronously in the background.

### Authentication
**Required:** Yes (Bearer Token)

**Header:**
```
Authorization: Bearer {your_access_token}
```

### Request Format
**Content-Type:** `multipart/form-data`

### Request Parameters

| Parameter | Type | Required | Description | Validation Rules |
|-----------|------|----------|-------------|------------------|
| `tour_code` | string | Yes | Unique tour code identifier | Must exist in bookings table |
| `slug` | string | Yes | URL-friendly tour slug | Max 255 chars, regex: `^[a-zA-Z0-9\/\-_]+$` |
| `location` | string | Yes | Tour location category name | Must be a valid location from locations API |
| `file` | file | Yes | ZIP file containing tour assets | Max 1GB, ZIP files only |

### Request Example (Form Data)
```
tour_code: "ABC123"
slug: "my-tour-slug"
location: "mumbai"
file: [ZIP file]
```

### Response Format

#### Success Response - Simple Upload (< 100MB) (200 OK)
```json
{
    "success": true,
    "message": "ZIP file uploaded successfully! Processing will continue in the background.",
    "upload_method": "simple",
    "file_size": 52428800,
    "file_size_mb": 50.0,
    "booking_id": 6,
    "tour_code": "ABC123",
    "processing": true,
    "tour_zip_status": "processing",
    "tour_zip_progress": 0,
    "tour_zip_message": "Queued for background processing (simple upload)",
    "tour": {
        "id": 10,
        "slug": "my-tour-slug",
        "location": "mumbai",
        "tour_code": "ABC123"
    }
}
```

#### Success Response - Chunked Upload (>= 100MB) (200 OK)
```json
{
    "success": true,
    "message": "Large ZIP file uploaded successfully! Processing will continue in the background.",
    "upload_method": "chunked",
    "file_size": 157286400,
    "file_size_mb": 150.0,
    "total_chunks": 15,
    "chunk_size_mb": 10.0,
    "booking_id": 6,
    "tour_code": "ABC123",
    "processing": true,
    "tour_zip_status": "processing",
    "tour_zip_progress": 0,
    "tour_zip_message": "Queued for background processing (chunked upload)",
    "tour": {
        "id": 10,
        "slug": "my-tour-slug",
        "location": "mumbai",
        "tour_code": "ABC123"
    }
}
```

#### Response Fields
| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Indicates if the upload was successful |
| `message` | string | Human-readable success message |
| `upload_method` | string | Either "simple" or "chunked" |
| `file_size` | integer | File size in bytes |
| `file_size_mb` | float | File size in megabytes (rounded to 2 decimals) |
| `total_chunks` | integer | Number of chunks (only for chunked upload) |
| `chunk_size_mb` | float | Size of each chunk in MB (only for chunked upload) |
| `booking_id` | integer | Booking ID associated with the tour |
| `tour_code` | string | Tour code identifier |
| `processing` | boolean | Indicates background processing has started |
| `tour_zip_status` | string | Current processing status ("processing", "completed", "failed") |
| `tour_zip_progress` | integer | Processing progress percentage (0-100) |
| `tour_zip_message` | string | Current processing message |
| `tour` | object | Tour information object |
| `tour.id` | integer | Tour ID |
| `tour.slug` | string | Tour slug |
| `tour.location` | string | Tour location |
| `tour.tour_code` | string | Tour code |

### cURL Example
```bash
curl -X POST "https://dev.proppik.in/api/tour-manager/upload-file" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -F "tour_code=ABC123" \
  -F "slug=my-tour-slug" \
  -F "location=mumbai" \
  -F "file=@/path/to/your/tour.zip"
```

### JavaScript/Fetch Example
```javascript
async function uploadTourFile(tourCode, slug, location, file) {
    const formData = new FormData();
    formData.append('tour_code', tourCode);
    formData.append('slug', slug);
    formData.append('location', location);
    formData.append('file', file);

    try {
        const response = await fetch('https://dev.proppik.in/api/tour-manager/upload-file', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${accessToken}`,
                'Accept': 'application/json'
                // Don't set Content-Type header - browser will set it with boundary
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            console.log('Upload successful:', data);
            console.log('Upload method:', data.upload_method);
            console.log('File size:', data.file_size_mb, 'MB');
            return data;
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    } catch (error) {
        console.error('Upload error:', error);
        throw error;
    }
}

// Usage
const fileInput = document.querySelector('input[type="file"]');
const file = fileInput.files[0];

uploadTourFile('ABC123', 'my-tour-slug', 'mumbai', file)
    .then(result => {
        console.log('Upload completed:', result);
    })
    .catch(error => {
        console.error('Upload failed:', error);
    });
```

### React Example with Progress
```jsx
import { useState } from 'react';

function TourUploadForm() {
    const [file, setFile] = useState(null);
    const [tourCode, setTourCode] = useState('');
    const [slug, setSlug] = useState('');
    const [location, setLocation] = useState('');
    const [uploading, setUploading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!file || !tourCode || !slug || !location) {
            setError('All fields are required');
            return;
        }

        // Validate file type
        if (!file.name.endsWith('.zip')) {
            setError('Only ZIP files are allowed');
            return;
        }

        setUploading(true);
        setError(null);
        setResult(null);

        const formData = new FormData();
        formData.append('tour_code', tourCode);
        formData.append('slug', slug);
        formData.append('location', location);
        formData.append('file', file);

        try {
            const response = await fetch('/api/tour-manager/upload-file', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                setResult(data);
                setUploadProgress(100);
                
                // Poll for processing status if needed
                if (data.processing) {
                    pollProcessingStatus(data.booking_id);
                }
            } else {
                setError(data.message || 'Upload failed');
            }
        } catch (err) {
            setError(err.message || 'Network error occurred');
        } finally {
            setUploading(false);
        }
    };

    const pollProcessingStatus = async (bookingId) => {
        // Implement polling logic to check tour_zip_status
        // This would require another API endpoint to check status
    };

    return (
        <form onSubmit={handleSubmit}>
            <div>
                <label>Tour Code:</label>
                <input
                    type="text"
                    value={tourCode}
                    onChange={(e) => setTourCode(e.target.value)}
                    required
                />
            </div>

            <div>
                <label>Slug:</label>
                <input
                    type="text"
                    value={slug}
                    onChange={(e) => setSlug(e.target.value)}
                    pattern="^[a-zA-Z0-9\/\-_]+$"
                    required
                />
            </div>

            <div>
                <label>Location:</label>
                <input
                    type="text"
                    value={location}
                    onChange={(e) => setLocation(e.target.value)}
                    required
                />
            </div>

            <div>
                <label>ZIP File:</label>
                <input
                    type="file"
                    accept=".zip"
                    onChange={(e) => setFile(e.target.files[0])}
                    required
                />
            </div>

            {error && <div className="error">{error}</div>}
            
            {uploading && (
                <div>
                    <progress value={uploadProgress} max={100} />
                    <span>Uploading... {uploadProgress}%</span>
                </div>
            )}

            {result && (
                <div className="success">
                    <p>{result.message}</p>
                    <p>Upload Method: {result.upload_method}</p>
                    <p>File Size: {result.file_size_mb} MB</p>
                    <p>Status: {result.tour_zip_status}</p>
                </div>
            )}

            <button type="submit" disabled={uploading}>
                {uploading ? 'Uploading...' : 'Upload Tour File'}
            </button>
        </form>
    );
}
```

### Axios Example
```javascript
import axios from 'axios';

async function uploadTourFile(tourCode, slug, location, file) {
    const formData = new FormData();
    formData.append('tour_code', tourCode);
    formData.append('slug', slug);
    formData.append('location', location);
    formData.append('file', file);

    try {
        const response = await axios.post(
            'https://dev.proppik.in/api/tour-manager/upload-file',
            formData,
            {
                headers: {
                    'Authorization': `Bearer ${accessToken}`,
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                },
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    );
                    console.log(`Upload Progress: ${percentCompleted}%`);
                }
            }
        );

        return response.data;
    } catch (error) {
        if (error.response) {
            // Server responded with error
            throw new Error(error.response.data.message || 'Upload failed');
        } else if (error.request) {
            // Request made but no response
            throw new Error('Network error - no response from server');
        } else {
            // Error in request setup
            throw new Error(error.message);
        }
    }
}
```

---

## Error Handling

### Common Error Responses

#### 401 Unauthorized
```json
{
    "message": "Unauthenticated."
}
```
**Cause:** Missing or invalid authentication token  
**Solution:** Ensure you're sending a valid Bearer token in the Authorization header

#### 404 Not Found (Tour Code)
```json
{
    "success": false,
    "message": "Tour code not found"
}
```
**Cause:** The provided `tour_code` doesn't exist in the system  
**Solution:** Verify the tour code is correct and exists

#### 404 Not Found (Tour)
```json
{
    "success": false,
    "message": "No tour found for this booking."
}
```
**Cause:** No tour configuration exists for the booking  
**Solution:** Ensure the booking has an associated tour

#### 422 Validation Error
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "tour_code": ["The tour code field is required."],
        "slug": ["The slug field is required."],
        "location": ["The selected location is invalid."],
        "file": ["The file must be a file of type: zip."]
    }
}
```
**Cause:** Request validation failed  
**Solution:** Check all required fields are present and valid

#### 422 Invalid File Type
```json
{
    "success": false,
    "message": "Only ZIP files are allowed. Please upload a ZIP file."
}
```
**Cause:** Uploaded file is not a ZIP file  
**Solution:** Ensure the file has a `.zip` extension

#### 500 Server Error
```json
{
    "success": false,
    "message": "File upload error: [error details]"
}
```
**Cause:** Server-side error during processing  
**Solution:** Check server logs, verify file permissions, ensure queue worker is running

### Error Handling Example
```javascript
async function uploadWithErrorHandling(tourCode, slug, location, file) {
    try {
        const result = await uploadTourFile(tourCode, slug, location, file);
        return { success: true, data: result };
    } catch (error) {
        if (error.response) {
            const status = error.response.status;
            const data = error.response.data;

            switch (status) {
                case 401:
                    return { 
                        success: false, 
                        error: 'Authentication failed. Please login again.',
                        code: 'UNAUTHORIZED'
                    };
                case 404:
                    return { 
                        success: false, 
                        error: data.message || 'Tour not found',
                        code: 'NOT_FOUND'
                    };
                case 422:
                    return { 
                        success: false, 
                        error: 'Validation failed',
                        errors: data.errors || {},
                        code: 'VALIDATION_ERROR'
                    };
                case 500:
                    return { 
                        success: false, 
                        error: 'Server error. Please try again later.',
                        code: 'SERVER_ERROR'
                    };
                default:
                    return { 
                        success: false, 
                        error: 'An unexpected error occurred',
                        code: 'UNKNOWN_ERROR'
                    };
            }
        } else {
            return { 
                success: false, 
                error: 'Network error. Please check your connection.',
                code: 'NETWORK_ERROR'
            };
        }
    }
}
```

---

## Implementation Examples

### Complete React Component Example
```jsx
import React, { useState, useEffect } from 'react';

function TourUploadComponent() {
    const [locations, setLocations] = useState([]);
    const [formData, setFormData] = useState({
        tourCode: '',
        slug: '',
        location: '',
        file: null
    });
    const [uploading, setUploading] = useState(false);
    const [uploadResult, setUploadResult] = useState(null);
    const [error, setError] = useState(null);

    // Fetch locations on component mount
    useEffect(() => {
        fetchLocations();
    }, []);

    const fetchLocations = async () => {
        try {
            const response = await fetch('/api/tour-manager/locations', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                setLocations(data.locations);
            }
        } catch (err) {
            console.error('Failed to fetch locations:', err);
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleFileChange = (e) => {
        setFormData(prev => ({
            ...prev,
            file: e.target.files[0]
        }));
    };

    const validateForm = () => {
        if (!formData.tourCode.trim()) {
            setError('Tour code is required');
            return false;
        }
        if (!formData.slug.trim()) {
            setError('Slug is required');
            return false;
        }
        if (!/^[a-zA-Z0-9\/\-_]+$/.test(formData.slug)) {
            setError('Slug contains invalid characters');
            return false;
        }
        if (!formData.location) {
            setError('Location is required');
            return false;
        }
        if (!formData.file) {
            setError('File is required');
            return false;
        }
        if (!formData.file.name.endsWith('.zip')) {
            setError('Only ZIP files are allowed');
            return false;
        }
        return true;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);
        setUploadResult(null);

        if (!validateForm()) {
            return;
        }

        setUploading(true);

        const uploadFormData = new FormData();
        uploadFormData.append('tour_code', formData.tourCode);
        uploadFormData.append('slug', formData.slug);
        uploadFormData.append('location', formData.location);
        uploadFormData.append('file', formData.file);

        try {
            const response = await fetch('/api/tour-manager/upload-file', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Accept': 'application/json'
                },
                body: uploadFormData
            });

            const data = await response.json();

            if (data.success) {
                setUploadResult(data);
                // Reset form
                setFormData({
                    tourCode: '',
                    slug: '',
                    location: '',
                    file: null
                });
            } else {
                setError(data.message || 'Upload failed');
            }
        } catch (err) {
            setError(err.message || 'Network error occurred');
        } finally {
            setUploading(false);
        }
    };

    return (
        <div className="tour-upload-container">
            <h2>Upload Tour File</h2>

            <form onSubmit={handleSubmit}>
                <div className="form-group">
                    <label htmlFor="tourCode">Tour Code *</label>
                    <input
                        type="text"
                        id="tourCode"
                        name="tourCode"
                        value={formData.tourCode}
                        onChange={handleInputChange}
                        required
                    />
                </div>

                <div className="form-group">
                    <label htmlFor="slug">Slug *</label>
                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        value={formData.slug}
                        onChange={handleInputChange}
                        pattern="^[a-zA-Z0-9\/\-_]+$"
                        required
                    />
                    <small>Only letters, numbers, slashes, hyphens, and underscores allowed</small>
                </div>

                <div className="form-group">
                    <label htmlFor="location">Location *</label>
                    <select
                        id="location"
                        name="location"
                        value={formData.location}
                        onChange={handleInputChange}
                        required
                    >
                        <option value="">Select a location</option>
                        {locations.map(loc => (
                            <option key={loc.id} value={loc.category_name}>
                                {loc.display_name}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="form-group">
                    <label htmlFor="file">ZIP File *</label>
                    <input
                        type="file"
                        id="file"
                        name="file"
                        accept=".zip"
                        onChange={handleFileChange}
                        required
                    />
                    {formData.file && (
                        <small>Selected: {formData.file.name} ({(formData.file.size / 1024 / 1024).toFixed(2)} MB)</small>
                    )}
                </div>

                {error && (
                    <div className="error-message">
                        {error}
                    </div>
                )}

                {uploadResult && (
                    <div className="success-message">
                        <h3>Upload Successful!</h3>
                        <p>{uploadResult.message}</p>
                        <p><strong>Method:</strong> {uploadResult.upload_method}</p>
                        <p><strong>File Size:</strong> {uploadResult.file_size_mb} MB</p>
                        <p><strong>Status:</strong> {uploadResult.tour_zip_status}</p>
                        <p><strong>Message:</strong> {uploadResult.tour_zip_message}</p>
                    </div>
                )}

                <button 
                    type="submit" 
                    disabled={uploading}
                    className="submit-button"
                >
                    {uploading ? 'Uploading...' : 'Upload Tour File'}
                </button>
            </form>
        </div>
    );
}

export default TourUploadComponent;
```

---

## Best Practices

### 1. Authentication
- Always store tokens securely (use httpOnly cookies or secure storage)
- Implement token refresh logic
- Handle token expiration gracefully

### 2. File Upload
- Validate file type and size on client-side before upload
- Show upload progress to users
- Handle large files appropriately (the API handles this automatically)
- Implement retry logic for failed uploads

### 3. Error Handling
- Always check the `success` field in responses
- Display user-friendly error messages
- Log errors for debugging
- Implement proper error boundaries in React

### 4. Performance
- Fetch locations once and cache them
- Show loading states during API calls
- Implement debouncing for form inputs if needed
- Use proper loading indicators

### 5. Security
- Never expose API tokens in client-side code
- Validate all inputs on both client and server
- Use HTTPS in production
- Sanitize user inputs

### 6. Background Processing
- The upload API processes files asynchronously
- Check `tour_zip_status` periodically to track processing
- Implement polling mechanism to check processing status
- Handle processing failures gracefully

---

## Testing

### Using Postman

1. **Get Locations:**
   - Method: GET
   - URL: `https://dev.proppik.in/api/tour-manager/locations`
   - Headers: `Authorization: Bearer YOUR_TOKEN`

2. **Upload File:**
   - Method: POST
   - URL: `https://dev.proppik.in/api/tour-manager/upload-file`
   - Headers: `Authorization: Bearer YOUR_TOKEN`
   - Body: form-data
     - `tour_code`: text
     - `slug`: text
     - `location`: text
     - `file`: file (select ZIP file)

---

## Support

For issues or questions:
- Check server logs for detailed error messages
- Verify queue worker is running for background processing
- Ensure all required fields are provided
- Validate file format and size

---

## Version History

- **v1.0** - Initial release
  - Get Tour Locations API
  - Upload Tour File API with automatic chunking

---

**Last Updated:** January 2026
