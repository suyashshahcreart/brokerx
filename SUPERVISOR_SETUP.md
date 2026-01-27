# Supervisor Queue Worker Setup Guide

## ✅ Setup Complete!

Supervisor has been successfully installed and configured to run Laravel queue workers in the background.

## 📋 Configuration Details

### Supervisor Configuration
- **Config File**: `/etc/supervisor/conf.d/laravel-worker-tour.conf`
- **Main Config**: `/etc/supervisor/supervisord.conf`
- **Systemd Service**: `/etc/systemd/system/supervisord.service`
- **Log File**: `/var/www/html/bk.proppikglobal.in/brokerx/storage/logs/worker-tour.log`

### Queue Worker Settings
- **Queue Name**: `tour-processing`
- **Number of Workers**: 2 processes
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
# Check worker status
sudo supervisorctl status

# Start all workers
sudo supervisorctl start laravel-worker-tour:*

# Stop all workers
sudo supervisorctl stop laravel-worker-tour:*

# Restart all workers
sudo supervisorctl restart laravel-worker-tour:*

# Reload configuration
sudo supervisorctl reread
sudo supervisorctl update
```

### Using Management Script
```bash
# Navigate to project directory
cd /var/www/html/bk.proppikglobal.in/brokerx

# Check status
./queue-manager.sh status

# View logs
./queue-manager.sh logs

# Restart workers
./queue-manager.sh restart
```

## 📊 Monitoring

### Check Queue Status
```bash
# View running processes
ps aux | grep "queue:work"

# View supervisor status
sudo supervisorctl status

# View logs
tail -f /var/www/html/bk.proppikglobal.in/brokerx/storage/logs/worker-tour.log
```

### Laravel Queue Commands
```bash
cd /var/www/html/bk.proppikglobal.in/brokerx

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush

# View queue jobs
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
# Fix log directory permissions
sudo chown -R ec2-user:ec2-user /var/www/html/bk.proppikglobal.in/brokerx/storage/logs
sudo chmod -R 775 /var/www/html/bk.proppikglobal.in/brokerx/storage/logs
```

## 📝 Configuration Files

### Worker Configuration
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
# Check Supervisor status
sudo supervisorctl status

# Should show:
# laravel-worker-tour:laravel-worker-tour_00   RUNNING
# laravel-worker-tour:laravel-worker-tour_01   RUNNING
```

## 🎯 What Happens Now?

1. **File Uploads > 75MB**: Automatically use chunked upload
2. **Background Processing**: ZIP files are processed in background via queue
3. **Auto-Restart**: Workers automatically restart if they crash
4. **Logging**: All worker activity is logged to `storage/logs/worker-tour.log`
5. **Auto-Start**: Workers start automatically on server reboot

Your tour file upload system is now fully configured with background processing! 🚀


