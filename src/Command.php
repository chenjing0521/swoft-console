<?php

namespace Swoft\Console;

use Swoft\App;
use Swoft\Bean\Annotation\Bean;
use Swoft\Console\Bean\Collector\CommandCollector;
use Swoft\Console\Helper\DocBlockHelper;
use Swoft\Helper\DocumentHelper;
use Swoft\Console\Router\HandlerAdapter;
use Swoft\Console\Router\HandlerMapping;

/**
 * @Bean("command")
 */
class Command
{
    // name -> {name}
    const ANNOTATION_VAR = '{%s}'; // '{$%s}';

    /**
     * 为命令注解提供可解析解析变量. 可以在命令的注释中使用
     * @return array
     */
    public function annotationVars(): array
    {
        // e.g: `more info see {name}:index`
        return [
            // 'name' => self::getName(),
            // 'group' => self::getName(),
            'workDir' => input()->getPwd(),
            'script' => input()->getScript(), // bin/app
            'command' => input()->getCommand(), // demo OR home:test
            'fullCommand' => input()->getScript() . ' ' . input()->getCommand(),
        ];
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function run()
    {
        $cmd = input()->getCommand();
        if (empty($cmd)) {
            $this->baseCommand();

            return;
        }

        /* @var HandlerMapping $router */
        $router = App::getBean('commandRoute');
        $handler = $router->getHandler();

        list($className, $method) = $handler;

        if ($router->isDefaultCommand($method)) {
            $this->indexComamnd($className);

            return;
        }

        $isHelp = input()->hasOpt('h') || input()->hasOpt('help');
        if ($isHelp) {
            $this->showCommandHelp($className, $method);
            return;
        }


        /* @var HandlerAdapter $adapter */
        $adapter = App::getBean(HandlerAdapter::class);
        $adapter->doHandler($handler);
    }

    /**
     * @param string $className
     * @throws \ReflectionException
     * @return void
     */
    private function indexComamnd(string $className)
    {
        /* @var HandlerMapping $router */
        $router = App::getBean('commandRoute');

        $collector = CommandCollector::getCollector();
        $routes = $collector[$className]['routes'] ?? [];

        $reflectionClass = new \ReflectionClass($className);
        $classDocument = $reflectionClass->getDocComment();
        $classDocAry = DocumentHelper::tagList($classDocument);
        $classDesc = $classDocAry['Description'];

        $methodCommands = [];

        foreach ($routes as $route) {
            $mappedName = $route['mappedName'];
            $methodName = $route['methodName'];
            $mappedName = empty($mappedName) ? $methodName : $mappedName;

            if ($methodName === 'init') {
                continue;
            }

            if ($router->isDefaultCommand($methodName)) {
                continue;
            }
            $reflectionMethod = $reflectionClass->getMethod($methodName);
            $methodDocument = $reflectionMethod->getDocComment();
            $methodDocAry = DocumentHelper::tagList($methodDocument);
            $methodCommands[$mappedName] = $methodDocAry['Description'];
        }

        // 命令显示结构
        $commandList = [
            'Description:' => [$classDesc],
            'Usage:'       => [ \input()->getCommand() . ':{command} [arguments] [options]'],
            'Commands:'    => $methodCommands,
            'Options:'     => [
                '-h, --help' => 'Show help of the command group or specified command action',
            ],
        ];

        output()->writeList($commandList);
    }

    /**
     * the help of group
     *
     * @param string $controllerClass
     * @param string $commandMethod
     * @throws \ReflectionException
     */
    private function showCommandHelp(string $controllerClass, string $commandMethod)
    {
        // 反射获取方法描述
        $reflectionClass = new \ReflectionClass($controllerClass);
        $reflectionMethod = $reflectionClass->getMethod($commandMethod);
        $document = $reflectionMethod->getDocComment();
        $document = $this->parseAnnotationVars($document, $this->annotationVars());
        // $docs = DocumentHelper::tagList($document);
        $docs = DocBlockHelper::getTags($document);

        $commands = [];

        // 描述
        if (isset($docs['Description'])) {
            $commands['Description:'] = explode("\n", $docs['Description']);
        }

        // 使用
        if (isset($docs['Usage'])) {
            $commands['Usage:'] = $docs['Usage'];
        }

        // 参数
        if (isset($docs['Arguments'])) {
            // $arguments = $this->parserKeyAndDesc($docs['Arguments']);
            $commands['Arguments:'] = $docs['Arguments'];
        }

        // 选项
        if (isset($docs['Options'])) {
            // $options = $this->parserKeyAndDesc($docs['Options']);
            $commands['Options:'] = $docs['Options'];
        }

        // 实例
        if (isset($docs['Example'])) {
            $commands['Example:'] = [$docs['Example']];
        }

        output()->writeList($commands);
    }

    /**
     * help list
     *
     * @throws \ReflectionException
     */
    private function showCommandList()
    {
        $commands = $this->parserCmdAndDesc();

        $commandList = [];
        $script = input()->getFullScript();
        $commandList['Usage:'] = ["php $script"];
        $commandList['Commands:'] = $commands;
        $commandList['Options:'] = [
            '-h, --help'    => 'show help information',
            '-v, --version' => 'show version',
        ];

        // show logo
        output()->writeLogo();

        // output list
        output()->writeList($commandList, 'comment', 'info');
    }

    /**
     * version
     */
    private function showVersion()
    {
        // 当前版本信息
        $swoftVersion = App::version();
        $phpVersion = PHP_VERSION;
        $swooleVersion = SWOOLE_VERSION;

        // 显示面板
        output()->writeLogo();
        output()->writeln("swoft: <info>$swoftVersion</info>, php: <info>$phpVersion</info>, swoole: <info>$swooleVersion</info>", true);
        output()->writeln('');
    }

    /**
     * the command list
     *
     * @return array
     * @throws \ReflectionException
     */
    private function parserCmdAndDesc(): array
    {
        $commands = [];
        $collector = CommandCollector::getCollector();

        /* @var \Swoft\Console\Router\HandlerMapping $route */
        $route = App::getBean('commandRoute');

        foreach ($collector as $className => $comamnd) {
            $rc = new \ReflectionClass($className);
            $docComment = $rc->getDocComment();
            $docAry = DocumentHelper::tagList($docComment);
            $desc = $docAry['Description'];

            $prefix = $comamnd['name'];
            $prefix = $route->getPrefix($prefix, $className);
            $commands[$prefix] = $desc;
        }

        return $commands;
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    private function baseCommand()
    {
        // 版本命令解析
        if (input()->hasOpt('v') || input()->hasOpt('version')) {
            $this->showVersion();

            return;
        }

        // 显示命令列表
        $this->showCommandList();
    }

    /**
     * 解析命令key和描述
     *
     * @param string $document 注解文档
     * @return array
     */
    private function parserKeyAndDesc(string $document): array
    {
        $keyAndDesc = [];
        $items = explode("\n", $document);
        foreach ($items as $item) {
            $pos = strpos($item, ' ');
            $key = substr($item, 0, $pos);
            $desc = substr($item, $pos + 1);
            $keyAndDesc[$key] = $desc;
        }

        return $keyAndDesc;
    }

    /**
     * 替换注解中的变量为对应的值
     * @param string $str
     * @param array $vars
     * @return string
     */
    protected function parseAnnotationVars(string $str, array $vars): string
    {
        // not use vars
        if (false === strpos($str, '{')) {
            return $str;
        }

        $map = [];

        foreach ($vars as $key => $value) {
            $key = sprintf(self::ANNOTATION_VAR, $key);
            $map[$key] = $value;
        }

        return $map ? strtr($str, $map) : $str;
    }
}
