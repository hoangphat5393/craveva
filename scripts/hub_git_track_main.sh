#!/usr/bin/env bash
# Gắn hub với origin/main và đồng bộ code (xử lý orphan / "No commits yet on main" sau zip deploy).
# Quy trình: máy dev push lên GitHub → trên hub chỉ cần chạy script này hoặc git pull.
# Nếu "unable to unlink ... Permission denied": code thuộc www-data, chạy một lần:
#   sudo chown -R "$(whoami):$(whoami)" /var/www/hub.craveva.com
#   sudo chown -R www-data:www-data /var/www/hub.craveva.com/storage /var/www/hub.craveva.com/bootstrap/cache
#   sudo chmod -R ug+rwX /var/www/hub.craveva.com/storage /var/www/hub.craveva.com/bootstrap/cache
# Trên server:  HUB_PATH=/var/www/hub.craveva.com bash hub_git_track_main.sh
# Từ Windows:   Get-Content scripts/hub_git_track_main.sh -Raw | ssh craveva-hub-server "bash -se"

set -euo pipefail

HUB_PATH="${HUB_PATH:-/var/www/hub.craveva.com}"
REMOTE="${GIT_REMOTE:-origin}"
BRANCH="${GIT_BRANCH:-main}"
OREF="${REMOTE}/${BRANCH}"

cd "$HUB_PATH"

if [ ! -d .git ]; then
  echo "ERROR: $HUB_PATH không phải git repo."
  exit 1
fi

echo "== Remote =="
git remote -v

echo "== Fetch $REMOTE =="
git fetch "$REMOTE"

if ! git rev-parse -q --verify "${OREF}" >/dev/null; then
  echo "ERROR: Không có ${OREF}."
  git branch -a
  exit 1
fi

NEED_REPAIR=0
if ! git rev-parse -q --verify HEAD >/dev/null 2>&1; then
  NEED_REPAIR=1
  echo "== Repair: không có HEAD =="
elif [ "$(git rev-list --count HEAD 2>/dev/null || echo 0)" -eq 0 ]; then
  NEED_REPAIR=1
  echo "== Repair: nhánh hiện tại 0 commit (orphan / zip + git) =="
fi

if [ "${NEED_REPAIR}" = "1" ]; then
  echo "== git checkout -f -B ${BRANCH} ${OREF} =="
  if ! git checkout -f -B "${BRANCH}" "${OREF}"; then
    echo "ERROR: checkout thất bại (Permission denied?). Chown như trong comment đầu file, rồi chạy lại."
    exit 4
  fi
else
  echo "== Checkout ${BRANCH} =="
  git checkout "${BRANCH}"
fi

git branch --set-upstream-to="${OREF}" "${BRANCH}"

echo "== Pull --ff-only =="
if ! git pull --ff-only; then
  echo "ERROR: pull thất bại — quyền ghi hoặc conflict."
  exit 4
fi

echo "== Xong =="
git status -sb
git log -1 --oneline
