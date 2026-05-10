<?php

declare(strict_types=1);

namespace Denic\Erd\Tests\Unit\Domain\Dto;

use Denic\Erd\Domain\Dto\FieldSchema;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FieldSchemaTest extends TestCase
{
    #[Test]
    public function constructorSetsAllProperties(): void
    {
        $field = new FieldSchema(
            'author',
            'relation',
            'Author',
            true,
            'fk',
            'tx_blog_author',
            'tx_blog_author_mm',
            85
        );

        self::assertSame('author', $field->getName());
        self::assertSame('relation', $field->getType());
        self::assertSame('Author', $field->getLabel());
        self::assertTrue($field->isRequired());
        self::assertSame('fk', $field->getRelationKind());
        self::assertSame('tx_blog_author', $field->getForeignTable());
        self::assertSame('tx_blog_author_mm', $field->getMmTable());
        self::assertSame(85, $field->getPopulationPercent());
    }

    #[Test]
    public function constructorDefaults(): void
    {
        $field = new FieldSchema('title');

        self::assertSame('title', $field->getName());
        self::assertSame('string', $field->getType());
        self::assertSame('', $field->getLabel());
        self::assertFalse($field->isRequired());
        self::assertSame('', $field->getRelationKind());
        self::assertSame('', $field->getForeignTable());
        self::assertSame('', $field->getMmTable());
        self::assertSame(-1, $field->getPopulationPercent());
    }

    #[Test]
    public function isRelationReturnsTrueForRelationFields(): void
    {
        $field = new FieldSchema('author', 'relation', '', false, 'fk', 'tx_blog_author');
        self::assertTrue($field->isRelation());
    }

    #[Test]
    public function isRelationReturnsFalseForNonRelationFields(): void
    {
        $field = new FieldSchema('title', 'string');
        self::assertFalse($field->isRelation());
    }
}
