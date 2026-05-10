<?php

declare(strict_types=1);

namespace Denic\Erd\Domain\Service;

use Denic\Erd\Domain\Dto\ErdConfiguration;
use Denic\Erd\Domain\Dto\FieldSchema;
use Denic\Erd\Domain\Dto\TableSchema;

class MermaidRenderer
{
    /**
     * Render full Obsidian-compatible markdown document.
     *
     * @param array<string, TableSchema> $tableSchemas
     */
    public function renderMarkdown(array $tableSchemas, ErdConfiguration $config): string
    {
        $lines = [];
        $lines[] = '# ER Diagram';
        $lines[] = '';

        // Extension or table info
        if ($config->getExtensionKey() !== '') {
            $lines[] = 'Extension: `' . $config->getExtensionKey() . '`';
        } else {
            $tableNames = array_keys($tableSchemas);
            $lines[] = 'Tables: ' . implode(', ', array_map(static function (string $t) { return '`' . $t . '`'; }, $tableNames));
        }
        $lines[] = 'Depth: ' . ($config->getDepth() === -1 ? 'unlimited' : (string)$config->getDepth());
        $lines[] = '';

        // Mermaid diagram
        $lines[] = '## Diagram';
        $lines[] = '';
        $lines[] = '```mermaid';
        $lines[] = $this->renderMermaidBlock($tableSchemas);
        $lines[] = '```';
        $lines[] = '';

        // Per-table field tables
        $lines[] = '## Tables';
        $lines[] = '';
        foreach ($tableSchemas as $tableSchema) {
            $lines[] = '### ' . $tableSchema->getTableName();
            if ($tableSchema->getLabel() !== '' && $tableSchema->getLabel() !== $tableSchema->getTableName()) {
                $lines[] = '**' . $tableSchema->getLabel() . '**';
            }
            if ($tableSchema->getRecordCount() >= 0) {
                $lines[] = 'Records: ' . $tableSchema->getRecordCount();
            }
            $lines[] = '';
            $lines[] = $this->renderFieldTable($tableSchema, $config);
            $lines[] = '';
        }

        // Relations overview
        $relations = $this->collectRelations($tableSchemas);
        if (!empty($relations)) {
            $lines[] = '## Relations';
            $lines[] = '';
            $lines[] = '| Source Table | Field | Kind | Target Table | MM Table |';
            $lines[] = '|-------------|-------|------|-------------|----------|';
            foreach ($relations as $rel) {
                $lines[] = '| ' . $rel['source'] . ' | ' . $rel['field'] . ' | ' . $rel['kind'] . ' | ' . $rel['target'] . ' | ' . $rel['mm'] . ' |';
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Render just the Mermaid erDiagram block content (without fences).
     *
     * @param array<string, TableSchema> $tableSchemas
     */
    public function renderMermaidBlock(array $tableSchemas): string
    {
        $lines = [];
        $lines[] = 'erDiagram';

        // Entity definitions
        foreach ($tableSchemas as $tableSchema) {
            $entityName = $this->sanitizeEntityName($tableSchema->getTableName());
            $fields = $tableSchema->getFields();

            if (empty($fields)) {
                $lines[] = '    ' . $entityName;
            } else {
                $lines[] = '    ' . $entityName . ' {';
                foreach ($fields as $field) {
                    $type = $this->mermaidType($field->getType());
                    $name = $this->sanitizeFieldName($field->getName());
                    $comment = '';
                    if ($field->isRequired()) {
                        $comment = ' "required"';
                    }
                    $lines[] = '        ' . $type . ' ' . $name . $comment;
                }
                $lines[] = '    }';
            }
        }

        $lines[] = '';

        // Relationships
        foreach ($tableSchemas as $tableSchema) {
            $sourceEntity = $this->sanitizeEntityName($tableSchema->getTableName());
            foreach ($tableSchema->getRelationFields() as $field) {
                $foreignTable = $field->getForeignTable();
                if ($foreignTable === '' || !isset($tableSchemas[$foreignTable])) {
                    continue;
                }
                $targetEntity = $this->sanitizeEntityName($foreignTable);
                $cardinality = $this->mermaidCardinality($field);
                $label = $this->sanitizeLabel($field->getName());
                $cardinalityLabel = $this->cardinalityLabel($field);
                $lines[] = '    ' . $sourceEntity . ' ' . $cardinality . ' ' . $targetEntity . ' : "' . $label . ' ' . $cardinalityLabel . '"';
            }
        }

        return implode("\n", $lines);
    }

    protected function renderFieldTable(TableSchema $tableSchema, ErdConfiguration $config): string
    {
        $lines = [];
        $hasPopulation = $config->isCheckDb();

        $header = '| Field | Type | Label | Required |';
        $separator = '|-------|------|-------|----------|';
        if ($hasPopulation) {
            $header .= ' Population |';
            $separator .= '------------|';
        }
        $lines[] = $header;
        $lines[] = $separator;

        foreach ($tableSchema->getFields() as $field) {
            $row = '| `' . $field->getName() . '` | ' . $field->getType();
            if ($field->isRelation()) {
                $row .= ' → ' . $field->getForeignTable();
            }
            $row .= ' | ' . $this->escapeMarkdown($field->getLabel());
            $row .= ' | ' . ($field->isRequired() ? 'yes' : '') . ' |';
            if ($hasPopulation) {
                $pct = $field->getPopulationPercent();
                $row .= ' ' . ($pct >= 0 ? $pct . '%' : '-') . ' |';
            }
            $lines[] = $row;
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<string, TableSchema> $tableSchemas
     * @return array<array{source: string, field: string, kind: string, target: string, mm: string}>
     */
    protected function collectRelations(array $tableSchemas): array
    {
        $relations = [];
        foreach ($tableSchemas as $tableSchema) {
            foreach ($tableSchema->getRelationFields() as $field) {
                if ($field->getForeignTable() === '') {
                    continue;
                }
                $relations[] = [
                    'source' => $tableSchema->getTableName(),
                    'field' => $field->getName(),
                    'kind' => $field->getRelationKind(),
                    'target' => $field->getForeignTable(),
                    'mm' => $field->getMmTable(),
                ];
            }
        }
        return $relations;
    }

    protected function sanitizeEntityName(string $name): string
    {
        // Mermaid only allows alphanumeric and underscores in entity names
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
    }

    protected function sanitizeFieldName(string $name): string
    {
        // Mermaid only allows alphanumeric and underscores in field names
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
    }

    protected function sanitizeLabel(string $label): string
    {
        // Escape quotes in relationship labels
        return str_replace(['"', "\n", "\r"], ['', ' ', ''], $label);
    }

    protected function mermaidType(string $type): string
    {
        $map = [
            'string' => 'varchar', 'email' => 'varchar', 'link' => 'varchar',
            'color' => 'varchar', 'password' => 'varchar', 'uuid' => 'varchar', 'slug' => 'varchar',
            'number' => 'int', 'boolean' => 'bool',
            'datetime' => 'date',
            'text' => 'varchar', 'richtext' => 'varchar',
            'file' => 'blob',
            'relation' => 'ref', 'category' => 'ref',
            'select' => 'int', 'radio' => 'int',
            'flexform' => 'blob',
        ];
        return $map[$type] ?? 'string';
    }

    protected function mermaidCardinality(FieldSchema $field): string
    {
        $map = [
            'fk' => '||--o|',
            'mm' => '}o--o{',
            'csv' => '}o--o{',
            'category' => '}o--o{',
            'inline' => '||--o{',
            'file' => '||--o{',
        ];
        return $map[$field->getRelationKind()] ?? '||--o{';
    }

    protected function cardinalityLabel(FieldSchema $field): string
    {
        $map = [
            'fk' => '(0:1)',
            'mm' => '(n:m)',
            'csv' => '(n:m)',
            'category' => '(n:m)',
            'inline' => '(1:n)',
            'file' => '(1:n)',
        ];
        return $map[$field->getRelationKind()] ?? '(1:n)';
    }

    protected function escapeMarkdown(string $text): string
    {
        return str_replace('|', '\\|', $text);
    }
}
