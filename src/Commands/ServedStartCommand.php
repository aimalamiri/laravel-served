<?php

namespace Sinnbeck\LaravelServed\Commands;

use Exception;
use Illuminate\Console\Command;
use Sinnbeck\LaravelServed\Commands\Traits\BindOutputToApp;
use Sinnbeck\LaravelServed\Commands\Traits\PortCheck;
use Sinnbeck\LaravelServed\Commands\Traits\DockerCheck;
use Sinnbeck\LaravelServed\Commands\Traits\RunningConfig;
use Sinnbeck\LaravelServed\Docker\Docker;
use Sinnbeck\LaravelServed\ServiceManager;
use Sinnbeck\LaravelServed\Exceptions\PortAlreadyInUseException;

class ServedStartCommand extends Command
{
    use BindOutputToApp,
        DockerCheck,
        PortCheck,
        RunningConfig;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'served:start {service?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start container(s)';

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
     * @param Docker $docker
     * @param ServiceManager $manager
     * @return int
     * @throws Exception
     */
    public function handle(Docker $docker, ServiceManager $manager): int
    {
        $this->checkPrerequisites($docker);
        try {
            $this->checkPortConflicts($docker, $manager);

        }
        catch (PortAlreadyInUseException $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $this->comment('Ensuring old containers are stopped ...');
        $this->callSilent('served:stop');

        $servedName = app('served.name');
        $docker->ensureNetworkExists($servedName);

        $onlyService = $this->argument('service');

        $serviceList = $manager->loadServices();

        foreach ($serviceList as $service) {
            if ($onlyService && $service->name() !== $onlyService) {
                continue;
            }
            $this->info(sprintf('Starting %s (%s) ...', $service->name(), $service->imageName()));
            $service->container()->start();

        }
        $this->line('');
        $this->servedRunning($manager);
        return 0;
    }
}
