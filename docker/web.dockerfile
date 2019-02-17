FROM node:10-alpine

USER node

COPY --chown=node:node . /home/node/app

WORKDIR /home/node/app
RUN \
	npm install \
	&& npm run production

FROM nginx:1.14-alpine

COPY ./docker/server.conf /etc/nginx/conf.d/default.conf

COPY --from=0 /home/node/app/public /var/www/html/public
