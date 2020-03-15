<?php

namespace tests\unit\Common\ValueObjects;

use App\Common\Exceptions\InvalidUrlException;
use App\Common\ValueObjects\Url;

class UrlTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testBuildUrlWithHttpSchemeValid()
    {
        $url = new Url('http://borisd.ru');

        $this->assertEquals(Url::SCHEME_HTTP, $url->getScheme());
        $this->assertEquals('borisd.ru', $url->getHost());
        $this->assertIsString($url->getPath());
    }

    public function testBuildUrlWithHttpsSchemeValid()
    {
        $url = new Url('https://borisd.ru');

        $this->assertEquals(Url::SCHEME_HTTPS, $url->getScheme());
        $this->assertEquals('borisd.ru', $url->getHost());
        $this->assertIsString($url->getPath());
    }

    public function testBuildUrlWithoutSchemeValid()
    {
        $url = new Url('borisd.ru');

        $this->assertEquals(Url::SCHEME_HTTP, $url->getScheme());
        $this->assertEquals('borisd.ru', $url->getHost());
        $this->assertIsString($url->getPath());
    }

    public function testBuildUrlWithOmittedSchemeValid()
    {
        $url = new Url('//borisd.ru');

        $this->assertEquals(Url::SCHEME_HTTP, $url->getScheme());
        $this->assertEquals('borisd.ru', $url->getHost());
        $this->assertIsString($url->getPath());
    }

    public function testBuildUrlPrefixedWithSlashFail()
    {
        $this->expectException(InvalidUrlException::class);

        new Url('/borisd.ru');
    }

    public function testBuildEmptyUrlFail()
    {
        $this->expectException(InvalidUrlException::class);

        new Url('');
    }

    public function testBuildSchemeOnlyUrlFail()
    {
        $this->expectException(InvalidUrlException::class);

        new Url('http://');
    }

    public function testBuildUrlWithDisallowedSchemeFail()
    {
        $this->expectException(InvalidUrlException::class);

        new Url('ftp://borisd.ru');
    }

    public function testBuildUrlWithPhpWrapperSchemeFail()
    {
        $this->expectException(InvalidUrlException::class);

        new Url('php://filter/resource=http://maliscious.com/?test=1');
    }
}
