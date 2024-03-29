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
    'required'      => 'ต้องการ',
    'equals'        => "จะต้องเหมือนกับ '%s'",
    'different'     => "จะต้องไม่ใช่ '%s'",
    'accepted'      => 'จะต้องยอมรับ',
    'numeric'       => 'จะต้องเป็นตัวเลข',
    'integer'       => 'จะต้องเป็นตัวเลขหลักเดียว (0-9)',
    'length'        => 'จะต้องมีความยาวมากกว่า %d',
    'min'           => 'จะต้องมีอย่างน้อย %s',
    'max'           => 'จะต้องมีไม่มากไปกว่า %s',
    'in'            => 'ประกอบด้วยค่าที่ไม่ถูกต้อง',
    'notIn'         => 'ประกอบด้วยค่าที่ไม่ถูกต้อง',
    'ip'            => 'ไม่ใช่ที่อยู่ไอพีที่ถูกต้อง',
    'email'         => 'ไม่ใช่ที่อยู่อีเมลที่ถูกต้อง',
    'url'           => 'ไม่ใช่ลิงก์',
    'urlActive'     => 'จะต้องเป็นโดเมนที่มีการใช้งานอยู่',
    'alpha'         => 'จะต้องประกอบไปด้วยตัวอักษร a-z เท่านั้น',
    'alphaNum'      => 'จะต้องประกอบไปด้วยตัวอักษร a-z และ/หรือ เลข 0-9',
    'slug'          => 'จะต้องประกอบไปด้วยตัวอักษร a-z เลข 0-9 ขีดกลาง และขีดล่าง',
    'regex'         => 'ประกอบด้วยอักขระที่ไม่ถูกต้อง',
    'date'          => 'ไม่ใช่วันที่ที่ถูกต้อง',
    'dateFormat'    => "จะต้องเป็นวันที่ที่มีรูปแบบ '%s'",
    'dateBefore'    => "จะต้องเป็นวันที่ก่อน '%s'",
    'dateAfter'     => "จะต้องเป็นวันที่หลังจาก '%s'",
    'contains'      => 'จะต้องประกอบไปด้วย %s',
    'boolean'       => 'จะต้องเป็นใช่ หรือ ไม่ใช่',
    'lengthBetween' => 'จะต้องอยู่ระหว่าง %d ถึง %d ตัวอักษร',
    'creditCard'    => 'จะต้องเป็นหมายเลขบัตรเครดิตที่ถูกต้อง',
    'lengthMin'     => 'จะต้องมีความยาวมากกว่า %d ตัวอักษร',
    'lengthMax'     => 'จะต้องมีความยาวน้อยกว่า %d ตัวอักษร',
    'instanceOf'    => "จะต้องเป็นกรณีของ '%s'",
];
