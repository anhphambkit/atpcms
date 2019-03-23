<?php

namespace Packages\Core\Sources\Traits\Providers;

trait CanPublishConfiguration
{
    /**
     * Publish the given configuration file name (without extension) and the given module
     * @param string $module
     * @param string $fileName
     */
    public function publishConfig($package, $fileName)
    {
        if (app()->environment() === 'testing') {
            return;
        }

        $this->mergeConfigFrom($this->getModuleConfigFilePath($package, $fileName), strtolower("atp.$package.$fileName"));
        $this->publishes([
            $this->getModuleConfigFilePath($package, $fileName) => config_path(strtolower("atp/$package/$fileName") . '.php'),
        ], 'config');
    }

    /**
     * Get path of the give file name in the given package
     * @param string $package
     * @param string $file
     * @return string
     */
    private function getModuleConfigFilePath($package, $file)
    {
        return $this->getModulePath($package) . "/Config/$file.php";
    }

    /**
     * @param $package
     * @return string
     */
    private function getModulePath($package)
    {
        return base_path('Packages' . DIRECTORY_SEPARATOR . ucfirst($package));
    }
}
