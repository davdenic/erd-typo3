<?php

declare(strict_types=1);

namespace Denic\Erd\Domain\Dto;

class TableSchema
{
    /**
     * @param array<string, FieldSchema> $fields
     */
    public function __construct(
        protected readonly string $tableName,
        protected readonly string $label = '',
        protected readonly string $extensionKey = '',
        protected array $fields = [],
        protected readonly int $recordCount = -1
    ) {}

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getExtensionKey(): string
    {
        return $this->extensionKey;
    }

    /**
     * @return array<string, FieldSchema>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getRecordCount(): int
    {
        return $this->recordCount;
    }

    public function addField(FieldSchema $field): void
    {
        $this->fields[$field->getName()] = $field;
    }

    /**
     * @return FieldSchema[]
     */
    public function getRelationFields(): array
    {
        return array_filter($this->fields, static function (FieldSchema $field): bool {
            return $field->isRelation();
        });
    }
}
