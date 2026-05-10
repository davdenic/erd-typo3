<?php

declare(strict_types=1);

namespace Denic\Erd\Tests\Unit\Domain\Dto;

use Denic\Erd\Domain\Dto\FieldSchema;
use Denic\Erd\Domain\Dto\TableSchema;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TableSchemaTest extends TestCase
{
    #[Test]
    public function constructorSetsAllProperties(): void
    {
        $fields = [
            'title' => new FieldSchema('title', 'string', 'Title', true),
        ];
        $table = new TableSchema('tx_blog_post', 'Blog Post', 'blog', $fields, 42);

        self::assertSame('tx_blog_post', $table->getTableName());
        self::assertSame('Blog Post', $table->getLabel());
        self::assertSame('blog', $table->getExtensionKey());
        self::assertCount(1, $table->getFields());
        self::assertSame(42, $table->getRecordCount());
    }

    #[Test]
    public function constructorDefaults(): void
    {
        $table = new TableSchema('pages');

        self::assertSame('pages', $table->getTableName());
        self::assertSame('', $table->getLabel());
        self::assertSame('', $table->getExtensionKey());
        self::assertEmpty($table->getFields());
        self::assertSame(-1, $table->getRecordCount());
    }

    #[Test]
    public function addFieldAppendsToFields(): void
    {
        $table = new TableSchema('tx_blog_post');
        $field = new FieldSchema('title', 'string', 'Title');
        $table->addField($field);

        self::assertCount(1, $table->getFields());
        self::assertSame($field, $table->getFields()['title']);
    }

    #[Test]
    public function getRelationFieldsFiltersCorrectly(): void
    {
        $table = new TableSchema('tx_blog_post', '', '', [
            'title' => new FieldSchema('title', 'string', 'Title'),
            'author' => new FieldSchema('author', 'relation', 'Author', false, 'fk', 'tx_blog_author'),
            'body' => new FieldSchema('body', 'text', 'Body'),
            'categories' => new FieldSchema('categories', 'relation', 'Categories', false, 'mm', 'sys_category'),
        ]);

        $relations = $table->getRelationFields();
        self::assertCount(2, $relations);
        self::assertArrayHasKey('author', $relations);
        self::assertArrayHasKey('categories', $relations);
    }
}
