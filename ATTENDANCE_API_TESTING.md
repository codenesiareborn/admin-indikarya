# Attendance API Testing Guide

## Prerequisites

1. **Backend Setup:**
```bash
cd /Users/almafazi/Documents/indikarya-admin

# Make sure database is migrated and seeded
php artisan migrate:fresh --seed

# Start Laravel server
php artisan serve

# In another terminal, start localtunnel
lt --port 8000 --subdomain indikarya
```

2. **Frontend Setup:**
```bash
cd /Users/almafazi/Documents/indikarya-monitoring

# Install dependencies (if not done)
npm install

# Start Expo
npm start
```

## Test Credentials

Use these credentials for testing:

### Cleaning Service Employee
- **NIP:** CS-0001
- **Password:** password
- **Role:** cleaning_services
- **Projects:** RS Harapan Sehat, Mall Grand Indonesia, Hotel Mulia Senayan

### Security Service Employee
- **NIP:** SEC-0001
- **Password:** password
- **Role:** security_services
- **Projects:** Gedung Perkantoran BCA, Apartemen Taman Anggrek

## API Endpoints

### 1. Check-In
**Endpoint:** `POST https://indikarya.loca.lt/api/attendances/check-in`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (FormData):**
- `project_id` (integer) - ID project yang aktif
- `photo` (file) - Foto selfie (JPEG/PNG, max 5MB)
- `latitude` (float) - GPS latitude (-90 to 90)
- `longitude` (float) - GPS longitude (-180 to 180)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Check-in berhasil",
  "data": {
    "id": 1,
    "user_id": 11,
    "project_id": 1,
    "project_name": "RS Harapan Sehat",
    "tanggal": "2026-01-27",
    "check_in": "08:15",
    "check_in_photo_url": "https://indikarya.loca.lt/storage/attendances/check_in_2026-01-27_1738000000_abc123.jpg",
    "check_in_latitude": -6.2088,
    "check_in_longitude": 106.8456,
    "check_out": null,
    "check_out_photo_url": null,
    "check_out_latitude": null,
    "check_out_longitude": null,
    "status": "terlambat",
    "status_label": "Terlambat",
    "keterangan": null
  }
}
```

**Error Responses:**

Already checked in (409):
```json
{
  "success": false,
  "message": "Anda sudah melakukan check-in hari ini"
}
```

Not assigned to project (422):
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "project_id": ["Anda tidak di-assign ke project ini."]
  }
}
```

### 2. Check-Out
**Endpoint:** `POST https://indikarya.loca.lt/api/attendances/check-out`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (FormData):**
- `project_id` (integer)
- `photo` (file)
- `latitude` (float)
- `longitude` (float)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Check-out berhasil",
  "data": {
    "id": 1,
    "user_id": 11,
    "project_id": 1,
    "project_name": "RS Harapan Sehat",
    "tanggal": "2026-01-27",
    "check_in": "08:15",
    "check_in_photo_url": "https://indikarya.loca.lt/storage/attendances/check_in_...",
    "check_in_latitude": -6.2088,
    "check_in_longitude": 106.8456,
    "check_out": "17:30",
    "check_out_photo_url": "https://indikarya.loca.lt/storage/attendances/check_out_...",
    "check_out_latitude": -6.2088,
    "check_out_longitude": 106.8456,
    "status": "terlambat",
    "status_label": "Terlambat",
    "keterangan": null
  }
}
```

**Error Responses:**

Not checked in yet (400):
```json
{
  "success": false,
  "message": "Anda belum melakukan check-in hari ini"
}
```

Already checked out (409):
```json
{
  "success": false,
  "message": "Anda sudah melakukan check-out hari ini"
}
```

### 3. Get Today's Attendance
**Endpoint:** `GET https://indikarya.loca.lt/api/attendances/today`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "check_in": "08:15",
    "check_out": null,
    "status": "terlambat",
    ...
  }
}
```

**No attendance today (200):**
```json
{
  "success": true,
  "message": "Belum ada presensi hari ini",
  "data": null
}
```

### 4. Get Attendance History
**Endpoint:** `GET https://indikarya.loca.lt/api/attendances/history?page=1&per_page=15`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

## Status Calculation Logic

Backend automatically calculates status based on check-in time:

- **Hadir:** Check-in <= jam_masuk + 5 minutes grace period
- **Terlambat:** Check-in > jam_masuk + 5 minutes

Example:
- Project jam_masuk: 08:00
- Grace period: 5 minutes
- Check-in at 08:04 → Status: **hadir**
- Check-in at 08:06 → Status: **terlambat**

## Mobile App Testing Flow

### Test Scenario 1: Complete Check-in Flow

1. **Login:**
   - Open app
   - Select "Cleaning Service"
   - Enter NIP: CS-0001
   - Enter Password: password
   - Click Login
   - ✅ Should redirect to Dashboard

2. **Check Today's Attendance:**
   - Dashboard should show schedule
   - Should show "Belum Check-in" if no attendance today

3. **Navigate to Presensi:**
   - Click "Presensi" menu
   - Should open Absensi screen

4. **Take Selfie:**
   - Click "Klik untuk Ambil Foto"
   - Allow camera permission
   - Take photo
   - ✅ Photo should appear in preview

5. **Get GPS Location:**
   - Allow location permission
   - Wait for location to be detected
   - ✅ Location string should appear

