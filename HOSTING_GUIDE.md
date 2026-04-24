# 🌐 Hosting Platform Compatibility & Recommendations

## Overview
CampusLink is a PHP-based application that works on all standard web hosting platforms. However, each platform has different features and limitations that affect performance and functionality.

---

## Platform Comparison

### 🥇 Best Overall: TrueHost (Recommended)
**Website:** https://www.truehost.ng/
**Price:** ~₦3,000-5,000/month
**Location:** Nigeria

**✅ Pros:**
- Excellent Nigerian support (Email/Chat in Igbo/English)
- Full SSH/SFTP access
- Unlimited cron jobs
- Fast servers (located in Lagos)
- Good uptime (99.5%)
- cPanel included
- Flexible MySQL versions
- Email hosting included

**⚠️ Cons:**
- Need to manage server somewhat
- Requires basic Linux knowledge for SSH

**CampusLink Compatibility:** ✅ Perfect fit
- All features work
- Cron jobs fully supported
- Email delivery reliable
- Payments process smoothly

**Setup Time:** ~30 minutes

---

### 🥈 Great Alternative: HostNigeria
**Website:** https://www.hostinginigeria.com/
**Price:** ~₦2,000-8,000/month
**Location:** Lagos, Nigeria

**✅ Pros:**
- Very affordable
- Nigerian company with support
- SSH access available
- Cron jobs supported
- Good server performance

**⚠️ Cons:**
- Support quality varies
- Account setup can be slow

**CampusLink Compatibility:** ✅ Excellent
- All features work perfectly
- Recommended for budget projects

**Setup Time:** ~20 minutes

---

### 🥉 Free Option: InfinityFree
**Website:** https://www.infinityfree.net/
**Price:** Free! (with ads)
**Limitation:** Subdomains only (username.infinityfree.app)

**✅ Pros:**
- Completely free
- No credit card required
- Good for learning
- Basic cPanel access
- phpMyAdmin included

**⚠️ Cons:**
- Websites have ads at bottom
- Limited cron job support (1x per day max)
- Limited storage (~5GB)
- Limited bandwidth
- Slower performance
- Email delivery unreliable
- **No custom domain** (unless you pay)

**CampusLink Compatibility:** ⚠️ Limited
- Core features work
- Expiry reminder cron jobs may not run reliably
- Email notifications may fail
- **Not recommended for production use**
- Good for testing/learning only

**Setup Time:** ~15 minutes

---

### NYFree
**Website:** https://www.nyfree.net/
**Price:** Free!

**✅ Pros:**
- Free hosting
- phpMyAdmin access
- Email hosting

**⚠️ Cons:**
- Very limited resources
- Slow response times
- Support is slow
- Emails often go to spam

**CampusLink Compatibility:** ⚠️ Poor
- Not recommended
- Will work but slowly
- Better alternatives available

---

## 🎯 Recommendation by Use Case

### You're Just Testing/Learning
→ Use **InfinityFree** (free, quick to set up)
- Don't expect production performance
- Perfect for development

### You Want Production Quality with Custom Domain
→ Use **TrueHost** (best all-around)
- Most reliable option
- Best support
- Everything works perfectly
- Worth the cost

### You Want Affordable Production Hosting
→ Use **HostNigeria** (budget option)
- Good performance
- Reliable
- Cheaper than TrueHost
- Still good support

### You Don't Want to Manage Servers
→ Use **InfinityFree** or **TrueHost cPanel**
- InfinityFree: No server management (but limited)
- TrueHost: Full cPanel without SSH (still easy)

---

## 📋 CampusLink Requirements Checklist

| Feature | Required? | InfinityFree | TrueHost | HostNigeria |
|---------|-----------|--------------|----------|-------------|
| **PHP 7.4+** | ✅ Yes | ✅ | ✅ | ✅ |
| **MySQL 5.7+** | ✅ Yes | ✅ | ✅ | ✅ |
| **cURL** | ✅ Yes (for Paystack) | ✅ | ✅ | ✅ |
| **JSON support** | ✅ Yes | ✅ | ✅ | ✅ |
| **File uploads** | ✅ Yes | ✅ Limited | ✅ | ✅ |
| **Cron jobs** | ✅ Yes* | ⚠️ Limited | ✅ | ✅ |
| **SMTP Email** | ✅ Yes | ⚠️ Unreliable | ✅ | ✅ |
| **SSH Access** | ❌ No | ❌ | ✅ | ✅ |

