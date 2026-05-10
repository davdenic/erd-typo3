# Changelog

## 4.x (TYPO3 14)

- Modernize PHP: constructor promotion, `match` expressions, `str_starts_with()`/`str_contains()`
- Fix: distinguish `csv` relation kind from `mm` for multi-value fields without MM table
- Move backend module from Web to System group (full width, no page tree)
- Move inline JS to external file for CSP compliance
- Theme-aware module icon (adapts to modern/classic/fresh, light/dark)
- ModuleTemplateFactory, proper backend styling
- fix mermaid reserved words (fk, text, etc.)
- fix empty entity blocks
- cardinality labels in relations (0:1, 1:n, n:m)
- copy to clipboard, download .md
- json and uuid TCA field types
- TYPO3 extension documentation

## 3.x (TYPO3 13)

- Move backend module from Web to System group
- Move inline JS to external file for CSP compliance
- upgrade to TYPO3 13.4
- --list-extensions, --list-tables CLI options
- fix population query on missing DB columns
- ModuleTemplateFactory, proper backend styling

## 2.x (TYPO3 12)

- Move backend module from Web to System group
- Move inline JS to external file for CSP compliance
- upgrade to TYPO3 12.4
- v12 TCA types, DB population stats
- PHP 8.2+

## 1.x (TYPO3 11)

- Move backend module from Web to System group
- Refactor templates to use Fluid form ViewHelpers
- initial release for TYPO3 11.5
- TCA schema extractor, relation resolver, mermaid renderer
- CLI command and backend module
