<?php
namespace Packages\Core\Sources\Commands;
use Illuminate\Console\Command;

class Build extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build {package} {assetFile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[ATP CMS] Build frontend module.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $package = strtolower($this->argument('package'));
        $file = $this->argument('assetFile');
        $command = "npm run build -- --env.pkg={$package} --env.src={$file}";
        $this->line('We are building asset file');
        $this->info('FROM: Packages/'. ucfirst($package). '/'. 'Resources/assets/'. $file);
        $this->info('TO: public/packages/'. $package. '/assets/'. $file);
        passthru($command);
    }
}