FROM ubuntu

# Set timezone and environment variables
ENV TZ=America/New_York
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Add source files to docker image
ADD .	/home/websocket

# Update and install dependencies
RUN apt-get -y update \
    && apt-get -y upgrade \
    && apt-get -y install php php-cli php-json php-mbstring git curl unzip

# Install composer
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \

# Install composer packages
RUN cd /home/websocket \
    && composer update

    
EXPOSE 8080

WORKDIR /home/websocket
CMD ["php", "bin/php_ratchet_websocket_benchmark.php"]
