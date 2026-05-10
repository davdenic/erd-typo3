<?php

declare(strict_types=1);

namespace Denic\Erd\Controller;

use Denic\Erd\Domain\Dto\ErdConfiguration;
use Denic\Erd\Domain\Service\MermaidRenderer;
use Denic\Erd\Domain\Service\RelationResolver;
use Denic\Erd\Domain\Service\TcaSchemaExtractor;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ErdController extends ActionController
{
    public function __construct(
        protected readonly TcaSchemaExtractor $tcaSchemaExtractor,
        protected readonly RelationResolver $relationResolver,
        protected readonly MermaidRenderer $mermaidRenderer,
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly PageRenderer $pageRenderer,
    ) {}

    public function indexAction(): ResponseInterface
    {
        $this->pageRenderer->addJsFooterFile('EXT:erd/Resources/Public/JavaScripts/ErdModule.js');
        $view = $this->moduleTemplateFactory->create($this->request);
        $this->assignFormData($view);
        return $view->renderResponse('Erd/Index');
    }

    public function generateAction(): ResponseInterface
    {
        $config = $this->buildConfigFromRequest();
        $rootTables = $this->resolveRootTables($config);

        $tableSchemas = [];
        $mermaidBlock = '';
        $markdown = '';
        $error = '';

        if (empty($rootTables)) {
            $error = 'No tables found. Select an extension or tables.';
        } else {
            $tableSchemas = $this->relationResolver->resolve($rootTables, $config);
            $mermaidBlock = $this->mermaidRenderer->renderMermaidBlock($tableSchemas);
            $markdown = $this->mermaidRenderer->renderMarkdown($tableSchemas, $config);
        }

        $this->pageRenderer->addJsFooterFile('EXT:erd/Resources/Public/JavaScripts/ErdModule.js');
        $view = $this->moduleTemplateFactory->create($this->request);
        $this->assignFormData($view);
        $view->assignMultiple([
            'tableSchemas' => $tableSchemas,
            'mermaidBlock' => $mermaidBlock,
            'markdown' => $markdown,
            'config' => $config,
            'error' => $error,
            'hasResult' => !empty($tableSchemas),
        ]);

        return $view->renderResponse('Erd/Generate');
    }

    public function downloadAction(): ResponseInterface
    {
        $config = $this->buildConfigFromRequest();
        $rootTables = $this->resolveRootTables($config);
        $tableSchemas = $this->relationResolver->resolve($rootTables, $config);
        $markdown = $this->mermaidRenderer->renderMarkdown($tableSchemas, $config);

        $filename = 'erd';
        if ($config->getExtensionKey() !== '') {
            $filename = 'erd-' . $config->getExtensionKey();
        }
        $filename .= '-' . date('Y-m-d') . '.md';

        $response = new Response();
        $response->getBody()->write($markdown);
        return $response
            ->withHeader('Content-Type', 'text/markdown; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    protected function assignFormData(object $view): void
    {
        $extensionsWithTables = $this->tcaSchemaExtractor->getAllExtensionsWithTables();
        $extensionOptions = [];
        foreach ($extensionsWithTables as $extKey => $tables) {
            $extensionOptions[$extKey] = $extKey . ' (' . count($tables) . ' tables)';
        }

        $allTables = array_keys($GLOBALS['TCA'] ?? []);
        sort($allTables);
        $allTableOptions = array_combine($allTables, $allTables);

        $view->assignMultiple([
            'extensionOptions' => $extensionOptions,
            'allTableOptions' => $allTableOptions,
        ]);
    }

    protected function buildConfigFromRequest(): ErdConfiguration
    {
        $config = new ErdConfiguration();

        $mode = $this->getRequestArgument('mode', 'extension');
        $extensionKey = $this->getRequestArgument('extensionKey', '');
        $tables = $this->getRequestArgument('tables', []);
        $depth = (int)$this->getRequestArgument('depth', '2');
        $lang = $this->getRequestArgument('lang', 'de');
        $includeInternal = (bool)$this->getRequestArgument('includeInternal', '0');
        $includeCoreTables = (bool)$this->getRequestArgument('includeCoreTables', '1');
        $checkDb = (bool)$this->getRequestArgument('checkDb', '0');
        $includeEmpty = (bool)$this->getRequestArgument('includeEmpty', '0');

        if ($mode === 'extension' && $extensionKey !== '') {
            $config->setExtensionKey($extensionKey);
        } elseif (is_array($tables)) {
            $config->setTables($tables);
        }

        $config->setDepth($depth);
        $config->setLang($lang);
        $config->setIncludeInternal($includeInternal);
        $config->setIncludeCoreTables($includeCoreTables);
        $config->setCheckDb($checkDb);
        $config->setIncludeEmpty($includeEmpty);

        return $config;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    protected function getRequestArgument(string $name, $default = null)
    {
        if ($this->request->hasArgument($name)) {
            return $this->request->getArgument($name);
        }
        return $default;
    }

    /**
     * @return string[]
     */
    protected function resolveRootTables(ErdConfiguration $config): array
    {
        if ($config->getExtensionKey() !== '') {
            return $this->tcaSchemaExtractor->getTablesForExtension($config->getExtensionKey());
        }
        return $config->getTables();
    }
}
