<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Func\Grammar;

use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Grammar\Nodes\LiteralToken;
use Polyphi\Parsers\Grammar\Nodes\Token;

class LiteralTokenTest extends TestCase
{
    function testIsToken()
    {
        $subject = new LiteralToken('');

        $this->assertInstanceOf(Token::class, $subject);
    }

    function testGetName()
    {
        $literal = 'test';
        $subject = new LiteralToken($literal);

        $this->assertEquals($literal, $subject->getValue());
    }

    function testToString()
    {
        $literal = 'test';
        $subject = new LiteralToken($literal);

        $expected = '\'' . $literal . '\'';

        $this->assertEquals($expected, $subject->toString());
    }
}
