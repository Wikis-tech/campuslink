# ⚡ Quick Start - Get CampusLink Running in 5 Minutes

## Every Time You Want to Use the Website

### Step 1: Start XAMPP
1. Open **XAMPP Control Panel**
2. Click **"Start"** next to **Apache** (wait for it to turn green)
3. Click **"Start"** next to **MySQL** (wait for it to turn green)

**Windows:** Both should show "Running" in green
**Done!** ✅

### Step 2: Open the Website
1. Open your web browser
2. Type this in the address bar: `http://localhost/campuslink`
3. Press Enter

**The website should load!** 🎉

---

## Test Accounts

### 👤 Login as Student/User
- Email: Any valid email you create via "Register"
- Password: Whatever you set

### 💼 Login as Vendor
- Email: Any vendor email you create via vendor registration
- Password: Whatever you set

### 🔐 Login as Admin
- URL: `http://localhost/campuslink/admin`
- Email: `admin@campuslink.com`
- Password: `Admin@CampusLink2025`

⚠️ **Remember:** Don't use this password in production!

---

## If Something Goes Wrong

| Problem | Fix |
|---------|-----|
| "This site can't be reached" | Make sure Apache is running (green in XAMPP) |
| Blank white page | Make sure MySQL is running (green in XAMPP) |
| "Database connection error" | Restart both Apache AND MySQL |
| "File not found (404)" | Check file is in `C:\xampp\htdocs\campuslink\` |
| Admin password wrong | Database might have reset - re-import from MASTER_SETUP.sql |

---

## Stop the Website

When you're done:
1. In XAMPP Control Panel, click **"Stop"** next to Apache
2. Click **"Stop"** next to MySQL
3. Close XAMPP

**That's it!** ✅

---

## Advanced: Restart Everything (Nuclear Option)

If things are really broken:

```
1. Click "Stop" on both Apache and MySQL in XAMPP
2. Wait 5 seconds
3. Close XAMPP completely
4. Open XAMPP again
5. Start both services
6. Go to http://localhost/campuslink
```

---

## Files Location
Your website files are at:
```
C:\xampp\htdocs\campuslink\
```

If you need to edit files, they're in that folder!

---

**Questions?** Check the full setup guide at `SETUP_GUIDE.md`