<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Func\Grammar;

use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Grammar\Nodes\Declaration;
use Polyphi\Parsers\Test\Helpers\TokenListTestHelper;

class DeclarationTest extends TestCase
{
    use TokenListTestHelper;

    function testGetType()
    {
        $subject = new Declaration($type = 'foobar');

        $this->assertEquals($type, $subject->getType());
    }

    function testGetTokens()
    {
        $tokens = $this->tokenList(['SOMETHING', 'ANOTHER', 'WTF']);

        $subject = new Declaration('', $tokens);

        $this->assertEquals($tokens, $subject->getTokens());
    }

    function testWithType()
    {
        $tokens = $this->tokenList(['SOMETHING', 'ANOTHER']);
        $subject = new Declaration($old = 'old_type', $tokens);
        $result = $subject->withType($new = 'new_type');

        $this->assertEquals($new, $result->getType());
        $this->assertEquals($tokens, $result->getTokens(), 'withType() should not change the tokens');
    }

    function testWithTokens()
    {
        $oldTokens = $this->tokenList(['SOMETHING', 'ANOTHER']);
        $newTokens = $this->tokenList(['USE_THE_FORCE', 'DAD_PLS_NO']);

        $subject = new Declaration($type = 'some_type', $oldTokens);
        $result = $subject->withTokens($newTokens);

        $this->assertEquals($newTokens, $result->getTokens());
        $this->assertEquals($type, $result->getType(), 'withTokens() should not change the type');
    }

    function testWithAddedTokens()
    {
        $oldTokens = $this->tokenList(['SOMETHING', 'ANOTHER']);
        $addTokens = $this->tokenList(['CHOCOLATE', 'SALTY']);
        $expected = $this->tokenList(['SOMETHING', 'ANOTHER', 'CHOCOLATE', 'SALTY']);

        $subject = new Declaration($type = 'some_type', $oldTokens);
        $result = $subject->withAddedTokens($addTokens);

        $this->assertEquals($expected, $result->getTokens());
        $this->assertEquals($type, $result->getType(), 'withTokens() should not change the type');
    }

    function testWithAddedTokensAtIndex()
    {
        $oldTokens = $this->tokenList(['SOMETHING', 'ANOTHER']);
        $addTokens = $this->tokenList(['CHOCOLATE', 'SALTY']);
        $expected = $this->tokenList(['SOMETHING', 'CHOCOLATE', 'SALTY', 'ANOTHER']);

        $subject = new Declaration($type = 'some_type', $oldTokens);
        $result = $subject->withAddedTokens($addTokens, 1);

        $this->assertEquals($expected, $result->getTokens());
        $this->assertEquals($type, $result->getType(), 'withTokens() should not change the type');
    }

    function testToString()
    {
        $tokens = $this->tokenList(['SOMETHING', 'ANOTHER']);

        $subject = new Declaration('some_type', $tokens);
        $expected = '%some_type SOMETHING ANOTHER';

        $this->assertEquals($expected, $subject->toString());
    }
}
