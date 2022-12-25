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

        if ($config->getExtensionKey() !== '') {
            $lines[] = 'Extension: `' . $config->getExtensionKey() . '`';
        } else {
            $tableNames = array_keys($tableSchemas);
            $lines[] = 'Tables: ' . implode(', ', array_map(static function (string $t) { return '`' . $t . '`'; }, $tableNames));
        }
        $lines[] = 'Depth: ' . ($config->getDepth() === -1 ? 'unlimited' : (string)$config->getDepth());
        $lines[] = '';

        $lines[] = '## Diagram';
        $lines[] = '';
        $lines[] = '```mermaid';
        $lines[] = $this->renderMermaidBlock($tableSchemas);
        $lines[] = '```';
        $lines[] = '';

        $lines[] = '## Tables';
        $lines[] = '';
        foreach ($tableSchemas as $tableSchema) {
            $lines[] = '### ' . $tableSchema->getTableName();
            if ($tableSchema->getLabel() !== '' && $tableSchema->getLabel() !== $tableSchema->getTableName()) {
                $lines[] = '**' . $tableSchema->getLabel() . '**';
            }
            $lines[] = '';
            $lines[] = $this->renderFieldTable($tableSchema);
            $lines[] = '';
        }

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

        // Relationships — simple lines for v1 (no cardinality symbols)
        foreach ($tableSchemas as $tableSchema) {
            $sourceEntity = $this->sanitizeEntityName($tableSchema->getTableName());
            foreach ($tableSchema->getRelationFields() as $field) {
                $foreignTable = $field->getForeignTable();
                if ($foreignTable === '' || !isset($tableSchemas[$foreignTable])) {
                    continue;
                }
                $targetEntity = $this->sanitizeEntityName($foreignTable);
                $label = $field->getName();
                $lines[] = '    ' . $sourceEntity . ' ||--|| ' . $targetEntity . ' : "' . $label . '"';
            }
        }

        return implode("\n", $lines);
    }

    protected function renderFieldTable(TableSchema $tableSchema): string
    {
        $lines = [];
        $lines[] = '| Field | Type | Label | Required |';
        $lines[] = '|-------|------|-------|----------|';

        foreach ($tableSchema->getFields() as $field) {
            $row = '| `' . $field->getName() . '` | ' . $field->getType();
            if ($field->isRelation()) {
                $row .= ' → ' . $field->getForeignTable();
            }
            $row .= ' | ' . $this->escapeMarkdown($field->getLabel());
            $row .= ' | ' . ($field->isRequired() ? 'yes' : '') . ' |';
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
            'color' => 'string', 'password' => 'string', 'slug' => 'string',
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

    protected function escapeMarkdown(string $text): string
    {
        return str_replace('|', '\\|', $text);
    }
}
