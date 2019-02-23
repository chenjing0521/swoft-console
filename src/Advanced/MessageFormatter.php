<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/12/22 0022
 * Time: 21:44
 */

namespace Swoft\Console\Advanced;

use Swoft\Console\Helper\Show;
use Swoft\Stdlib\Helper\ObjectHelper;

/**
 * Class MessageFormatter - message formatter
 * @package Swoft\Console\Advanced
 */
abstract class MessageFormatter implements FormatterInterface
{
    // content align
    public const ALIGN_LEFT   = 'left';
    public const ALIGN_CENTER = 'center';
    public const ALIGN_RIGHT  = 'right';

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param array $config
     * @return MessageFormatter
     */
    public static function create(array $config = []): self
    {
        return new static($config);
    }

    /**
     * Formatter constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        ObjectHelper::init($this, $config);

        $this->config = $config;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return string
     */
    public function format(): string
    {
        throw new \RuntimeException('Please implement the method on sub-class');
    }

    /**
     * Format and output message to steam.
     *
     * @return int
     */
    public function render(): int
    {
        return $this->display();
    }

    /**
     * Format and output message to steam.
     *
     * @return int
     */
    public function display(): int
    {
        return Show::write($this->toString());
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->format();
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
