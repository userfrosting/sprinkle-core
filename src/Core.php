<?php

/**
 * Bootstrapper class for the 'core' sprinkle.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com
 */
 
namespace UserFrosting\Sprinkle\Core;

use UserFrosting\Sprinkle\Core\ServicesProvider\CoreServicesProvider;
use UserFrosting\Sprinkle\Core\Initialize\Sprinkle;

class Core extends Sprinkle
{

    public function init()
    { 
        // Register default UserFrosting services
        $serviceProvider = new CoreServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
