#!/usr/bin/env bash
set -euo pipefail
: "${GITHUB_TOKEN:?Set GITHUB_TOKEN}"
LIVE="/var/www/hub.craveva.com"
WRK="/var/www/hub.craveva.com.git-src"
BRANCH="${1:-main}"
if [ -e "$WRK" ] && [ ! -d "$WRK/.git" ]; then
  echo "ERROR: $WRK exists but is not a git repo."
  exit 1
fi
if [ -d "$WRK/.git" ]; then
  echo "Repo exists, pulling branch $BRANCH"
  git -C "$WRK" fetch origin
  git -C "$WRK" pull --ff-only "origin" "$BRANCH"
else
  sudo mkdir -p "$WRK"
  sudo chown "$(id -un):www-data" "$WRK"
  git clone --branch "$BRANCH" "https://x-access-token:${GITHUB_TOKEN}@github.com/CRAVEVA/craveva-hub-server.git" "$WRK"
fi
cp -a "$LIVE/.env" "$WRK/.env"
rsync -a "$LIVE/storage/" "$WRK/storage/"
sudo chown -R www-data:www-data "$WRK"
git -C "$WRK" remote set-url origin "https://github.com/CRAVEVA/craveva-hub-server.git"
echo "--- remote ---"
git -C "$WRK" remote -v
git -C "$WRK" status -sb
echo "DONE: $WRK"
