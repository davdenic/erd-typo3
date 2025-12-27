# ERD Generator for TYPO3

Generate Entity-Relationship diagrams from TYPO3 TCA definitions — as Mermaid
diagrams, Markdown documents, or directly in the TYPO3 backend.

## Requirements

- TYPO3 13.4 LTS
- PHP 8.2+

## Installation

```bash
composer require denic/erd
```

## Features

- **Backend module** — select an extension or individual tables, configure depth
  and options, generate and download ER diagrams
- **CLI command** `erd:generate` — generate diagrams from the command line
- **Mermaid output** — renders `erDiagram` blocks compatible with Obsidian,
  GitHub, GitLab, and other Mermaid renderers
- **Relation traversal** — follows foreign keys, inline relations, MM tables,
  and categories up to a configurable depth (BFS)
- **Label resolution** — resolves `LLL:` references to human-readable labels
- **DB population statistics** — optional per-field population percentages
  (how many records have a non-empty value)
- **Mermaid cardinality** — renders proper ER notation (`||--o|`, `}o--o{`, etc.)
- **Copy to clipboard** — one-click copy of Mermaid code or full Markdown
- **Discovery** — `--list-extensions` and `--list-tables` to explore available TCA

## CLI Usage

```bash
# List all extensions with TCA tables
vendor/bin/typo3 erd:generate --list-extensions

# List all TCA tables
vendor/bin/typo3 erd:generate --list-tables

# By extension
vendor/bin/typo3 erd:generate --extension=my_ext

# By table names
vendor/bin/typo3 erd:generate tx_myext_domain_model_event tx_myext_domain_model_location

# Write to file
vendor/bin/typo3 erd:generate --extension=my_ext --output=docs/erd.md

# With DB population check
vendor/bin/typo3 erd:generate --extension=my_ext --check-db

# Include 0%-populated fields
vendor/bin/typo3 erd:generate --extension=my_ext --check-db --include-empty

# All options
vendor/bin/typo3 erd:generate --extension=my_ext \
  --depth=3 \
  --lang=en \
  --check-db \
  --include-empty \
  --include-internal \
  --no-core-tables
```

## Backend Module

The module is available under **Web → ERD Generator** for admin users.

1. Select mode: by extension or by table(s)
2. Choose relationship depth (0–3 or unlimited)
3. Optionally enable DB population check
4. Click **Generate ERD**
5. View the rendered Mermaid diagram and per-table field lists
6. Copy Mermaid code or full Markdown to clipboard
7. Download as `.md` file

## License

GPL-2.0-or-later
