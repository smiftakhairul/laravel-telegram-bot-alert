<?php

namespace App\Enum;

trait StatusEnum
{
    public static $_SUCCESS = 'SUCCESS';
    public static $_ERROR = 'ERROR';
    public static $_UNKNOWN = 'ERROR';

    public static $_SUCCESS_CODE = 200;
    public static $_ERROR_CODE = 400;
    public static $_UNKNOWN_CODE = 520;
}
