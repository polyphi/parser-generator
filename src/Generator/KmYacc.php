<?php

declare(strict_types=1);

namespace Polyphi\Parsers\Generator;

use ComposerLocator;
use DomainException;
use RuntimeException;

/**
 * A simple wrapper for kmyacc. Exposes a single-method API for running kmyacc and obtaining its output.
 *
 * @psalm-type KmYaccArgs = array{
 *      verbose?: bool,
 *      debugFile?: bool,
 *      debugCode?: bool,
 *      semRefByName?: bool,
 *      parserName?: string,
 *      template?: string,
 *      inputFile?: string,
 *      inputStream?: resource | null,
 * }
 */
class KmYacc
{
    /** Default values for the arguments */
    public const DEFAULT_ARGS = [
        'verbose' => false,
        'debugFile' => false,
        'debugCode' => false,
        'semRefByName' => false,
        'parserName' => '',
        'template' => '',
        'inputFile' => '',
        'inputStream' => null,
    ];
    /** Mapping of arg names to their corresponding flags */
    public const ARG_FLAG_MAP = [
        'verbose' => 'x',
        'debugFile' => 'v',
        'debugCode' => 't',
        'semRefByName' => 'n',
    ];

    /** @var string|null A cache for the result of {@link autoFindExec()}. */
    protected static $execCache;

    /** @var string */
    protected $exec;

    /**
     * Pre-applied arguments.
     *
     * - "verbose":    Verbose debug output ("-x" flag).
     * - "debugFile":  Generate "y.output" file ("-v" flag).
     * - "debugCode":  Generates debugging code ("-t" flag).
     * - "semRefName": Allows referencing semantic values by name ("-n" flag).
     * - "parserName": The name of the parser class ("-p" option).
     * - "template":  The path to the template file, a.k.a. skeleton file ("-m" option).
     * - "inputFile":  The path to the input file (the last argument, taken positionally).
     * - "inputStream": A stream for the grammar code to use as stdin for kmyacc.
     *
     * @var array<string, mixed>
     *
     * @psalm-var KmYaccArgs
     */
    protected $preArgs;

    /**
     * Constructor.
     *
     * @param string|null          $exec    The kmyacc executable. If null, kmyacc will be auto located.
     * @param array<string, mixed> $preArgs Optional set of arguments to pre-apply for each {@link run()}. These
     *                                      args can be overridden at {@link run()} time.
     *
     * @psalm-param KmYaccArgs     $preArgs
     *
     * @throws RuntimeException If the $exec argument is null and kmyacc could not be auto located.
     */
    public function __construct(?string $exec = null, array $preArgs = [])
    {
        $exec = $exec ?? static::autoFindExec();
        if ($exec === null) {
            throw new RuntimeException('Could not auto locate kmyacc executable');
        }

        $this->exec = $exec;
        $this->preArgs = $preArgs;
    }

