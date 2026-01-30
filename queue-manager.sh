#!/bin/bash
# Laravel Queue Worker Management Script
# Auto-detects environment based on current directory or APP_URL in .env
# Usage: ./queue-manager.sh [start|stop|restart|status|logs]

# Auto-detect environment based on current directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Detect environment and set variables
if [[ "$SCRIPT_DIR" == *"dev.proppik.in"* ]]; then
    WORKER_NAME="laravel-worker-dev-proppik"
    LOG_FILE="/var/www/html/dev.proppik.in/public_html/storage/logs/worker-tour.log"
elif [[ "$SCRIPT_DIR" == *"bk.proppikglobal.in"* ]]; then
    WORKER_NAME="laravel-worker-tour"
    LOG_FILE="/var/www/html/bk.proppikglobal.in/brokerx/storage/logs/worker-tour.log"
else
    # Fallback: try to detect from APP_URL in .env if available
    if [ -f "$SCRIPT_DIR/.env" ]; then
        APP_URL=$(grep "^APP_URL=" "$SCRIPT_DIR/.env" | cut -d '=' -f2 | tr -d '"' | tr -d "'")
        if [[ "$APP_URL" == *"dev.proppik.in"* ]]; then
            WORKER_NAME="laravel-worker-dev-proppik"
            LOG_FILE="$SCRIPT_DIR/storage/logs/worker-tour.log"
        elif [[ "$APP_URL" == *"bk.proppikglobal.in"* ]]; then
            WORKER_NAME="laravel-worker-tour"
            LOG_FILE="$SCRIPT_DIR/storage/logs/worker-tour.log"
        else
            echo "Error: Could not detect environment. Please set WORKER_NAME manually."
            exit 1
        fi
    else
        echo "Error: Could not detect environment. Please set WORKER_NAME manually."
        exit 1
    fi
fi

case "$1" in
    start)
        echo "Starting Supervisor and queue workers..."
        echo "Environment detected: $WORKER_NAME"
        sudo systemctl start supervisord
        sudo supervisorctl start ${WORKER_NAME}:*
        echo "Queue workers started!"
        ;;
    stop)
        echo "Stopping queue workers..."
        echo "Environment detected: $WORKER_NAME"
        sudo supervisorctl stop ${WORKER_NAME}:*
        echo "Queue workers stopped!"
        ;;
    restart)
        echo "Restarting queue workers..."
        echo "Environment detected: $WORKER_NAME"
        sudo supervisorctl restart ${WORKER_NAME}:*
        echo "Queue workers restarted!"
        ;;
    status)
        echo "=== Environment: $WORKER_NAME ==="
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
        echo "Log file: $LOG_FILE"
        tail -50 "$LOG_FILE" 2>/dev/null || echo "No logs yet"
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|status|logs}"
        echo ""
        echo "Detected environment: $WORKER_NAME"
        echo "Log file: $LOG_FILE"
        exit 1
        ;;
esac

exit 0
