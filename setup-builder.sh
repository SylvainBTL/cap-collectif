#!/bin/bash
set -e

echo "=== 1) Installer pipenv + fabric ==="
pip install --upgrade pipenv fabric

echo "=== 2) Créer l'environnement Python du projet ==="
pipenv install

echo "=== 3) Générer .env.local (erreurs Docker ignorées) ==="
pipenv run fab local.app.setup-default-env-vars || true

echo "=== 4) Installer les dépendances front en SEQUENTIEL (workspaces OFF) ==="
export YARN_IGNORE_WORKSPACE=1
export YARN_WORKSPACES=false
export YARN_ENABLE_IMMUTABLE_INSTALLS=false
export YARN_NETWORK_CONCURRENCY=1
export YARN_NETWORK_TIMEOUT=600000

yarn install --pure-lockfile --network-timeout 600000 --network-concurrency 1

echo "=== 5) Générer les schémas Relay (rapide) ==="
yarn build-relay-schema

echo "=== Setup Builder terminé (sans yarn build:prod) ==="
