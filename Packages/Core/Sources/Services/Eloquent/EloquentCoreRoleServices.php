<?php
/**
 * CoreRole service implemented
 */
namespace Packages\Core\Sources\Services\Eloquent;
use Packages\Core\Sources\Repositories\CoreRoleRepositories;
use Packages\Core\Sources\Services\CoreRoleServices;
use Packages\Core\Sources\Services\CoreServices;

class EloquentCoreRoleServices implements CoreRoleServices {

    /**
     * App CoreService
     * @var CoreServices
     */
    private $coreServices;

    public function __construct(CoreServices $coreServices)
    {
        $this->coreServices = $coreServices;
    }

    /**
     * List all permissions of package or all packages
     * @param null $package : List all permission in specific package, $package = null will list permissions all package
     * @return null || Collection
     */
    public function listPermissions($package = null)
    {
        $packages = collect();
        if($package){
            $packages->push($package);
        } else {
            $availablePackages = $this->coreServices->listPackages();
            $packages = collect($availablePackages);
        }

        $permissions = collect();
        foreach ($packages as $p){
            $interface = 'Packages\\'. $p. '\\Permissions\\Permission';
            if(interface_exists($interface)){
                $packagePermissions = new \ReflectionClass($interface);
                $permissions = $permissions->push([
                    'package'   => $p,
                    'permissions'   => $packagePermissions->getConstants()
                ]);
            }
        }
        return $permissions;
    }
}