<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Unit\Grammar;

use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Grammar\GrammarNode;
use Polyphi\Parsers\Grammar\Token;

class TokenTest extends TestCase
{
    function testIsGrammarNode()
    {
        $subject = $this->createMock(Token::class);

        $this->assertInstanceOf(GrammarNode::class, $subject);
    }
}
