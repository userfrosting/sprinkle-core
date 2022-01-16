<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Listeners;

use DI\FactoryInterface;
use UserFrosting\Event\AppInitiatedEvent;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Event Listener for AppInitiatedEvent event.
 * Manually Inject the ContainerInterface into Abstract Model class.
 */
class ModelInitiated
{
    /**
     * @param FactoryInterface $ci
     */
    public function __construct(
        protected FactoryInterface $ci,
    ) {
    }

    /**
     * @param AppInitiatedEvent $event
     */
    public function __invoke(AppInitiatedEvent $event): void
    {
        Model::$ci = $this->ci;
    }
}
