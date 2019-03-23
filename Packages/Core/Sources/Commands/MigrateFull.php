<?php
namespace Packages\Core\Sources\Commands;
use Illuminate\Console\Command;
use Packages\Core\Sources\Services\CoreServices;

class MigrateFull extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:full';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[ATP CMS] Migrate all packages.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->call('publish');
        $this->call('migrate');
    }
}