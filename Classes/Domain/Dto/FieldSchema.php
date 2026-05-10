<?php

declare(strict_types=1);

namespace Denic\Erd\Domain\Dto;

class FieldSchema
{
    public function __construct(
        protected readonly string $name,
        protected readonly string $type = 'string',
        protected readonly string $label = '',
        protected readonly bool $required = false,
        protected readonly string $relationKind = '',
        protected readonly string $foreignTable = '',
        protected readonly string $mmTable = '',
        protected readonly int $populationPercent = -1
    ) {}

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

    public function getPopulationPercent(): int
    {
        return $this->populationPercent;
    }

    public function isRelation(): bool
    {
        return $this->relationKind !== '';
    }
}