    /**
     * Runs kmyacc.
     *
     * If the 'input' argument is a stream resource, this function will NOT close it. The caller must take care to
     * close the input stream after running this function.
     *
     * @param array<string, mixed> $args Optional arguments. Can override the instance's pre-applied arguments.
     *
     * @psalm-param KmYaccArgs     $args
     *
     * @return string If the 'input' argument is a file, the path to the output file is returned. If the 'input'
     *                argument is a stream, the generated parser code will be returned.
     *
     * @throws DomainException If the arguments are invalid.
     * @throws RuntimeException If failed to spawn a process for kmyacc.
     * @throws KmYaccException If kmyacc did not terminate successfully.
     */
    public function run(array $args = []): string
    {
        $fullArgs = array_merge($this->preArgs, $args);
        $fullCommand = $this->buildCommand($fullArgs);

        $inputStream = $fullArgs['inputStream'] ?? false;
        $inputFile = $fullArgs['inputFile'] ?? false;
        $inputIsResource = is_resource($inputStream);

        // Must supply at least one type of input, file or stream
        if (!$inputIsResource && !$inputFile) {
            throw new DomainException('No input given. Supply a `inputFile` or `inputStream` arg');
        }

        // If using an input file, make sure it exists
        if (!$inputIsResource && !file_exists($inputFile)) {
            throw new DomainException('Input file does not exist');
        }

        // If using a template file, make sure it exists
        if (array_key_exists('template', $fullArgs) && !file_exists($fullArgs['template'])) {
            throw new DomainException('Template file does not exist');
        }

        $stdin = $inputIsResource ? $inputStream : ['pipe', 'r'];
        $stdout = ['pipe', 'w'];
        $stderr = ['pipe', 'w'];

        $process = proc_open($fullCommand, [$stdin, $stdout, $stderr], $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to open process for kmyacc');
        }

        // Read stdout and stderr
        $stdoutStr = stream_get_contents($pipes[1]);
        $stderrStr = stream_get_contents($pipes[2]);

        // Close streams
        fclose($pipes[1]);
        fclose($pipes[2]);
        if (!$inputIsResource) {
            fclose($pipes[0]);
        }

        // Close the process
        $code = proc_close($process);

        // Success
        if ($code === 0) {
            return $inputIsResource
                ? $stdoutStr
                : substr($inputFile, 0, -1);
        }

        // Failed with stderr output
        if ($stderrStr !== false && strlen($stderrStr) > 0) {
            throw new KmYaccException("kmyacc exited with code $code and the following error: $stderrStr");
        }

        // Failed with stdout output
        if ($stdoutStr !== false && strlen($stdoutStr) > 0) {
            throw new KmYaccException("kmyacc exited with code $code and the following output: $stdoutStr");
        }

        // Failed silently
        throw new KmYaccException('kmyacc exited with a non-zero code and no output');
    }

    /**
     * Builds a command.
     *
     * @param array<string, mixed> $args The arguments for the command.
     *
     * @return string The full command string.
     */
    public function buildCommand(array $args): string
    {
        $parts = [$this->exec];

        foreach (static::ARG_FLAG_MAP as $arg => $flag) {
            if ($args[$arg] ?? static::DEFAULT_ARGS[$arg]) {
                $parts[] = '-' . $flag;
            }
        }

        $options = [];
        foreach (['parserName', 'template', 'inputFile'] as $key) {
            $options[] = trim(strval($args[$key] ?? static::DEFAULT_ARGS[$key]));
        }

        [$parserName, $template, $inputFile] = $options;

        if (!empty($parserName)) {
            $parts[] = '-p ' . $parserName;
        }

        if (!empty($template)) {
            $parts[] = '-m ' . $template;
        }

        // Input stream takes precedence over input file
        if (is_resource($args['inputStream'] ?? null)) {
            $parts[] = '-';
        } elseif (!empty($inputFile)) {
            $parts[] = $inputFile;
        }

        return implode(' ', $parts);
    }

    /**
     * Auto locates the  kmyacc executable, searching for the KMYACC and POLYPHI_KMYACC environment variables and
     * falling back to the binary in the Composer "vendor/bin" directory. Most of the time, the bundled binary will be
     * used, which is provided by the "ircmaxell/php-yacc" library.
     *
     * @param bool $ignoreCache If true, this method will ignore the previously found and cached executable.
     *
     * @return string|null The path to the kmyacc executable, or null if kmyacc could not be found. The latter should
     *                     never happen since a PHP kmyacc implementation is bundled with this package.
     */
    public static function autoFindExec(bool $ignoreCache = false): ?string
    {
        if (static::$execCache !== null && !$ignoreCache) {
            return static::$execCache;
        }

        $kmYaccEnv = getenv('KMYACC');
        if (!empty($kmYaccEnv)) {
            return static::$execCache = $kmYaccEnv;
        }

        $polyphiEnv = getenv('POLYPHI_KMYACC');
        if (!empty($polyphiEnv)) {
            return static::$execCache = $polyphiEnv;
        }

        $kmyaccBin = ComposerLocator::getRootPath()
                     . DIRECTORY_SEPARATOR . 'vendor'
                     . DIRECTORY_SEPARATOR . 'bin'
                     . DIRECTORY_SEPARATOR . 'phpyacc';

        if (is_file($kmyaccBin)) {
            return static::$execCache = 'php ' . $kmyaccBin;
        }

        return null;
    }
}
