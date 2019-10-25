FROM php:7.1-cli-alpine3.8

LABEL maintainer="funsoul <https://github.com/funsoul/>"

ENV USER=www \
    UID=1000 \
    GID=1000 \
    SWOOLE_VERSION=4.4.8 \
    COMPOSER_VERSION=1.8.6

RUN addgroup --gid "$GID" "$USER" \
  && adduser \
  --disabled-password \
  --gecos "" \
  --home "$(pwd)" \
  --ingroup "$USER" \
  --no-create-home \
  --uid "$UID" \
  "$USER"

RUN echo -e "http://mirrors.ustc.edu.cn/alpine/v3.8/main\nhttp://mirrors.ustc.edu.cn/alpine/v3.8/community" > /etc/apk/repositories && \
  apk update && \
  apk add tzdata && \
  cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime && \
  echo "Asia/Shanghai" >  /etc/timezone && \
  apk add $PHPIZE_DEPS \
  vim \
  strace \
  tcpdump \
  git \
  gdb \
  lsof \
  apache2-utils

# Swoole
RUN curl -L -o swoole.tar.gz "https://github.com/swoole/swoole-src/archive/v${SWOOLE_VERSION}.tar.gz" \
&& mkdir -p swoole \
&& tar -xf swoole.tar.gz -C swoole --strip-components=1 \
&& rm swoole.tar.gz \
&& ( \
cd swoole \
&& phpize \
&& ./configure \
&& make install \
) \
&& rm -r swoole \
&& docker-php-ext-enable swoole

# Composer
RUN cd /tmp \
    && wget https://github.com/composer/composer/releases/download/${COMPOSER_VERSION}/composer.phar \
    && chmod u+x composer.phar \
    && mv composer.phar /usr/local/bin/composer

COPY . /var/www

WORKDIR /var/www/

RUN composer install --no-dev \
    && composer dump-autoload -o

EXPOSE 9500
EXPOSE 9501

ENTRYPOINT ["sh", "/var/www/entrypoint.sh"]