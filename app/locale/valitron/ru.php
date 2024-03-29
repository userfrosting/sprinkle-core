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
    'required'      => 'обязательно для заполнения',
    'equals'        => "должно содержать '%s'",
    'different'     => "должно отличаться от '%s'",
    'accepted'      => 'должно быть указано',
    'numeric'       => 'должно содержать числовое значение',
    'integer'       => 'должно быть числом',
    'length'        => 'должно быть длиннее, чем %d',
    'min'           => 'должно быть больше, чем %s',
    'max'           => 'должно быть меньше, чем %s',
    'in'            => 'содержит неверное значение',
    'notIn'         => 'содержит неверное значение',
    'ip'            => 'не является валидным IP адресом',
    'email'         => 'не является валидным email адресом',
    'url'           => 'не является ссылкой',
    'urlActive'     => 'содержит не активную ссылку',
    'alpha'         => 'должно содержать только латинские символы',
    'alphaNum'      => 'должно содержать только латинские символы и/или цифры',
    'slug'          => 'должно содержать только латинские символы, цифры, тире и подчёркивания',
    'regex'         => 'содержит недопустимые символы',
    'date'          => 'не является датой',
    'dateFormat'    => 'должно содержать дату следующего формата: %s',
    'dateBefore'    => 'должно содержать дату не позднее, чем %s',
    'dateAfter'     => 'должно содержать дату не ранее, чем %s',
    'contains'      => 'должно содержать %s',
    'boolean'       => 'должно содержать логическое значение',
    'lengthBetween' => 'должно содержать от %d до %d символов',
    'creditCard'    => 'должно быть номером кредитной карты',
    'lengthMin'     => 'должно содержать более %d символов',
    'lengthMax'     => 'должно содержать менее %d символов',
];
