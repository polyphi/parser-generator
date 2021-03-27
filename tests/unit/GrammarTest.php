<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Unit;

use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Grammar;
use Polyphi\Parsers\Grammar\Nodes;

class GrammarTest extends TestCase
{
    function testGetDeclarations()
    {
        $decls = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];

        $subject = new Grammar($decls, []);

        $this->assertSame($decls, $subject->getDeclarations());
    }

    function testGetRules()
    {
        $rules = [
            'foo' => [
                $this->createMock(Nodes\Rule::class),
            ],
            'bar' => [
                $this->createMock(Nodes\Rule::class),
                $this->createMock(Nodes\Rule::class),
            ],
        ];

        $subject = new Grammar([], $rules);

        $this->assertSame($rules, $subject->getRules());
    }

    function testGetRulesForNonTerminal()
    {
        $rules = [
            'foo' => [
                $this->createMock(Nodes\Rule::class),
            ],
            'bar' => $barRules = [
                $this->createMock(Nodes\Rule::class),
                $this->createMock(Nodes\Rule::class),
            ],
        ];

        $subject = new Grammar([], $rules);

        $this->assertSame($barRules, $subject->getRulesFor('bar'));
    }

    function testGetRulesForNonExistent()
    {
        $rules = [
            'foo' => [
                $this->createMock(Nodes\Rule::class),
            ],
            'bar' => [
                $this->createMock(Nodes\Rule::class),
                $this->createMock(Nodes\Rule::class),
            ],
        ];

        $subject = new Grammar([], $rules);

        $this->assertEmpty($subject->getRulesFor('baz'));
    }

    function testWithDeclarations()
    {
        $oldDecls = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];

        $newDecls = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];

        $rules = [
            'foo' => [
                $this->createMock(Nodes\Rule::class),
            ],
            'bar' => [
                $this->createMock(Nodes\Rule::class),
                $this->createMock(Nodes\Rule::class),
            ],
        ];

        $subject = new Grammar($oldDecls, $rules);
        $result = $subject->withDeclarations($newDecls);

        $this->assertSame($newDecls, $result->getDeclarations());
        $this->assertSame($rules, $result->getRules(), 'withDeclarations() should not change the rules');
    }

    function testWithAddedDeclarations()
    {
        $oldDecls = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];

        $newDecls = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];

        $rules = [
            'foo' => [
                $this->createMock(Nodes\Rule::class),
            ],
            'bar' => [
                $this->createMock(Nodes\Rule::class),
                $this->createMock(Nodes\Rule::class),
            ],
        ];

        $expected = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];

        $index = 2;

        $subject = $this->getMockBuilder(Grammar::class)
                        ->setConstructorArgs([$oldDecls, $rules])
                        ->onlyMethods(['insertArray'])
                        ->getMock();

        $subject->expects($this->once())
                ->method('insertArray')
                ->with($oldDecls, $newDecls, $index)
                ->willReturn($expected);

        $result = $subject->withAddedDeclarations($newDecls, $index);

        $this->assertSame($expected, $result->getDeclarations());
        $this->assertSame($rules, $result->getRules(), 'withAddedDeclarations() should not change the rules');
    }

    function testWithRules()
    {
        $decls = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];

        $oldRules = [
            'foo' => [
                $this->createMock(Nodes\Rule::class),
            ],
            'bar' => [
                $this->createMock(Nodes\Rule::class),
                $this->createMock(Nodes\Rule::class),
            ],
        ];

        $newRules = [
            'lorem' => [
                $this->createMock(Nodes\Rule::class),
            ],
            'ipsum' => [
                $this->createMock(Nodes\Rule::class),
                $this->createMock(Nodes\Rule::class),
            ],
        ];

        $subject = new Grammar($decls, $oldRules);
        $result = $subject->withRules($newRules);

        $this->assertSame($newRules, $result->getRules());
        $this->assertSame($decls, $result->getDeclarations(), 'withRules() should not change the declarations');
    }

    function testWithAddedRules()
    {
        $decls = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];

        $oldRules = [
            'foo' => [
                $this->createMock(Nodes\Rule::class),
            ],
            'bar' => [
                $this->createMock(Nodes\Rule::class),
                $this->createMock(Nodes\Rule::class),
            ],
        ];

        $nonTerminal = 'bar';
        $rulesToAdd = [
            $this->createMock(Nodes\Rule::class),
            $this->createMock(Nodes\Rule::class),
        ];
        $index = 2;

        $newRules = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];

        $expected = [
            'foo' => $oldRules['foo'],
            'bar' => $newRules,
        ];

        $subject = $this->getMockBuilder(Grammar::class)
                        ->setConstructorArgs([$decls, $oldRules])
                        ->onlyMethods(['insertArray'])
                        ->getMock();

        $subject->expects($this->once())
                ->method('insertArray')
                ->with($oldRules[$nonTerminal], $rulesToAdd, $index)
                ->willReturn($newRules);

        $result = $subject->withAddedRules($nonTerminal, $rulesToAdd, $index);

        $this->assertSame($expected, $result->getRules());
        $this->assertSame($decls, $result->getDeclarations(), 'withAddedRules() should not change the declarations');
    }

    function testWithAddedRulesNewNonTerminal()
    {
        $decls = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];

        $oldRules = [
            'foo' => [
                $this->createMock(Nodes\Rule::class),
            ],
            'bar' => [
                $this->createMock(Nodes\Rule::class),
                $this->createMock(Nodes\Rule::class),
            ],
        ];

        $nonTerminal = 'new';
        $rulesToAdd = [
            $this->createMock(Nodes\Rule::class),
            $this->createMock(Nodes\Rule::class),
        ];
        $index = 2;

        $expected = array_merge($oldRules, [
            'new' => $rulesToAdd,
        ]);

        $subject = $this->getMockBuilder(Grammar::class)
                        ->setConstructorArgs([$decls, $oldRules])
                        ->onlyMethods(['insertArray'])
                        ->getMock();

        $subject->expects($this->once())
                ->method('insertArray')
                ->with([], $rulesToAdd, $index)
                ->willReturn($rulesToAdd);

        $result = $subject->withAddedRules($nonTerminal, $rulesToAdd, $index);

        $this->assertSame($expected, $result->getRules());
        $this->assertSame($decls, $result->getDeclarations(), 'withAddedRules() should not change the declarations');
    }

    function testMerge()
    {
        $decls1 = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];
        $decls2 = [
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
            $this->createMock(Nodes\Declaration::class),
        ];

        $rules1 = [
            'foo' => $fooRules1 = [
                $this->createMock(Nodes\Rule::class),
            ],
            'bar' => $barRules = [
                $this->createMock(Nodes\Rule::class),
                $this->createMock(Nodes\Rule::class),
            ],
        ];
        $rules2 = [
            'foo' => $fooRules2 = [
                $this->createMock(Nodes\Rule::class),
            ],
            'baz' => $bazRules = [
                $this->createMock(Nodes\Rule::class),
                $this->createMock(Nodes\Rule::class),
            ],
        ];

        $expectedDecls = array_merge($decls1, $decls2);
        $expectedRules = [
            'foo' => array_merge($fooRules1, $fooRules2),
            'bar' => $barRules,
            'baz' => $bazRules,
        ];

        $subject1 = new Grammar($decls1, $rules1);
        $subject2 = new Grammar($decls2, $rules2);
        $result = $subject1->merge($subject2);

        $this->assertSame($expectedDecls, $result->getDeclarations());
        $this->assertSame($expectedRules, $result->getRules());
    }

    function testToString()
    {
        $mockToStr = function (string $className, string $toString) {
            return $this->createConfiguredMock($className, ['toString' => $toString, '__toString' => $toString]);
        };

        $decls = [
            $mockToStr(Nodes\Declaration::class, 'decl1'),
            $mockToStr(Nodes\Declaration::class, 'decl2'),
        ];

        $rules = [
            'foo' => [
                $mockToStr(Nodes\Rule::class, 'foo_rule'),
            ],
            'bar' => $barRules = [
                $mockToStr(Nodes\Rule::class, 'bar_rule_1'),
                $mockToStr(Nodes\Rule::class, 'bar_rule_2'),
            ],
        ];

        $expected = <<<EXPECTED
decl1
decl2

%%

foo:
    foo_rule
;

bar:
    bar_rule_1
  | bar_rule_2
;

%%

EXPECTED;

        $subject = new Grammar($decls, $rules);

        $this->assertEquals($expected, $subject->toString());
    }

    function testCastToString()
    {
        $expected = 'some string result';

        $subject = $this->createPartialMock(Grammar::class, ['toString']);
        $subject->expects($this->once())->method('toString')->willReturn($expected);

        $this->assertEquals($expected, (string) $subject);
    }
}
