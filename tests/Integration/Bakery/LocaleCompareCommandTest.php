<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use UserFrosting\Sprinkle\Core\Bakery\LocaleCompareCommand;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Test for LocaleCompareCommand (locale:compare)
 */
class LocaleCompareCommandTest extends TestCase
{
    protected Command $command;
    
    /**
     * {@inheritdoc}
     */
    // TODO : Could be moved to Unit with improvement in Locale class.
    public function setUp(): void
    {
        parent::setUp();

        $ci = ContainerStub::create();

        // Use test locale data
        $locator = new ResourceLocator(__DIR__ . '/data');
        $locator->registerStream('locale', '', null, true);
        $ci->set(ResourceLocatorInterface::class, $locator);

        // Force config to only three locales
        $config = $ci->get(Config::class);
        $config->set('site.locales.available', [
            'en_US' => true,
            'es_ES' => false,
            'fr_FR' => true,
        ]);

        // Command to test
        $this->command = $ci->get(LocaleCompareCommand::class);
    }

    public function testCommandWithArguments(): void
    {
        $result = BakeryTester::runCommand($this->command, [
            '--left'  => 'en_US',
            '--right' => 'fr_FR',
        ]);

        // Assert result
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Comparing `en_US` with `fr_FR`', $result->getDisplay());
    }

    /**
     * @depends testCommandWithArguments
     */
    public function testCommandWithNoDifferences(): void
    {
        $result = BakeryTester::runCommand($this->command, [
            '--left'  => 'en_US',
            '--right' => 'en_US',
        ]);
        
        // Assert results
        $this->assertSame(0, $result->getStatusCode());
        $output = $result->getDisplay();
        $this->assertStringContainsString('Comparing `en_US` with `en_US`', $output);
        $this->assertStringContainsString('No difference between the two locales.', $output);
        $this->assertStringContainsString('No missing keys.', $output);
        $this->assertStringContainsString('No empty values.', $output);
    }

    /**
     * @depends testCommandWithArguments
     */
    public function testCommand(): void
    {
        $result = BakeryTester::runCommand(
            command: $this->command, 
            userInput: ['en_US', 'fr_FR',]
        );
        
        // Assert results
        $this->assertSame(0, $result->getStatusCode());
        $output = $result->getDisplay();
        $this->assertStringContainsString('Comparing `en_US` with `fr_FR`', $output);
        $this->assertStringNotContainsString('No difference between the two locales.', $output);
        $this->assertStringNotContainsString('No missing keys.', $output);
        $this->assertStringNotContainsString('No empty values.', $output);
    }
}
