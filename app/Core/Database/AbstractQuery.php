<?php


namespace App\Core\Database;


abstract class AbstractQuery
{
    abstract public function tableName(): string;
}