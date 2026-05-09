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

You get the Mermaid code and per-table field lists. Download as ``.md``.

CLI
===

..  code-block:: bash

    vendor/bin/typo3 erd:generate --extension=my_ext
    vendor/bin/typo3 erd:generate --extension=my_ext --output=docs/erd.md
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
   * - ``--include-internal``
     - Show uid, pid, hidden, etc.
     - off
   * - ``--no-core-tables``
     - Skip sys_category, sys_file_reference
     - off

Output
======

The ``.md`` file contains a Mermaid ``erDiagram`` block and field tables per entity.

Viewing
=======

Open the ``.md`` in:

-  `Obsidian <https://obsidian.md/>`__ — renders Mermaid natively
-  `Markdown Viewer <https://markdownviewer.pages.dev/>`__ — online viewer
-  **GitHub / GitLab** — renders Mermaid in Markdown automatically
