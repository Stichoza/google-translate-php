<?php

namespace Stichoza\GoogleTranslate\Tests;

use ReflectionClass;
use Stichoza\GoogleTranslate\TranslateClient;

class UtilityTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tr = new TranslateClient();
        $reflection = new ReflectionClass(get_class($this->tr));
        $this->method = $reflection->getMethod('isValidLocale');
        $this->method->setAccessible(true);
    }

    public function testIsValidLocale()
    {
        $m = $this->method;
        $t = $this->tr;

        $booleanAssertions = [
            'ab'      => true,
            'ab-CD'   => true,
            'ab-CDE'  => false,
            'abc-DE'  => false,
            'abc-DEF' => false,
            'abc'     => false,
            'ab-'     => false,
            'a'       => false,
        ];

        foreach ($booleanAssertions as $key => $value) {
            $this->assertEquals($m->invokeArgs($t, [$key]), $value);
        }
    }

    public function testSetHttpOption()
    {
        $res = fopen('php://memory', 'r+');

        $this->tr->setHttpOption([
            'debug'   => $res,
            'headers' => [
                'User-Agent' => 'Foo',
            ],
        ])->translate('hello');
        rewind($res);
        $output = str_replace("\r", '', stream_get_contents($res));
        $this->assertContains('User-Agent: Foo', $output);

        $this->tr->setHttpOption([
            'debug'   => $res,
            'headers' => [
                'User-Agent' => 'Bar',
            ],
        ])->translate('world');
        rewind($res);
        $output = str_replace("\r", '', stream_get_contents($res));
        $this->assertContains('User-Agent: Bar', $output);
        fclose($res);
    }
}
