# TalentProve - Task-Based Hiring Platform

A modern hiring platform where companies post practical tasks and students prove their skills through real work submissions.

## 🚀 Quick Start

### Method 1: Automated Setup (Recommended)
1. Double-click `START_XAMPP.bat` in this folder
2. Wait for services to start
3. Browser will open automatically to system check page

### Method 2: Manual Setup
1. Open XAMPP Control Panel
2. Start Apache and MySQL
3. Open browser and visit: `http://localhost/test.php`
4. Follow the on-screen instructions

### First Time Setup
After starting services, visit these URLs in order:
1. **System Check**: `http://localhost/check.php` - Verify everything is working
2. **Test Page**: `http://localhost/test.php` - Quick diagnostics
3. **Homepage**: `http://localhost/` - Main site (database auto-setup)
4. **Register**: `http://localhost/auth/register.php` - Create your account

## 🔧 Troubleshooting

### ❌ Getting 404 Errors?
- Make sure Apache is running (green in XAMPP)
- Check that `.htaccess` file exists in root folder
- Enable mod_rewrite in Apache (see SETUP_INSTRUCTIONS.md)
- Try accessing: `http://localhost/index.php` directly

### ❌ Database Connection Failed?
- Make sure MySQL is running (green in XAMPP)
- Default credentials: host=localhost, user=root, password=(empty)
- Database name should be: `talentprover`
- Visit homepage to auto-create database

### ❌ Blank White Page?
- You're opening the file directly (wrong!)
- Use: `http://localhost/` not `file:///C:/xampp2.0/...`
- Make sure Apache is running first

### ❌ Apache Won't Start?
- Port 80 is already in use
- Solution: Change Apache port to 8080, then use `http://localhost:8080/`
- Or stop Skype / IIS / other services using port 80

## 📁 Important Files

- `START_XAMPP.bat` - Quick start script (Windows)
- `check.php` - Complete system diagnostics
- `test.php` - Quick PHP and routing test
- `SETUP_INSTRUCTIONS.md` - Detailed setup guide
- `.htaccess` - URL rewriting configuration (must exist!)
- `config/db.php` - Database credentials

## ✨ Features

- **For Students**: Browse tasks, submit work, track applications
- **For Companies**: Post tasks, review submissions, shortlist candidates
- **Real-time Messaging**: Direct communication between companies and candidates
- **AI-Powered Matching**: Smart task recommendations based on skills
- **Portfolio Building**: Showcase completed work and skills

## 🛠️ Tech Stack

- PHP 8.4+ (works with 7.4+)
- MySQL/MariaDB
- Vanilla JavaScript
- Tailwind CSS (CDN)
- PDO for database operations

## 📂 Directory Structure

```
├── .htaccess              # URL rewriting (IMPORTANT!)
├── index.php              # Landing page
├── test.php               # Quick test page
├── check.php              # System diagnostics
├── START_XAMPP.bat        # Quick start script
├── SETUP_INSTRUCTIONS.md  # Detailed guide
├── database.sql           # Database schema
├── api/                   # API endpoints
├── assets/                # CSS, JS, images
├── auth/                  # Authentication pages
├── config/                # Configuration files
│   ├── db.php            # Database config
│   └── session.php       # Session management
├── Dashboard/             # User dashboards
└── storage/               # Session and file storage
```

## 💾 Database Setup

The database is created automatically on first visit. Manual setup:
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database: `talentprover`
3. Import: `database.sql` or `talentprove.sql`

Tables created:
- users, student_profiles, company_profiles
- tasks, submissions
- messages, notifications
- ai_matches

## 👥 User Roles

- **Student**: Browse and apply to tasks
- **Company**: Post tasks and review submissions  
- **Admin**: Manage all users and content

## 🔒 Security Features

- PDO prepared statements (SQL injection prevention)
- Password hashing with bcrypt
- Session management with custom storage
- Input validation and sanitization
- XSS protection headers
- CSRF token support

## 🌐 Hosting on Live Server

To deploy on InfinityFree or other hosting:
1. Upload all files via FTP
2. Edit `config/db.php` with hosting database credentials
3. Import database through hosting phpMyAdmin
4. Ensure `.htaccess` is uploaded (show hidden files)
5. Set permissions: storage/sessions = 755, assets/uploads = 755

## 📞 Support

**Check these first:**
1. Visit: `http://localhost/check.php` for diagnostics
2. Read: `SETUP_INSTRUCTIONS.md` for detailed help
3. Check Apache error logs: `C:\xampp\apache\logs\error.log`

**Common URLs:**
- Homepage: `http://localhost/`
- System Check: `http://localhost/check.php`
- Test Page: `http://localhost/test.php`
- Login: `http://localhost/auth/login.php`
- Register: `http://localhost/auth/register.php`

## 📄 License

Proprietary - Team Startup

---

**Built by Team Startup** - Garima Chaudhary, Harshit Chaudhary, Siddharth Lama
