"""Translate user prompt ```text blocks to English; write atomically to avoid partial files."""

from __future__ import annotations

import re
import shutil
import time
from pathlib import Path

from deep_translator import GoogleTranslator

REPORT = Path(__file__).resolve().parent.parent / "FUNC_IMPROVE" / "CURSOR_AND_GIT_ACTIVITY_REPORT_2026-04-01_TO_2026-05-14.md"
TMP = REPORT.with_suffix(".md.tmp")

VI_CHARS = re.compile(
    r"[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ"
    r"ÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ]"
)


def has_vietnamese(text: str) -> bool:
    return bool(VI_CHARS.search(text))


def looks_like_ascii_config(text: str) -> bool:
    lines = text.strip().splitlines()
    if len(lines) < 5:
        return False
    hits = sum(1 for ln in lines if ln.startswith("#") or ln.startswith("Host ") or ln.startswith("  HostName"))
    return hits / max(len(lines), 1) > 0.4


def should_translate(block: str) -> bool:
    b = block.strip()
    if not b or len(b) < 2:
        return False
    if looks_like_ascii_config(b):
        return False
    if has_vietnamese(b):
        return True
    low = f" {b.lower()} "
    ascii_markers = (
        " toi ",
        " khong ",
        " duoc ",
        " chuc nang ",
        " hay ",
        " giup ",
        " kiem tra ",
        " tai lieu ",
        " huong dan ",
        " hay ssh ",
        " hay vao ",
        " tren server ",
    )
    if any(m in low for m in ascii_markers):
        return True
    non_ascii = sum(1 for c in b if ord(c) > 127)
    return non_ascii > max(len(b) * 0.02, 8)


def translate_one(inner: str) -> str:
    translator = GoogleTranslator(source="auto", target="en")
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
        time.sleep(0.08)
    return "\n".join(parts)


def main() -> None:
    raw = REPORT.read_text(encoding="utf-8")
    pattern = re.compile(r"```text\n(.*?)```", re.DOTALL)
    matches = list(pattern.finditer(raw))
    originals = [m.group(1) for m in matches]
    n_do = sum(1 for b in originals if should_translate(b))
    print(f"Blocks: {len(matches)}; translate: {n_do}", flush=True)

    results: list[str] = []
    done_tr = 0
    for i, inner in enumerate(originals):
        if should_translate(inner):
            results.append(translate_one(inner))
            done_tr += 1
            if done_tr % 20 == 0:
                print(f"  {done_tr}/{n_do}", flush=True)
        else:
            results.append(inner)

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
    print("Wrote", REPORT, flush=True)


if __name__ == "__main__":
    main()
