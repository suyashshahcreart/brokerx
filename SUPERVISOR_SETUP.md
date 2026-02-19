# Supervisor Queue Worker Setup Guide

## ✅ Setup Complete!

Supervisor has been successfully installed and configured to run Laravel queue workers in the background for both **Development** and **Production** environments.

## 🌍 Environment Overview

This setup supports two environments:

- **Development**: `dev.proppik.in` 
- **Production**: `bk.proppikglobal.in`

The `queue-manager.sh` script **auto-detects** the environment based on the directory path or `APP_URL` in `.env` file, so the same script works for both environments!

## 📋 Configuration Details

### Development Environment (dev.proppik.in)

- **Supervisor Config**: `/etc/supervisor/conf.d/laravel-worker-dev-proppik.conf`
- **Worker Name**: `laravel-worker-dev-proppik`
- **Project Path**: `/var/www/html/dev.proppik.in/public_html`
- **Log File**: `/var/www/html/dev.proppik.in/public_html/storage/logs/worker-tour.log`

### Production Environment (bk.proppikglobal.in)

- **Supervisor Config**: `/etc/supervisor/conf.d/laravel-worker-tour.conf`
- **Worker Name**: `laravel-worker-tour`
- **Project Path**: `/var/www/html/bk.proppikglobal.in/brokerx`
- **Log File**: `/var/www/html/bk.proppikglobal.in/brokerx/storage/logs/worker-tour.log`

### Queue Worker Settings (Both Environments)

- **Queue Name**: `tour-processing`
- **Number of Workers**: 2 processes per environment
- **User**: `ec2-user`
- **Timeout**: 18000 seconds (5 hours)
- **Max Jobs**: 1000 jobs per worker before restart
- **Max Time**: 3600 seconds (1 hour) per worker before restart
- **Retries**: 2 attempts per job

## 🚀 Management Commands

### Using Systemd (Recommended)

```bash
# Start Supervisor
sudo systemctl start supervisord

# Stop Supervisor
sudo systemctl stop supervisord

# Restart Supervisor
sudo systemctl restart supervisord

# Check Status
sudo systemctl status supervisord

# Enable on Boot
sudo systemctl enable supervisord
```

### Using Supervisorctl

```bash
# Check all workers status (both environments)
sudo supervisorctl status

# Development Environment
sudo supervisorctl start laravel-worker-dev-proppik:*
sudo supervisorctl stop laravel-worker-dev-proppik:*
sudo supervisorctl restart laravel-worker-dev-proppik:*

# Production Environment
sudo supervisorctl start laravel-worker-tour:*
sudo supervisorctl stop laravel-worker-tour:*
sudo supervisorctl restart laravel-worker-tour:*

# Reload configuration
sudo supervisorctl reread
sudo supervisorctl update
```

### Using Management Script (Auto-Detecting)

The `queue-manager.sh` script automatically detects the environment:

#### Development Environment

```bash
# Navigate to development project directory
cd /var/www/html/dev.proppik.in/public_html

# Check status (auto-detects dev environment)
./queue-manager.sh status

# View logs
./queue-manager.sh logs

# Restart workers
./queue-manager.sh restart

# Start workers
./queue-manager.sh start

# Stop workers
./queue-manager.sh stop
```

#### Production Environment

```bash
# Navigate to production project directory
cd /var/www/html/bk.proppikglobal.in/brokerx

# Check status (auto-detects production environment)
./queue-manager.sh status

# View logs
./queue-manager.sh logs

# Restart workers
./queue-manager.sh restart

# Start workers
./queue-manager.sh start

# Stop workers
./queue-manager.sh stop
```

**Note**: The same `queue-manager.sh` script works in both environments! It auto-detects based on:
1. Directory path (checks if path contains `dev.proppik.in` or `bk.proppikglobal.in`)
2. Falls back to reading `APP_URL` from `.env` file if path detection fails

## 📊 Monitoring

### Check Queue Status

```bash
# View all running queue processes
ps aux | grep "queue:work"

# View supervisor status (shows both environments)
sudo supervisorctl status

# View development logs
tail -f /var/www/html/dev.proppik.in/public_html/storage/logs/worker-tour.log

# View production logs
tail -f /var/www/html/bk.proppikglobal.in/brokerx/storage/logs/worker-tour.log
```

### Laravel Queue Commands

#### Development Environment

