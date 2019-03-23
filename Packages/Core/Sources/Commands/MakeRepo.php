<?php
namespace Packages\Core\Sources\Commands;
use Illuminate\Console\Command;
use Packages\Core\Sources\Services\CoreServices;

class MakeRepo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repo {package} {repo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[ATP CMS] Make new repo.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $package = $this->argument('package');
        $repo = $this->argument('repo');
        $coreServices = app()->make(CoreServices::class);
        $coreServices->exportTemplate($coreServices->packagePath('Core'). '/Publication/Repository', $coreServices->packagePath($package) . '/Sources/', [ 'package' => $package, 'repo' => $repo ]);

        if ($this->confirm('Do you want to bind repo with interface?')) {
            return $this->call("make:bind-repo",['package' => $package, 'repo' => $repo]);
        }
    }
}