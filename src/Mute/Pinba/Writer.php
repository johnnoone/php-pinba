<?php

namespace Mute\Pinba;

use RuntimeException;

class Writer
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var resource|null
     */
    private $local_resource;

    public function __construct($resource=null)
    {
        if (!$resource) {
            $this->local_resource = $resource = fopen('php://memory', 'wb');
        }
        $this->resource = $resource;
    }

    public function __destruct()
    {
        if ($this->local_resource) {
            fclose($this->local_resource);
        }
    }

    public function buffer()
    {
        fseek($this->resource, 0, SEEK_SET);

        return stream_get_contents($this->resource);
    }

    public function writeString($value)
    {
        $bytes = $value;
        $length = strlen($bytes);
        $this->writeInteger($length);

        return $this->write($bytes, $length);
    }

    public function writeFloat($value)
    {
        static $packer;

        if (!$packer) {
            list(, $result) = unpack('L', pack('V', 1));
            $packer = ($result === 1)
                ? function ($value) { return pack('f*', $value); }
                : function ($value) { return strrev(pack('f*', $value)); };
        }
        $bytes = $packer($value);

        return $this->write($bytes, 4);
    }

    public function writeInteger($value)
    {
        if ($value < 0x80) {

            return $this->write(chr($value), 1);
        }

        $values = array('C*');
        do {
            $values[] = 0x80 | ($value & 0x7f);
            $value = $value >> 7;
        } while ($value > 0);

        // last MSB flag removal
        end($values);
        $values[key($values)] &= 0x7f;

        $bytes = call_user_func_array('pack', $values);
        $length = strlen($bytes);

        return $this->write($bytes, $length);
    }

    public function write($bytes, $length)
    {
        fwrite($this->resource, $bytes, $length);

        return $this;
    }

    public function stringField($field_id, $field_value)
    {
        $this->writeInteger($field_id << 3 | 2);
        $this->writeString($field_value);

        return $this;
    }

    public function integerField($field_id, $field_value)
    {
        $this->writeInteger($field_id << 3 | 0);
        $this->writeInteger($field_value);

        return $this;
    }

    public function floatField($field_id, $field_value)
    {
        $this->writeInteger($field_id << 3 | 5);
        $this->writeFloat($field_value);

        return $this;
    }
}
