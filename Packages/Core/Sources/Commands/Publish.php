<?php
namespace Packages\Core\Sources\Commands;
use Illuminate\Console\Command;
use Packages\Core\Sources\Services\CoreServices;

class Publish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[ATP CMS] This will publish all packages.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $coreServices = app()->make(CoreServices::class);
        $packages = $coreServices->listPackages(false, true, true);
        foreach( $packages as $p){
            $this->call('vendor:publish', [
                '--tag' => strtolower($p), '--force' => true
            ]);
        }
    }
}