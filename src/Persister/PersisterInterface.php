<?php

namespace App\Persister;

interface PersisterInterface
{
    public function load();

    public function flush(object $content): void;
}
