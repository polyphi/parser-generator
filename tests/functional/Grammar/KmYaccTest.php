<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Test\Func\Grammar;

use DomainException;
use PHPUnit\Framework\TestCase;
use Polyphi\Parsers\Generator\KmYacc;
use Polyphi\Parsers\Grammar;
use Polyphi\Parsers\Grammar\Nodes\Declaration;
use Polyphi\Parsers\Test\Helpers\KmYaccTestHelper;

class KmYaccTest extends TestCase
{
    function testAutoFindExecEnv()
    {
        putenv('KMYACC=TEST_MY_TESTS');
        putenv('POLYPHI_KMYACC');

        $result = KmYacc::autoFindExec(true);

        $this->assertEquals('TEST_MY_TESTS', $result);
    }

    function testAutoFindExecPolyphiEnv()
    {
        putenv('KMYACC');
        putenv('POLYPHI_KMYACC=MOCK_MY_MOCKS');

        $result = KmYacc::autoFindExec(true);

        $this->assertEquals('MOCK_MY_MOCKS', $result);
    }

    function testAutoFindExecVendor()
    {
        putenv('KMYACC');
        putenv('POLYPHI_KMYACC');

        $result = KmYacc::autoFindExec(true);

        $this->assertNotNull($result);
        $this->assertStringContainsString('vendor/bin/phpyacc', $result);
    }

    function testBuildCommandNoArgs()
    {
        putenv('KMYACC=TEST_EXEC');

        $subject = new KmYacc(KmYacc::autoFindExec(true));
        $result = $subject->buildCommand([]);

        $this->assertEquals('TEST_EXEC', $result);
    }

    function testBuildCommandWithFlags()
    {
        putenv('KMYACC=TEST_EXEC');

        $subject = new KmYacc(KmYacc::autoFindExec(true));
        $result = $subject->buildCommand([
            'verbose' => true,
            'debugFile' => true,
            'debugCode' => false,
            'semRefByName' => false,
        ]);

        $this->assertEquals('TEST_EXEC -x -v', $result);
    }

    function testBuildCommandWithOptions()
    {
        putenv('KMYACC=TEST_EXEC');

        $subject = new KmYacc(KmYacc::autoFindExec(true));
        $result = $subject->buildCommand([
            'parserName' => 'FoobarParser',
            'template' => 'path/to/template',
        ]);

        $this->assertEquals('TEST_EXEC -p FoobarParser -m path/to/template', $result);
    }

    function testBuildCommandWithInputFile()
    {
        putenv('KMYACC=TEST_EXEC');

        $subject = new KmYacc(KmYacc::autoFindExec(true));
        $result = $subject->buildCommand([
            'inputFile' => 'path/to/file',
        ]);

        $this->assertEquals('TEST_EXEC path/to/file', $result);
    }

    function testBuildCommandWithInputStream()
    {
        putenv('KMYACC=TEST_EXEC');

        $dummyStream = fopen('php://memory', 'r');

        $subject = new KmYacc(KmYacc::autoFindExec(true));
        $result = $subject->buildCommand([
            'inputStream' => $dummyStream,
        ]);

        fclose($dummyStream);

        $this->assertEquals('TEST_EXEC -', $result);
    }

    function testBuildCommandWithInputFileAndStream()
    {
        putenv('KMYACC=TEST_EXEC');

        $dummyStream = fopen('php://memory', 'r');

        $subject = new KmYacc(KmYacc::autoFindExec(true));
        $result = $subject->buildCommand([
            'inputFile' => 'path/to/grammar',
            'inputStream' => $dummyStream,
        ]);

        fclose($dummyStream);

        $this->assertEquals('TEST_EXEC -', $result);
    }

    function testBuildCommandWithEverything()
    {
        putenv('KMYACC=TEST_EXEC');

        $subject = new KmYacc(KmYacc::autoFindExec(true));
        $result = $subject->buildCommand([
            'verbose' => true,
            'debugFile' => true,
            'debugCode' => false,
            'semRefByName' => false,
            'parserName' => 'FoobarParser',
            'template' => 'path/to/template',
            'inputFile' => 'path/to/grammar',
        ]);

        $this->assertEquals('TEST_EXEC -x -v -p FoobarParser -m path/to/template path/to/grammar', $result);
    }

