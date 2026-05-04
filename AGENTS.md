# Purpose

Keep future OpenCode sessions evidence-based. This repo is small enough that wrong assumptions about tooling or runtime will cause more mistakes than missing detail.

## Repository Snapshot

- Observed root files/directories: `satupage1A4.php`, `.gitignore`, `.cocoindex_code/`.
- No `README`, package manifests, CI workflows, task runner config, or existing instruction files were found in this repo snapshot.
- `.gitignore` ignores `/.cocoindex_code` only.

## Confirmed Behavior

- `satupage1A4.php` is the only visible application entrypoint.
- The script requires `pdf/fpdf.php` through a relative path.
- It reads `*.png` files from `Label Berlaku/` and assumes numbered filenames such as `1.png`, `2.png`, and so on.
- It counts images with `glob(...)`, lays them out with `FPDF('L', 'cm', 'A4')`, and writes directly to PDF output.
- `FPDF->Output()` is called with no arguments, so the current behavior is direct/inline PDF output rather than saving a file path.

## Known Constraints

- Treat this as a single-script PHP workflow unless the repo gains more structure.
- Hardcoded relative paths matter: changes can break runtime if `pdf/fpdf.php` or `Label Berlaku/` move.
- FPDF layout units here are centimeters because the constructor uses `cm`.
- `.cocoindex_code/settings.yml` is local indexing config, not application logic.

## What Not To Assume

- Do not assume Composer, npm, or any other package manager workflow exists here.
- Do not assume tests, linting, CI/CD, deployment config, or framework conventions that are not present in the files.
- Do not claim `pdf/fpdf.php` or `Label Berlaku/` exist unless you verify them in the workspace.
- Do not invent run commands beyond what the visible PHP script supports.

## Working Rules For Future Sessions

- Read `satupage1A4.php` first before proposing changes.
- Preserve direct PDF output unless the user explicitly asks to save files or change delivery mode.
- Make the smallest possible change; this repo has no visible safety net such as tests or CI.
- Verify every repo-level claim against observed files instead of filling gaps with generic PHP assumptions.
