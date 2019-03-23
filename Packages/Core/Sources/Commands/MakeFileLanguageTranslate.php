<?php
namespace Packages\Core\Sources\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class MakeFileLanguageTranslate extends Command
{
    /**
     * @var string
     */
    protected $signature = 'make:make-file-translate {name}';

    /**
     * @var string
     */
    protected $description = 'Make a translation file.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $locales = $this->getLocales(base_path('Packages/System').'/Resources/lang/locales/locale.json');
        $this->setProgressBar($locales);
        $this->line('Location list to create language file:');
        $headers = ['id','Code', 'Language'];
        $this->table($headers, $locales);
        $choice = $this->ask('Please ! Type your index selected:');
        if(is_numeric($choice)){
            if($choice > count($locales) -1){
                $this->warn("Out of index range !");
            }else{
                $localeSelected = $locales[$choice];
                $jsonFile = base_path('Packages/System').'/Resources/lang/'.$localeSelected['code'].'.json';
                if(! file_exists($jsonFile)) {
                    $this->askForCreatingFile($localeSelected['code'], $jsonFile);
                    $this->line('You choosed location:');
                    $this->line($localeSelected);
                }else{
                    $this->error("This file is exist !");
                }
            }
        }
        
    }

     /**
     *
     */
     protected function askForCreatingFile($fileName, $jsonFile)
     {
        if ($this->confirm('Would you like to create this file? [yes|no]'))
        {
         file_put_contents($jsonFile, '{}');
         $this->info($fileName.'.json'.' Files Created Successfully.');
     }
 }

    /**
     * @param string $filePath
     * @return array
     */
    private function getLocales($filePath)
    {
        $current = json_decode(file_get_contents($filePath), true);
        foreach ($current['locale'] as $key => $value) {
           $current[$key]['id'] = $key;
           $current[$key]['code'] = $value['code'];
           $current[$key]['language'] = $value['language'];
       }
       unset($current['locale']);
       return $current;

   }

    /**
     * @param array $length
     * @return void
     */
    private function setProgressBar($length){
        $this->output->progressStart(100);
        for ($i = 0; $i < 10; $i++) {
            sleep(1);

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }
}
