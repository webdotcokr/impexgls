FROM php:7.4-apache

# 필수 패키지 및 PHP 확장 설치
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql mysqli zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache 모듈 활성화
RUN a2enmod rewrite headers

# 작업 디렉토리 설정
WORKDIR /var/www/html

# 웹사이트 파일 복사
COPY website_backup/ /var/www/html/

# 업로드 디렉토리 생성 및 권한 설정
RUN mkdir -p /var/www/html/admin/uploads \
    && mkdir -p /var/www/html/assets/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/admin/uploads \
    && chmod -R 777 /var/www/html/assets/uploads

# PHP 설정 최적화
RUN { \
    echo 'upload_max_filesize = 50M'; \
    echo 'post_max_size = 50M'; \
    echo 'max_execution_time = 300'; \
    echo 'memory_limit = 256M'; \
    echo 'display_errors = On'; \
    echo 'error_reporting = E_ALL'; \
} > /usr/local/etc/php/conf.d/custom.ini

# 포트 노출
EXPOSE 80

# Apache 실행
CMD ["apache2-foreground"]
