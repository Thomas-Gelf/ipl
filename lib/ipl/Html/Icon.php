<?php

namespace ipl\Html;

class Icon extends BaseElement
{
    protected $tag = 'i';

    protected function __construct()
    {
    }

    /**
     * @param string $name
     * @param array $attributes
     *
     * @return static
     */
    public static function create($name, array $attributes = null)
    {
        return new static($name, $attributes);
    }

    public function forcesClosingTag()
    {
        return true;
    }
}
