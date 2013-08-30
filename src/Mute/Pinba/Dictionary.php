<?php

namespace Mute\Pinba;

class Dictionary
{
    public $words;

    public function __construct()
    {
        $this->words = array();
    }

    public function getIndex($word)
    {
        $index = array_search($word, $this->words);
        if ($index == false) {
            $index = count($this->words);
            $this->words[] = $word;
        }

        return $index;
    }
}
