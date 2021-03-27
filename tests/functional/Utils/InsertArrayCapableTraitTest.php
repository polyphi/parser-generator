<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Func\Utils;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Utils\InsertArrayCapableTrait;

class InsertArrayCapableTraitTest extends TestCase
{
    /** @var MockObject&InsertArrayCapableTrait */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = $this->getMockForTrait(InsertArrayCapableTrait::class);
    }

    function testInsertArrayNoIdx()
    {
        $array = ['A', 'B', 'C'];
        $toAdd = ['D', 'E'];
        $expected = ['A', 'B', 'C', 'D', 'E'];
        $result = $this->subject->insertArray($array, $toAdd);

        $this->assertEquals($expected, $result);
    }

    function testInsertArrayNullIdx()
    {
        $array = ['A', 'B', 'C'];
        $toAdd = ['D', 'E'];
        $expected = ['A', 'B', 'C', 'D', 'E'];
        $result = $this->subject->insertArray($array, $toAdd, null);

        $this->assertEquals($expected, $result);
    }

    function testInsertArrayIdxZero()
    {
        $array = ['A', 'B', 'C'];
        $toAdd = ['D', 'E'];
        $expected = ['D', 'E', 'A', 'B', 'C'];
        $result = $this->subject->insertArray($array, $toAdd, 0);

        $this->assertEquals($expected, $result);
    }

    function testInsertArrayIdxOne()
    {
        $array = ['A', 'B', 'C'];
        $toAdd = ['D', 'E'];
        $expected = ['A', 'D', 'E', 'B', 'C'];
        $result = $this->subject->insertArray($array, $toAdd, 1);

        $this->assertEquals($expected, $result);
    }

    function testInsertArrayIdxNegative()
    {
        $array = ['A', 'B', 'C'];
        $toAdd = ['D', 'E'];
        $expected = ['A', 'B', 'D', 'E', 'C'];
        $result = $this->subject->insertArray($array, $toAdd, -1);

        $this->assertEquals($expected, $result);
    }

    function testInsertArrayIdxTooLarge()
    {
        $array = ['A', 'B', 'C'];
        $toAdd = ['D', 'E'];
        $expected = ['A', 'B', 'C', 'D', 'E'];
        $result = $this->subject->insertArray($array, $toAdd, 10);

        $this->assertEquals($expected, $result);
    }
}
