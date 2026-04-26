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
                $lines[] = '    ' . $entityName . ' {';
                $lines[] = '    }';
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
                $label = $field->getName();
                $lines[] = '    ' . $sourceEntity . ' ' . $cardinality . ' ' . $targetEntity . ' : "' . $label . '"';
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
        return str_replace(['-', '.'], '_', $name);
    }

    protected function sanitizeFieldName(string $name): string
    {
        return str_replace(['-', '.', ' '], '_', $name);
    }

    protected function mermaidType(string $type): string
    {
        $map = [
            'string' => 'string', 'email' => 'string', 'link' => 'string',
            'color' => 'string', 'password' => 'string', 'uuid' => 'string', 'slug' => 'string',
            'number' => 'int', 'boolean' => 'int',
            'datetime' => 'datetime',
            'text' => 'text', 'richtext' => 'text',
            'file' => 'file',
            'relation' => 'fk', 'category' => 'fk',
            'select' => 'enum', 'radio' => 'enum',
            'flexform' => 'json',
        ];
        return $map[$type] ?? 'string';
    }

    protected function mermaidCardinality(FieldSchema $field): string
    {
        $map = [
            'fk' => '||--o|',
            'mm' => '}o--o{',
            'category' => '}o--o{',
            'inline' => '||--o{',
            'file' => '||--o{',
        ];
        return $map[$field->getRelationKind()] ?? '||--o{';
    }

    protected function escapeMarkdown(string $text): string
    {
        return str_replace('|', '\\|', $text);
    }
}
