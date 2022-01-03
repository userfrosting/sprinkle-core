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
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\ExtensionInterface;
use UserFrosting\Sprinkle\Core\Twig\WebpackEncore\TagRenderer;
use UserFrosting\Sprinkle\Core\Twig\WebpackEncore\WebpackEncoreTwigExtension;

/**
 * Tests for WebpackEncoreTwigExtension.
 */
class WebpackEncoreTwigExtensionTest extends TestCase
{
    protected EntrypointLookupInterface $entryPoints;
    protected TagRenderer $tagRenderer;
    protected ExtensionInterface $extension;
    protected Twig $view;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entryPoints = new EntrypointLookup(__DIR__ . '/entrypoints.json');
        $this->tagRenderer = new TagRenderer($this->entryPoints);
        $this->extension = new WebpackEncoreTwigExtension($this->entryPoints, $this->tagRenderer);

        // Create dumb Twig and test adding extension
        $this->view = Twig::create('');
        $this->view->addExtension($this->extension);
    }

    public function testEncoreEntryJsFiles(): void
    {
        $expected = [
            '/assets/runtime.js',
            '/assets/vendors-node_modules_bootstrap-6feb83.js',
            '/assets/admin.js'
        ];

        $result = $this->view->fetchFromString("{{ encore_entry_js_files('admin')|join(', ') }}");
        $this->assertSame(implode(', ', $expected), $result);
    }

    public function testEncoreEntryCssFiles(): void
    {
        $expected = [
            '/assets/vendors-node_modules_bootstrap-6feb83.css',
            '/assets/admin.css'
        ];

        $result = $this->view->fetchFromString("{{ encore_entry_css_files('admin')|join(', ') }}");
        $this->assertSame(implode(', ', $expected), $result);
    }

    public function testEncoreEntryScriptTags(): void
    {
        $expected = [
            '<script src="/assets/runtime.js"></script>',
            '<script src="/assets/vendors-node_modules_bootstrap-6feb83.js"></script>',
            '<script src="/assets/admin.js"></script>'
        ];

        $result = $this->view->fetchFromString("{{ encore_entry_script_tags('admin') }}");
        $this->assertSame(implode('', $expected), $result);
    }

    public function testEncoreEntryLinkTags(): void
    {
        $expected = [
            '<link rel="stylesheet" href="/assets/vendors-node_modules_bootstrap-6feb83.css">',
            '<link rel="stylesheet" href="/assets/admin.css">'
        ];

        $result = $this->view->fetchFromString("{{ encore_entry_link_tags('admin') }}");
        $this->assertSame(implode('', $expected), $result);
    }
}
