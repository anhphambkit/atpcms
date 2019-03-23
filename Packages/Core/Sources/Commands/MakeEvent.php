<?php
namespace Packages\Core\Sources\Commands;
use Illuminate\Console\Command;
use Packages\Core\Sources\Services\CoreServices;

class MakeEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:b-event {package}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[ATP CMS] Make event template.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        $coreServices = app()->make(CoreServices::class);
        $package = $this->argument('package');
        $packagePath = $coreServices->packagePath(ucfirst($package));
        if(!file_exists($packagePath))
            throw new Exception("Package not found", 1);
            
        while (!$eventName = $this->ask('Event name?')) {}
        while (!$handle = $this->ask('Handle event name?')) {}

        $coreServices->exportTemplate($coreServices->packagePath('Core'). '/Publication/Events', $coreServices->packagePath($package). '/Sources/', [ 'package' => $package, 'event'  => $eventName, 'handle' => $handle ]);

        return 'Make event success.';
    }
}