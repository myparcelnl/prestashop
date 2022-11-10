ARG PHP_VERSION=7.2
ARG ALPINE_VERSION=""

FROM ghcr.io/myparcelnl/php-xd:${PHP_VERSION}-alpine${ALPINE_VERSION} AS build

ARG NODE_VERSION=18.0.0

RUN apk update \
   && apk add --no-cache \
      nodejs>${NODE_VERSION} \
      npm \
    && npm install -g yarn

CMD ["sh", "/app/private/docker/entrypoint.sh"]
