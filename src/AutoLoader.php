<?php
namespace Swoft\Console;

/**
 * class AutoLoader
 */
class AutoLoader extends \Swoft\Annotation\AutoLoader
{
    /**
     * Get namespace and dirs
     *
     * @return array
     */
    public function getPrefixDirs(): array
    {
        return [
            //
            __NAMESPACE__ => __DIR__,
        ];
    }
}
