#!/usr/bin/env bash
# Run ON hub server: bash hub_git_setup_remote.sh
set -eu
KEY="$HOME/.ssh/id_ed25519_github_craveva_hub"
if [ ! -f "$KEY" ]; then
  ssh-keygen -t ed25519 -N "" -f "$KEY" -C "hub-craveva-deploy"
  chmod 600 "$KEY"
fi
mkdir -p "$HOME/.ssh"
chmod 700 "$HOME/.ssh"
if ! grep -q "id_ed25519_github_craveva_hub" "$HOME/.ssh/config" 2>/dev/null; then
  {
    echo ""
    echo "# CRAVEVA hub: GitHub deploy key"
    echo "Host github.com"
    echo "  HostName github.com"
    echo "  User git"
    echo "  IdentityFile $KEY"
    echo "  IdentitiesOnly yes"
  } >> "$HOME/.ssh/config"
  chmod 600 "$HOME/.ssh/config"
fi
echo "=== PUBLIC KEY (GitHub: Settings > Deploy keys > Add) ==="
cat "${KEY}.pub"
echo ""
echo "=== GitHub SSH test ==="
ssh -o BatchMode=yes -T git@github.com 2>&1 || true
