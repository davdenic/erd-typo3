<?php

declare(strict_types=1);

namespace Denic\Erd\Domain\Service;

use Denic\Erd\Domain\Dto\ErdConfiguration;
use Denic\Erd\Domain\Dto\FieldSchema;
use Denic\Erd\Domain\Dto\TableSchema;
use TYPO3\CMS\Core\Localization\LanguageService;

class TcaSchemaExtractor
{
    public const INTERNAL_FIELDS = [
        'uid', 'pid', 'tstamp', 'crdate', 'cruser_id', 'deleted', 'hidden',
        'sorting', 'sys_language_uid', 'l10n_parent', 'l10n_diffsource',
        'l10n_source', 't3ver_oid', 't3ver_wsid', 't3ver_state', 't3ver_stage',
        't3_origuid', 'editlock', 'starttime', 'endtime', 'fe_group',
    ];

    public const CORE_TABLES = [
        'sys_category', 'sys_file_reference', 'sys_file', 'sys_file_metadata',
    ];

    public function extractTable(string $tableName, ErdConfiguration $config): TableSchema
    {
        $tca = $GLOBALS['TCA'][$tableName] ?? null;
        if ($tca === null) {
            return new TableSchema($tableName, $tableName, '', []);
        }

        $label = $this->resolveLabel($tca['ctrl']['title'] ?? $tableName, $config->getLang());
        $extensionKey = $this->detectExtensionKey($tableName, $tca);

        $fields = [];
        foreach ($tca['columns'] ?? [] as $fieldName => $fieldConfig) {
            if (!$config->isIncludeInternal() && in_array($fieldName, self::INTERNAL_FIELDS, true)) {
                continue;
            }

            $field = $this->extractField($fieldName, $fieldConfig, $config);
            $fields[$fieldName] = $field;
        }

        return new TableSchema($tableName, $label, $extensionKey, $fields);
    }

    protected function extractField(string $fieldName, array $fieldConfig, ErdConfiguration $config): FieldSchema
    {
        $colConfig = $fieldConfig['config'] ?? [];
        $type = $this->resolveFieldType($colConfig);
        $label = $this->resolveLabel($fieldConfig['label'] ?? $fieldName, $config->getLang());
        $required = $this->isRequired($colConfig);
        $relationKind = $this->resolveRelationKind($colConfig);
        $foreignTable = (string)($colConfig['foreign_table'] ?? '');
        $mmTable = (string)($colConfig['MM'] ?? '');

        if ($type === 'category') {
            $foreignTable = 'sys_category';
            $relationKind = 'category';
        }

        return new FieldSchema(
            $fieldName,
            $type,
            $label,
            $required,
            $relationKind,
            $foreignTable,
            $mmTable
        );
    }

    protected function resolveFieldType(array $config): string
    {
        $type = (string)($config['type'] ?? 'input');

        // v12 dedicated types
        if ($type === 'email') {
            return 'email';
        }
        if ($type === 'link') {
            return 'link';
        }
        if ($type === 'datetime') {
            return 'datetime';
        }
        if ($type === 'number') {
            return 'number';
        }
        if ($type === 'color') {
            return 'color';
        }
        if ($type === 'password') {
            return 'password';
        }
        if ($type === 'file') {
            return 'file';
        }
        if ($type === 'folder') {
            return 'folder';
        }
        if ($type === 'category') {
            return 'category';
        }
        if ($type === 'slug') {
            return 'slug';
        }

        // v11 input type with renderType/eval disambiguation
        if ($type === 'input') {
            $renderType = (string)($config['renderType'] ?? '');
            $eval = (string)($config['eval'] ?? '');

            if ($renderType === 'inputDateTime') {
                return 'datetime';
            }
            if ($renderType === 'inputLink') {
                return 'link';
            }
            if ($renderType === 'colorPicker') {
                return 'color';
            }
            if (strpos($eval, 'email') !== false) {
                return 'email';
            }
            if (strpos($eval, 'int') !== false || strpos($eval, 'double2') !== false) {
                return 'number';
            }
            if (strpos($eval, 'password') !== false) {
                return 'password';
            }
            return 'string';
        }

        if ($type === 'text') {
            if (!empty($config['enableRichtext'])) {
                return 'richtext';
            }
            return 'text';
        }

        if ($type === 'check') {
            return 'boolean';
        }

        if ($type === 'radio') {
            return 'radio';
        }

        if ($type === 'select') {
            if (!empty($config['foreign_table'])) {
                return 'relation';
            }
            return 'select';
        }

        if ($type === 'group') {
            if (!empty($config['allowed'])) {
                return 'relation';
            }
            return 'group';
        }

        if ($type === 'inline') {
            $foreignTable = (string)($config['foreign_table'] ?? '');
            if ($foreignTable === 'sys_file_reference') {
                return 'file';
            }
            return 'relation';
        }

        if ($type === 'flex') {
            return 'flexform';
        }

        if ($type === 'passthrough') {
            return 'passthrough';
        }

        if ($type === 'user') {
            return 'user';
        }

        if ($type === 'none') {
            return 'none';
        }

        return $type;
    }

