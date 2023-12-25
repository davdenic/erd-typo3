<?php

declare(strict_types=1);

namespace Denic\Erd\Domain\Dto;

class ErdConfiguration
{
    protected array $tables = [];
    protected string $extensionKey = '';
    protected int $depth = 2;
    protected bool $includeInternal = false;
    protected bool $includeCoreTables = true;
    protected string $lang = 'de';
    protected bool $checkDb = false;
    protected bool $includeEmpty = false;

    public function getTables(): array
    {
        return $this->tables;
    }

    public function setTables(array $tables): self
    {
        $this->tables = $tables;
        return $this;
    }

    public function getExtensionKey(): string
    {
        return $this->extensionKey;
    }

    public function setExtensionKey(string $extensionKey): self
    {
        $this->extensionKey = $extensionKey;
        return $this;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): self
    {
        $this->depth = $depth;
        return $this;
    }

    public function isIncludeInternal(): bool
    {
        return $this->includeInternal;
    }

    public function setIncludeInternal(bool $includeInternal): self
    {
        $this->includeInternal = $includeInternal;
        return $this;
    }

    public function isIncludeCoreTables(): bool
    {
        return $this->includeCoreTables;
    }

    public function setIncludeCoreTables(bool $includeCoreTables): self
    {
        $this->includeCoreTables = $includeCoreTables;
        return $this;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    public function isCheckDb(): bool
    {
        return $this->checkDb;
    }

    public function setCheckDb(bool $checkDb): self
    {
        $this->checkDb = $checkDb;
        return $this;
    }

    public function isIncludeEmpty(): bool
    {
        return $this->includeEmpty;
    }

    public function setIncludeEmpty(bool $includeEmpty): self
    {
        $this->includeEmpty = $includeEmpty;
        return $this;
    }
}
