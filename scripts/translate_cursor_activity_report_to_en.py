"""
Thin wrapper for the historical activity-report path.

Prefer the general tool (offline Argos by default, optional Google batching):

  python scripts/bulk_translate_file.py ^
    -i FUNC_IMPROVE/CURSOR_AND_GIT_ACTIVITY_REPORT_2026-04-01_TO_2026-05-14.md ^
    -s vi -t en --engine argos --mode md-text-fences
"""

from __future__ import annotations

import subprocess
import sys
from pathlib import Path

REPORT = Path(__file__).resolve().parent.parent / "FUNC_IMPROVE" / "CURSOR_AND_GIT_ACTIVITY_REPORT_2026-04-01_TO_2026-05-14.md"


def main() -> int:
    bulk = Path(__file__).resolve().parent / "bulk_translate_file.py"
    cmd = [
        sys.executable,
        str(bulk),
        "-i",
        str(REPORT),
        "-s",
        "vi",
        "-t",
        "en",
        "--engine",
        "argos",
        "--mode",
        "md-text-fences",
    ]
    return subprocess.call(cmd)


if __name__ == "__main__":
    raise SystemExit(main())
