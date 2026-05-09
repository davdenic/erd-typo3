..  _usage:

=====
Usage
=====

Backend Module
==============

Go to **Web → ERD Generator**.

1. Pick an extension or select tables.
2. Set depth.
3. Hit **Generate ERD**.

You get the Mermaid code, a full Markdown doc, and per-table field lists.
Copy or download as ``.md``.

CLI
===

..  code-block:: bash

    vendor/bin/typo3 erd:generate --extension=my_ext
    vendor/bin/typo3 erd:generate --extension=my_ext --output=docs/erd.md --check-db
    vendor/bin/typo3 erd:generate --list-extensions
    vendor/bin/typo3 erd:generate --list-tables
    vendor/bin/typo3 erd:generate tx_myext_domain_model_person tx_myext_domain_model_address

Options
-------

.. list-table::
   :header-rows: 1

   * - Flag
     - What it does
     - Default
   * - ``-e, --extension``
     - Extension key
     -
   * - ``-o, --output``
     - Write to file instead of stdout
     -
   * - ``-d, --depth``
     - Relation depth (0, 1, 2, -1=all)
     - ``2``
   * - ``-l, --lang``
     - Label language
     - ``de``
   * - ``--check-db``
     - Show field population %
     - off
   * - ``--include-empty``
     - Keep 0% fields (needs ``--check-db``)
     - off
   * - ``--include-internal``
     - Show uid, pid, hidden, etc.
     - off
   * - ``--no-core-tables``
     - Skip sys_category, sys_file_reference
     - off
   * - ``--list-extensions``
     - List extensions and exit
     -
   * - ``--list-tables``
     - List tables and exit
     -

Output
======

The ``.md`` file contains a Mermaid ``erDiagram`` block, field tables per entity,
a relations overview, and cardinality labels (0:1, 1:n, n:m).

Viewing
=======

Open the ``.md`` in:

-  `Obsidian <https://obsidian.md/>`__ — renders Mermaid natively
-  `Markdown Viewer <https://markdownviewer.pages.dev/>`__ — online viewer
-  **GitHub / GitLab** — renders Mermaid in Markdown automatically
