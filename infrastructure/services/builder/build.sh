#!/bin/bash
set -e

if [ "$PRODUCTION" ]; then
  echo "Building for production"
  # We create var directory used by Symfony
  mkdir -m 777 -p var
  # We install vendors with composer
  # We don't use `--no-scripts` or `--no-plugins` because a script in a composer plugin
  # will generate the file vendor/ocramius/package-versions/src/PackageVersions/Versions.php
  composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --ignore-platform-reqs --no-progress
  # We build bootstrap.php.cache in the `var` directory
  php vendor/sensio/distribution-bundle/Resources/bin/build_bootstrap.php var

  # Frontend deps
  yarn install --pure-lockfile
  bower install --config.interactive=false --allow-root
  yarn run build-relay-schema
  yarn run build:prod

  # Server side rendering deps
  yarn run build-server-bundle:prod
else
  echo "Building for development/testing"
  # Symfony deps
  if [ -n "CI" ]; then
      composer install --prefer-dist --no-interaction --ignore-platform-reqs --no-suggest --no-progress
  else
      composer install --prefer-dist --no-interaction --ignore-platform-reqs
  fi
  composer dump-autoload

  # Frontend deps
  yarn install --pure-lockfile
  bower install --config.interactive=false
  yarn run build-relay-schema

  echo "Testing node-sass binding..."
  if ./node_modules/node-sass/bin/node-sass >/dev/null 2>&1 | grep --quiet `npm rebuild node-sass` >/dev/null 2>&1; then
      echo "Building node-sass binding for the container..."
      npm rebuild node-sass > /dev/null
  fi
  echo "Binding ready!"
  yarn run build

  # Server side rendering deps
  yarn run build-server-bundle
fi