*Cron required for subscription expiry reminders - InfinityFree has limited support

---

## 🚀 Quick Deployment Steps

### For TrueHost (Recommended)

**Step 1: Buy Hosting**
1. Go to truehost.ng
2. Choose plan (2GB/3GB recommended for CampusLink)
3. Add domain during checkout
4. Complete payment

**Step 2: Setup via cPanel**
1. Log into cPanel
2. Create MySQL database
3. Create database user with all privileges
4. Upload files via File Manager or FTP

**Step 3: Import Database**
1. Open phpMyAdmin in cPanel
2. Select your database
3. Import `database/MASTER_SETUP.sql`

**Step 4: Configure**
1. Edit `config/config.php`:
```php
define('SITE_URL', 'https://yourdomain.com/campuslink');
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_db_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_pass');
```

**Step 5: Set Cron Job**
1. In cPanel, find "Cron Jobs"
2. Add: `0 9 * * * /usr/bin/php /home/yourusername/public_html/campuslink/scripts/subscription_cron.php`

**Done!** Your site is live! 🎉

---

## ⚠️ Important Notes

### Domain Names
- **TrueHost:** Can use any domain (via addon domains)
- **HostNigeria:** Can use any domain
- **InfinityFree:** Only free domains (no custom domain unless upgrade)

### Email Sending
- Use Gmail SMTP with App Password (safest)
- Paystack requires SMTP to be configured
- Test email sending after deployment

### HTTPS/SSL
- TrueHost: Let's Encrypt included (free SSL)
- HostNigeria: Let's Encrypt included
- InfinityFree: Sometimes included

### File Uploads
- Set proper permissions: `chmod 755 assets/uploads/`
- Verify upload_max_filesize in PHP settings
- InfinityFree limits files to ~50MB

### Database Backups
- TrueHost: Daily automatic backups
- HostNigeria: Check their backup policy
- InfinityFree: Manual backups only
- **Always keep your own local backup**

---

## 🔒 Security After Deployment

1. **Change Admin Password** immediately!
   - Don't use `Admin@CampusLink2025` in production
   - Use strong password

2. **Update Paystack Keys:**
   - Use live keys (not test keys)
   - Store in environment variables or `.env.local`
   - Never commit to git

3. **Enable HTTPS:**
   - All hosting platforms offer free SSL
   - Redirect HTTP → HTTPS in .htaccess

4. **Disable Debug Mode:**
   - Set `define('DEBUG', false);` in production
   - Don't show errors to users

5. **Regular Backups:**
   - Download database weekly
   - Download uploaded files monthly
   - Keep both locally

---

## 📞 Support Contacts

### TrueHost Support
- **Email:** support@truehost.ng
- **Chat:** In cPanel
- **Response Time:** Usually 1-2 hours (business hours)

### HostNigeria Support
- **Email:** support@hostinginigeria.com
- **Ticket System:** In control panel

### InfinityFree Support
- **Forum:** Community-based
- **Email:** Response time can be slow (days)

---

## 🎯 Final Recommendation

**For Production Launch:** 🚀 **TrueHost**
- Best for serious projects
- Reliable support
- All features supported
- Worth the investment
- ~₦3,000-5,000/month

**For Learning/Testing:** 🎓 **InfinityFree**
- Free to test
- Quick setup
- Limited features but good for learning
- Not for production

**For Budget Production:** 💰 **HostNigeria**
- Good middle ground
- Affordable
- Reliable
- All features work

---

## Deployment Checklist

- [ ] Choose hosting platform
- [ ] Register domain
- [ ] Buy hosting + domain
- [ ] Access cPanel/Admin panel
- [ ] Create MySQL database
- [ ] Upload files via FTP/File Manager
- [ ] Import MASTER_SETUP.sql
- [ ] Update config files with real credentials
- [ ] Test admin login
- [ ] Configure Paystack (live keys)
- [ ] Set up SMTP email
- [ ] Configure cron job
- [ ] Enable HTTPS/SSL
- [ ] Change admin password
- [ ] Backup database + files locally
- [ ] Test all features
- [ ] Launch! 🚀

---

**Need help?** Refer to `SETUP_GUIDE.md` for detailed step-by-step instructions.