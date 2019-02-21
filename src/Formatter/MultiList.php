<?php

namespace Swoft\Console\Formatter;

use Inhere\Console\Util\Show;

/**
 * Class MultiList
 * @package Swoft\Console\Formatter
 */
class MultiList extends Formatter
{
    /**
     * Format and render multi list
     *
     * ```php
     * [
     *   'list1 title' => [
     *      'name' => 'value text',
     *      'name2' => 'value text 2',
     *   ],
     *   'list2 title' => [
     *      'name' => 'value text',
     *      'name2' => 'value text 2',
     *   ],
     *   ... ...
     * ]
     * ```
     * @param array $data
     * @param array $opts
     */
    public static function show(array $data, array $opts = []): void
    {
        $stringList = [];
        $ignoreEmpty = $opts['ignoreEmpty'] ?? true;
        $lastNewline = true;

        $opts['returned'] = true;
        if (isset($opts['lastNewline'])) {
            $lastNewline = $opts['lastNewline'];
            unset($opts['lastNewline']);
        }

        foreach ($data as $title => $list) {
            if ($ignoreEmpty && !$list) {
                continue;
            }

            $stringList[] = SingleList::show($list, $title, $opts);
        }

        Show::write(\implode("\n", $stringList), $lastNewline);
    }
}
