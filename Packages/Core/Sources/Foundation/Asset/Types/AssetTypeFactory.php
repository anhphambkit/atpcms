<?php

namespace Packages\Core\Sources\Foundation\Asset\Types;

class AssetTypeFactory
{
    /**
     * @param $asset
     * @return \Packages\Core\Sources\Foundation\Asset\Types\AssetType
     * @throws \InvalidArgumentException
     */
    public function make($asset)
    {
        $typeClass = 'Packages\Core\Sources\Foundation\Asset\Types\\' . ucfirst(key($asset)) . 'Asset';

        if (class_exists($typeClass) === false) {
            throw new \InvalidArgumentException("Asset Type Class [$typeClass] not found");
        }

        return (new $typeClass($asset));
    }
}
