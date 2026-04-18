// Run this once in a temporary PHP file to get the hash

echo password_hash('YourAdminPassword123!', PASSWORD_BCRYPT);

// Paste the output into your admins table