    function testRunWithInputFile()
    {
        $grammar = new Grammar(
            [
                new Declaration(Declaration::TYPE_TOKEN, [
                    new Grammar\Nodes\NamedToken('T_TEST'),
                ]),
            ],
            [
                'start' => [
                    new Grammar\Nodes\Rule([
                        new Grammar\Nodes\NamedToken('T_TEST'),
                    ]),
                ],
            ]
        );

        $template = KmYaccTestHelper::getTempFile('test-template.php');
        $grammarFile = KmYaccTestHelper::getTempFile('test-grammar.y');
        file_put_contents($template, '<?php');
        file_put_contents($grammarFile, $grammar->toString());

        putenv('KMYACC');
        putenv('POLYPHI_KMYACC');
        $subject = new KmYacc(KmYacc::autoFindExec(true));
        $outFile = $subject->run(['template' => $template, 'inputFile' => $grammarFile]);

        $this->assertFileExists($outFile);
        $this->assertStringStartsWith('<?php', file_get_contents($outFile));
    }

    function testRunWithInputStream()
    {
        $grammar = new Grammar(
            [
                new Declaration(Declaration::TYPE_TOKEN, [
                    new Grammar\Nodes\NamedToken('T_TEST'),
                ]),
            ],
            [
                'start' => [
                    new Grammar\Nodes\Rule([
                        new Grammar\Nodes\NamedToken('T_TEST'),
                    ]),
                ],
            ]
        );

        $template = KmYaccTestHelper::getTempFile('test-template.php');
        $stream = KmYaccTestHelper::createStream($grammar->toString());

        file_put_contents($template, '<?php');

        putenv('KMYACC');
        putenv('POLYPHI_KMYACC');
        $subject = new KmYacc(KmYacc::autoFindExec(true));

        try {
            $output = $subject->run(['template' => $template, 'inputStream' => $stream]);
            $this->assertStringStartsWith('<?php', $output);
        } finally {
            fclose($stream);
        }
    }

    function testRunWithInputFileNotExists()
    {
        $this->expectException(DomainException::class);

        $template = KmYaccTestHelper::getTempFile('test-template.php');
        $grammarFile = KmYaccTestHelper::getTempFile('test-grammar.y');

        file_put_contents($template, '<?php');
        @unlink($grammarFile);

        putenv('KMYACC');
        putenv('POLYPHI_KMYACC');
        $subject = new KmYacc(KmYacc::autoFindExec(true));
        $subject->run(['template' => $template, 'inputFile' => $grammarFile]);
    }

    function testRunWithTemplateNotExists()
    {
        $this->expectException(DomainException::class);

        $grammar = new Grammar(
            [
                new Declaration(Declaration::TYPE_TOKEN, [
                    new Grammar\Nodes\NamedToken('T_TEST'),
                ]),
            ],
            [
                'start' => [
                    new Grammar\Nodes\Rule([
                        new Grammar\Nodes\NamedToken('T_TEST'),
                    ]),
                ],
            ]
        );

        $template = KmYaccTestHelper::getTempFile('test-template.php');
        $grammarFile = KmYaccTestHelper::getTempFile('test-grammar.y');

        @unlink($template);
        file_put_contents($grammarFile, $grammar->toString());

        putenv('KMYACC');
        putenv('POLYPHI_KMYACC');
        $subject = new KmYacc(KmYacc::autoFindExec(true));
        $subject->run(['template' => $template, 'inputFile' => $grammarFile]);
    }

    function testRunWithNoInput()
    {
        $this->expectException(DomainException::class);

        $template = KmYaccTestHelper::getTempFile('test-template.php');
        file_put_contents($template, '<?php');

        putenv('KMYACC');
        putenv('POLYPHI_KMYACC');
        $subject = new KmYacc(KmYacc::autoFindExec(true));
        $subject->run(['template' => $template]);
    }
}
