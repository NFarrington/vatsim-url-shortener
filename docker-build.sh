#!/bin/bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do
  DIR="$(cd -P "$(dirname "$SOURCE")" >/dev/null 2>&1 && pwd)"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE"
done
DIR="$(cd -P "$(dirname "$SOURCE")" >/dev/null 2>&1 && pwd)"

export DOCKERFILE_PATH="Dockerfile"
export SOURCE_COMMIT=$(git rev-parse --short HEAD)

CACHE_TAG=$(git describe --abbrev=0 --tags)
export CACHE_TAG="${CACHE_TAG//v}"

(export IMAGE_NAME=nfarrington/vats.im-nginx:${CACHE_TAG} TARGET=nginx \
    && echo "Building $IMAGE_NAME" \
    && ./hooks/build)
(export IMAGE_NAME=nfarrington/vats.im-php-fpm:${CACHE_TAG} TARGET=php-fpm \
    && echo "Building $IMAGE_NAME" \
    && ./hooks/build)
