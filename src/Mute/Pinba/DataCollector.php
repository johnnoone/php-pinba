<?php

namespace Mute\Pinba;
use SplObjectStorage;

class DataCollector extends Bases\Timed
{
    public $hostname;
    public $serverName;
    public $scriptName;
    public $documentSize;
    public $memoryPeak;
    public $RUUtime;
    public $RUStime;
    public $timers;
    public $status;

    public function __construct($scriptname='', $hostname='')
    {
        $this->serverName = '';
        $this->scriptName = $scriptname;
        $this->hostname = $hostname;
        $this->documentSize = 0;
        $this->memoryPeak = 0;
        $this->RUUTime = '';
        $this->RUStime = '';
        $this->status = 200;
        $this->timers = array();
    }

    public function timer(array $tags, array $data=null)
    {
        $timer = new Timer($tags, $data);

        return $this->timers[] = $timer;
    }

    function start()
    {
        $this->_start();

        return $this;
    }

    function stop()
    {
        $this->_stop();
        foreach ($this->timers as $timer) if ($timer->isStarted()) {
            $timer->stop();
        }

        return $this;
    }


    function flush()
    {
        $this->_flush();
        $this->timers = array();

        return $this;
    }
}
