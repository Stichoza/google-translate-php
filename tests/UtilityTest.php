<?php

namespace Stichoza\GoogleTranslate\Tests;

use ReflectionClass;
use PHPUnit\Framework\TestCase;
use Stichoza\GoogleTranslate\GoogleTranslate;

class UtilityTest extends TestCase
{
    public $tr;

    public $method;

    public function setUp()
    {
        $this->tr = new GoogleTranslate();
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

    public function testSetOptions()
    {
        $res = fopen('php://memory', 'r+');

        $this->tr->setOptions([
            'debug'   => $res,
            'headers' => [
                'User-Agent' => 'Foo',
            ],
        ])->translate('hello');
        rewind($res);
        $output = str_replace("\r", '', stream_get_contents($res));
        $this->assertContains('User-Agent: Foo', $output);

        GoogleTranslate::trans('world', 'en', null, [
            'debug'   => $res,
            'headers' => [
                'User-Agent' => 'Bar',
            ],
        ]);
        rewind($res);
        $output = str_replace("\r", '', stream_get_contents($res));
        $this->assertContains('User-Agent: Bar', $output);
        fclose($res);
    }
}
