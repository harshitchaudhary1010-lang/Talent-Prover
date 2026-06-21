# TalentProve Setup Instructions

## Quick Start (Local XAMPP)

### Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** service (click "Start" button)
3. Start **MySQL** service (click "Start" button)
4. Wait for both to show "Running" status

### Step 2: Access Your Website
Once services are running, open your browser and go to:
```
http://localhost/
```

If you see errors, try:
```
http://localhost/index.php
```

### Step 3: Database Setup
The database will be created automatically on first visit. If you see a database error:

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a database named: `talentprover`
3. Import the file: `database.sql` or `talentprove.sql`

## Common Issues and Fixes

### Issue 1: "404 Not Found" Error
**Solution:** Make sure `.htaccess` file exists in `c:\xampp2.0\htdocs\`

If still not working:
- Check if `mod_rewrite` is enabled in Apache
- Go to `c:\xampp\apache\conf\httpd.conf`
- Find line: `#LoadModule rewrite_module modules/mod_rewrite.so`
- Remove the `#` to uncomment it
- Restart Apache

### Issue 2: Apache Won't Start
**Solution 1:** Port 80 is being used by another program
- Open XAMPP Config for Apache
- Change port from 80 to 8080
- Access site at: `http://localhost:8080/`

**Solution 2:** Skype or World Wide Web Publishing Service conflict
- Stop Skype or disable World Wide Web Publishing Service
- Restart Apache

### Issue 3: MySQL Won't Start
**Solution:** Port 3306 is being used
- Change MySQL port in XAMPP config
- Update `config/db.php` to use new port:
  ```php
  define('DB_HOST', 'localhost:3307'); // if using port 3307
  ```

### Issue 4: "Database connection failed"
**Solution:**
1. Make sure MySQL is running in XAMPP
2. Check credentials in `config/db.php`:
   - Default host: `localhost`
   - Default user: `root`
   - Default password: `` (empty)
   - Database name: `talentprover`

### Issue 5: Blank Page or PHP Not Working
**Solution:**
- Make sure you're accessing through `http://localhost/` not just opening the file
- Check if PHP is enabled in Apache (it should be by default)
- Check `php.ini` file and ensure `display_errors = On` for debugging

## Accessing from Web Hosting (InfinityFree, etc.)

### For InfinityFree or Similar Services:

1. **Upload Files:**
   - Upload ALL files from `c:\xampp2.0\htdocs\` to your `htdocs` folder on the server
   - Make sure `.htaccess` is uploaded

2. **Update Database Config:**
   - Edit `config/db.php` with hosting database details:
   ```php
   define('DB_HOST', 'your-db-host');     // Usually provided by host
   define('DB_NAME', 'your-db-name');     // Your database name
   define('DB_USER', 'your-db-username'); // Your database user
   define('DB_PASS', 'your-db-password'); // Your database password
   ```

3. **Import Database:**
   - Go to hosting control panel → phpMyAdmin
   - Create a database
   - Import `database.sql` or `talentprove.sql`

4. **Check .htaccess:**
   - Make sure `.htaccess` file is present in root directory
   - Some hosts don't show hidden files - enable "Show hidden files"

5. **Set Permissions:**
   - `storage/sessions/` folder: 755 or 775
   - `assets/uploads/` folder: 755 or 775

## File Structure
```
htdocs/
├── .htaccess               (Important!)
├── index.php              (Main landing page)
├── database.sql           (Database schema)
├── config/
│   ├── db.php             (Database connection)
│   └── session.php        (Session handling)
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── Dashboard/
├── api/
├── assets/
└── storage/
    └── sessions/
```

## Testing Your Setup

1. Visit: `http://localhost/`
   - Should show TalentProve homepage

2. Try registering: `http://localhost/auth/register.php`
   - Should show registration form

3. Check database connection:
   - Homepage should load without errors
   - If database errors appear, check MySQL is running

## Need Help?

### Check Apache Error Logs:
- Location: `c:\xampp\apache\logs\error.log`
- Look for recent errors

### Check PHP Errors:
- Enable in `php.ini`: `display_errors = On`
- Restart Apache after changes

### Verify Services:
```cmd
netstat -ano | findstr ":80"    (Check if Apache is on port 80)
netstat -ano | findstr ":3306"  (Check if MySQL is on port 3306)
```

## Default Login Credentials
After importing database, you may have:
- **Admin:** Check `database.sql` for default admin account
- **Or create new account:** Use registration page

---
**Note:** Always use `http://localhost/` when testing locally, NOT `http://127.0.0.1/` or file paths!
