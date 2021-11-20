<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Log;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;

/**
 * Monolog handler for storing the record to a database.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class DatabaseHandler extends AbstractProcessingHandler
{
    /**
     * @var ClassMapper
     */
    protected $classMapper;

    /**
     * @var string
     */
    protected $modelIdentifier;

    /**
     * Create a new DatabaseHandler object.
     *
     * @param ClassMapper $classMapper     Maps the modelIdentifier to the specific Eloquent model.
     * @param string      $modelIdentifier
     * @param int         $level           The minimum logging level at which this handler will be triggered
     * @param bool        $bubble          Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(ClassMapper $classMapper, $modelIdentifier, $level = Logger::DEBUG, $bubble = true)
    {
        $this->classMapper = $classMapper;
        $this->modelName = $modelIdentifier;
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        $log = $this->classMapper->createInstance($this->modelName, $record['extra']);
        $log->save();
    }
}