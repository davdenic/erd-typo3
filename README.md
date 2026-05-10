# ERD Generator for TYPO3

Generate Entity-Relationship Diagrams from your TYPO3 TCA configuration. Outputs Mermaid diagrams with field details, relation cardinalities, and optional DB population stats.

Supports **TYPO3 11.5 – 14**.

## Install

```bash
ddev composer require denic/erd
```

No additional configuration needed — just install and use.

## Backend Module

Open the TYPO3 backend and go to **System → ERD Generator**.

1. Select an extension or pick individual tables
2. Set the relationship depth
3. Optionally enable DB population check
4. Click **Generate ERD**

Download the result as `.md` and open it in:

- [Obsidian](https://obsidian.md/) — renders Mermaid natively
- [Markdown Viewer](https://markdownviewer.pages.dev/) — online viewer
- **GitHub / GitLab** — renders Mermaid in Markdown automatically

## CLI

```bash
vendor/bin/typo3 erd:generate --extension=my_ext
vendor/bin/typo3 erd:generate --extension=my_ext --output=docs/erd.md --check-db
vendor/bin/typo3 erd:generate --list-extensions
vendor/bin/typo3 erd:generate --list-tables
```

## License

GPL-2.0-or-later
