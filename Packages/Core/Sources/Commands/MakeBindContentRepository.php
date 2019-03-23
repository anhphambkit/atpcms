<?php
namespace Packages\Core\Sources\Commands;
use Illuminate\Console\Command;
use Packages\Core\Sources\Services\CoreServices;

class MakeBindContentRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:bind-repo {package} {repo}';

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
        $uses = $this->getLib();
        $this->line('');
        foreach ($uses as $lib) {
            $this->line($lib);
        }

        $content = $this->getContent();
        $this->line($content);
    }

    /**
     * No comment. This is a magic function
     */
    protected function getLib()
    {
        $package = ucfirst(trim($this->input->getArgument('package')));
        $repo = ucfirst(trim($this->input->getArgument('repo')));
        return [
            "use Packages\\{$package}\Sources\Repositories\\{$repo}Repositories;",
            "use Packages\\{$package}\Sources\Repositories\Eloquent\Eloquent{$repo}Repositories;",
            "use Packages\\{$package}\Sources\Repositories\Cache\Cache{$repo}Repositories;",
        ];
    }

    /**
     * Description
     * @return type
     */
    protected function getContent(){
        $repo = ucfirst(trim($this->input->getArgument('repo')));

        return '
            $this->app->singleton('.$repo.'Repositories::class, function () {
                $repository = new Eloquent'.$repo.'Repositories();

                if (! config("app.cache")) {
                    return $repository;
                }
                return new Cache'.$repo.'Repositories($repository);
            });
        ';
    }
}