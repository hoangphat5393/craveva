"""Legacy second pass (Google) for Vietnamese leftovers in ```text fences.

For new workflows use ``bulk_translate_file.py`` instead, e.g. re-run with
``--engine google`` on the same file, or translate ``md-whole`` once with Argos.

Second pass: translate ```text blocks that still contain Vietnamese."""

from __future__ import annotations

import re
import shutil
import time
from pathlib import Path

from deep_translator import GoogleTranslator

REPORT = Path(__file__).resolve().parent.parent / "FUNC_IMPROVE" / "CURSOR_AND_GIT_ACTIVITY_REPORT_2026-04-01_TO_2026-05-14.md"
TMP = REPORT.with_suffix(".md.tmp2")

VI_CHARS = re.compile(
    r"[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ"
    r"ÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ]"
)


def needs_pass2(block: str) -> bool:
    return bool(VI_CHARS.search(block))


def translate_one(inner: str) -> str:
    translator = GoogleTranslator(source="vi", target="en")
    inner = inner.strip("\n")
    if not inner:
        return inner
    parts: list[str] = []
    for i in range(0, len(inner), 4500):
        chunk = inner[i : i + 4500]
        try:
            parts.append(translator.translate(chunk))
        except Exception:
            parts.append(chunk)
        time.sleep(0.1)
    return "\n".join(parts)


def main() -> None:
    raw = REPORT.read_text(encoding="utf-8")
    pattern = re.compile(r"```text\n(.*?)```", re.DOTALL)
    matches = list(pattern.finditer(raw))
    originals = [m.group(1) for m in matches]
    to_fix = [i for i, b in enumerate(originals) if needs_pass2(b)]
    print(f"Pass2: fixing {len(to_fix)} blocks", flush=True)
    results = list(originals)
    for n, i in enumerate(to_fix):
        results[i] = translate_one(results[i])
        if (n + 1) % 10 == 0:
            print(f"  {n + 1}/{len(to_fix)}", flush=True)
    out: list[str] = []
    pos = 0
    for i, m in enumerate(matches):
        out.append(raw[pos : m.start()])
        out.append("```text\n")
        out.append(results[i])
        out.append("\n```")
        pos = m.end()
    out.append(raw[pos:])
    TMP.write_text("".join(out), encoding="utf-8")
    shutil.move(str(TMP), str(REPORT))
    print("Done pass2", REPORT, flush=True)


if __name__ == "__main__":
    main()
