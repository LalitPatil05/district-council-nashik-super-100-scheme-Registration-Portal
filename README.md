### a comprehensive and production ready Online Registration System for the Two years of free residential coaching for NEET and JEE / Advance exams for Scheduled Caste (SC), Scheduled Tribe (ST) and Other category students from rural areas of Nashik district.

# Scheme Portal Setup

## 1) Database
- Create database `scheme` (or change name in config).
- Import schema from `sql/schema.sql`.

## 2) Composer (PDF)
From the `scheme-app` folder:

```bash
composer install
```

If `composer` is not found:
- Install Composer globally.
- Or use: `php composer.phar install`

PDF uses FPDF (no `dom` extension required).

## 3) Permissions (Uploads + PDF)
Make sure Apache can write to `storage`:

```bash
chmod -R 775 storage
```

If still failing, set ownership to the web server user (e.g., `www-data`).

## 4) PHP Upload Limits
Check `php.ini` settings:
- `file_uploads = On`
- `upload_max_filesize = 5M`
- `post_max_size = 6M`

Restart Apache after changes.

## 5) Run
Open in browser:
`http://localhost/supernashik/scheme-app/public/`

## 6) Officer Account
Create any user, then update role in DB:

```sql
UPDATE users SET role = 'officer' WHERE email = 'officer@example.com';
```
