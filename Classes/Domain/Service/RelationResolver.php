<?php

declare(strict_types=1);

namespace Denic\Erd\Domain\Service;

use Denic\Erd\Domain\Dto\ErdConfiguration;
use Denic\Erd\Domain\Dto\TableSchema;

class RelationResolver
{
    protected TcaSchemaExtractor $tcaSchemaExtractor;

    public function __construct(TcaSchemaExtractor $tcaSchemaExtractor)
    {
        $this->tcaSchemaExtractor = $tcaSchemaExtractor;
    }

    /**
     * BFS traversal from root tables, following relations up to $depth levels.
     *
     * @param string[] $rootTableNames
     * @return array<string, TableSchema>
     */
    public function resolve(array $rootTableNames, ErdConfiguration $config): array
    {
        $depth = $config->getDepth();
        $visited = [];
        $result = [];

        // Queue: [tableName, currentDepth]
        $queue = [];
        foreach ($rootTableNames as $tableName) {
            if (!isset($GLOBALS['TCA'][$tableName])) {
                continue;
            }
            $queue[] = [$tableName, 0];
        }

        while (!empty($queue)) {
            [$tableName, $currentDepth] = array_shift($queue);

            if (isset($visited[$tableName])) {
                continue;
            }
            $visited[$tableName] = true;

            // Skip core tables if not wanted (but still mark as visited)
            if (!$config->isIncludeCoreTables() && in_array($tableName, TcaSchemaExtractor::CORE_TABLES, true)) {
                continue;
            }

            $tableSchema = $this->tcaSchemaExtractor->extractTable($tableName, $config);
            $result[$tableName] = $tableSchema;

            // If we haven't reached max depth, enqueue related tables
            if ($depth === -1 || $currentDepth < $depth) {
                foreach ($tableSchema->getRelationFields() as $field) {
                    $foreignTable = $field->getForeignTable();
                    if ($foreignTable !== '' && !isset($visited[$foreignTable]) && isset($GLOBALS['TCA'][$foreignTable])) {
                        $queue[] = [$foreignTable, $currentDepth + 1];
                    }
                }
            }
        }

        return $result;
    }
}
