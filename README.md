# cloudflare-backup-script

Back up all Cloudflare DNS zones as BIND export files and commit them to a Git repository.

This project includes:
- `dns-export.php`: Calls the Cloudflare API and writes one `*.txt` file per zone into `exports/`
- `export-dns.sh`: Runs the export, then commits and pushes changes from `exports/`

## Prerequisites

- PHP CLI with cURL enabled
- Git installed and configured (`user.name`, `user.email`, remote auth)
- A Cloudflare API token with permission to export DNS records
- A Git repository initialized inside `exports/` for committing the exported zone records

## 1) Clone this project

```bash
git clone <your-repo-url>
cd cloudflare-backup-script
```

## 2) Create a Cloudflare API token

In Cloudflare, create a token that has at least:
- `Zone DNS: Read`

The script uses `GET /zones/:zone_id/dns_records/export`, so the token must be able to read zone DNS data for all zones you want to back up.

Also note:
- You must provide your Cloudflare account email in the script
- Keep the token secret; do not commit it

## 3) Configure credentials

Open `dns-export.php` and update:

```php
$apitoken         = 'ENTER_API_TOKEN_FROM_CLOUDFLARE_HERE';
$cf_account_email = 'ENTER_YOUR_CLOUDFLARE_EMAIL_HERE';
```

Replace with your real values.

## 4) Initialize Git inside `exports/`

The `export-dns.sh` script commits from `exports/`, so that folder must be its own git repo.

```bash
cd exports
git init
git branch -M main
git remote add origin <your-exports-repo-url>
cd ..
```

If `exports/` is already a repo, just verify:

```bash
cd exports
git remote -v
cd ..
```

## 5) Update `export-dns.sh` paths

Edit `export-dns.sh` and replace placeholder paths:

```bash
#!/usr/bin/bash

php /path/to/exporter/directory/dns-export.php
cd /path/to/exporter/directory/exports/
git add -A
git commit -m "update"
git push -u origin main
```

You should change:
- PHP binary path (first line command) if needed based on your hosting, for example `/usr/bin/php`
- Project path (both `/path/to/exporter/directory/...` values)

Optional improvement:
- Change `git push -u origin main` to `git push origin main` after the first successful push

## 6) Test manually

From the project root:

```bash
php dns-export.php
```

Then confirm files were created:

```bash
ls exports/
```

Run the full flow:

```bash
bash export-dns.sh
```

## 7) Schedule periodic backups (cron)

Example: run every hour

```cron
0 * * * * /path/to/exporter/directory/export-dns.sh >> /path/to/exporter/directory/cron.log 2>&1
```

## Notes

- `dns-export.php` is CLI-only and will refuse web access.
- Errors are logged to `error.log` in the project root.
- If no files are changing, `git commit` may report "nothing to commit".
- Make sure your token has access to every zone you want exported.

## Security recommendations

- Do not commit API tokens to source control.
- Consider moving credentials to environment variables or a local config file excluded by `.gitignore`.

