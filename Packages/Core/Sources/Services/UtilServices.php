<?php
/**
 * The interface of corerole services
 */
namespace Packages\Core\Sources\Services;

interface UtilServices {
    /**
     * Generate a slug from string
     * @param String $string
     * @return String slug
     */
    public function generateSlug($string);

    /**
     * Auto convert datetime to string match with the system config
     * @param \DateTime $dateTime
     * @param string $type: d: Date, t: time, dt: datetime
     * @return string
     */
    public function formatDateTime($dateTime, $type = 'd');

    /**
     * Format number
     * @param double $number
     * @param int $delimiter
     * @return double
     */
    public function formatNumber($number, $delimiter = null);

    /**
     * Like fmod PHP but work better
     * @param $x
     * @param $y
     * @return mixed
     */
    public function fmod($x, $y);
}