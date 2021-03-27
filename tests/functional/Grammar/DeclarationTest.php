<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Func\Grammar;

use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Grammar\Declaration;

class DeclarationTest extends TestCase
{
    function testGetType()
    {
        $subject = new Declaration($type = 'foobar');

        $this->assertEquals($type, $subject->getType());
    }

    function testGetValues()
    {
        $subject = new Declaration('', $values = [
            'SOMETHING',
            'ANOTHER_THING',
            '\'%\'',
        ]);

        $this->assertEquals($values, $subject->getValues());
    }

    function testWithType()
    {
        $subject = new Declaration($old = 'old_type', $values = ['SOMETHING', 'ANOTHER_THING']);
        $result = $subject->withType($new = 'new_type');

        $this->assertEquals($new, $result->getType());
        $this->assertEquals($values, $result->getValues());
    }

    function testWithValues()
    {
        $subject = new Declaration($type = 'some_type', $old = ['SOMETHING', 'ANOTHER_THING']);
        $result = $subject->withValues($new = ['FOOBAR', 'USE_THE_FORCE']);

        $this->assertEquals($type, $result->getType());
        $this->assertEquals($new, $result->getValues());
    }

    function testWithAddedValues()
    {
        $subject = new Declaration($type = 'some_type', ['SOMETHING', 'ANOTHER_THING']);
        $result = $subject->withAddedValues(['FOOBAR', 'USE_THE_FORCE']);

        $expected = ['SOMETHING', 'ANOTHER_THING', 'FOOBAR', 'USE_THE_FORCE'];

        $this->assertEquals($type, $result->getType());
        $this->assertEquals($expected, $result->getValues());
    }

    function testWithAddedValuesAtIndex()
    {
        $subject = new Declaration($type = 'some_type', ['SOMETHING', 'ANOTHER_THING']);
        $result = $subject->withAddedValues(['FOOBAR', 'USE_THE_FORCE'], 1);

        $expected = ['SOMETHING', 'FOOBAR', 'USE_THE_FORCE', 'ANOTHER_THING'];

        $this->assertEquals($type, $result->getType());
        $this->assertEquals($expected, $result->getValues());
    }

    function testToString()
    {
        $subject = new Declaration('some_type', ['SOMETHING', 'ANOTHER_THING']);
        $expected = '%some_type SOMETHING ANOTHER_THING';

        $this->assertEquals($expected, $subject->toString());
    }

    function testCastToString()
    {
        $subject = new Declaration('some_type', ['SOMETHING', 'ANOTHER_THING']);
        $expected = '%some_type SOMETHING ANOTHER_THING';

        $this->assertEquals($expected, (string) $subject);
    }
}
