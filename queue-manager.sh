#!/bin/bash
# Laravel Queue Worker Management Script
# Usage: ./queue-manager.sh [start|stop|restart|status|logs]

case "$1" in
    start)
        echo "Starting Supervisor and queue workers..."
        sudo systemctl start supervisord
        sudo supervisorctl start laravel-worker-tour:*
        echo "Queue workers started!"
        ;;
    stop)
        echo "Stopping queue workers..."
        sudo supervisorctl stop laravel-worker-tour:*
        echo "Queue workers stopped!"
        ;;
    restart)
        echo "Restarting queue workers..."
        sudo supervisorctl restart laravel-worker-tour:*
        echo "Queue workers restarted!"
        ;;
    status)
        echo "=== Supervisor Status ==="
        sudo systemctl status supervisord --no-pager | head -10
        echo ""
        echo "=== Queue Workers Status ==="
        sudo supervisorctl status
        echo ""
        echo "=== Running Queue Processes ==="
        ps aux | grep "queue:work" | grep -v grep
        ;;
    logs)
        echo "=== Queue Worker Logs (last 50 lines) ==="
        tail -50 /var/www/html/bk.proppikglobal.in/brokerx/storage/logs/worker-tour.log 2>/dev/null || echo "No logs yet"
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|status|logs}"
        exit 1
        ;;
esac

exit 0

