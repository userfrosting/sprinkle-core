<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Twig\WebpackEncore;

use PHPUnit\Framework\TestCase;
use Slim\Views\Twig;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Twig\Extension\ExtensionInterface;
use UserFrosting\Sprinkle\Core\Twig\WebpackEncore\VersionedAssetsTwigExtension;

/**
 * Tests for VersionedAssetsTwigExtension.
 */
class VersionedAssetsTwigExtensionTest extends TestCase
{
    protected JsonManifestVersionStrategy $manifest;
    protected ExtensionInterface $extension;
    protected Twig $view;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manifest = new JsonManifestVersionStrategy(__DIR__ . '/manifest.json');
        $this->extension = new VersionedAssetsTwigExtension($this->manifest);

        // Create dumb Twig and test adding extension
        $this->view = Twig::create('');
        $this->view->addExtension($this->extension);
    }

    /**
     * @dataProvider pathDataProvider
     */
    public function testFunction(string $asset, string $versioned): void
    {
        $this->assertSame($versioned, $this->manifest->applyVersion($asset));
        $result = $this->view->fetchFromString("{{ asset('" . $asset . "') }}");
        $this->assertSame($versioned, $result);
    }

    /**
     * @return string[][]
     */
    public function pathDataProvider(): array
    {
        return [
            ['assets/images/cupcake.png', '/assets/images/cupcake.6714f07e.png'],
            ['assets/admin.css', '/assets/admin.css'],
            ['assets/admin.js', 'assets/admin.js'], // Not in manifest.json. Will be returned as is.
        ];
    }
}
