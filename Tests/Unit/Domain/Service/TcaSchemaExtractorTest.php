<?php

declare(strict_types=1);

namespace Denic\Erd\Tests\Unit\Domain\Service;

use Denic\Erd\Domain\Dto\ErdConfiguration;
use Denic\Erd\Domain\Service\TcaSchemaExtractor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TcaSchemaExtractorTest extends TestCase
{
    private TcaSchemaExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = new TcaSchemaExtractor();
        $GLOBALS['TCA'] = [];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TCA']);
    }

    #[Test]
    public function extractTableReturnsEmptySchemaForUnknownTable(): void
    {
        $config = new ErdConfiguration();
        $schema = $this->extractor->extractTable('nonexistent', $config);

        self::assertSame('nonexistent', $schema->getTableName());
        self::assertEmpty($schema->getFields());
    }

    #[Test]
    public function extractTableExtractsStringField(): void
    {
        $GLOBALS['TCA']['tx_blog_post'] = [
            'ctrl' => ['title' => 'Blog Post'],
            'columns' => [
                'title' => [
                    'label' => 'Title',
                    'config' => ['type' => 'input'],
                ],
            ],
        ];

        $config = new ErdConfiguration();
        $schema = $this->extractor->extractTable('tx_blog_post', $config);

        self::assertCount(1, $schema->getFields());
        $field = $schema->getFields()['title'];
        self::assertSame('title', $field->getName());
        self::assertSame('string', $field->getType());
        self::assertSame('Title', $field->getLabel());
    }

    #[Test]
    public function extractTableSkipsInternalFieldsByDefault(): void
    {
        $GLOBALS['TCA']['tx_blog_post'] = [
            'ctrl' => ['title' => 'Blog Post'],
            'columns' => [
                'title' => ['label' => 'Title', 'config' => ['type' => 'input']],
                'uid' => ['label' => 'UID', 'config' => ['type' => 'input']],
                'pid' => ['label' => 'PID', 'config' => ['type' => 'input']],
                'hidden' => ['label' => 'Hidden', 'config' => ['type' => 'check']],
                'sorting' => ['label' => 'Sorting', 'config' => ['type' => 'input']],
            ],
        ];

        $config = new ErdConfiguration();
        $schema = $this->extractor->extractTable('tx_blog_post', $config);

        self::assertCount(1, $schema->getFields());
        self::assertArrayHasKey('title', $schema->getFields());
    }

    #[Test]
    public function extractTableIncludesInternalFieldsWhenConfigured(): void
    {
        $GLOBALS['TCA']['tx_blog_post'] = [
            'ctrl' => ['title' => 'Blog Post'],
            'columns' => [
                'title' => ['label' => 'Title', 'config' => ['type' => 'input']],
                'hidden' => ['label' => 'Hidden', 'config' => ['type' => 'check']],
            ],
        ];

        $config = (new ErdConfiguration())->setIncludeInternal(true);
        $schema = $this->extractor->extractTable('tx_blog_post', $config);

        self::assertCount(2, $schema->getFields());
        self::assertArrayHasKey('hidden', $schema->getFields());
    }

    #[Test]
    #[DataProvider('fieldTypeProvider')]
    public function extractTableResolvesFieldTypes(array $tcaConfig, string $expectedType): void
    {
        $GLOBALS['TCA']['test'] = [
            'ctrl' => ['title' => 'Test'],
            'columns' => [
                'field' => ['label' => 'Field', 'config' => $tcaConfig],
            ],
        ];

        $config = new ErdConfiguration();
        $schema = $this->extractor->extractTable('test', $config);
        $field = $schema->getFields()['field'];

        self::assertSame($expectedType, $field->getType());
    }

    public static function fieldTypeProvider(): array
    {
        return [
            'input' => [['type' => 'input'], 'string'],
            'email' => [['type' => 'email'], 'email'],
            'link' => [['type' => 'link'], 'link'],
            'datetime' => [['type' => 'datetime'], 'datetime'],
            'number' => [['type' => 'number'], 'number'],
            'color' => [['type' => 'color'], 'color'],
            'password' => [['type' => 'password'], 'password'],
            'uuid' => [['type' => 'uuid'], 'uuid'],
            'json' => [['type' => 'json'], 'json'],
            'slug' => [['type' => 'slug'], 'slug'],
            'text' => [['type' => 'text'], 'text'],
            'richtext' => [['type' => 'text', 'enableRichtext' => true], 'richtext'],
            'check' => [['type' => 'check'], 'boolean'],
            'radio' => [['type' => 'radio'], 'radio'],
            'select' => [['type' => 'select', 'renderType' => 'selectSingle'], 'select'],
            'select with foreign_table' => [['type' => 'select', 'foreign_table' => 'pages'], 'relation'],
            'group' => [['type' => 'group'], 'group'],
            'group with allowed' => [['type' => 'group', 'allowed' => 'pages'], 'relation'],
            'inline' => [['type' => 'inline', 'foreign_table' => 'tx_blog_comment'], 'relation'],
            'inline file' => [['type' => 'inline', 'foreign_table' => 'sys_file_reference'], 'file'],
            'file' => [['type' => 'file'], 'file'],
            'category' => [['type' => 'category'], 'category'],
            'flex' => [['type' => 'flex'], 'flexform'],
            'passthrough' => [['type' => 'passthrough'], 'passthrough'],
            'folder' => [['type' => 'folder'], 'folder'],
            'user' => [['type' => 'user'], 'user'],
            'none' => [['type' => 'none'], 'none'],
        ];
    }

    #[Test]
    #[DataProvider('relationKindProvider')]
    public function extractTableResolvesRelationKinds(array $tcaConfig, string $expectedKind): void
    {
        $GLOBALS['TCA']['test'] = [
            'ctrl' => ['title' => 'Test'],
            'columns' => [
                'field' => ['label' => 'Field', 'config' => $tcaConfig],
            ],
        ];

        $config = new ErdConfiguration();
        $schema = $this->extractor->extractTable('test', $config);
        $field = $schema->getFields()['field'];

        self::assertSame($expectedKind, $field->getRelationKind());
    }

    public static function relationKindProvider(): array
    {
        return [
            'no relation' => [['type' => 'input'], ''],
            'category' => [['type' => 'category'], 'category'],
            'inline file ref' => [['type' => 'inline', 'foreign_table' => 'sys_file_reference'], 'file'],
            'inline' => [['type' => 'inline', 'foreign_table' => 'tx_blog_comment'], 'inline'],
            'inline mm' => [['type' => 'inline', 'foreign_table' => 'tx_blog_tag', 'MM' => 'tx_blog_post_tag_mm'], 'mm'],
            'select fk' => [['type' => 'select', 'foreign_table' => 'pages', 'maxitems' => 1], 'fk'],
            'select csv by maxitems' => [['type' => 'select', 'foreign_table' => 'pages', 'maxitems' => 10], 'csv'],
            'select explicit mm' => [['type' => 'select', 'foreign_table' => 'pages', 'MM' => 'some_mm'], 'mm'],
            'group fk' => [['type' => 'group', 'allowed' => 'pages'], 'fk'],
            'group allowed with mm' => [['type' => 'group', 'allowed' => 'pages', 'MM' => 'tx_test_mm'], 'mm'],
        ];
    }

    #[Test]
    #[DataProvider('requiredProvider')]
    public function extractTableDetectsRequiredFields(array $tcaConfig, bool $expected): void
    {
        $GLOBALS['TCA']['test'] = [
            'ctrl' => ['title' => 'Test'],
            'columns' => [
                'field' => ['label' => 'Field', 'config' => $tcaConfig],
            ],
        ];

        $config = new ErdConfiguration();
        $schema = $this->extractor->extractTable('test', $config);

        self::assertSame($expected, $schema->getFields()['field']->isRequired());
    }

    public static function requiredProvider(): array
    {
        return [
            'not required' => [['type' => 'input'], false],
            'eval required' => [['type' => 'input', 'eval' => 'required,trim'], true],
            'minitems' => [['type' => 'select', 'minitems' => 1], true],
            'v12 required flag' => [['type' => 'input', 'required' => true], true],
            'minitems zero' => [['type' => 'select', 'minitems' => 0], false],
        ];
    }

    #[Test]
    public function extractTableSetsCategoryForeignTable(): void
    {
        $GLOBALS['TCA']['test'] = [
            'ctrl' => ['title' => 'Test'],
            'columns' => [
                'categories' => ['label' => 'Cat', 'config' => ['type' => 'category']],
            ],
        ];

        $config = new ErdConfiguration();
        $schema = $this->extractor->extractTable('test', $config);
        $field = $schema->getFields()['categories'];

        self::assertSame('sys_category', $field->getForeignTable());
        self::assertSame('category', $field->getRelationKind());
    }

    #[Test]
    public function extractTableResolvesLllLabels(): void
    {
        $GLOBALS['TCA']['test'] = [
            'ctrl' => ['title' => 'LLL:EXT:test/Resources/Private/Language/locallang.xlf:table.title'],
            'columns' => [
                'field' => [
                    'label' => 'LLL:EXT:test/Resources/Private/Language/locallang.xlf:field.label',
                    'config' => ['type' => 'input'],
                ],
            ],
        ];

        $languageServiceMock = $this->createMock(\TYPO3\CMS\Core\Localization\LanguageService::class);
        $languageServiceMock->method('sL')->willReturnMap([
            ['LLL:EXT:test/Resources/Private/Language/locallang.xlf:table.title', 'Resolved Table'],
            ['LLL:EXT:test/Resources/Private/Language/locallang.xlf:field.label', 'Resolved Field'],
        ]);
        $GLOBALS['LANG'] = $languageServiceMock;

        $config = new ErdConfiguration();
        $schema = $this->extractor->extractTable('test', $config);

        self::assertSame('Resolved Table', $schema->getLabel());
        self::assertSame('Resolved Field', $schema->getFields()['field']->getLabel());

        unset($GLOBALS['LANG']);
    }

    #[Test]
    public function getTablesForExtensionFindsTablesByIconPath(): void
    {
        $GLOBALS['TCA']['tx_blog_post'] = [
            'ctrl' => ['title' => 'Post', 'iconfile' => 'EXT:blog/Resources/Public/Icons/post.svg'],
            'columns' => [],
        ];
        $GLOBALS['TCA']['tx_blog_author'] = [
            'ctrl' => ['title' => 'Author', 'iconfile' => 'EXT:blog/Resources/Public/Icons/author.svg'],
            'columns' => [],
        ];
        $GLOBALS['TCA']['tx_news_domain_model_news'] = [
            'ctrl' => ['title' => 'News', 'iconfile' => 'EXT:news/Resources/Public/Icons/news.svg'],
            'columns' => [],
        ];

        $tables = $this->extractor->getTablesForExtension('blog');
        self::assertCount(2, $tables);
        self::assertContains('tx_blog_post', $tables);
        self::assertContains('tx_blog_author', $tables);
    }

    #[Test]
    public function getTablesForExtensionFindsTablesByPrefix(): void
    {
        $GLOBALS['TCA']['tx_blog_domain_model_post'] = [
            'ctrl' => ['title' => 'Post'],
            'columns' => [],
        ];

        $tables = $this->extractor->getTablesForExtension('blog');
        self::assertCount(1, $tables);
        self::assertContains('tx_blog_domain_model_post', $tables);
    }

    #[Test]
    public function getAllExtensionsWithTablesGroupsTables(): void
    {
        $GLOBALS['TCA']['tx_blog_post'] = [
            'ctrl' => ['title' => 'Post', 'iconfile' => 'EXT:blog/Resources/Public/Icons/post.svg'],
            'columns' => [],
        ];
        $GLOBALS['TCA']['pages'] = [
            'ctrl' => ['title' => 'Pages'],
            'columns' => [],
        ];

        $result = $this->extractor->getAllExtensionsWithTables();
        self::assertArrayHasKey('blog', $result);
        self::assertArrayHasKey('core', $result);
        self::assertContains('tx_blog_post', $result['blog']);
        self::assertContains('pages', $result['core']);
    }
}
