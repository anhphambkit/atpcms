<?php
namespace Packages\Core\Sources\Commands;
use Illuminate\Console\Command;

class CleanProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[ATP CMS] Clean logs + other unused info.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->call('activitylog:clean');
    }
}