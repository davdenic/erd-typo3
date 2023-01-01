# Changelog

## v1.0.0 — 2023-01-01

Initial release for TYPO3 11.5.

### Features

- TCA schema extractor with field type resolution (v11 `input` + `eval`/`renderType` patterns)
- Relation resolver with BFS traversal up to configurable depth
- Mermaid `erDiagram` renderer with Obsidian-compatible Markdown output
- CLI command `erd:generate` with `--extension`, `--depth`, `--lang`, `--output` options
- Backend module (Web → ERD Generator) with extension/table selector and download
- German translations for backend labels
