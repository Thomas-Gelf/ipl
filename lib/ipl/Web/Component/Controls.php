<?php

namespace ipl\Web\Component;

use ipl\Html\Container;
use ipl\Html\Html;

class Controls extends Container
{
    protected $contentSeparator = "\n";

    protected $defaultAttributes = array('class' => 'controls');

    /** @var Tabs */
    private $tabs;

    /** @var ActionBar */
    private $actions;

    /** @var string */
    private $title;

    /** @var string */
    private $subTitle;

    /**
     * @param $title
     * @param null $subTitle
     * @return $this
     */
    public function addTitle($title, $subTitle = null)
    {
        $this->title = $title;
        if ($subTitle !== null) {
            $this->subTitle = $subTitle;
        }

        return $this->add($this->renderTitleElement());
    }

    /**
     * @return Tabs
     */
    public function getTabs()
    {
        if ($this->tabs === null) {
            $this->tabs = new Tabs();
        }

        return $this->tabs;
    }

    /**
     * @return ActionBar
     */
    public function getActionBar()
    {
        if ($this->actions === null) {
            $this->actions = new ActionBar();
        }

        $this->add($this->actions);

        return $this->actions;
    }

    protected function renderTitleElement()
    {
        $h1 = Html::tag('h1')->setContent($this->title);
        if ($this->subTitle) {
            $h1->setSeparator(' ')->add(
                Html::tag('small', null, $this->subTitle)
            );
        }

        return $h1;
    }

    public function renderContent()
    {
        if (null !== $this->tabs) {
            $this->prepend($this->tabs);
        }

        return parent::renderContent();
    }
}
