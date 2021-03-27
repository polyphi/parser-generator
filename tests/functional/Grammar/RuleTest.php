<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Func\Grammar;

use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Grammar\GrammarNode;
use Polyphi\Parsers\Grammar\Rule;
use Polyphi\Parsers\Test\Helpers\TokenListTestHelper;

class RuleTest extends TestCase
{
    use TokenListTestHelper;

    function testIsGrammarNode()
    {
        $subject = new Rule([], '');

        $this->assertInstanceOf(GrammarNode::class, $subject);
    }

    function testGetTokens()
    {
        $tokens = $this->tokenList(['foo', 'bar', 'lorem', 'ipsum']);
        $subject = new Rule($tokens, '');

        $this->assertEquals($tokens, $subject->getTokens());
    }

    function testGetCode()
    {
        $subject = new Rule([], $code = 'runSomething(); $stack[] = "add me!!";');

        $this->assertEquals($code, $subject->getCode());
    }

    function testWithTokens()
    {
        $tokens = $this->tokenList(['foo', 'bar']);
        $newTokens = $this->tokenList(['lorem', 'ipsum']);
        $code = 'someCode();';

        $subject = new Rule($tokens, $code);
        $result = $subject->withTokens($newTokens);

        $this->assertEquals($newTokens, $result->getTokens());
        $this->assertEquals($code, $result->getCode());
    }

    function testWithAddedTokens()
    {
        $oldTokens = $this->tokenList(['foo', 'bar']);
        $addTokens = $this->tokenList(['lorem', 'ipsum']);
        $expected = $this->tokenList(['foo', 'bar', 'lorem', 'ipsum']);
        $code = 'someCode();';

        $subject = new Rule($oldTokens, $code);
        $result = $subject->withAddedTokens($addTokens);

        $this->assertEquals($expected, $result->getTokens());
        $this->assertEquals($code, $result->getCode());
    }

    function testWithAddedTokensAtIndex()
    {
        $oldTokens = $this->tokenList(['foo', 'bar']);
        $addTokens = $this->tokenList(['lorem', 'ipsum']);
        $expected = $this->tokenList(['foo', 'lorem', 'ipsum', 'bar']);
        $code = 'someCode();';

        $subject = new Rule($oldTokens, $code);
        $result = $subject->withAddedTokens($addTokens, 1);

        $this->assertEquals($expected, $result->getTokens());
        $this->assertEquals($code, $result->getCode());
    }

    function testToString()
    {
        $tokens = $this->tokenList(['foo', 'bar']);
        $code = '$result = runSomeFn();';

        $subject = new Rule($tokens, $code);
        $expected = 'foo bar { $result = runSomeFn(); }';

        $this->assertEquals($expected, $subject->toString());
    }
}
