<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\CommandReceipe;
use UserFrosting\Sprinkle\SprinkleManager;

/**
 * Sprinkle:list CLI tool.
 */
class SprinkleListCommand extends CommandReceipe
{
    /**
     * @var array The table header
     */
    protected $headers = ['Sprinkle', 'Path'];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sprinkle:list')
             ->setDescription('List all available sprinkles and their params');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Loaded Sprinkles');

        /** @var \UserFrosting\System\Sprinkle\SprinkleManager $sprinkleManager */
        $sprinkleManager = $this->ci->get(SprinkleManager::class);

        // Get sprinkle list
        $sprinkles = $sprinkleManager->getSprinkles();

        // Compile the routes into a displayable format
        $sprinklesTable = collect($sprinkles)->map(function ($sprinkle) {
            return [
                'sprinkle'  => $sprinkle::getName(),
                'path'      => $sprinkle::getPath(),
            ];
        })->all();

        // Display table
        $this->io->table($this->headers, $sprinklesTable);

        return self::SUCCESS;
    }
}
