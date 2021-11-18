<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

/**
 * US English message token translations for the 'core' sprinkle.
 */

return [
    'ERROR' => [
        '@TRANSLATION' => 'Error',

        '400' => [
            'TITLE'       => 'Error 400: Bad Request',
            'DESCRIPTION' => 'The server cannot or will not process the request due to an apparent client error.',
        ],
        '401' => [
            'TITLE'       => 'Error 401: Unauthorized',
            'DESCRIPTION' => 'The request requires valid user authentication.',
        ],
        '403' => [
            'TITLE'       => 'Error 403: Forbidden',
            'DESCRIPTION' => 'You are not permitted to perform the requested operation.',
        ],
        '404' => [
            'TITLE'       => 'Error 404: Not Found',
            'DESCRIPTION' => 'The requested resource could not be found.',
        ],
        '405' => [
            'TITLE'       => 'Error 405: Method Not Allowed',
            'DESCRIPTION' => 'The request method is not supported for the requested resource.',
        ],
        '410' => [
            'TITLE'       => 'Error 410: Gone',
            'DESCRIPTION' => 'The target resource is no longer available at the origin server.',
        ],

        'CONFIG' => [
            'TITLE'       => 'UserFrosting Configuration Issue!',
            'DESCRIPTION' => 'Some UserFrosting configuration requirements have not been met.',
            'DETAIL'      => "Something's not right here.",
            'RETURN'      => 'Please fix the following errors, then <a href="{{url}}">reload</a>.',
        ],

        'DESCRIPTION' => "Oops, looks like our server might have goofed. If you're an admin, please check the PHP or UserFrosting logs.",
        'DETAIL'      => "Here's what we got:",

        'ENCOUNTERED' => "Uhhh...something happened.  We don't know what.",

        'MAIL' => 'Fatal error attempting mail, contact your server administrator.  If you are the admin, please check the UserFrosting log.',

        'RETURN' => 'Click <a href="{{url}}">here</a> to return to the front page.',

        'TITLE' => "We've sensed a great disturbance in the Force.",
    ],
];
