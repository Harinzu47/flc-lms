###############################################
# Stage 1: build frontend assets (Vite)
###############################################

FROM node:20-alpine AS frontend

WORKDIR /build

COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build


###############################################
# Stage 2: nginx
###############################################

FROM nginx:1.28-alpine

COPY public /var/www/html/public

# Asset hasil vite build (manifest.json + compiled css/js) tidak ikut ter-commit
# ke repo, jadi harus dibuild ulang di sini, bukan cuma COPY public mentah-mentah.
COPY --from=frontend /build/public/build /var/www/html/public/build

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
