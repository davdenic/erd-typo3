<?php

declare(strict_types=1);

namespace Denic\Erd\Tests\Unit\Domain\Service;

use Denic\Erd\Domain\Dto\ErdConfiguration;
use Denic\Erd\Domain\Dto\FieldSchema;
use Denic\Erd\Domain\Dto\TableSchema;
use Denic\Erd\Domain\Service\MermaidRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MermaidRendererTest extends TestCase
{
    private MermaidRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new MermaidRenderer();
    }

    #[Test]
    public function renderMermaidBlockStartsWithErDiagram(): void
    {
        $tables = [
            'tx_blog_post' => new TableSchema('tx_blog_post', 'Blog Post', 'blog', [
                'title' => new FieldSchema('title', 'string', 'Title', true),
            ]),
        ];

        $output = $this->renderer->renderMermaidBlock($tables);
        self::assertStringStartsWith('erDiagram', $output);
    }

    #[Test]
    public function renderMermaidBlockRendersEntityWithFields(): void
    {
        $tables = [
            'tx_blog_post' => new TableSchema('tx_blog_post', 'Blog Post', 'blog', [
                'title' => new FieldSchema('title', 'string', 'Title', true),
                'body' => new FieldSchema('body', 'text', 'Body'),
            ]),
        ];

        $output = $this->renderer->renderMermaidBlock($tables);
        self::assertStringContainsString('tx_blog_post {', $output);
        self::assertStringContainsString('varchar title "required"', $output);
        self::assertStringContainsString('varchar body', $output);
    }

    #[Test]
    public function renderMermaidBlockRendersEmptyEntity(): void
    {
        $tables = [
            'tx_blog_tag' => new TableSchema('tx_blog_tag', 'Tag', 'blog', []),
        ];

        $output = $this->renderer->renderMermaidBlock($tables);
        self::assertStringContainsString('    tx_blog_tag', $output);
        self::assertStringNotContainsString('tx_blog_tag {', $output);
    }

    #[Test]
    public function renderMermaidBlockRendersRelationships(): void
    {
        $tables = [
            'tx_blog_post' => new TableSchema('tx_blog_post', 'Blog Post', 'blog', [
                'author' => new FieldSchema('author', 'relation', 'Author', false, 'fk', 'tx_blog_author'),
            ]),
            'tx_blog_author' => new TableSchema('tx_blog_author', 'Author', 'blog', [
                'name' => new FieldSchema('name', 'string', 'Name'),
            ]),
        ];

        $output = $this->renderer->renderMermaidBlock($tables);
        self::assertStringContainsString('tx_blog_post ||--o| tx_blog_author : "author (0:1)"', $output);
    }

    #[Test]
    public function renderMermaidBlockRendersMmRelation(): void
    {
        $tables = [
            'tx_blog_post' => new TableSchema('tx_blog_post', '', '', [
                'categories' => new FieldSchema('categories', 'relation', 'Categories', false, 'csv', 'sys_category', 'tx_blog_post_category_mm'),
            ]),
            'sys_category' => new TableSchema('sys_category', 'Category', 'core'),
        ];

        $output = $this->renderer->renderMermaidBlock($tables);
        self::assertStringContainsString('}o--o{', $output);
        self::assertStringContainsString('(n:m)', $output);
    }

    #[Test]
    public function renderMermaidBlockRendersInlineRelation(): void
    {
        $tables = [
            'tx_blog_post' => new TableSchema('tx_blog_post', '', '', [
                'comments' => new FieldSchema('comments', 'relation', 'Comments', false, 'inline', 'tx_blog_comment'),
            ]),
            'tx_blog_comment' => new TableSchema('tx_blog_comment', 'Comment', 'blog'),
        ];

        $output = $this->renderer->renderMermaidBlock($tables);
        self::assertStringContainsString('||--o{', $output);
        self::assertStringContainsString('(1:n)', $output);
    }

    #[Test]
    public function renderMermaidBlockSkipsRelationToMissingTable(): void
    {
        $tables = [
            'tx_blog_post' => new TableSchema('tx_blog_post', '', '', [
                'author' => new FieldSchema('author', 'relation', 'Author', false, 'fk', 'tx_blog_author'),
            ]),
        ];

        $output = $this->renderer->renderMermaidBlock($tables);
        self::assertStringNotContainsString('tx_blog_author', $output);
    }

    #[Test]
    public function renderMermaidBlockSanitizesEntityNames(): void
    {
        $tables = [
            'my-table.name' => new TableSchema('my-table.name', '', '', [
                'title' => new FieldSchema('title', 'string'),
            ]),
        ];

        $output = $this->renderer->renderMermaidBlock($tables);
        self::assertStringContainsString('my_table_name {', $output);
        self::assertStringNotContainsString('my-table.name', $output);
    }

    #[Test]
    #[DataProvider('mermaidTypeProvider')]
    public function renderMermaidBlockMapsFieldTypes(string $inputType, string $expectedMermaidType): void
    {
        $tables = [
            'test' => new TableSchema('test', '', '', [
                'field' => new FieldSchema('field', $inputType),
            ]),
        ];

        $output = $this->renderer->renderMermaidBlock($tables);
        self::assertStringContainsString($expectedMermaidType . ' field', $output);
    }

    public static function mermaidTypeProvider(): array
    {
        return [
            'string' => ['string', 'varchar'],
            'email' => ['email', 'varchar'],
            'uuid' => ['uuid', 'varchar'],
            'slug' => ['slug', 'varchar'],
            'number' => ['number', 'int'],
            'boolean' => ['boolean', 'bool'],
            'datetime' => ['datetime', 'date'],
            'text' => ['text', 'varchar'],
            'richtext' => ['richtext', 'varchar'],
            'file' => ['file', 'blob'],
            'relation' => ['relation', 'ref'],
            'category' => ['category', 'ref'],
            'select' => ['select', 'int'],
            'flexform' => ['flexform', 'blob'],
            'json' => ['json', 'string'],
        ];
    }

    #[Test]
    public function renderMarkdownContainsAllSections(): void
    {
        $tables = [
            'tx_blog_post' => new TableSchema('tx_blog_post', 'Blog Post', 'blog', [
                'title' => new FieldSchema('title', 'string', 'Title', true),
                'author' => new FieldSchema('author', 'relation', 'Author', false, 'fk', 'tx_blog_author'),
            ]),
            'tx_blog_author' => new TableSchema('tx_blog_author', 'Author', 'blog', [
                'name' => new FieldSchema('name', 'string', 'Name'),
            ]),
        ];
        $config = (new ErdConfiguration())->setExtensionKey('blog')->setDepth(2);

        $output = $this->renderer->renderMarkdown($tables, $config);

        self::assertStringContainsString('# ER Diagram', $output);
        self::assertStringContainsString('Extension: `blog`', $output);
        self::assertStringContainsString('Depth: 2', $output);
        self::assertStringContainsString('## Diagram', $output);
        self::assertStringContainsString('```mermaid', $output);
        self::assertStringContainsString('## Tables', $output);
        self::assertStringContainsString('### tx_blog_post', $output);
        self::assertStringContainsString('**Blog Post**', $output);
        self::assertStringContainsString('## Relations', $output);
        self::assertStringContainsString('| tx_blog_post | author | fk | tx_blog_author |', $output);
    }

    #[Test]
    public function renderMarkdownShowsTablesListWhenNoExtension(): void
    {
        $tables = [
            'pages' => new TableSchema('pages', 'Pages', 'core', [
                'title' => new FieldSchema('title', 'string', 'Title'),
            ]),
        ];
        $config = new ErdConfiguration();

        $output = $this->renderer->renderMarkdown($tables, $config);
        self::assertStringContainsString('Tables: `pages`', $output);
    }

    #[Test]
    public function renderMarkdownShowsUnlimitedDepth(): void
    {
        $tables = [
            'pages' => new TableSchema('pages', '', '', []),
        ];
        $config = (new ErdConfiguration())->setDepth(-1);

        $output = $this->renderer->renderMarkdown($tables, $config);
        self::assertStringContainsString('Depth: unlimited', $output);
    }

    #[Test]
    public function renderMarkdownFieldTableShowsPopulation(): void
    {
        $tables = [
            'tx_blog_post' => new TableSchema('tx_blog_post', '', '', [
                'title' => new FieldSchema('title', 'string', 'Title', true, '', '', '', 95),
                'subtitle' => new FieldSchema('subtitle', 'string', 'Subtitle', false, '', '', '', 12),
            ]),
        ];
        $config = (new ErdConfiguration())->setCheckDb(true);

        $output = $this->renderer->renderMarkdown($tables, $config);
        self::assertStringContainsString('Population', $output);
        self::assertStringContainsString('95%', $output);
        self::assertStringContainsString('12%', $output);
    }

    #[Test]
    public function renderMarkdownFieldTableHidesPopulationWhenNotChecked(): void
    {
        $tables = [
            'tx_blog_post' => new TableSchema('tx_blog_post', '', '', [
                'title' => new FieldSchema('title', 'string', 'Title'),
            ]),
        ];
        $config = new ErdConfiguration();

        $output = $this->renderer->renderMarkdown($tables, $config);
        self::assertStringNotContainsString('Population', $output);
    }

    #[Test]
    public function renderMarkdownShowsRecordCount(): void
    {
        $tables = [
            'tx_blog_post' => new TableSchema('tx_blog_post', 'Post', 'blog', [
                'title' => new FieldSchema('title', 'string'),
            ], 150),
        ];
        $config = new ErdConfiguration();

        $output = $this->renderer->renderMarkdown($tables, $config);
        self::assertStringContainsString('Records: 150', $output);
    }

    #[Test]
    public function renderMarkdownEscapesPipeInLabels(): void
    {
        $tables = [
            'test' => new TableSchema('test', '', '', [
                'field' => new FieldSchema('field', 'string', 'Label | with pipe'),
            ]),
        ];
        $config = new ErdConfiguration();

        $output = $this->renderer->renderMarkdown($tables, $config);
        self::assertStringContainsString('Label \\| with pipe', $output);
    }

    #[Test]
    public function renderMarkdownNoRelationsSectionWhenNone(): void
    {
        $tables = [
            'test' => new TableSchema('test', '', '', [
                'title' => new FieldSchema('title', 'string'),
            ]),
        ];
        $config = new ErdConfiguration();

        $output = $this->renderer->renderMarkdown($tables, $config);
        self::assertStringNotContainsString('## Relations', $output);
    }
}
