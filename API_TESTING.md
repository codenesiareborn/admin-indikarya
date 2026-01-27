# API Testing Guide - Indikarya Admin

## Setup Backend

### 1. Reset Database
```bash
cd /Users/almafazi/Documents/indikarya-admin
php artisan migrate:fresh --seed
```

### 2. Start Laravel Server
```bash
php artisan serve
```

### 3. Setup Localtunnel
```bash
# Install localtunnel globally if not installed
npm install -g localtunnel

# Start tunnel
lt --port 8000 --subdomain indikarya
```

Expected output:
```
your url is: https://indikarya.loca.lt
```

## Test Credentials

After running seeder, you'll have these test accounts:

### Super Admin
- NIP: N/A (no NIP field)
- Email: admin@indikarya.com
- Password: password

### Cleaning Service Employees (50 users)
- NIP: CS-0001 to CS-0050
- Email: cleaning1@indikarya.com to cleaning50@indikarya.com
- Password: password
- Role: cleaning_services

### Security Service Employees (50 users)
- NIP: SEC-0001 to SEC-0050
- Email: security1@indikarya.com to security50@indikarya.com
- Password: password
- Role: security_services

## API Endpoints Testing

### 1. Login
**Endpoint:** `POST https://indikarya.loca.lt/api/login`

**Request Body:**
```json
{
  "nip": "CS-0001",
  "password": "password",
  "role": "cleaning_services"
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 11,
      "name": "Cleaning Staff 1",
      "nip": "CS-0001",
      "email": "cleaning1@indikarya.com",
      "staf": "cleaning_services",
      "role": "employee",
      "status_pegawai": "aktif"
    },
    "projects": [...]
  }
}
```

**Error Cases:**

Wrong NIP:
```json
{
  "success": false,
  "message": "NIP atau password salah"
}
```

Wrong Role:
```json
{
  "success": false,
  "message": "Role yang dipilih tidak sesuai dengan data pegawai"
}
```

### 2. Get User Profile
**Endpoint:** `GET https://indikarya.loca.lt/api/user`

**Headers:**
```
Authorization: Bearer {token}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 11,
    "name": "Cleaning Staff 1",
    "nip": "CS-0001",
    "email": "cleaning1@indikarya.com",
    "staf": "cleaning_services",
    "role": "employee"
  }
}
```

### 3. Get User Projects
**Endpoint:** `GET https://indikarya.loca.lt/api/user/projects`

**Headers:**
```
Authorization: Bearer {token}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nama_project": "RS Harapan Sehat",
      "jenis_project": "cleaning_services",
      "alamat_lengkap": "Jl. Sudirman No. 123, Jakarta Pusat",
      "tanggal_mulai": "2026-01-01",
      "tanggal_selesai": "2026-12-31",
      "jam_masuk": "07:00",
      "jam_keluar": "17:00",
      "status": "aktif",
      "rooms": [...]
    }
  ]
}
```

### 4. Refresh Token
**Endpoint:** `POST https://indikarya.loca.lt/api/refresh`

**Headers:**
```
Authorization: Bearer {token}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Token berhasil di-refresh",
  "data": {
    "token": "2|xyz789...",
    "user": {...}
  }
}
```

### 5. Logout
**Endpoint:** `POST https://indikarya.loca.lt/api/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Logout berhasil"
}
```

## Testing with Postman/Insomnia

### Import Collection

1. Create new request collection
2. Add environment variable:
   - `base_url`: https://indikarya.loca.lt/api
   - `token`: (will be set after login)

3. Test sequence:
   - Login → Save token
   - Get Profile → Use saved token
   - Get Projects → Use saved token
   - Refresh Token → Update saved token
   - Logout → Clear token

## Common Issues

### 1. CORS Error
**Solution:** Check `config/cors.php` allows your origin

### 2. 401 Unauthorized
**Solution:** Check token is valid and not expired

### 3. Localtunnel Connection Issues
**Solution:** 
- Restart localtunnel
- Use different subdomain
- Check Laravel server is running

### 4. Token Not Working
**Solution:**
- Check Sanctum is installed
- Check `bootstrap/app.php` has API routes enabled
- Verify token format: `Bearer {token}`

## Mobile App Testing

### 1. Update API Base URL
File: `/Users/almafazi/Documents/indikarya-monitoring/config/api.ts`
```typescript
export const API_BASE_URL = 'https://indikarya.loca.lt/api';
```

### 2. Test Login Flow
1. Open mobile app
2. Select role (Security or Cleaning Service)
3. Enter NIP: CS-0001 (for cleaning) or SEC-0001 (for security)
4. Enter Password: password
5. Click Login
6. Should redirect to Dashboard
7. Projects should load automatically

### 3. Test Logout
1. Click profile icon on Dashboard
2. Click Logout
3. Should redirect to Login screen
4. Token should be cleared

## Verification Checklist

- [ ] Backend server running on port 8000
- [ ] Localtunnel active with subdomain `indikarya`
- [ ] Database seeded with test users
- [ ] Login API returns token and user data
- [ ] Protected endpoints require Bearer token
- [ ] Mobile app can login successfully
- [ ] Projects load after login
- [ ] Logout clears token and redirects
- [ ] Token persists after app restart
- [ ] Invalid credentials show error message
- [ ] Role validation works correctly

## Next Steps

After authentication is working:
1. Implement Attendance API (check-in/check-out)
2. Implement Task Submission API
3. Implement Patrol/Checkpoint API
4. Implement History & Reports API
