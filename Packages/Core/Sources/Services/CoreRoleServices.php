<?php
/**
 * The interface of corerole services
 */
namespace Packages\Core\Sources\Services;

interface CoreRoleServices {
    /**
     * List all permissions of package or all packages
     * @param null $package: List all permission in specific package, $package = null will list permissions all package
     * @return null || Collection
     */
    public function listPermissions($package = null);
}