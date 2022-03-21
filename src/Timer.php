<?php

declare(strict_types=1);

namespace Gett\MyparcelBE;

class Timer
{
    /**
     * @var float
     */
    private $time;

    public function __construct() {
        $this->time = microtime(true);
    }

    /**
     * @return int
     */
    public function getTimeTaken(): int
    {
        return (int) ((microtime(true) - $this->time) * 1000);
    }
}
