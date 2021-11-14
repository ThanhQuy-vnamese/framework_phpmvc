<?php

declare(strict_types=1);

namespace App\Adapter;

use stdClass;

class ModelAdapter
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function setDataToAttributes()
    {
        $object = new stdClass();
        foreach ($this->data as $key => $value) {
            $object->$key = (object) $value;
        }

        return $object;
    }
}