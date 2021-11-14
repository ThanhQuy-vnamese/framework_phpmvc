<?php

declare(strict_types=1);

namespace App\Adapter;

abstract class BaseAdapter
{
    abstract public function set($data);

    abstract public function get();
}