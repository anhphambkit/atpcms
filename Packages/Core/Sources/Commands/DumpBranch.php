<?php
namespace Packages\Core\Sources\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Composer;

class DumpBranch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[ATP CMS] Clean branch.';

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new composer install command instance.
     *
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() 
    {   
        $caches = [
            'boostrap' => $this->laravel->bootstrapPath().DIRECTORY_SEPARATOR.'cache',

            'cache'    => $this->laravel->storagePath().DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'data',

            'view'     => $this->laravel->storagePath().DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'views',
        ];

        foreach($caches as $key => $value)
        {
            if($this->folderExist($value))
            {
                $this->recursiveDelete($value);
            }
        }

        $this->composer->dumpAutoloads();
    }

    /**
     * Delete directory not empty
     * @param type $dirPath 
     * @return type
     */
    public function recursiveDelete($str) {
        if (is_file($str)) {
            return @unlink($str);
        }
        elseif (is_dir($str)) {
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path) {
                $this->recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }

    /**
     * Check folder exists
     * @param type $folder 
     * @return type
     */
    protected function folderExist($folder)
    {
        // Get canonicalized absolute pathname
        $path = realpath($folder);

        // If it exist, check if it's a directory
        if($path !== false AND is_dir($path))
        {
            // Return canonicalized absolute pathname
            return $path;
        }

        // Path/folder does not exist
        return false;
    } 
}