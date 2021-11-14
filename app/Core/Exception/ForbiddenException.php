<?php


namespace App\Core\Exception;


use Exception;

class ForbiddenException extends Exception
{
    protected $message = 'You don\' have permission to access this page';
    protected $code = 403;
}
