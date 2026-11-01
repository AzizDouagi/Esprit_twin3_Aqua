#!/bin/sh
set -eu
cd "$(dirname "$0")/.."
if [ -z "${PUPPETEER_EXECUTABLE_PATH:-}" ] && [ -x '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' ]; then
    export PUPPETEER_EXECUTABLE_PATH='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'
fi
for source in docs/diagrams/*.mmd; do
    mmdc -i "$source" -o "${source%.mmd}.svg" -b white
    mmdc -i "$source" -o "${source%.mmd}.png" -b white -s 2
done
