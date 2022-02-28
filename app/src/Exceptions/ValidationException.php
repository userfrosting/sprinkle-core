<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Exceptions;

use Exception;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\UserMessageException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Validation related Exception.
 */
final class ValidationException extends Exception implements UserMessageException
{
    /**
     * @var string[]
     */
    protected array $errors = [];

    /**
     * Add errors returned by Valitron/Validator.
     *
     * @param string[][] $errors
     *
     * @return static
     */
    public function addErrors(array $errors): static
    {
        foreach ($errors as $field) {
            foreach ($field as $error) {
                $this->addError($error);
            }
        }

        return $this;
    }

    /**
     * @param string $error
     *
     * @return static
     */
    public function addError(string $error): static
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle(): string|UserMessage
    {
        return 'VALIDATE.ERROR';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string|UserMessage
    {
        return implode(' ', $this->getErrors());
    }
}
