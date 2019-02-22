<?php

namespace Swoorpc\Commands;
use Illuminate\Console\Command;
use Swoorpc\DTO\RpcInput;
use Swoorpc\DTO\Rpcobj;
use Swoorpc\Swoorpc;


class SwoorpcCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoorpc:server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'swoole rpc 服务';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->outputInfo();
        $server = app('swoorpc.server');
        $methods = \SwoorpcRouter::getMethods();
        foreach ($methods as list($method, $class, $alias, $options)) {
            $server->addMethod($class, $method, $alias);
        }
        $server->start();
    }


    /**
     * 输出基础信息
     *
     * @return void
     */
    protected function outputInfo()
    {
        $this->comment("---------------" . date('Y-m-d H:i:s') . "-------------------");
        $this->comment('版本:');
        $this->output->writeln(sprintf('Laravel:<info>%s</>', app()::VERSION), $this->parseVerbosity(null));
        $this->output->writeln(sprintf('Swoole:<info>%s</>', SWOOLE_VERSION), $this->parseVerbosity(null));
        $this->output->newLine();

        $this->comment('监听:');
        $config = config('swoorpc.server');
        $this->line(sprintf(' = <info>%s:%s</>', $config['host'], $config['port']));

        $this->output->newLine();
        $this->comment('远程方法:');
        $methods = \SwoorpcRouter::getMethods();
        if ($methods) {
            foreach ($methods as $name => $method) {
                $this->line(sprintf(' -> <info>%s</>', $name));
            }
            $this->output->newLine();
        } else {
            $this->line(sprintf(' -- <info>无可调用方法</>'));
        }

        $this->comment('-----------------------------------------------------' . PHP_EOL);
    }
}