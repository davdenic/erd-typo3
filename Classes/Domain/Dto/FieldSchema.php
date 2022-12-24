<?php

declare(strict_types=1);

namespace Denic\Erd\Domain\Dto;

class FieldSchema
{
    protected string $name;
    protected string $type;
    protected string $label;
    protected bool $required;
    protected string $relationKind;
    protected string $foreignTable;
    protected string $mmTable;

    public function __construct(
        string $name,
        string $type = 'string',
        string $label = '',
        bool $required = false,
        string $relationKind = '',
        string $foreignTable = '',
        string $mmTable = ''
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->label = $label;
        $this->required = $required;
        $this->relationKind = $relationKind;
        $this->foreignTable = $foreignTable;
        $this->mmTable = $mmTable;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getRelationKind(): string
    {
        return $this->relationKind;
    }

    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }

    public function getMmTable(): string
    {
        return $this->mmTable;
    }

    public function isRelation(): bool
    {
        return $this->relationKind !== '';
    }
}
