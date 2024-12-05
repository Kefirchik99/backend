<?php

namespace App\Models;

abstract class Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    abstract public function insert(array $data);
}
