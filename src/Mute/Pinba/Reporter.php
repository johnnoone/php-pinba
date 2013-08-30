<?php

namespace Mute\Pinba;

use RuntimeException;

class Reporter
{
    public function __construct($host='127.0.0.1', $port=30002)
    {
        $this->address = is_array($host)
            ? $host
            : array($host, $port);
        $this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$this->sock) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new Exception($errormsg, $errorcode);
        }
    }

    public function __destruct()
    {
        socket_close($this->sock);
    }

    public function __invoke(DataCollector $collector)
    {
        $msg = $this->prepare($collector);

        return $this->send($msg);
    }

    public function prepare(DataCollector $collector)
    {
        $writer = new Writer();

        $writer->stringField(1, $collector->hostname);
        $writer->stringField(2, $collector->serverName);
        $writer->stringField(3, $collector->scriptName);
        $writer->integerField(4, 1);
        $writer->integerField(5, $collector->documentSize);
        $writer->integerField(6, $collector->memoryPeak);
        $writer->floatField(7, $collector->getElapsedTime());
        $writer->floatField(8, $collector->RUUtime);
        $writer->floatField(9, $collector->RUStime);

        $dictionary = new Dictionary();
        foreach ($collector->timers as $timer) {
            $writer->integerField(10, 1);
            $writer->floatField(11, $timer->getElapsedTime());

            $tag_count = 0;
            foreach (static::flattener($timer->tags) as $pair) {
                list($name, $value) = $pair;
                $writer->integerField(13, $dictionary->getIndex($name));
                $writer->integerField(14, $dictionary->getIndex($value));

                $tag_count += 1;
            }

            // tag_count
            $writer->integerField(12, $tag_count);
        }

        foreach ($dictionary->words as $value) {
            $writer->stringField(15, $value);
        }

        if (is_int($collector->status)) {
            $writer->integerField(16, $collector->status);
        }

        return $writer->buffer();
    }

    public function send($message)
    {
        list($server, $port) = $this->address;
        if (!socket_sendto($this->sock, $message, strlen($message), 0, $server, $port)) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new RuntimeException($errormsg, $errorcode);
        }
    }

    public static function flattener(array $data)
    {
        $flat = array();
        foreach ($data as $key => $value) {
            if (is_array($value)) foreach (self::flattener($value) as $subkey => $subvalue) {
                if (is_int($subkey)) {
                    $flat[] = array($key, (string) $subvalue);
                }
                else {
                    $flat[] = array($key . '.' . $subkey, (string) $subvalue);
                }
            }
            else {
                    $flat[] = array($key, (string) $value);
            }
        }

        return $flat;
    }
}
