FROM php:8.0-cli
RUN docker-php-ext-install pdo pdo_mysql mysqli
WORKDIR /app
COPY src/ /app
EXPOSE 80
CMD ["php", "-S", "0.0.0.0:80", "-t", "/app"]
