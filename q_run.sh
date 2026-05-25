echo "Starting queue worker for tour-processing..."
php artisan queue:work --queue=tour-processing