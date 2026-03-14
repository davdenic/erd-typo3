# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TYPO3 extension (`denic/erd`) that generates Entity-Relationship diagrams from TCA definitions. Outputs Mermaid `erDiagram` blocks and Markdown documents. Provides both a backend module (Web → ERD Generator) and a CLI command (`erd:generate`). Supports TYPO3 12.4 and 13.4.

**Vendor namespace:** `Denic\Erd\` (PSR-4 root: `Classes/`)

## Architecture

The extension has a simple three-service pipeline:

1. **TcaSchemaExtractor** — Reads `$GLOBALS['TCA']` and produces `TableSchema`/`FieldSchema` DTOs. Handles v12+ dedicated TCA field types. Optionally queries the DB for record counts and per-field population percentages.

2. **RelationResolver** — BFS traversal starting from root tables, following foreign keys/inline/MM/category relations up to a configurable depth. Returns a map of `TableSchema` objects including discovered related tables.

3. **MermaidRenderer** — Converts the resolved `TableSchema` map into Mermaid `erDiagram` syntax and full Markdown documents with field tables and a relations overview.

Both entry points (`ErdController` for the backend module, `GenerateErdCommand` for CLI) build an `ErdConfiguration` DTO and feed it through this pipeline.

## Key DTOs

- `ErdConfiguration` — Settings (depth, lang, checkDb, includeInternal, includeCoreTables, includeEmpty)
- `TableSchema` — Table name, label, extension key, fields map, record count
- `FieldSchema` — Field name, type, label, required, relationKind (fk/mm/inline/file/category), foreignTable, mmTable, populationPercent

## Backend Module

Registered via `Configuration/Backend/Modules.php` (v12+ style).

## CLI Command

```bash
# Requires a running TYPO3 instance with loaded TCA
vendor/bin/typo3 erd:generate --extension=my_ext
vendor/bin/typo3 erd:generate --extension=my_ext --output=docs/erd.md --check-db --depth=3
vendor/bin/typo3 erd:generate --list-extensions
vendor/bin/typo3 erd:generate --list-tables
vendor/bin/typo3 erd:generate tx_myext_domain_model_foo tx_myext_domain_model_bar
```

## Development Notes

- No test suite exists in this repository.
- No build tooling (no npm/webpack/SCSS) — assets are plain SVG icons only.
- No `ext_localconf.php` — the extension has no frontend plugins.
- The extension has no Extbase domain models or repositories; all data comes directly from TCA and raw DB queries via `ConnectionPool`.
- Relation kinds: `fk` (single foreign key), `mm` (many-to-many or multi-select), `inline` (IRRE), `file` (sys_file_reference), `category` (sys_category).