    protected function resolveRelationKind(array $config): string
    {
        $type = (string)($config['type'] ?? 'input');
        $foreignTable = (string)($config['foreign_table'] ?? '');
        $mm = (string)($config['MM'] ?? '');

        if ($type === 'category') {
            return 'category';
        }

        if ($type === 'inline' && $foreignTable === 'sys_file_reference') {
            return 'file';
        }

        if ($type === 'inline' && $foreignTable !== '') {
            if ($mm !== '') {
                return 'mm';
            }
            return 'inline';
        }

        if (($type === 'select' || $type === 'group') && $foreignTable !== '') {
            if ($mm !== '') {
                return 'mm';
            }
            $maxitems = (int)($config['maxitems'] ?? 1);
            return $maxitems <= 1 ? 'fk' : 'mm';
        }

        if ($type === 'group' && !empty($config['allowed']) && $foreignTable === '') {
            $allowed = (string)$config['allowed'];
            if ($mm !== '') {
                return 'mm';
            }
            if (strpos($allowed, ',') === false) {
                return 'fk';
            }
        }

        return '';
    }

    protected function isRequired(array $config): bool
    {
        $eval = (string)($config['eval'] ?? '');
        if (strpos($eval, 'required') !== false) {
            return true;
        }

        $minitems = (int)($config['minitems'] ?? 0);
        if ($minitems >= 1) {
            return true;
        }

        // v12+ required flag
        if (!empty($config['required'])) {
            return true;
        }

        return false;
    }

    protected function resolveLabel(string $label, string $lang): string
    {
        if (strpos($label, 'LLL:') === 0) {
            $languageService = $this->getLanguageService();
            if ($languageService !== null) {
                $resolved = $languageService->sL($label);
                if ($resolved !== '' && $resolved !== $label) {
                    return $resolved;
                }
            }
        }
        return $label;
    }

    protected function detectExtensionKey(string $tableName, array $tca): string
    {
        // Try iconfile path: EXT:my_ext/...
        $iconfile = (string)($tca['ctrl']['iconfile'] ?? '');
        if (strpos($iconfile, 'EXT:') === 0) {
            $parts = explode('/', substr($iconfile, 4), 2);
            return $parts[0] ?? '';
        }

        // Try table prefix: tx_myext_domain_model_*
        if (strpos($tableName, 'tx_') === 0) {
            $parts = explode('_', $tableName);
            if (count($parts) >= 3) {
                $domainIdx = array_search('domain', $parts, true);
                if ($domainIdx !== false && $domainIdx > 1) {
                    return implode('_', array_slice($parts, 1, $domainIdx - 1));
                }
                return $parts[1];
            }
        }

        // Core tables
        if (strpos($tableName, 'sys_') === 0 || strpos($tableName, 'be_') === 0 || $tableName === 'pages' || $tableName === 'tt_content') {
            return 'core';
        }

        return '';
    }

    /**
     * @return string[]
     */
    public function getTablesForExtension(string $extensionKey): array
    {
        $tables = [];
        foreach (array_keys($GLOBALS['TCA'] ?? []) as $tableName) {
            $tca = $GLOBALS['TCA'][$tableName];
            $detected = $this->detectExtensionKey($tableName, $tca);
            if ($detected === $extensionKey) {
                $tables[] = $tableName;
            }
        }
        return $tables;
    }

    /**
     * @return array<string, string[]> Extension key => table names
     */
    public function getAllExtensionsWithTables(): array
    {
        $extensions = [];
        foreach (array_keys($GLOBALS['TCA'] ?? []) as $tableName) {
            $tca = $GLOBALS['TCA'][$tableName];
            $extKey = $this->detectExtensionKey($tableName, $tca);
            if ($extKey === '') {
                $extKey = '_unknown';
            }
            $extensions[$extKey][] = $tableName;
        }
        ksort($extensions);
        return $extensions;
    }

    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}
