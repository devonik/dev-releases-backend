FROM dragoono/laravel-craftable:1.5

WORKDIR /app
COPY . /app

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader

# Copy hello-cron file to the cron.d directory
COPY docker-cron /etc/cron.d/docker-cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/docker-cron

# Apply cron job
RUN crontab /etc/cron.d/docker-cron

# Create the log file to be able to run tail
RUN touch /var/log/cron.log

CMD cron && php artisan serve --host=0.0.0.0 --port=80

EXPOSE 80
