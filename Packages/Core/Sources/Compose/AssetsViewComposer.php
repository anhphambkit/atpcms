<?php

namespace Packages\Core\Sources\Compose;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Packages\Core\Sources\Foundation\Asset\Manager\AssetManager;
use Packages\Core\Sources\Foundation\Asset\Pipeline\AssetPipeline;
use Packages\Core\Sources\Foundation\Asset\Types\AssetTypeFactory;

class AssetsViewComposer
{
    /**
     * @var AssetManager
     */
    protected $assetManager;
    /**
     * @var AssetPipeline
     */
    protected $assetPipeline;
    /**
     * @var AssetTypeFactory
     */
    protected $assetFactory;
    /**
     * @var Request
     */
    private $request;

    public function __construct(AssetManager $assetManager, AssetPipeline $assetPipeline, AssetTypeFactory $assetTypeFactory, Request $request)
    {
        $this->assetManager = $assetManager;
        $this->assetPipeline = $assetPipeline;
        $this->assetFactory = $assetTypeFactory;
        $this->request = $request;
    }

    public function compose(View $view)
    {
       
        if ($this->onBackend() === false) {
            return;
        }
        foreach (config('resources.admin-assets') as $assetName => $path) {
            $path = $this->assetFactory->make($path)->url();
            $this->assetManager->addAsset($assetName, $path);
        }
        $this->assetPipeline->requireCss(config('resources.admin-required-assets.css'));
        $this->assetPipeline->requireJs(config('resources.admin-required-assets.js'));

        $view->with('cssFiles',$this->assetPipeline->allCss());
        $view->with('jsFiles', $this->assetPipeline->allJs());
    }

    /**
     * Checks if the current url matches the configured backend uri
     * @param int $indexSegment
     * @return bool
     */
    private function onBackend(int $indexSegment = 1)
    {
        if (app(Request::class)->segment($indexSegment) === config('atp-cms-settings.prefix-backend')) {
            return true;
        }

        return false;
    }
}
