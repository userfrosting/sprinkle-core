<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

return [
    'required'      => 'is required',
    'equals'        => "must be the same as '%s'",
    'different'     => "must be different than '%s'",
    'accepted'      => 'must be accepted',
    'numeric'       => 'must be numeric',
    'integer'       => 'must be an integer (0-9)',
    'length'        => 'must be longer than %d',
    'min'           => 'must be at least %s',
    'max'           => 'must be no more than %s',
    'in'            => 'contains invalid value',
    'notIn'         => 'contains invalid value',
    'ip'            => 'is not a valid IP address',
    'email'         => 'is not a valid email address',
    'url'           => 'not a URL',
    'urlActive'     => 'must be an active domain',
    'alpha'         => 'must contain only letters a-z',
    'alphaNum'      => 'must contain only letters a-z and/or numbers 0-9',
    'slug'          => 'must contain only letters a-z, numbers 0-9, dashes and underscores',
    'regex'         => 'contains invalid characters',
    'date'          => 'is not a valid date',
    'dateFormat'    => "must be date with format '%s'",
    'dateBefore'    => "must be date before '%s'",
    'dateAfter'     => "must be date after '%s'",
    'contains'      => 'must contain %s',
    'boolean'       => 'must be a boolean',
    'lengthBetween' => 'must be between %d and %d characters',
    'creditCard'    => 'must be a valid credit card number',
    'lengthMin'     => 'must contain greater than %d characters',
    'lengthMax'     => 'must contain less than %d characters',
    'instanceOf'    => "must be an instance of '%s'",
];
