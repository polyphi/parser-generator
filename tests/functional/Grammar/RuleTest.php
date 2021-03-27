<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Func\Grammar;

use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Grammar\Rule;

class RuleTest extends TestCase
{
    function testGetSymbols()
    {
        $subject = new Rule($symbols = ['foo', 'bar', 'lorem', 'ipsum'], '');

        $this->assertEquals($symbols, $subject->getSymbols());
    }

    function testGetCode()
    {
        $subject = new Rule([], $code = 'runSomething(); $stack[] = "add me!!";');

        $this->assertEquals($code, $subject->getCode());
    }

    function testWithSymbols()
    {
        $subject = new Rule(['foo', 'bar'], $code = 'someCode();');
        $result = $subject->withSymbols($new = ['lorem', 'ipsum']);

        $this->assertEquals($new, $result->getSymbols());
        $this->assertEquals($code, $result->getCode());
    }

    function testWithAddedSymbols()
    {
        $subject = new Rule(['foo', 'bar'], $code = 'someCode();');
        $result = $subject->withAddedSymbols(['lorem', 'ipsum']);
        $expected = ['foo', 'bar', 'lorem', 'ipsum'];

        $this->assertEquals($expected, $result->getSymbols());
        $this->assertEquals($code, $result->getCode());
    }

    function testWithAddedSymbolsAtIndex()
    {
        $subject = new Rule(['foo', 'bar'], $code = 'someCode();');
        $result = $subject->withAddedSymbols(['lorem', 'ipsum'], 1);
        $expected = ['foo', 'lorem', 'ipsum', 'bar'];

        $this->assertEquals($expected, $result->getSymbols());
        $this->assertEquals($code, $result->getCode());
    }

    function testToString()
    {
        $subject = new Rule($symbols = ['foo', 'bar'], '$result = runSomeFn();');
        $expected = 'foo bar { $result = runSomeFn(); }';

        $this->assertEquals($expected, $subject->toString());
    }

    function testCastToString()
    {
        $subject = new Rule($symbols = ['foo', 'bar'], '$result = runSomeFn();');
        $expected = 'foo bar { $result = runSomeFn(); }';

        $this->assertEquals($expected, (string) $subject);
    }
}
