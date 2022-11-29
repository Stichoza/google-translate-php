<?php

namespace Stichoza\GoogleTranslate\Tests;

use ErrorException;
use PHPUnit\Framework\TestCase;
use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationDecodingException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;
use UnexpectedValueException;

class ExceptionTest extends TestCase
{
    public GoogleTranslate $tr;

    public function setUp(): void
    {
        $this->tr = new GoogleTranslate();
    }

    public function testRateLimitException(): void
    {
        $this->expectException(RateLimitException::class);

        $this->tr->setUrl('https://httpstat.us/429')->translate('Test');
    }

    public function testRateLimitCaptchaException(): void
    {
        $this->expectException(RateLimitException::class);

        $this->tr->setUrl('https://httpstat.us/503')->translate('Test');
    }

    public function testLargeTextException(): void
    {
        $this->expectException(LargeTextException::class);

        $this->tr->setUrl('https://httpstat.us/413')->translate('Test');
    }

    public function testTranslationRequestException(): void
    {
        $this->expectException(TranslationRequestException::class);

        $this->tr->setUrl('https://httpstat.us/418')->translate('Test');
    }

    public function testTranslationDecodingException(): void
    {
        $this->expectException(TranslationDecodingException::class);

        $this->tr->setUrl('https://httpstat.us/200')->translate('Test');
    }

    public function testInheritanceForUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->tr->setUrl('https://httpstat.us/200')->translate('Test');
    }

    public function testInheritanceForErrorException(): void
    {
        $this->expectException(ErrorException::class);

        $this->tr->setUrl('https://httpstat.us/413')->translate('Test');
    }
}
