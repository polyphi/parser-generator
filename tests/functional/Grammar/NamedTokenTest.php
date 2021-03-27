<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Func\Grammar;

use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Grammar\Nodes\NamedToken;
use Polyphi\Parsers\Grammar\Nodes\Token;

class NamedTokenTest extends TestCase
{
    function testIsToken()
    {
        $subject = new NamedToken('');

        $this->assertInstanceOf(Token::class, $subject);
    }

    function testGetName()
    {
        $expected = 'T_SERIES';
        $subject = new NamedToken($expected);

        $this->assertEquals($expected, $subject->getName());
    }

    function testToString()
    {
        $expected = 'T_SERIES';
        $subject = new NamedToken($expected);

        $this->assertEquals($expected, $subject->toString());
    }
}
