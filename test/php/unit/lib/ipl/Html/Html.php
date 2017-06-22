<?php

namespace test\ipl\Html;

use ipl\Html\Html;
use ipl\Test\BaseTestCase;

class HtmlTest extends BaseTestCase
{
    public function testStaticCallsGiveValidElements()
    {
        $this->assertInstanceOf(
            'ipl\\Html\\Html',
            Html::span()
        );
        /*
        $this->assertXmlStringEqualsXmlString(
            '<a href="/base/url/some/url?param=one" title="Some information" class="some more">Label</a>',
            $this->simpleLink()->render()
        );
*/
    }

    public function testStaticCallsAcceptContentAsFirstAttribute()
    {
        $this->assertXmlStringEqualsXmlString(
            '<span>&gt;5</span>',
            Html::span('>5')->render()
        );
        $this->assertXmlStringEqualsXmlString(
            '<span>&gt;5</span>',
            Html::span(['>5'])->render()
        );
        $this->assertXmlStringEqualsXmlString(
            '<span><b>&gt;5</b>&lt;</span>',
            Html::span(Html::b(['>5']), '<')->render()
        );
    }

    public function testStaticCallsAcceptAttributesAsFirstAttribute()
    {
        $this->assertXmlStringEqualsXmlString(
            '<span class="test it" />',
            Html::span(['class' => 'test it'])->render()
        );
        $this->assertXmlStringEqualsXmlString(
            '<span class="test it">&gt;5</span>',
            Html::span(['class' => 'test it'], '>5')->render()
        );
    }

    public function testAttributesAndContentAreAccepted()
    {
        $this->assertXmlStringEqualsXmlString(
            '<span class="test it">&gt;5</span>',
            Html::span(['class' => 'test it'], ['>5'])->render()
        );
    }
}
