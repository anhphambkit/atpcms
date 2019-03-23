<?php
namespace Packages\Core\Sources\Commands;
use Illuminate\Console\Command;
use Packages\Core\Sources\Services\CoreServices;

class MakeFunctionRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:func {package} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[ATP CMS] Make func.';

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var array
     */
    protected $functionName;

    /**
     * @var array
     */
    protected $defaultValues = [];

    /**
     * @var Base path set file.
     */
    protected $basePath;

    /**
     * @var prefix file name
     */
    protected $prefix = 'Repositories.php';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        #TODO
        $this->setBasePath();

        $paths = $this->buildFilePath();

        $this->writeFunction();

        $mainContent = $this->buildFunction();

        foreach($paths as $key => $path)
        {
            $revert = $this->getOldFileContent($key, $path);
            $newContent =  $this->overwrite($key, $revert, $mainContent);
            if(!empty($newContent))
                file_put_contents($path, $newContent);
        }
    }

    /**
     * Description
     * @param type $key 
     * @param type $revert 
     * @param type $mainContent 
     * @return type
     */
    protected function overwrite($key, $revert, $mainContent)
    {
        if($key === 'interface'){
            return  $revert . $mainContent .';' .PHP_EOL . '}';
        }

        if($key === 'repository'){
            return  $revert . $mainContent .'{}' .PHP_EOL . '}';
        }

        if($key === 'cache')
        {
            return $revert . $mainContent .'{

        return $this->getDataWithoutCache(__FUNCTION__, func_get_args());
    }' .PHP_EOL . '}';
        }
    }

    /**
     * Description
     * @param type $key 
     * @param type $path 
     * @return type
     */
    protected function getOldFileContent($key, $path)
    {
        $file = file_get_contents($path);

        $arrays = explode('}', $file);

        array_pop($arrays);

        $revert = implode("}", $arrays);

        return $revert;
    }


    /**
     * Function build path
     * @return type
     */
    protected function buildFilePath()
    {
        $file = trim($this->input->getArgument('file'));

        return [
            'interface'  => $this->getPathWithType($file,'interface'),
            'repository' => $this->getPathWithType($file,'repository'),
            'cache'      => $this->getPathWithType($file, 'cache')
        ];
    }

    /**
     * Set base path.
     */
    protected function setBasePath(){

        $package = trim($this->input->getArgument('package'));

        $basePath = $this->laravel->basePath().DIRECTORY_SEPARATOR.'Packages'.DIRECTORY_SEPARATOR.ucfirst($package).DIRECTORY_SEPARATOR.'Sources';

        $basePath .= DIRECTORY_SEPARATOR.'Repositories';

        $this->basePath = $basePath;
    }

    /**
     * Get path with type
     * @param string $fileName 
     * @param string $type 
     * @return string
     */
    protected function getPathWithType($fileName, $type = 'interface')
    {
        $filePath = '';

        if($type === 'interface')
            $filePath =  $this->basePath . DIRECTORY_SEPARATOR . ucfirst($fileName);

        if($type === 'repository')
            $filePath =  $this->basePath . DIRECTORY_SEPARATOR .'Eloquent'. DIRECTORY_SEPARATOR .'Eloquent' . ucfirst($fileName);

        if($type === 'cache')
            $filePath =  $this->basePath . DIRECTORY_SEPARATOR .'Cache'. DIRECTORY_SEPARATOR . 'Cache'. ucfirst($fileName);

        $fullPath =  $filePath . $this->prefix;

        if(!file_exists($fullPath))
        {
            throw new \Exception("file $fullPath does not exists");
        }

        return $fullPath;
    }

    /**
     * Write function php 
     */
    protected function writeFunction(){

        while (empty($this->functionName)) {
            # code...
            $this->functionName = $this->ask('What is your function name?');
        }

        $this->askForParams();
    }

    /**
     * Description
     */
    protected function buildFunction(){
        $content = PHP_EOL . "\tpublic function " . $this->functionName;

        $params = [];

        foreach($this->params as $key => $param)
        {
            if($this->defaultValues[$key] !== '<none>')
                $params[] = $this->optionParams($param, $this->defaultValues[$key]);
            else
                $params[] = '$'.$param;
        }

        $params = '(' . implode(', ',$params) . ')';

        return $content . $params;
    }

    /**
     *
     */
    protected function askForParams()
    {
        do {
            $entity = $this->ask('Enter param name. Leaving option empty will continue script.', '<none>');
            if (!empty($entity) && $entity !== '<none>') {
                $this->params[] = $entity;
                $this->askForDefaultValues();
            }
        } while ($entity !== '<none>');
    }

    /**
     *
     */
    protected function askForDefaultValues()
    {
        $entity = $this->ask('Enter default value. Leaving option empty will continue script.', '<none>');
        if (!empty($entity) && $entity !== '<none>') {
            $this->defaultValues[] = $entity;
        }else
            $this->defaultValues[] = '<none>';
    }

    /**
     * Description
     * @param type $value 
     * @return type
     */
    protected function optionParams($param, $value)
    {
        $check = json_decode($value);

        if(is_array($check))
            return 'array $'.$param . ' = ' . $value;

        if(is_bool($check))
            return 'bool $'.$param . ' = ' . $value;

        return '$'.$param . ' = ' . $value;
    }
}