```bash
cd /var/www/html/dev.proppik.in/public_html

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush

# View queue jobs (test run)
php artisan queue:work --queue=tour-processing --once
```

#### Production Environment

```bash
cd /var/www/html/bk.proppikglobal.in/brokerx

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush

# View queue jobs (test run)
php artisan queue:work --queue=tour-processing --once
```

## 🔧 Troubleshooting

### Workers Not Starting

1. Check Supervisor status:
   ```bash
   sudo systemctl status supervisord
   ```

2. Check Supervisor logs:
   ```bash
   sudo tail -f /var/log/supervisor/supervisord.log
   ```

3. Check worker logs:
   ```bash
   # Development
   tail -f /var/www/html/dev.proppik.in/public_html/storage/logs/worker-tour.log
   
   # Production
   tail -f /var/www/html/bk.proppikglobal.in/brokerx/storage/logs/worker-tour.log
   ```

4. Reload Supervisor:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   ```

### Jobs Not Processing

1. Check if queue connection is set to 'database' in `.env`:
   ```bash
   QUEUE_CONNECTION=database
   ```

2. Make sure jobs table exists:
   ```bash
   php artisan queue:table
   php artisan migrate
   ```

3. Check for failed jobs:
   ```bash
   php artisan queue:failed
   ```

### Permission Issues

If you see permission errors:

```bash
# Development Environment
sudo chown -R ec2-user:ec2-user /var/www/html/dev.proppik.in/public_html/storage/logs
sudo chmod -R 775 /var/www/html/dev.proppik.in/public_html/storage/logs

# Production Environment
sudo chown -R ec2-user:ec2-user /var/www/html/bk.proppikglobal.in/brokerx/storage/logs
sudo chmod -R 775 /var/www/html/bk.proppikglobal.in/brokerx/storage/logs
```

## 📝 Configuration Files

### Development Worker Configuration

The worker configuration is in `/etc/supervisor/conf.d/laravel-worker-dev-proppik.conf`:

```ini
[program:laravel-worker-dev-proppik]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/dev.proppik.in/public_html/artisan queue:work --queue=tour-processing --tries=2 --timeout=18000 --sleep=3 --max-jobs=1000 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=ec2-user
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/dev.proppik.in/public_html/storage/logs/worker-tour.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
```

### Production Worker Configuration

The worker configuration is in `/etc/supervisor/conf.d/laravel-worker-tour.conf`:

```ini
[program:laravel-worker-tour]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/bk.proppikglobal.in/brokerx/artisan queue:work --queue=tour-processing --tries=2 --timeout=18000 --sleep=3 --max-jobs=1000 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=ec2-user
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/bk.proppikglobal.in/brokerx/storage/logs/worker-tour.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
```

### To Modify Configuration

1. Edit the config file:
   ```bash
   # Development
   sudo nano /etc/supervisor/conf.d/laravel-worker-dev-proppik.conf
   
   # Production
   sudo nano /etc/supervisor/conf.d/laravel-worker-tour.conf
   ```

2. Reload Supervisor:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   ```

## ✅ Verification

The setup is complete and workers are running. You can verify by:

```bash
# Check Supervisor status (shows both environments)
sudo supervisorctl status

# Should show:
# laravel-worker-dev-proppik:laravel-worker-dev-proppik_00   RUNNING
# laravel-worker-dev-proppik:laravel-worker-dev-proppik_01   RUNNING
# laravel-worker-tour:laravel-worker-tour_00                 RUNNING
# laravel-worker-tour:laravel-worker-tour_01                 RUNNING
```

## 🎯 What Happens Now?

1. **File Uploads > 75MB**: Automatically use chunked upload
2. **Background Processing**: ZIP files are processed in background via queue
3. **Auto-Restart**: Workers automatically restart if they crash
4. **Logging**: All worker activity is logged to `storage/logs/worker-tour.log` in each environment
5. **Auto-Start**: Workers start automatically on server reboot
6. **Environment-Aware**: The `queue-manager.sh` script automatically detects which environment it's running in

## 📦 Git Management

The `queue-manager.sh` script is **environment-aware** and can be safely tracked in Git:

- ✅ Same file works in both dev and production
- ✅ Auto-detects environment automatically
- ✅ No manual configuration needed
- ✅ Safe to commit to repository

Your tour file upload system is now fully configured with background processing for both environments! 🚀
