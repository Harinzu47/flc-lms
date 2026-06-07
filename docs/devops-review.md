# DevOps & Infrastructure Readiness

## Current Infrastructure State
The application currently uses **Laravel Sail** (Docker Compose) for local development:
* **Services Defined:**
  1. `laravel.test` (PHP 8.5 environment using Sail runtime).
  2. `mysql` (MySQL 8.4 database service).
  3. `redis` (Redis Alpine service, configured but not integrated into the application cache or queue system).
* **Limitations:** The local Sail Docker configuration binds the host directory `.:/var/www/html` to sync changes. It is meant **only for development** and cannot be used in production because it lacks embedded web servers (like Nginx), production asset compilation, and execution caching.

---

## DevOps Gap Analysis & Recommendations

### 1. Production Dockerization
* **Finding:** No production-ready Dockerfile exists in the project root.
* **Impact:** The code cannot be deployed directly to containerized services like AWS ECS, Google Cloud Run, or Kubernetes.
* **Severity:** **Medium-High**
* **Recommendation:** Create a multi-stage `Dockerfile` that installs composer dependencies, compiles assets with Vite, optimizes PHP configuration (`php.ini-production`), and runs an Nginx-PHP-FPM combo.
* **Example Implementation:**
  ```dockerfile
  # --- Stage 1: Build PHP & Node Assets ---
  FROM php:8.3-fpm-alpine AS builder
  WORKDIR /var/www/html
  COPY . .
  RUN apk add --no-cache nodejs npm libpng-dev libzip-dev zip \
      && docker-php-ext-install gd zip pdo_mysql \
      && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
      && composer install --no-dev --optimize-autoloader \
      && npm install && npm run build

  # --- Stage 2: Production Release ---
  FROM php:8.3-fpm-alpine
  WORKDIR /var/www/html
  COPY --from=builder /var/www/html /var/www/html
  RUN apk add --no-cache nginx supervisor \
      && chown -R www-data:www-data storage bootstrap/cache
  COPY docker/nginx.conf /etc/nginx/nginx.conf
  COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
  EXPOSE 80
  CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
  ```

---

### 2. CI/CD Readiness
* **Finding:** No continuous integration or deployment workflows exist.
* **Impact:** Development is prone to regression bugs. Deployments are done manually (e.g. FTP/Git pull on server), which is high risk and causes downtime.
* **Severity:** **Medium**
* **Recommendation:** Set up a GitHub Actions workflow `.github/workflows/ci-cd.yml` to automate testing, security linting, and building production Docker containers.
* **Example workflow:**
  ```yaml
  name: CI Pipeline
  on: [push, pull_request]
  jobs:
    test:
      runs-on: ubuntu-latest
      services:
        mysql:
          image: mysql:8.4
          env:
            MYSQL_ROOT_PASSWORD: password
            MYSQL_DATABASE: laravel_test
          ports:
            - 3306:3306
      steps:
        - uses: actions/checkout@v4
        - name: Set up PHP
          uses: shivammathur/setup-php@v2
          with:
            php-version: '8.3'
            extensions: mbstring, pdo_mysql, bcmath
        - name: Run Tests
          run: |
            cp .env.example .env
            composer install
            php artisan key:generate
            php artisan migrate --env=testing
            ./vendor/bin/phpunit
  ```

---

### 3. Secret and Environment Management
* **Current State:** Plain-text `.env` file stored in production server instances.
* **Impact:** Vulnerable to unauthorized access if a developer commits keys, or if web server configurations accidentally expose the file.
* **Recommendation:** Store secrets in secure storage managers (e.g., HashiCorp Vault, AWS Secrets Manager, or GCP Secret Manager). Load secrets into containers via environment variables at runtime rather than copying physical files.

---

### 4. Monitoring, Logging, and Observability
* **Logging Driver:** Change Laravel's logging driver from `single` to `stderr` or `syslog` inside containers so logs are gathered dynamically by container daemons.
* **APM Tooling:** Integrate **Sentry** for real-time error alerts and **Datadog** or **Prometheus + Grafana** to track request latency, API throughput, and MySQL database performance.

---

### 5. Kubernetes Readiness
* **Plan:** To support high availability, deploy the LMS on a Kubernetes (K8s) cluster.
* **Architecture:**
  * **Ingress Controller (Nginx):** Handles SSL termination and routes public traffic.
  * **Application Deployment:** A replica-set of PHP pods scaling horizontally based on CPU load.
  * **Queue Worker Deployment:** Separate pods running `artisan queue:work` to offload event logic.
  * **State Management:** Sessions and database storage kept outside pods (e.g., AWS RDS for MySQL, ElastiCache for Redis).
