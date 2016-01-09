<?php

namespace Stichoza\GoogleTranslate\Tests;

use Stichoza\GoogleTranslate\TranslateClient;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tr = new TranslateClient();
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testBadMethodCallException()
    {
        $this->tr->fooBar('baz');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgumentException()
    {
        $this->tr->translate();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgumentException2()
    {
        $this->tr->translate(1);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testStaticBadMethodCallException()
    {
        TranslateClient::fooBar('baz');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStaticInvalidArgumentException()
    {
        TranslateClient::translate();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStaticInvalidArgumentException2()
    {
        TranslateClient::translate(1);
    }
}
