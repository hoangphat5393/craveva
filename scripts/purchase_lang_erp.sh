#!/usr/bin/env bash
# Purchase module ERP wording — LanguagePack audit/apply + optional sync/publish.
#
#   ./scripts/purchase_lang_erp.sh
#   ./scripts/purchase_lang_erp.sh --apply --publish
#   ./scripts/purchase_lang_erp.sh --apply --sync-keys --publish
#
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

APPLY=0
SYNC_KEYS=0
PUBLISH=0
PATTERNS_ONLY=0
LOCALE="all"
CSV=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --apply) APPLY=1 ;;
    --sync-keys) SYNC_KEYS=1 ;;
    --publish) PUBLISH=1 ;;
    --patterns-only) PATTERNS_ONLY=1 ;;
    --locale=*) LOCALE="${1#*=}" ;;
    --csv=*) CSV="${1#*=}" ;;
    -h|--help)
      echo "Usage: $0 [--apply] [--sync-keys] [--publish] [--patterns-only] [--locale=vi|en|all] [--csv=path]"
      exit 0
      ;;
    *) echo "Unknown option: $1" >&2; exit 1 ;;
  esac
  shift
done

ARGS=()
[[ $APPLY -eq 1 ]] && ARGS+=(--apply)
[[ $PATTERNS_ONLY -eq 1 ]] && ARGS+=(--patterns-only)
[[ "$LOCALE" != "all" ]] && ARGS+=(--locale="$LOCALE")
[[ -n "$CSV" ]] && ARGS+=(--csv="$CSV")

echo "=== Purchase LanguagePack ERP wording ==="
php scripts/audit_purchase_lang.php "${ARGS[@]}"

if [[ $SYNC_KEYS -eq 1 ]]; then
  echo ""
  echo "=== languagepack:sync-keys (Modules/Purchase) ==="
  php artisan languagepack:sync-keys --paths=Modules/Purchase --no-interaction
fi

if [[ $PUBLISH -eq 1 ]]; then
  echo ""
  echo "=== languagepack:publish-translation ==="
  php artisan languagepack:publish-translation --no-interaction
fi

if [[ $APPLY -eq 0 && $PUBLISH -eq 0 ]]; then
  echo ""
  echo "Tip: --apply to write glossary; --publish to push LanguagePack to runtime lang files."
fi
