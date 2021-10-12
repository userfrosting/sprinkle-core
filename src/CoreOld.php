<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core;

use RocketTheme\Toolbox\Event\Event;
use UserFrosting\Sprinkle\Core\Csrf\SlimCsrfProvider;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\I18n\LocaleServicesProvider;
use UserFrosting\Sprinkle\Core\I18n\TranslatorService;
use UserFrosting\Sprinkle\Core\Util\EnvironmentInfo;
use UserFrosting\Sprinkle\Core\Util\ShutdownHandler;
use UserFrosting\System\Sprinkle\Sprinkle;

/**
 * Bootstrapper class for the core sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CoreOld extends Sprinkle
{
    /**
     * @var string[] List of services provider to register
     */
    protected $servicesproviders = [
        LocaleServicesProvider::class,
        TranslatorService::class,
    ];

    /**
     * Defines which events in the UF lifecycle our Sprinkle should hook into.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onSprinklesInitialized'      => ['onSprinklesInitialized', 0],
            'onSprinklesRegisterServices' => ['onSprinklesRegisterServices', 0],
            'onAddGlobalMiddleware'       => ['onAddGlobalMiddleware', 0],
            'onAppInitialize'             => ['onAppInitialize', 0],
        ];
    }

    /**
     * Set static references to DI container in necessary classes.
     */
    public function onSprinklesInitialized()
    {
        // Set container for data model
        // Model::$ci = $this->ci;

        // Set container for environment info class
        EnvironmentInfo::$ci = $this->ci;
    }

    /**
     * Register all sprinkles services providers.
     */
    public function onSprinklesRegisterServices()
    {
        $this->setupShutdownHandlerService();
    }

    /**
     * Steps required to register the ShutdownHandler Service.
     * Get shutdownHandler set up.  This needs to be constructed explicitly because it's invoked natively by PHP.
     *
     * @TODO: Move to it's own serviceProvider class (Target UF 5.0)
     */
    public function setupShutdownHandlerService(): void
    {
        // Set up any global PHP settings from the config service.
        $config = $this->ci->config;

        // Display PHP fatal errors natively.
        if (isset($config['php.display_errors_native'])) {
            ini_set('display_errors', $config['php.display_errors_native']);
        }

        // Log PHP fatal errors
        if (isset($config['php.log_errors'])) {
            ini_set('log_errors', $config['php.log_errors']);
        }

        // Configure error-reporting level
        if (isset($config['php.error_reporting'])) {
            error_reporting($config['php.error_reporting']);
        }

        // Configure time zone
        if (isset($config['php.timezone'])) {
            date_default_timezone_set($config['php.timezone']);
        }

        // Determine if error display is enabled in the shutdown handler.
        $displayErrors = false;
        if (in_array(strtolower($config['php.display_errors']), [
            '1',
            'on',
            'true',
            'yes',
        ])) {
            $displayErrors = true;
        }

        $sh = new ShutdownHandler($this->ci, $displayErrors);
        $sh->register();
    }

    /**
     * Add CSRF middleware.
     *
     * @param Event $event
     */
    public function onAddGlobalMiddleware(Event $event)
    {
        // Don't register CSRF if CLI
        if (!$this->ci->cli) {
            SlimCsrfProvider::registerMiddleware($event->getApp(), $this->ci->request, $this->ci->csrf);
        }
    }
}
