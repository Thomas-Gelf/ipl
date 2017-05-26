<?php

namespace ipl\Html;

use Exception;

class DeferredText implements ValidHtml
{
    /** @var callable */
    protected $callback;

    protected $escaped = false;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public static function create(callable $callback)
    {
        return new static($callback);
    }

    /**
     * @return string
     */
    public function render()
    {
        $callback = $this->callback;
        return Util::escapeForHtml($callback());
    }

    /**
     * @param Exception|string $error
     * @return string
     */
    protected function renderError($error)
    {
        return Util::renderError($error);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $e) {
            return $this->renderError($e);
        }
    }
}
