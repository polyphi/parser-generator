<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Func\Grammar;

use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Grammar;
use Polyphi\Parsers\Grammar\GrammarParser;
use Polyphi\Parsers\Grammar\Nodes\Declaration;
use Polyphi\Parsers\Grammar\Nodes\LiteralToken;
use Polyphi\Parsers\Grammar\Nodes\NamedToken;

class GrammarParserTest extends TestCase
{
    /** @var GrammarParser */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new GrammarParser();
    }

    protected function assertParse(string $code, array $decls, array $rules)
    {
        $result = $this->subject->parse($code);

        $this->assertEquals($decls, $result->getDeclarations());
        $this->assertEquals($rules, $result->getRules());
    }

    function testParseEmpty()
    {
        $result = $this->subject->parse("%% %%");

        $this->assertEquals(new Grammar(), $result);
    }

    function testParseNoEndOfInputMarker()
    {
        $result = $this->subject->parse("%%");

        $this->assertEquals(new Grammar(), $result);
    }

    function testParseNamedToken()
    {
        $result = $this->subject->parse("
            %token SOME_TOKEN
            %%
        ");

        $decls = [
            new Declaration(Declaration::TYPE_TOKEN, [
                new NamedToken('SOME_TOKEN'),
            ]),
        ];

        $this->assertEquals(new Grammar($decls), $result);
    }

    function testParseNamedTokenList()
    {
        $result = $this->subject->parse("
            %token SOME_TOKEN ANOTHER_TOKEN OMG_MORE_TOKENS
            %%
        ");

        $decls = [
            new Declaration(Declaration::TYPE_TOKEN, [
                new NamedToken('SOME_TOKEN'),
                new NamedToken('ANOTHER_TOKEN'),
                new NamedToken('OMG_MORE_TOKENS'),
            ]),
        ];

        $this->assertEquals(new Grammar($decls), $result);
    }

    function testParseLiteralToken()
    {
        $result = $this->subject->parse("
            %left '+'
            %%
        ");

        $decls = [
            new Declaration(Declaration::TYPE_LEFT, [
                new LiteralToken('+'),
            ]),
        ];

        $this->assertEquals(new Grammar($decls), $result);
    }

    function testParseLiteralTokenList()
    {
        $result = $this->subject->parse("
            %right '+' 'text' '$#%^#$@#%$^'
            %%
        ");

        $decls = [
            new Declaration(Declaration::TYPE_RIGHT, [
                new LiteralToken('+'),
                new LiteralToken('text'),
                new LiteralToken('$#%^#$@#%$^'),
            ]),
        ];

        $this->assertEquals(new Grammar($decls), $result);
    }

    function testParseLiteralTokenEscapedQuote()
    {
        $result = $this->subject->parse("
            %nonassoc '\''
            %%
        ");

        $decls = [
            new Declaration(Declaration::TYPE_NONASSOC, [
                new LiteralToken('\\\''),
            ]),
        ];

        $this->assertEquals(new Grammar($decls), $result);
    }

    function testParseLiteralTokenEscapedBackslash()
    {
        $result = $this->subject->parse("
            %token '\\\\'
            %%
        ");

        $decls = [
            new Declaration(Declaration::TYPE_TOKEN, [
                new LiteralToken('\\\\'),
            ]),
        ];

        $this->assertEquals(new Grammar($decls), $result);
    }

    function testParseLiteralTokenDoubleQuotes()
    {
        $this->expectException(Grammar\GrammarParseException::class);

        $this->subject->parse('
            %token "+"
            %%
        ');
    }

    function testParseUnclosedLiteralToken()
    {
        $this->expectException(Grammar\GrammarParseException::class);

        $this->subject->parse("
            %token '+
            %%
        ");
    }

    function testParseNamedTokenInvalidChar()
    {
        $this->expectException(Grammar\GrammarParseException::class);

        $this->subject->parse("
            %token Invalid-name
            %%
        ");
    }

    function testParseDeclarationNoTokens()
    {
        $result = $this->subject->parse("
            %pure_parser
            %%
        ");

        $decls = [
            new Declaration('pure_parser'),
        ];

        $this->assertEquals(new Grammar($decls), $result);
    }

    function testParseEmptyRule()
    {
        $result = $this->subject->parse("
            %%
            rule:
            ;
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule(),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseRuleWithLiteralToken()
    {
        $result = $this->subject->parse("
            %%
            rule:
                '^'
            ;
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule([new LiteralToken('^')]),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseRuleWithUnclosedLiteralToken()
    {
        $this->expectException(Grammar\GrammarParseException::class);

        $this->subject->parse("
            %%
            rule:
                '^
            ;
        ");
    }

    function testParseRuleWithNamedToken()
    {
        $result = $this->subject->parse("
            %%
            rule:
                NAMED_TOK
            ;
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule([new NamedToken('NAMED_TOK')]),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseRuleWithInvalidNamedToken()
    {
        $this->expectException(Grammar\GrammarParseException::class);

        $this->subject->parse("
            %%
            rule:
                NAMED-TOK
            ;
        ");
    }

    function testParseRuleWithTokenList()
    {
        $result = $this->subject->parse("
            %%
            rule:
                expr '+' expr 
            ;
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule([
                    new NamedToken('expr'),
                    new LiteralToken('+'),
                    new NamedToken('expr'),
                ]),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseRuleWithCode()
    {
        $result = $this->subject->parse("
            %%
            rule:
                expr '+' expr { someCode(); }
            ;
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule([
                    new NamedToken('expr'),
                    new LiteralToken('+'),
                    new NamedToken('expr'),
                ], 'someCode();'),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseRuleWithBracesInCode()
    {
        $result = $this->subject->parse("
            %%
            rule:
                expr '+' expr { if (\$condition) { someCode(); } }
            ;
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule([
                    new NamedToken('expr'),
                    new LiteralToken('+'),
                    new NamedToken('expr'),
                ], 'if ($condition) { someCode(); }'),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseRuleWithMissingClosingBraceInCode()
    {
        $this->expectException(Grammar\GrammarParseException::class);

        $this->subject->parse("
            %%
            rule:
                expr '+' expr { if (\$condition) { someCode(); }
            ;
        ");
    }

    function testParseRuleWithMissingOpeningBraceInCode()
    {
        $this->expectException(Grammar\GrammarParseException::class);

        $this->subject->parse("
            %%
            rule:
                expr '+' expr { if (\$condition) someCode(); } }
            ;
        ");
    }

    function testParseRuleGroup()
    {
        $result = $this->subject->parse("
            %%
            rule:
                NAMED_TOK
              | 'LITERAL'
              | NAMED_TOK 'LITERAL'
            ;
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule([
                    new NamedToken('NAMED_TOK'),
                ]),
                new Grammar\Nodes\Rule([
                    new LiteralToken('LITERAL'),
                ]),
                new Grammar\Nodes\Rule([
                    new NamedToken('NAMED_TOK'),
                    new LiteralToken('LITERAL'),
                ]),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseRuleGroupWithCode()
    {
        $result = $this->subject->parse("
            %%
            rule:
                NAMED_TOK           { first[one()] }
              | 'LITERAL'           { second[one()] }
              | NAMED_TOK 'LITERAL' { third[one()] }
            ;
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule([
                    new NamedToken('NAMED_TOK'),
                ], 'first[one()]'),
                new Grammar\Nodes\Rule([
                    new LiteralToken('LITERAL'),
                ], 'second[one()]'),
                new Grammar\Nodes\Rule([
                    new NamedToken('NAMED_TOK'),
                    new LiteralToken('LITERAL'),
                ], 'third[one()]'),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseMultipleRules()
    {
        $result = $this->subject->parse("
            %%
            term:
                'a'  { $$ = $1; }
              | 'b'  { $$ = $1; }
              | 'c'  { $$ = $1; }
            ;
            add_expr:
                add_expr '+' term   { $$ = new AddExpr($1, $3); }
              | term                { $$ = new AddExpr($1); }
            ;
        ");

        $rules = [
            'term' => [
                new Grammar\Nodes\Rule([new LiteralToken('a')], '$$ = $1;'),
                new Grammar\Nodes\Rule([new LiteralToken('b')], '$$ = $1;'),
                new Grammar\Nodes\Rule([new LiteralToken('c')], '$$ = $1;'),
            ],
            'add_expr' => [
                new Grammar\Nodes\Rule([
                    new NamedToken('add_expr'),
                    new LiteralToken('+'),
                    new NamedToken('term'),
                ], '$$ = new AddExpr($1, $3);'),
                new Grammar\Nodes\Rule([
                    new NamedToken('term'),
                ], '$$ = new AddExpr($1);'),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseRuleWithMissingSemicolon()
    {
        $this->expectException(Grammar\GrammarParseException::class);

        $this->subject->parse("
            %%
            rule:
                expr '+' expr
            
        ");
    }

    function testParseWithRulesAndEndMarker()
    {
        $result = $this->subject->parse("
            %%
            rule:
                expr
            ;
            %%
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule([new NamedToken('expr')]),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseWithMultipleRulesSameName()
    {
        $result = $this->subject->parse("
            %%
            rule:
                NAMED_TOKEN   { doNamedToken(); }
              | 'a'           { doA(); }
            ;
            rule:
                'b'           { doB(); }
              | 'c'           { doC(); }
            ;
            %%
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule([new NamedToken('NAMED_TOKEN')], 'doNamedToken();'),
                new Grammar\Nodes\Rule([new LiteralToken('a')], 'doA();'),
                new Grammar\Nodes\Rule([new LiteralToken('b')], 'doB();'),
                new Grammar\Nodes\Rule([new LiteralToken('c')], 'doC();'),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseRulePrecLiteral()
    {
        $result = $this->subject->parse("
            %%
            rule:
                SOME_TOK %prec '+'   { doNamedToken(); }
            ;
            %%
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule([
                    new NamedToken('SOME_TOK'),
                ],
                    'doNamedToken();',
                    new LiteralToken('+')
                ),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }

    function testParseRulePrecNamed()
    {
        $result = $this->subject->parse("
            %%
            rule:
                SOME_TOK %prec OTHER_TOK   { doNamedToken(); }
            ;
            %%
        ");

        $rules = [
            'rule' => [
                new Grammar\Nodes\Rule([
                    new NamedToken('SOME_TOK'),
                ],
                    'doNamedToken();',
                    new NamedToken('OTHER_TOK')
                ),
            ],
        ];

        $this->assertEquals(new Grammar([], $rules), $result);
    }
}
