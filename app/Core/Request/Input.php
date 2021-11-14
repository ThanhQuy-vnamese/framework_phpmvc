<?php

declare(strict_types=1);

namespace App\Core\Request;

class Input
{
    public array $input;

    public function __construct(array $input = [])
    {
        $this->input = $input;
    }

    public function get($key)
    {
        return $this->input[$key] ?? '';
    }

    public function getAll(): array
    {
        return $this->input;
    }

    public function getInt($key): int
    {
        $value = $this->input[$key] ?? '';
        return (int)$value;
    }

    public function getString($key): string
    {
        $value = $this->input[$key] ?? '';
        return (string)$value;
    }
}