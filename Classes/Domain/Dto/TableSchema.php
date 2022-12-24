<?php

declare(strict_types=1);

namespace Denic\Erd\Domain\Dto;

class TableSchema
{
    protected string $tableName;
    protected string $label;
    protected string $extensionKey;

    /** @var array<string, FieldSchema> */
    protected array $fields;

    /**
     * @param array<string, FieldSchema> $fields
     */
    public function __construct(
        string $tableName,
        string $label = '',
        string $extensionKey = '',
        array $fields = []
    ) {
        $this->tableName = $tableName;
        $this->label = $label;
        $this->extensionKey = $extensionKey;
        $this->fields = $fields;
    }

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
