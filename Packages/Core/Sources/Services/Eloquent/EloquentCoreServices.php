<?php
/**
 * Created by PhpStorm.
 * User: minh.truong
 * Date: 3/23/18
 * Time: 10:37 AM
 */
namespace Packages\Core\Sources\Services\Eloquent;

use Illuminate\Support\Facades\Cache;
use Packages\Core\Sources\Services\CoreServices;

class EloquentCoreServices implements CoreServices {
    /**
     * Get all Packages name in Packages directory
     * @param bool $isIgnoreCore : Ignore Core package
     * @param bool $includeDisablePackage : Include disabled package or not
     * @return array : List packages name
     * @throws \Exception
     */
    public function listPackages($isIgnoreCore = true, $includeDisablePackage = false){
        $cacheKey = 'CoreServices_listPackages'.$isIgnoreCore.'_'.$includeDisablePackage;
        if(Cache::has($cacheKey)){
            return Cache::get($cacheKey);
        }

        // Search packages and include
        $packages = scandir(base_path('Packages'));
        $packages = array_diff($packages, [".", ".."]); // Remove item "." , ".."

        foreach($packages as $i => $p){
            if(!is_dir(base_path('Packages/'. $p))){
                unset($packages[$i]);
            }
        }

        if($isIgnoreCore){
            $packages = array_diff($packages, ["Core"]);
        }

        if($includeDisablePackage){
            Cache::put($cacheKey, array_values($packages), env('SESSION_LIFETIME',120));
            return array_values($packages);
        } else {
            $enablePackages = [];
            foreach ($packages as $p){
                $path = $this->packagePath($p);
                if(file_exists($path. '/package.php')){
                    $config = (require $path. '/package.php');
                    if($config['enable']){
                        $enablePackages []= $p;
                    }
                } else {
                    throw new \Exception('Not found file package.php in '. $path);
                }
            }

            Cache::put($cacheKey, array_values($enablePackages), env('SESSION_LIFETIME',120));
            return array_values($enablePackages);
        }
    }

    /**
     * Get path of package
     * @param $package: Package name
     * @return string: Full path of package
     */
    public function packagePath($package){
        return base_path('Packages/'. $package);
    }

    /**
     * Copy template directory and replace new value
     * @param $source: Template directory source
     * @param $destination: Path to export
     * @param array $data: Data will be replaced
     */
    public function exportTemplate($source, $destination, $data){

        if (!file_exists($destination)) {
            mkdir($destination);
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $fullPath => $fileInfo) {
            if (in_array($fileInfo->getBasename(), [".", ".."])) {
                continue;
            }

            $path = str_replace($source, "", $fileInfo->getPathname());
            if ($fileInfo->isDir()) {
                try {
                    $fileName = $this->parseTunTemplate($path, '', $data);
                    mkdir($destination . "/" . $fileName['file_name']);
                } catch (\ErrorException $e){
                    // Directory already exist we will continue process
                }
            } else {
                if(strlen($path) > 4 && substr($fullPath, -4) === '.tun'){
                    $content = file_get_contents($fullPath, FILE_USE_INCLUDE_PATH);
                    $fileExport = $this->parseTunTemplate($path, $content, $data);
                    $path = substr($fileExport['file_name'], 0, -4); // Remove .tun extension
                    file_put_contents($destination . "/" . $path, $fileExport['content']);
                } else {
                    copy($fullPath, $destination . "/" . $path);
                }
            }
        }
    }

    /**
     * Replace content and file name by expression (Tun template)
     * * @param $fileName : Path to source file
     * @param $content
     * @param array | null $data : key will be replaced by value. [ 'full_name' => 'Mr Smith' ]. => {full_name} = Mr Smith
     * @return array: Path to store file
     */
    private function parseTunTemplate($fileName, $content, $data){
        foreach ($data as $expression => $value){
            $regex = '/{'. $expression. '}/';
            $content = preg_replace($regex, $value, $content);
            $fileName = preg_replace($regex, $value, $fileName);

            // Replace capitalize word
            $regex = '/{c '. $expression. '}/';
            $content = preg_replace($regex, ucwords($value), $content);
            $fileName = preg_replace($regex, ucwords($value), $fileName);

            // Replace upper word
            $regex = '/{u '. $expression. '}/';
            $content = preg_replace($regex, mb_strtoupper($value), $content);
            $fileName = preg_replace($regex, mb_strtoupper($value), $fileName);

            // Replace lowercase word
            $regex = '/{l '. $expression. '}/';
            $content = preg_replace($regex, mb_strtolower($value), $content);
            $fileName = preg_replace($regex, mb_strtolower($value), $fileName);
        }

        return ['file_name' => $fileName, 'content' => $content];
    }
}