<?php
namespace Packages\Core\Sources\Services\Eloquent;
use Carbon\Carbon;
use Packages\Core\Sources\Services\UtilServices;

class EloquentUtilServices implements UtilServices  {

    public function __construct(){}

    /**
     * Generate a slug from string
     * @param String $string
     * @return String slug
     */
    public function generateSlug($string)
    {
        if(!$string) return false;
        $unicode = array(
            'a'=>array('á','à','ả','ã','ạ','ă','ắ','ặ','ằ','ẳ','ẵ','â','ấ','ầ','ẩ','ẫ','ậ'),
            'A'=>array('Á','À','Ả','Ã','Ạ','Ă','Ắ','Ặ','Ằ','Ẳ','Ẵ','Â','Ấ','Ầ','Ẩ','Ẫ','Ậ'),
            'd'=>array('đ'),
            'D'=>array('Đ'),
            'e'=>array('é','è','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ'),
            'E'=>array('É','È','Ẻ','Ẽ','Ẹ','Ê','Ế','Ề','Ể','Ễ','Ệ'),
            'i'=>array('í','ì','ỉ','ĩ','ị'),
            'I'=>array('Í','Ì','Ỉ','Ĩ','Ị'),
            'o'=>array('ó','ò','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ'),
            'O'=>array('Ó','Ò','Ỏ','Õ','Ọ','Ô','Ố','Ồ','Ổ','Ỗ','Ộ','Ơ','Ớ','Ờ','Ở','Ỡ','Ợ'),
            'u'=>array('ú','ù','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự'),
            'U'=>array('Ú','Ù','Ủ','Ũ','Ụ','Ư','Ứ','Ừ','Ử','Ữ','Ự'),
            'y'=>array('ý','ỳ','ỷ','ỹ','ỵ'),
            'Y'=>array('Ý','Ỳ','Ỷ','Ỹ','Ỵ'),
            '-'=>array(' ','&quot;','.','-–-')
        );
        foreach($unicode as $nonUnicode=>$uni){
            foreach($uni as $value)
                $string = @str_replace($value,$nonUnicode,$string);
            $string = preg_replace("/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'| |\"|\&|\#|\[|\]|~|$|_/","-",$string);
            $string = preg_replace("/-+-/","-",$string);
            $string = preg_replace("/^\-+|\-+$/","",$string);
        }
        return strtolower($string);
    }

    /**
     * Auto convert datetime to string match with the system config
     * @param Carbon $input
     * @param string $type : d: Date, t: time, dt: datetime
     * @return string
     * @throws \Exception
     */
    public function formatDateTime($input, $type = 'd')
    {
        if(get_class($input) !== \Illuminate\Support\Carbon::class && get_class($input) !== Carbon::class){
            throw new \Exception('Invalid datetime input. We require input is instance of '. \Illuminate\Support\Carbon::class);
        }

        $dateFormat = env('DATE_FORMAT', 'd/m/Y'); // 25/12/1992
        $dateTimeFormat = env('DATE_TIME_FORMAT', 'd/m/Y H:i'); // 25/12/1992 02:12
        $timeFormat = env('TIME_FORMAT', 'H:i'); // 23:12

        if($type === 'd'){
            return $input->format($dateFormat);
        } elseif ($type === 't') {
            return $input->format($timeFormat);
        } else {
            return $input->format($dateTimeFormat);
        }
    }

    /**
     * Format datetime request
     * @param string $string input data to convert
     * @param string $type: One of these types: ['d', 'dt', 't']
     * @return Carbon
     */
    public function stringToDateTime($string, $type = 'd'){
        $dateFormat = env('DATE_FORMAT', 'd/m/Y'); // 25/12/1992
        $dateTimeFormat = env('DATE_TIME_FORMAT', 'd/m/Y H:i'); // 25/12/1992 02:12
        $timeFormat = env('TIME_FORMAT', 'H:i'); // 23:12

        if($type === 'd'){
            return Carbon::instance(date_create_from_format($dateFormat, $string));
        } elseif ($type === 't') {
            return Carbon::instance(date_create_from_format($timeFormat, $string));
        } else {
            return Carbon::instance(date_create_from_format($dateTimeFormat, $string));
        }
    }

    /**
     * Format number
     * @param double $number
     * @param int $delimiter
     * @return double
     */
    public function formatNumber($number, $delimiter = null)
    {
        if(is_null($delimiter)){
            $delimiter = env('DELIMITER_NUMBER', 3);
        }
        return number_format($number, $delimiter);
    }

    /**
     * Like fmod PHP but work better
     * @param $x
     * @param $y
     * @return mixed
     */
    public function fmod($x, $y)
    {
        return $x - round($x / $y) * $y;
    }
}