<?php
namespace Packages\Core\Sources\Commands;

use Illuminate\Console\Command;
use Stichoza\GoogleTranslate\TranslateClient;
use Illuminate\Support\Facades\App;

class Translator extends Command
{
    /**
     * @var string
     */
    protected $signature = 'config:translation';

    /**
     * @var string
     */
    protected $description = 'Search new keys and update translation file';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {


        $jsonFile = base_path('Packages/System').'/Resources/lang/'.App::getLocale().'.json';

        if(! file_exists($jsonFile)) {
             file_put_contents($jsonFile, '{}');
             $this->info(App::getLocale().'.json'.' Files Created Successfully.');
        }
        $translationKeys = $this->findProjectTranslationsKeys();
        $translationFiles = $this->getProjectTranslationFiles();

        foreach ($translationFiles as $file) {
            $lang = str_replace('.json', '', basename($file));
            if ($lang == 'en'){
                continue;
            }
            $translationData = $this->getAlreadyTranslatedKeys($file);
            $this->line("lang " . $lang);
            $added = [];

            foreach ($translationKeys as $key) {
                if (!isset($translationData[$key])) {
                    $this->warn(" - Added {$key}");
                    $translationData[trans($key)] = TranslateClient::translate('en', $lang, trans($key));
                    $added[] = $key;
                    usleep(6666);
                }
            }

            if ($added) {
                $this->line("updating file...");
                $this->writeNewTranslationFile($file, $translationData);
                $this->info("done!");
            } else {
                $this->warn("new keys not found for this language");
            }
            $this->line("");
        }
    }

    /**
     * @return array
     */
    private function findProjectTranslationsKeys()
    {
        $allKeys = [];
        $viewsDirectories = config('laravel-translator.views_directories');
        foreach($viewsDirectories as $directory) {
            $this->getTranslationKeysFromDir($allKeys, $directory);
        }
        ksort($allKeys);

        return $allKeys;
    }

    /**
     * @param array $keys
     * @param string $dirPath
     * @param string $fileExt
     */
    private function getTranslationKeysFromDir(&$keys, $dirPath, $fileExt = 'php,js')
    {
        $files = $this->glob_recursive("{$dirPath}/*.{{$fileExt}}", GLOB_BRACE);

        foreach ($files as $file) {
            $content = $this->getSanitizedContent($file);

            $this->getTranslationKeysFromFunction($keys, 'lang', $content);
            $this->getTranslationKeysFromFunction($keys, '__', $content);
            $this->getTranslationKeysFromFunction($keys, 'trans', $content);
        }
    }

    /**
     * @param array $keys
     * @param string $functionName
     * @param string $content
     */
    private function getTranslationKeysFromFunction(&$keys, $functionName, $content)
    {
        $matches = [];
        preg_match_all("#{$functionName}\((.*?)\)#", $content, $matches);

        if (!empty($matches)) {
            foreach ($matches[1] as $match) {
                $strings = [];
                preg_match('#\'(.*?)\'#', str_replace('"', "'", $match), $strings);

                if (!empty($strings)) {
                    $keys[$strings[1]] = $strings[1];
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getProjectTranslationFiles()
    {
        $path = config('laravel-translator.translations_output');
        $files = glob("{$path}/*.json", GLOB_BRACE);

        return $files;
    }

    /**
     * @param string $filePath
     * @return array
     */
    private function getAlreadyTranslatedKeys($filePath)
    {
        $current = json_decode(file_get_contents($filePath), true);
        ksort($current);

        return $current;
    }

    /**
     * @param string $filePath
     * @param array $translations
     */
    private function writeNewTranslationFile($filePath, $translations)
    {
        file_put_contents($filePath, json_encode($translations, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param string $filePath
     * @return string
     */
    private function getSanitizedContent($filePath)
    {
        return str_replace("\n", ' ', file_get_contents($filePath));
    }

    private function glob_recursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->glob_recursive($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }

}
