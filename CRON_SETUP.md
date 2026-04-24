# CampusLink Subscription Management Cron Job

This script handles automated subscription expiry reminders and deactivation of expired subscriptions.

## Setup Instructions

### Windows Task Scheduler

1. Open Task Scheduler (search for "Task Scheduler" in Windows)
2. Click "Create Basic Task"
3. Name: "CampusLink Subscription Cron"
4. Description: "Send expiry reminders and deactivate expired vendor subscriptions"
5. Trigger: Daily at 9:00 AM
6. Action: Start a program
7. Program/script: `C:\xampp\php\php.exe`
8. Add arguments: `C:\xampp\htdocs\campuslink\scripts\subscription_cron.php`
9. Start in: `C:\xampp\htdocs\campuslink`

### Linux/Unix Cron Job

Add this line to your crontab (`crontab -e`):

```
0 9 * * * /usr/bin/php /path/to/campuslink/scripts/subscription_cron.php
```

### What the script does:

1. **Expiry Reminders**: Sends email and in-app notifications to vendors 10 days before subscription expiry
2. **Deactivation**: Automatically deactivates vendors whose subscriptions have expired (hides them from browse/category pages)
3. **Notifications**: Sends notifications to vendors when their subscriptions expire

### Configuration

- **Reminder timing**: 10 days before expiry (configurable in the script)
- **Grace period**: Configurable in config/database.php (GRACE_PERIOD_DAYS)
- **Email templates**: Located in config/mailer.php

### Testing

To test the script manually:

```bash
cd /path/to/campuslink
php scripts/subscription_cron.php
```

Check the logs in `logs/` directory for execution details.