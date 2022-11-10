#!/usr/bin/env bash

yarn
yarn compile

exec "$@"
