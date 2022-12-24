# ERD Generator for TYPO3

Generate Entity-Relationship diagrams from TYPO3 TCA definitions — as Mermaid
diagrams, Markdown documents, or directly in the TYPO3 backend.

## Requirements

- TYPO3 11.5 LTS
- PHP 7.4+

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

## CLI Usage

```bash
# By extension
vendor/bin/typo3 erd:generate --extension=my_ext

# By table names
vendor/bin/typo3 erd:generate tx_myext_domain_model_event tx_myext_domain_model_location

# Write to file
vendor/bin/typo3 erd:generate --extension=my_ext --output=docs/erd.md

# Options
vendor/bin/typo3 erd:generate --extension=my_ext \
  --depth=3 \
  --lang=en \
  --include-internal \
  --no-core-tables
```

## Backend Module

The module is available under **Web → ERD Generator** for admin users.

1. Select mode: by extension or by table(s)
2. Choose relationship depth (0–3 or unlimited)
3. Click **Generate ERD**
4. View the rendered Mermaid diagram and per-table field lists
5. Download as `.md` file

## License

GPL-2.0-or-later
