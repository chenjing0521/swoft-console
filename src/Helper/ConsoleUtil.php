<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018/3/14
 * Time: 下午3:21
 */

namespace Swoft\Console\Helper;

/**
 * Class ConsoleUtil
 * @package Swoft\Console\Helper
 */
class ConsoleUtil
{
    /**
     * 确认, 发出信息要求确认
     * @param string $question 发出的信息
     * @param bool $default Default value
     * @return bool
     */
    public static function confirm(string $question, $default = true): bool
    {
        if (!$question = trim($question)) {
            \output()->writeln('Please provide a question message!', true, 2);
        }

        $question = ucfirst(trim($question, '?'));
        $default = (bool)$default;
        $defaultText = $default ? 'yes' : 'no';
        $message = "<comment>$question ?</comment>\nPlease confirm (yes|no)[default:<info>$defaultText</info>]: ";

        while (true) {
            \output()->writeln($message, false);
            $answer = \input()->read();

            if (empty($answer)) {
                return $default;
            }

            if (0 === stripos($answer, 'y')) {
                return true;
            }

            if (0 === stripos($answer, 'n')) {
                return false;
            }
        }

        return false;
    }
}