6. **Submit Check-in:**
   - Click "Simpan Masuk"
   - Wait for upload (loading indicator)
   - ✅ Should show success message
   - ✅ Should show check-in time and location

7. **Verify on Dashboard:**
   - Go back to Dashboard
   - ✅ Should show actual check-in time
   - ✅ Should show status (Hadir/Terlambat)

### Test Scenario 2: Check-out Flow

1. **After Check-in:**
   - Go to Presensi screen
   - ✅ "Simpan Masuk" button should be disabled
   - ✅ Should show "Sudah Check-in" status

2. **Take New Selfie:**
   - Take new photo for check-out

3. **Submit Check-out:**
   - Click "Simpan Pulang"
   - Wait for upload
   - ✅ Should show success message

4. **Verify on Dashboard:**
   - ✅ Should show check-out time
   - ✅ Both buttons should be disabled

### Test Scenario 3: Error Handling

1. **Check-in without photo:**
   - Don't take photo
   - Click "Simpan Masuk"
   - ✅ Should show error: "Foto Diperlukan"

2. **Check-in without GPS:**
   - Disable location services
   - Try check-in
   - ✅ Should show error: "Lokasi Diperlukan"

3. **Duplicate check-in:**
   - Try check-in twice in same day
   - ✅ Should show error: "Anda sudah melakukan check-in hari ini"

4. **Check-out before check-in:**
   - Without check-in, try check-out
   - ✅ Should show error: "Anda belum melakukan check-in hari ini"

## Testing with Postman

### 1. Login First
```
POST https://indikarya.loca.lt/api/login
Body (JSON):
{
  "nip": "CS-0001",
  "password": "password",
  "role": "cleaning_services"
}

Save the token from response.
```

### 2. Test Check-in
```
POST https://indikarya.loca.lt/api/attendances/check-in
Headers:
  Authorization: Bearer {token}
  Content-Type: multipart/form-data

Body (form-data):
  project_id: 1
  photo: [select image file]
  latitude: -6.2088
  longitude: 106.8456
```

### 3. Test Get Today
```
GET https://indikarya.loca.lt/api/attendances/today
Headers:
  Authorization: Bearer {token}
```

### 4. Test Check-out
```
POST https://indikarya.loca.lt/api/attendances/check-out
Headers:
  Authorization: Bearer {token}
  Content-Type: multipart/form-data

Body (form-data):
  project_id: 1
  photo: [select image file]
  latitude: -6.2088
  longitude: 106.8456
```

## Verification Checklist

### Backend
- [ ] Storage link created (`php artisan storage:link`)
- [ ] API routes registered in `routes/api.php`
- [ ] AttendanceController created with all methods
- [ ] Validation requests working (CheckInRequest, CheckOutRequest)
- [ ] AttendanceService calculating status correctly
- [ ] Photos uploaded to `storage/app/public/attendances/`
- [ ] Photos accessible via URL

### Frontend
- [ ] Axios installed (`npm install axios`)
- [ ] API config created with multipart support
- [ ] Attendance service created
- [ ] Attendance store created
- [ ] Absensi screen updated with API integration
- [ ] Dashboard showing real attendance data
- [ ] Loading states working
- [ ] Error messages displayed correctly

### Integration
- [ ] Login successful
- [ ] Projects loaded
- [ ] Check-in successful with photo upload
- [ ] Photo visible in response URL
- [ ] GPS coordinates saved correctly
- [ ] Status calculated correctly (hadir/terlambat)
- [ ] Check-out successful
- [ ] Dashboard shows correct times
- [ ] Duplicate check-in prevented
- [ ] Check-out before check-in prevented

## Common Issues & Solutions

### 1. Photo Upload Failed
**Error:** "The photo field is required"
**Solution:** 
- Check FormData is created correctly
- Verify Content-Type is multipart/form-data
- Check photo object has uri, name, type

### 2. GPS Coordinates Invalid
**Error:** "Latitude tidak valid"
**Solution:**
- Check coordinates are numbers, not strings
- Verify latitude is between -90 and 90
- Verify longitude is between -180 and 180

### 3. Project Not Found
**Error:** "Anda tidak di-assign ke project ini"
**Solution:**
- Check user is assigned to project in employee_projects table
- Verify project_id is correct
- Run seeder to assign employees to projects

### 4. Storage Link Not Working
**Error:** Photos not accessible via URL
**Solution:**
```bash
php artisan storage:link
# Check public/storage symlink exists
```

### 5. CORS Error on File Upload
**Solution:**
- Check config/cors.php allows multipart/form-data
- Verify 'allowed_headers' includes '*'

## Database Verification

Check attendance records in database:
```sql
-- View today's attendances
SELECT a.*, u.name, p.nama_project 
FROM attendances a
JOIN users u ON a.user_id = u.id
JOIN projects p ON a.project_id = p.id
WHERE a.tanggal = CURDATE();

-- Check photo paths
SELECT id, check_in_photo, check_out_photo 
FROM attendances 
WHERE tanggal = CURDATE();
```

## Next Features to Implement

After attendance is working:
1. **Task Submission API** - Submit task completion per room
2. **Patrol/Checkpoint API** - For security patrol routes
3. **History with Filters** - Filter by date range, status
4. **Attendance Reports** - Export to PDF/Excel
