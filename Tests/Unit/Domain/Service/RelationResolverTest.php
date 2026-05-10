<?php

declare(strict_types=1);

namespace Denic\Erd\Tests\Unit\Domain\Service;

use Denic\Erd\Domain\Dto\ErdConfiguration;
use Denic\Erd\Domain\Dto\FieldSchema;
use Denic\Erd\Domain\Dto\TableSchema;
use Denic\Erd\Domain\Service\RelationResolver;
use Denic\Erd\Domain\Service\TcaSchemaExtractor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RelationResolverTest extends TestCase
{
    private RelationResolver $resolver;
    private TcaSchemaExtractor $extractorMock;

    protected function setUp(): void
    {
        $this->extractorMock = $this->createMock(TcaSchemaExtractor::class);
        $this->resolver = new RelationResolver($this->extractorMock);
        $GLOBALS['TCA'] = [];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TCA']);
    }

    #[Test]
    public function resolveReturnsSingleTableWithNoRelations(): void
    {
        $GLOBALS['TCA']['tx_blog_post'] = ['columns' => []];

        $tableSchema = new TableSchema('tx_blog_post', 'Post', 'blog', [
            'title' => new FieldSchema('title', 'string', 'Title'),
        ]);

        $this->extractorMock->method('extractTable')->willReturn($tableSchema);

        $config = (new ErdConfiguration())->setDepth(2);
        $result = $this->resolver->resolve(['tx_blog_post'], $config);

        self::assertCount(1, $result);
        self::assertArrayHasKey('tx_blog_post', $result);
    }

    #[Test]
    public function resolveFollowsRelationsToDepth(): void
    {
        $GLOBALS['TCA']['tx_blog_post'] = ['columns' => []];
        $GLOBALS['TCA']['tx_blog_author'] = ['columns' => []];
        $GLOBALS['TCA']['tx_blog_company'] = ['columns' => []];

        $postSchema = new TableSchema('tx_blog_post', 'Post', 'blog', [
            'author' => new FieldSchema('author', 'relation', 'Author', false, 'fk', 'tx_blog_author'),
        ]);
        $authorSchema = new TableSchema('tx_blog_author', 'Author', 'blog', [
            'company' => new FieldSchema('company', 'relation', 'Company', false, 'fk', 'tx_blog_company'),
        ]);
        $companySchema = new TableSchema('tx_blog_company', 'Company', 'blog', [
            'name' => new FieldSchema('name', 'string'),
        ]);

        $this->extractorMock->method('extractTable')->willReturnCallback(
            function (string $tableName) use ($postSchema, $authorSchema, $companySchema) {
                return match ($tableName) {
                    'tx_blog_post' => $postSchema,
                    'tx_blog_author' => $authorSchema,
                    'tx_blog_company' => $companySchema,
                };
            }
        );

        $config = (new ErdConfiguration())->setDepth(2);
        $result = $this->resolver->resolve(['tx_blog_post'], $config);

        self::assertCount(3, $result);
    }

    #[Test]
    public function resolveRespectsDepthLimit(): void
    {
        $GLOBALS['TCA']['tx_blog_post'] = ['columns' => []];
        $GLOBALS['TCA']['tx_blog_author'] = ['columns' => []];
        $GLOBALS['TCA']['tx_blog_company'] = ['columns' => []];

        $postSchema = new TableSchema('tx_blog_post', 'Post', 'blog', [
            'author' => new FieldSchema('author', 'relation', 'Author', false, 'fk', 'tx_blog_author'),
        ]);
        $authorSchema = new TableSchema('tx_blog_author', 'Author', 'blog', [
            'company' => new FieldSchema('company', 'relation', 'Company', false, 'fk', 'tx_blog_company'),
        ]);

        $this->extractorMock->method('extractTable')->willReturnCallback(
            function (string $tableName) use ($postSchema, $authorSchema) {
                return match ($tableName) {
                    'tx_blog_post' => $postSchema,
                    'tx_blog_author' => $authorSchema,
                    default => new TableSchema($tableName),
                };
            }
        );

        $config = (new ErdConfiguration())->setDepth(1);
        $result = $this->resolver->resolve(['tx_blog_post'], $config);

        self::assertCount(2, $result);
        self::assertArrayHasKey('tx_blog_post', $result);
        self::assertArrayHasKey('tx_blog_author', $result);
        self::assertArrayNotHasKey('tx_blog_company', $result);
    }

    #[Test]
    public function resolveHandlesCircularRelations(): void
    {
        $GLOBALS['TCA']['table_a'] = ['columns' => []];
        $GLOBALS['TCA']['table_b'] = ['columns' => []];

        $schemaA = new TableSchema('table_a', '', '', [
            'ref' => new FieldSchema('ref', 'relation', '', false, 'fk', 'table_b'),
        ]);
        $schemaB = new TableSchema('table_b', '', '', [
            'ref' => new FieldSchema('ref', 'relation', '', false, 'fk', 'table_a'),
        ]);

        $this->extractorMock->method('extractTable')->willReturnCallback(
            function (string $tableName) use ($schemaA, $schemaB) {
                return match ($tableName) {
                    'table_a' => $schemaA,
                    'table_b' => $schemaB,
                };
            }
        );

        $config = (new ErdConfiguration())->setDepth(-1);
        $result = $this->resolver->resolve(['table_a'], $config);

        self::assertCount(2, $result);
    }

    #[Test]
    public function resolveSkipsCoreTablesWhenConfigured(): void
    {
        $GLOBALS['TCA']['tx_blog_post'] = ['columns' => []];
        $GLOBALS['TCA']['sys_category'] = ['columns' => []];

        $postSchema = new TableSchema('tx_blog_post', 'Post', 'blog', [
            'categories' => new FieldSchema('categories', 'relation', '', false, 'category', 'sys_category'),
        ]);

        $this->extractorMock->method('extractTable')->willReturn($postSchema);

        $config = (new ErdConfiguration())->setDepth(2)->setIncludeCoreTables(false);
        $result = $this->resolver->resolve(['tx_blog_post'], $config);

        self::assertCount(1, $result);
        self::assertArrayNotHasKey('sys_category', $result);
    }

    #[Test]
    public function resolveSkipsTablesNotInTca(): void
    {
        $config = (new ErdConfiguration())->setDepth(2);
        $result = $this->resolver->resolve(['nonexistent_table'], $config);

        self::assertEmpty($result);
    }

    #[Test]
    public function resolveDepthZeroReturnsOnlyRootTables(): void
    {
        $GLOBALS['TCA']['tx_blog_post'] = ['columns' => []];
        $GLOBALS['TCA']['tx_blog_author'] = ['columns' => []];

        $postSchema = new TableSchema('tx_blog_post', 'Post', 'blog', [
            'author' => new FieldSchema('author', 'relation', 'Author', false, 'fk', 'tx_blog_author'),
        ]);

        $this->extractorMock->method('extractTable')->willReturn($postSchema);

        $config = (new ErdConfiguration())->setDepth(0);
        $result = $this->resolver->resolve(['tx_blog_post'], $config);

        self::assertCount(1, $result);
        self::assertArrayHasKey('tx_blog_post', $result);
    }
}
