# Changelog

## v4.0.0 — 2026-05-03

Multi-version support for TYPO3 11.5 / 12.4 / 13.4.

### Features

- Support for TYPO3 14 native `json` and `uuid` TCA field types
- Wide composer constraint: `^11.5 || ^12.4 || ^13.4`
- Dual backend module registration (`ext_tables.php` for v11 + `Modules.php` for v12+)

## v3.1.0 — 2026-02-14

- Add relations overview table to generated Markdown

## v3.0.1 — 2026-01-04

- Fix clipboard API blocked in TYPO3 13 backend iframe context

## v3.0.0 — 2025-12-27

Upgrade to TYPO3 13.4 LTS.

### Breaking

- Dropped TYPO3 12.4 support

### Features

- `--list-extensions` CLI option: list all extensions that own TCA tables
- `--list-tables` CLI option: list all registered TCA table names
- Copy-to-clipboard buttons in backend module (Mermaid code / full Markdown)

### Fixes

- Fixed field population query failing on tables where TCA columns don't exist
  in the database schema (now uses `SHOW COLUMNS` to intersect)

## v2.2.0 — 2024-09-15

- Include date in download filename for easier archiving

## v2.1.1 — 2024-08-04

- Fix division by zero in population query for empty tables

## v2.1.0 — 2024-05-19

- Detect group fields with allowed tables as relations
- Add slug field type to Mermaid type map

## v2.0.2 — 2024-03-30

- Fix Mermaid entity names containing dots breaking diagram

## v2.0.1 — 2024-01-02

### Fixes

- Fix fatal error in TYPO3 12: remove `BackendTemplateView` reference
  (`$defaultViewObjectName`) removed in TYPO3 12 (Breaking #96107,
  deprecated since v11.5 via #95164)

## v2.0.0 — 2023-12-31

Upgrade to TYPO3 12.4 LTS.

### Breaking

- Minimum PHP version raised to 8.2
- Dropped TYPO3 11.5 support
- Backend module now uses `Configuration/Backend/Modules.php` (removed `ext_tables.php`)

### Features

- v12 dedicated TCA type resolution (`email`, `link`, `datetime`, `number`, `color`, `password`, `file`, `folder`)
- v12 `required` field flag support
- DB population statistics: per-field population percentages via `--check-db`
- Improved Mermaid cardinality rendering (`||--o|`, `}o--o{`, `||--o{`)
- Mermaid.js live preview in backend module (CDN)

### Internal

- Constructor promotion with `readonly` throughout
- Removed manual property assignments

## v1.1.1 — 2023-08-05

- Fix TypeError on passthrough fields without config array

## v1.1.0 — 2023-05-20

- Create output directory recursively if it does not exist
- Show resolved table count in verbose CLI output

## v1.0.1 — 2023-02-18

- Fix empty extension list when iconfile path is missing

## v1.0.0 — 2023-01-01

Initial release for TYPO3 11.5.

### Features

- TCA schema extractor with field type resolution (v11 `input` + `eval`/`renderType` patterns)
- Relation resolver with BFS traversal up to configurable depth
- Mermaid `erDiagram` renderer with Obsidian-compatible Markdown output
- CLI command `erd:generate` with `--extension`, `--depth`, `--lang`, `--output` options
- Backend module (Web → ERD Generator) with extension/table selector and download
- German translations for backend labels
