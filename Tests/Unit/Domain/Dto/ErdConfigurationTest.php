<?php

declare(strict_types=1);

namespace Denic\Erd\Tests\Unit\Domain\Dto;

use Denic\Erd\Domain\Dto\ErdConfiguration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ErdConfigurationTest extends TestCase
{
    #[Test]
    public function defaultValues(): void
    {
        $config = new ErdConfiguration();

        self::assertSame([], $config->getTables());
        self::assertSame('', $config->getExtensionKey());
        self::assertSame(2, $config->getDepth());
        self::assertFalse($config->isIncludeInternal());
        self::assertTrue($config->isIncludeCoreTables());
        self::assertSame('de', $config->getLang());
        self::assertFalse($config->isCheckDb());
        self::assertFalse($config->isIncludeEmpty());
    }

    #[Test]
    public function settersReturnSelfForFluent(): void
    {
        $config = new ErdConfiguration();

        self::assertSame($config, $config->setTables(['pages']));
        self::assertSame($config, $config->setExtensionKey('blog'));
        self::assertSame($config, $config->setDepth(3));
        self::assertSame($config, $config->setIncludeInternal(true));
        self::assertSame($config, $config->setIncludeCoreTables(false));
        self::assertSame($config, $config->setLang('en'));
        self::assertSame($config, $config->setCheckDb(true));
        self::assertSame($config, $config->setIncludeEmpty(true));
    }

    #[Test]
    public function settersUpdateValues(): void
    {
        $config = (new ErdConfiguration())
            ->setTables(['pages', 'tt_content'])
            ->setExtensionKey('blog')
            ->setDepth(-1)
            ->setIncludeInternal(true)
            ->setIncludeCoreTables(false)
            ->setLang('en')
            ->setCheckDb(true)
            ->setIncludeEmpty(true);

        self::assertSame(['pages', 'tt_content'], $config->getTables());
        self::assertSame('blog', $config->getExtensionKey());
        self::assertSame(-1, $config->getDepth());
        self::assertTrue($config->isIncludeInternal());
        self::assertFalse($config->isIncludeCoreTables());
        self::assertSame('en', $config->getLang());
        self::assertTrue($config->isCheckDb());
        self::assertTrue($config->isIncludeEmpty());
    }
}
