<?php


namespace App\Core\Storage;


use App\Core\Application;

class Storage
{
    public function __construct()
    {
        var_dump(Application::$ROOT_DIR);die;
    }
}