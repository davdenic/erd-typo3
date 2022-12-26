<?php

declare(strict_types=1);

namespace Denic\Erd\Command;

use Denic\Erd\Domain\Dto\ErdConfiguration;
use Denic\Erd\Domain\Service\MermaidRenderer;
use Denic\Erd\Domain\Service\RelationResolver;
use Denic\Erd\Domain\Service\TcaSchemaExtractor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateErdCommand extends Command
{
    protected TcaSchemaExtractor $tcaSchemaExtractor;
    protected RelationResolver $relationResolver;
    protected MermaidRenderer $mermaidRenderer;

    public function __construct(
        TcaSchemaExtractor $tcaSchemaExtractor,
        RelationResolver $relationResolver,
        MermaidRenderer $mermaidRenderer,
        ?string $name = null
    ) {
        $this->tcaSchemaExtractor = $tcaSchemaExtractor;
        $this->relationResolver = $relationResolver;
        $this->mermaidRenderer = $mermaidRenderer;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Generate ER diagram from TCA');
        $this->addArgument('tables', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Table names (optional if --extension is used)');
        $this->addOption('extension', 'e', InputOption::VALUE_REQUIRED, 'Extension key (alternative to listing tables)');
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file path (default: stdout)');
        $this->addOption('depth', 'd', InputOption::VALUE_REQUIRED, 'Relationship depth: 0, 1, 2, -1=unlimited', '2');
        $this->addOption('lang', 'l', InputOption::VALUE_REQUIRED, 'Label language', 'de');
        $this->addOption('include-internal', null, InputOption::VALUE_NONE, 'Include internal TYPO3 fields');
        $this->addOption('no-core-tables', null, InputOption::VALUE_NONE, 'Exclude sys_category, sys_file_reference');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tables = $input->getArgument('tables');
        $extensionKey = (string)$input->getOption('extension');

        if (empty($tables) && $extensionKey === '') {
            $output->writeln('<error>Provide table names or --extension</error>');
            return Command::FAILURE;
        }

        $config = new ErdConfiguration();
        $config->setDepth((int)$input->getOption('depth'));
        $config->setLang((string)$input->getOption('lang'));
        $config->setIncludeInternal((bool)$input->getOption('include-internal'));
        $config->setIncludeCoreTables(!$input->getOption('no-core-tables'));

        if ($extensionKey !== '') {
            $config->setExtensionKey($extensionKey);
            $rootTables = $this->tcaSchemaExtractor->getTablesForExtension($extensionKey);
            if (empty($rootTables)) {
                $output->writeln('<error>No TCA tables found for extension "' . $extensionKey . '"</error>');
                return Command::FAILURE;
            }
            $output->writeln('<info>Extension "' . $extensionKey . '" — tables: ' . implode(', ', $rootTables) . '</info>', OutputInterface::VERBOSITY_VERBOSE);
        } else {
            $rootTables = $tables;
            $config->setTables($tables);
        }

        $tableSchemas = $this->relationResolver->resolve($rootTables, $config);

        if (empty($tableSchemas)) {
            $output->writeln('<error>No tables resolved</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Resolved ' . count($tableSchemas) . ' tables</info>', OutputInterface::VERBOSITY_VERBOSE);

        $markdown = $this->mermaidRenderer->renderMarkdown($tableSchemas, $config);

        $outputFile = $input->getOption('output');
        if ($outputFile) {
            $dir = dirname($outputFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents($outputFile, $markdown);
            $output->writeln('<info>Written to ' . $outputFile . '</info>');
        } else {
            $output->write($markdown);
        }

        return Command::SUCCESS;
    }
}
