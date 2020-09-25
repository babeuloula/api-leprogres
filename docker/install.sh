#!/usr/bin/env bash

set -e

readonly DOCKER_PATH=$(dirname $(realpath $0))
cd ${DOCKER_PATH};

. ./lib/functions.sh

block_info "Welcome to API LeProgres installer!"

check_requirements
parse_env ".env.dist" ".env"
. ./.env
echo -e "${GREEN}Configuration done!${RESET}" > /dev/tty

block_info "Build & start Docker"
./stop.sh
./start.sh
echo -e "${GREEN}Docker is started with success!${RESET}" > /dev/tty

block_info "Install dependencies"
install_composer "${APP_ENV}"
echo -e "${GREEN}Dependencies installed with success!${RESET}" > /dev/tty

add_host "${HTTP_HOST}"
block_success "API LeProgres is started http://${HTTP_HOST}"
