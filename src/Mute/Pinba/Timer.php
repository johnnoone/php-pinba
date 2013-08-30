<?php

namespace Mute\Pinba;

class Timer extends Bases\Timed
{
    public function __construct(array $tags, array $data=null, DataCollector $parent=null)
    {
        $this->tags = $tags;
        $this->data = $data;
        $this->parent = $parent;
    }

    function delete()
    {
        if ($this->parent) {
            $key = array_search($this, $this->parent->timers);
            if ($key !== false) {
                unset($this->parent->timers[$key]);
            }

            unset($this->parent);
        }

        return $this;
    }

    function __clone()
    {
        $timer = new Timer($this->tags, $this->data, $this->parent);
        if ($this->parent) {
            $this->parent->timers[] = $timer;
        }

        return $timer;
    }

    function start()
    {
        $this->_start();

        return $this;
    }

    function stop()
    {
        $this->_stop();

        return $this;
    }

}
