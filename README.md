# ERD Generator for TYPO3

Generate ER diagrams from TCA — Mermaid output, backend module, CLI.

## Requirements

- TYPO3 12.4
- PHP 8.2+

## Install

```bash
composer require denic/erd
```

## Usage

Backend module: **Web → ERD Generator**

CLI:
```bash
vendor/bin/typo3 erd:generate --extension=my_ext
vendor/bin/typo3 erd:generate --extension=my_ext --output=docs/erd.md --check-db
vendor/bin/typo3 erd:generate tx_myext_domain_model_foo tx_myext_domain_model_bar
```

## License

GPL-2.0-or-later
