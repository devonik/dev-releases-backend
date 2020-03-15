FROM dragoono/laravel-craftable:1.2

WORKDIR /app
COPY . /app

#Configure Env file with github secrets
RUN chmod +x /setup_env.sh && ./setup_env.sh

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install

RUN apt-get update && apt-get -y install cron

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
