<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Unit\Grammar;

use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Grammar\GrammarNode;

class GrammarNodeTest extends TestCase
{
    function testCastToString()
    {
        $expected = 'terms and conditions apply';

        $subject = $this->createPartialMock(GrammarNode::class, ['toString']);
        $subject->expects($this->once())
                ->method('toString')
                ->willReturn($expected);

        $this->assertEquals($expected, (string) $subject);
    }
}
