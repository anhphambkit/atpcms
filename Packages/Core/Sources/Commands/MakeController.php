<?php
namespace Packages\Core\Sources\Commands;
use Illuminate\Console\Command;
use Packages\Core\Sources\Services\CoreServices;

class MakeController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:ctrler {package} {type} {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[ATP CMS] Make controller template.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $package = $this->argument('package');
        $model = $this->argument('model');
        $type = $this->argument('type');
        $coreServices = app()->make(CoreServices::class);
        $coreServices->exportTemplate($coreServices->packagePath('Core'). '/Publication/Controllers/'. ucfirst($type), $coreServices->packagePath($package). '/Sources/', [ 'package' => $package, 'model'  => $model ]);
    }
}