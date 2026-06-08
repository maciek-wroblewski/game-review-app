# Game Review App
A simple web application for reviewing, rating and browsing various games


For development use docker:

1. Find and enable Docker service and socket if not running on docker desktop (Linux/systemd)
```
sudo usermod -aG docker $USER
systemctl start docker.service docker.socket
```

2. Copy the example environment file

```
cp .env.example .env
```

3. Install Composer packages via a temporary Docker container (so you get the 'sail' executable)
```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

4. Build the Docker/Sail images
```
./vendor/bin/sail build
```

5. Start the Sail environment in the background to run the rest of the commands
```
./vendor/bin/sail up -d
```

6. Generate the application encryption key
```
./vendor/bin/sail artisan key:generate
```

7. Link the storage directory
```
./vendor/bin/sail artisan storage:link
```

8. Run database migrations and seed the database
```
./vendor/bin/sail artisan migrate:fresh --seed
```

9. Install Node.js/NPM packages and build frontend assets
```
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```
