#!/usr/bin/env bash
# Chay test local — giam token khi lam viec voi AI.
# Usage: ./scripts/test.sh [full|phase1|unit|feature|file PATH|filter NAME]
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ ! -f vendor/bin/pest ]]; then
  echo "Chua co vendor. Chay: composer install" >&2
  exit 1
fi

MODE="${1:-full}"
EXTRA="${2:-}"
ARGS=(test --compact)

case "${MODE,,}" in
  phase1)
    ARGS+=(--filter=Estimate)
    echo ">>> Phase 1 / Estimate tests"
    ;;
  unit)
    ARGS+=(tests/Unit)
    echo ">>> tests/Unit"
    ;;
  feature)
    ARGS+=(tests/Feature)
    echo ">>> tests/Feature"
    ;;
  file)
    [[ -n "$EXTRA" ]] || { echo "Usage: $0 file EstimateSubmitForReviewTest.php"; exit 1; }
    if [[ "$EXTRA" == tests/* ]]; then
      ARGS+=("$EXTRA")
    elif [[ -f "tests/Feature/$EXTRA" ]]; then
      ARGS+=("tests/Feature/$EXTRA")
    else
      ARGS+=("tests/Unit/$EXTRA")
    fi
    echo ">>> ${ARGS[-1]}"
    ;;
  filter)
    [[ -n "$EXTRA" ]] || { echo "Usage: $0 filter 'vp margin'"; exit 1; }
    ARGS+=(--filter="$EXTRA")
    echo ">>> filter: $EXTRA"
    ;;
  full)
    echo ">>> Full suite"
    ;;
  *)
    echo "Modes: full | phase1 | unit | feature | file <path> | filter <name>" >&2
    exit 1
    ;;
esac

START=$(date +%s)
php artisan "${ARGS[@]}"
CODE=$?
END=$(date +%s)
echo ""
if [[ $CODE -eq 0 ]]; then
  echo "PASS ($((END - START))s)"
else
  echo "FAIL ($((END - START))s) — chi gui agent doan FAILED"
fi
exit $CODE
