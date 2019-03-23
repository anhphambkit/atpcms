<?php
namespace Packages\Core\Sources\Commands;
use Illuminate\Console\Command;
use Packages\Core\Sources\Services\CoreServices;

class MakeRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:req {package} {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[ATP CMS] Make new form request.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $package = $this->argument('package');
        $model = $this->argument('model');
        $coreServices = app()->make(CoreServices::class);
        $coreServices->exportTemplate($coreServices->packagePath('Core'). '/Publication/Request', $coreServices->packagePath($package) . '/Sources/', [ 'package' => $package, 'model' => $model ]);
    }
}