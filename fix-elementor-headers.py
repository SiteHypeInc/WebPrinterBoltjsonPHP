#!/usr/bin/env python3
"""
fix-elementor-headers.py
Adds required Elementor import wrapper fields to all JSON template files.
Safe to run multiple times — skips files that already have the fields.

Usage: python3 fix-elementor-headers.py /path/to/repo
"""

import json
import os
import sys
from pathlib import Path

REQUIRED_FIELDS = {"version", "title", "type"}
ELEMENTOR_VERSION = "0.4"
ELEMENTOR_TYPE = "page"

def get_title_from_filename(filepath):
 """Convert filename to a readable title. e.g. elementor-bold-home.json → Bold Home"""
 name = Path(filepath).stem # strip .json
 parts = name.replace("elementor-", "").replace("-", " ").title()
 return parts

def fix_file(filepath):
 with open(filepath, "r", encoding="utf-8") as f:
  try:
   data = json.load(f)
  except json.JSONDecodeError as e:
   print(f" ❌ INVALID JSON — skipping: {filepath} ({e})")
   return "invalid"

 # Skip if data is not a dict (could be a list or other type)
 if not isinstance(data, dict):
  print(f" ⚠️  Not a dict — skipping: {Path(filepath).name}")
  return "skipped"

 # Check if all required fields already present
 if REQUIRED_FIELDS.issubset(data.keys()):
  print(f" ✅ Already has headers — skipping: {Path(filepath).name}")
  return "skipped"

 # Build fixed version preserving existing content
 title = data.get("title") or get_title_from_filename(filepath)
 fixed = {
  "version": data.get("version", ELEMENTOR_VERSION),
  "title": title,
  "type": data.get("type", ELEMENTOR_TYPE),
  **{k: v for k, v in data.items() if k not in ("version", "title", "type")}
 }

 with open(filepath, "w", encoding="utf-8") as f:
  json.dump(fixed, f, indent=2)

 print(f" 🔧 Fixed: {Path(filepath).name}")
 return "fixed"

def main():
 repo_path = sys.argv[1] if len(sys.argv) > 1 else "."
 repo = Path(repo_path)

 if not repo.exists():
  print(f"❌ Path not found: {repo_path}")
  sys.exit(1)

 json_files = list(repo.rglob("*.json"))
 if not json_files:
  print("No JSON files found.")
  sys.exit(0)

 print(f"\n🔍 Scanning {len(json_files)} JSON files in {repo_path}\n")

 counts = {"fixed": 0, "skipped": 0, "invalid": 0}

 for filepath in sorted(json_files):
  result = fix_file(filepath)
  counts[result] += 1

 print(f"\n{'='*50}")
 print(f"✅ Already correct : {counts['skipped']}")
 print(f"🔧 Fixed : {counts['fixed']}")
 print(f"❌ Invalid JSON : {counts['invalid']}")
 print(f"{'='*50}")
 print(f"\nDone. Run: git add -A && git commit -m 'fix: add Elementor import headers to all templates' && git push")

if __name__ == "__main__":
 main()