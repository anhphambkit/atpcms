<?php
/**
 * Created by PhpStorm.
 * User: minh.truong
 * Date: 3/23/18
 * Time: 10:37 AM
 */
namespace Packages\Core\Sources\Services;

interface CoreServices
{
    public function listPackages($isIgnoreCore = true, $includeDisablePackage = false);

    /**
     * Get path of package
     * @param $package: Package name
     * @return string: Full path of package
     */
    public function packagePath($package);

    /**
     * Copy template directory and replace new value
     * @param $source: Template directory source
     * @param $destination: Path to export
     * @param array $data: Data will be replaced
     */
    public function exportTemplate($source, $destination, $data);
}