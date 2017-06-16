<?php

namespace test\ipl\Html;

use ipl\Html\Link;
use ipl\Test\BaseTestCase;
use ipl\Web\FakeRequest;

class LinkTest extends BaseTestCase
{
    public function testCanBeCreated()
    {
        $this->assertInstanceOf(
            'ipl\\Html\\Link',
            $this->simpleLink()
        );
    }

    public function testRendersExpectedHtml()
    {
        $this->assertXmlStringEqualsXmlString(
            '<a href="/base/url/some/url?param=one" title="Some information" class="some more">Label</a>',
            $this->simpleLink()->render()
        );
    }

    public function testOutputsAttributesInTheExpectedOrder()
    {
        // Parameter ordering
        $this->assertEquals(
            '<a href="/base/url/some/url?param=one" title="Some information" class="some more">Label</a>',
            $this->simpleLink()->render()
        );
    }

    protected function simpleLink()
    {
        FakeRequest::setConfiguredBaseUrl('/base/url');
        return Link::create(
            'Label',
            'some/url',
            ['param' => 'one'],
            ['title' => 'Some information', 'class' => ['some', 'more']]
        );
    }
}
