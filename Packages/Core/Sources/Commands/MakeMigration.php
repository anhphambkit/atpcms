<?php 
namespace Packages\Core\Sources\Commands;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Str;
class MakeMigration extends MigrateMakeCommand
{
	/**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:db {name : The name of the migration.} {package : The name of the migration.}
    	{--create= : The table to be created.}
        {--table= : The table to migrate.}
        {--path= : The location where the migration file should be created.}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths.}';


    /**
     * Write the migration file to disk.
     *
     * @param  string  $name
     * @param  string  $table
     * @param  bool    $create
     * @return string
     */
    protected function writeMigration($name, $table, $create)
    {
        $file = pathinfo(app(MigrationCreator::class)->create(
            $name, $this->getMigrationPath(), $table, $create
        ), PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> {$file}");
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath()
    {
       	$package = trim($this->input->getArgument('package'));
       	$folder = $this->laravel->basePath().DIRECTORY_SEPARATOR.'Packages'.DIRECTORY_SEPARATOR.$package.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR.'Migrations';
       
       	if($this->folderExist($folder))
    		return $folder;
    	throw new \Exception("Packages does not exist", 1);
    }

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