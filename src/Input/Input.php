<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2016/12/7
 * Time: 19:23
 */

namespace Swoft\Console\Input;

use Toolkit\Cli\Flags;

/**
 * Class Input - The input information. by parse global var $argv.
 * @package Swoft\Console\Input
 */
class Input extends AbstractInput
{
    /**
     * @var resource
     */
    protected $inputStream = \STDIN;

    /**
     * Input constructor.
     * @param null|array $args
     * @param bool       $parsing
     */
    public function __construct(array $args = null, bool $parsing = true)
    {
        if (null === $args) {
            $args = (array)$_SERVER['argv'];
        }

        $this->pwd = $this->getPwd();
        $this->tokens = $args;
        $this->script = \array_shift($args);
        $this->fullScript = \implode(' ', $args);

        if ($parsing) {
            // list($this->args, $this->sOpts, $this->lOpts) = InputParser::fromArgv($args);
            [$this->args, $this->sOpts, $this->lOpts] = Flags::parseArgv($args);

            // find command name
            $this->findCommand();
        }
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $tokens = \array_map(function ($token) {
            if (\preg_match('{^(-[^=]+=)(.+)}', $token, $match)) {
                return $match[1] . Flags::escapeToken($match[2]);
            }

            if ($token && $token[0] !== '-') {
                return Flags::escapeToken($token);
            }

            return $token;
        }, $this->tokens);

        return \implode(' ', $tokens);
    }

    /**
     * Read input information
     * @param  string $question 若不为空，则先输出文本消息
     * @param  bool   $nl true 会添加换行符 false 原样输出，不添加换行符
     * @return string
     */
    public function read(string $question = '', bool $nl = false): string
    {
        if ($question) {
            \fwrite(\STDOUT, $question . ($nl ? "\n" : ''));
        }

        return \trim(\fgets($this->inputStream));
    }

    /***********************************************************************************
     * getter/setter
     ***********************************************************************************/

    /**
     * @return string
     */
    public function getFullCommand(): string
    {
        return $this->script . ' ' . $this->command;
    }

    /**
     * @return string
     */
    public function getScriptName(): string
    {
        return $this->script;
    }

    /**
     * @return string
     */
    public function getBinName(): string
    {
        return $this->script;
    }

    /**
     * @return resource
     */
    public function getInputStream()
    {
        return $this->inputStream;
    }
}
