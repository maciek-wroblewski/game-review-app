# Game Review App
A simple web application for reviewing, rating and browsing various games


For development on windows/mac use laravel herd. Site will run in either .test domain.
For development on linux use "docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs"
    to build the container then "./vendor/bin/sail up -d" inside project folder. Site will run in localhost.
