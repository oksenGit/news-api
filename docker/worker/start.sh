#!/bin/bash

cd /var/www

composer install --no-interaction --no-dev --optimize-autoloader

supervisord -c /etc/supervisor/supervisord.conf

tail -f /dev/null

#!/bin/sh
php artisan schedule:work & php artisan queue:work --tries=3 --timeout=300 