<?php

namespace Mute\Pinba\Bases;

use RuntimeException;

abstract class Timed
{
    const INITIALIZED = 0;
    const STARTED = 1;
    const STOPED = 2;

    private $state;
    private $tt_elapsed;
    protected $tt_start;
    protected $tt_end;

    public function __construct()
    {
        $this->state = static::INITIALIZED;
    }

    function isStarted()
    {
        return $this->state == static::STARTED;
    }

    function getElapsedTime()
    {
        if ($this->state == static::STOPED) {
            return $this->tt_elapsed;
        }
    }

    protected function _start()
    {
        if ($this->state == static::STARTED) {
            throw new RuntimeException('Already started');
        }
        $this->state = static::STARTED;
        $this->tt_start = microtime(true);
    }

    protected function _stop()
    {
        if ($this->state != static::STARTED) {
            throw new RuntimeException('Not started');
        }
        $this->state = static::STOPED;
        $this->tt_end = microtime(true);
        $this->tt_elapsed = $this->tt_end - $this->tt_start;
    }

    protected function _flush()
    {
        $this->tt_start = microtime(true);
        $this->state = static::STARTED;
    }
}
