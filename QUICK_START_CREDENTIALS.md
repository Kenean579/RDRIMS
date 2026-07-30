# 🔐 RDRIMS Quick Start - Login Credentials

## Login URL
**Frontend**: http://127.0.0.1:8001/login

---

## Default Password
All seeded accounts use: **`Admin@123`**

⚠️ **Important**: The password is case-sensitive! Use exactly: `Admin@123`

---

## Available Test Accounts

### 1. 👑 Super Admin (Full Platform Access)
```
Email:    admin@rdrims.local
Password: Admin@123
Role:     Super Administrator
Access:   All universities, all features
```

### 2. 🎓 Addis Ababa University - Research Admin
```
Email:    research.admin@aau.edu.et
Password: Admin@123
Role:     Research Administrator  
Access:   AAU university scope
Name:     Dr. Mesfin Tadesse
```

### 3. 🎓 Wollo University - Research Admin
```
Email:    research.admin@wollo.edu.et
Password: Admin@123
Role:     Research Administrator
Access:   Wollo university scope
Name:     Dr. Abebe Kebede
```

---

## Quick Login Steps

1. Open browser and go to: **http://127.0.0.1:8001/login**

2. Enter credentials (example):
   - Email: `research.admin@aau.edu.et`
   - Password: `Admin@123`

3. Click **"Sign In to Portal"**

4. You'll be redirected to the dashboard automatically

---

## Troubleshooting

### ❌ Login Not Working?

**Check these common issues:**

1. **Wrong Password Format**
   - ✗ `admin@123` (lowercase) - WRONG
   - ✗ `Admin@1234` (extra digit) - WRONG
   - ✓ `Admin@123` (exact match) - CORRECT

2. **Wrong Email**
   - Make sure you're using one of the emails listed above
   - Check for typos (e.g., `.et` not `.com`)

3. **Browser Console Errors**
   - Open Developer Tools (F12)
   - Check Console tab for errors
   - Check Network tab for failed requests

4. **Backend Not Running**
   - Make sure Laravel is running on port 8000
   - Test: http://127.0.0.1:8000/api/system/health

5. **Frontend Not Running**
   - Make sure Vite dev server is running on port 8001
   - Check terminal for build errors

---

## Password Reset (For Production)

For production environments, use the "Forgot Password" link on the login page to reset passwords via email.

For development, passwords can be reset via database seeders or manual database updates.

---

## Security Note

⚠️ **Change default passwords in production!**

The `Admin@123` password is for development and testing only. 
In production:
1. Change all default passwords immediately
2. Use strong passwords (min 12 characters)
3. Enable two-factor authentication if available
4. Regularly audit user accounts

---

## Need Help?

If login still doesn't work after trying the correct credentials:
1. Check `LOGIN_ISSUE_RESOLUTION.md` for detailed troubleshooting
2. Check Laravel logs: `backend/storage/logs/laravel.log`
3. Check browser console for frontend errors
4. Verify both backend and frontend servers are running

---

**Last Updated**: July 30, 2026
**System Status**: ✓ All authentication components working correctly
