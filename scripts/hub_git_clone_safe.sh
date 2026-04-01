#!/usr/bin/env bash
# Run ON hub server AFTER deploy key is added to GitHub.
# Clones repo next to live app, copies .env + storage from live — does NOT switch nginx or touch DB.
set -eu
REPO="git@github.com:CRAVEVA/craveva-hub-server.git"
BRANCH="${1:-main}"
LIVE="/var/www/hub.craveva.com"
WORKDIR="/var/www/hub.craveva.com.git-src"

if ! ssh -o BatchMode=yes -T git@github.com 2>&1 | grep -q "successfully authenticated"; then
  echo "ERROR: GitHub SSH not working. Add deploy key: cat ~/.ssh/id_ed25519_github_craveva_hub.pub"
  ssh -o BatchMode=yes -T git@github.com 2>&1 || true
  exit 1
fi

if [ -e "$WORKDIR" ] && [ ! -d "$WORKDIR/.git" ]; then
  echo "ERROR: $WORKDIR exists but is not a git repo. sudo rm -rf it (after backup) or pick another WRKDIR."
  exit 1
fi
if [ -d "$WORKDIR/.git" ]; then
  echo "WORKDIR already a git repo: $WORKDIR — fetching/pulling"
  git -C "$WORKDIR" fetch origin
  git -C "$WORKDIR" pull --ff-only "origin" "$BRANCH"
else
  sudo mkdir -p "$WORKDIR"
  sudo chown "$(id -un):www-data" "$WORKDIR"
  git clone --branch "$BRANCH" "$REPO" "$WORKDIR"
fi

cp -a "$LIVE/.env" "$WORKDIR/.env"
rsync -a "$LIVE/storage/" "$WORKDIR/storage/"
sudo chown -R www-data:www-data "$WORKDIR"
echo ""
echo "DONE. Live site unchanged: nginx still uses $LIVE/public"
echo "Verify: cd $WORKDIR && php artisan --version"
echo "When ready to cut over: backup nginx, point root to $WORKDIR/public, reload nginx"
