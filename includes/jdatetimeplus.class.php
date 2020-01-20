<?php
/**
 * Jalali (Shamsi) DateTime Class. Supports years higher than 2038. Updated in 2016.
 *
 * Copyright (c) 2016 Vahid Amiri Motlagh <vahid.a1996@gmail.com>
 * http://atvsg.com
 *
 * The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * 1- The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * 2- THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 * Original Jalali to Gregorian (and vice versa) methods from "jdf 2.60" package:
 * Copyright(C)2015, Reza Gholampanahi
 * http://jdf.scr.ir
 *
 * List of supported timezones can be found here:
 * http://www.php.net/manual/en/timezones.php
 *
 *
 * @package    jDateTimePlus
 * @author     Vahid Amiri Motlagh <vahid.a1996@gmail.com>
 * @copyright  2016 Vahid Amiri Motlagh
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @link       https://github.com/vsg24/jDateTimePlus
 * @see        DateTime
 * @version    1.0.0
 */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class jDateTimePlus
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
{
    /**
     * Defaults
     */
    private static $jalali   = true; //Use Jalali Date, If set to false, falls back to gregorian
    private static $convert  = true; //Convert numbers to Farsi characters in utf-8
    private static $timezone = null; //Timezone String e.g Asia/Tehran, Defaults to Server Timezone Settings
    private static $temp = array();
    /**
     * jDateTimePlus::Constructor
     *
     * Pass these parameters when creating a new instance
     * of this Class, and they will be used as defaults.
     * e.g $obj = new jDateTime(false, true, 'Asia/Tehran');
     * To use system defaults pass null for each one or just
     * create the object without any parameters.
     *
     * @author Sallar Kaboli
     * @param $convert bool Converts numbers to Farsi
     * @param $jalali bool Converts date to Jalali
     * @param $timezone string Timezone string
     */
    public function __construct($convert = null, $jalali = null, $timezone = null)
    {
        if ($jalali   !== null) {
            self::$jalali   = (bool) $jalali;
        }
        if ($convert  !== null) {
            self::$convert  = (bool) $convert;
        }
        if ($timezone !== null) {
            self::$timezone = $timezone;
        }
    }
    /**
     * Convert a formatted string from Georgian Calendar to Jalali Calendar.
     * This will be useful to directly convert time strings coming from databases.
     * Example:
     *
     *  // Suppose this comes from database
     *  $a = '2016-02-14 14:20:38';
     *  $date = \jDateTime::convertFormatToFormat('Y-m-d H:i:s', 'Y-m-d H:i:s', $a);
     *  // $date will now be '۱۳۹۴-۱۱-۲۵ ۱۴:۲۰:۳۸'
     *
     * @author Vahid Fazlollahzade
     * @param string $jalaliFormat Return format. Same as static::date(...)
     * @param string $georgianFormat The format of $timeString. See php.net/date
     * @param string $timeString The time itself, formatted as $georgianFormat
     * @param null|\DateTimeZone|string $timezone The timezone. Same as static::date(...)
     * @param bool $convertNumbers Whether convert numbers to Persian or not
     * @return string
     */
    public static function convertFormatToFormat(
        $jalaliFormat,
        $georgianFormat,
        $timeString,
        $timezone = null,
        $convertNumbers = false
    ) {
        // Normalize $timezone, take from static::date(...)
        $timezone = ($timezone != null) ? $timezone : ((self::$timezone != null) ? self::$timezone : date_default_timezone_get());
        if (is_string($timezone)) {
            $timezone = new \DateTimeZone($timezone);
        } elseif (!$timezone instanceof \DateTimeZone) {
            throw new \RuntimeException('Provided timezone is not correct.');
        }
        // Convert to timestamp, then to Jalali
        $datetime = \DateTime::createFromFormat($georgianFormat, $timeString, $timezone);
        return static::date($jalaliFormat, $datetime->getTimestamp(), $convertNumbers);
    }

    /**
     * jDateTimePlus::Date
     *
     * Formats and returns given timestamp just like php's
     * built in date() function.
     * e.g:
     * $obj->date("Y-m-d H:i", time());
     * $obj->date("Y-m-d", time(), false, false, 'America/New_York');
     *
     * @param $format string Acceps format string based on: php.net/date
     * @param $stamp int|bool Unix Timestamp (Epoch Time)
     * @param $convert bool (Optional) forces convert action. pass null to use system default
     * @param $jalali bool (Optional) forces jalali conversion. pass null to use system default
     * @param $timezone string (Optional) forces a different timezone. pass null to use system default
     * @return string Formatted input
     * @throws Exception
     * @author Sallar Kaboli
     */
    public static function date($format, $stamp = false, $convert = null, $jalali = null, $timezone = null)
    {
        //Timestamp + Timezone
        $stamp    = ($stamp !== false) ? $stamp : time();
        $timezone = ($timezone != null) ? $timezone : ((self::$timezone != null) ? self::$timezone : date_default_timezone_get());
        $obj      = new DateTime('@' . $stamp, new DateTimeZone($timezone));
        $obj->setTimezone(new DateTimeZone($timezone));
        if ((self::$jalali === false && $jalali === null) || $jalali === false) {
            return $obj->format($format);
        } else {
            //Find what to replace
            $chars  = (preg_match_all('/([a-zA-Z]{1})/', $format, $chars)) ? $chars[0] : array();

            //Intact Keys
            $intact = array('B','h','H','g','G','i','s','I','U','u','Z','O','P');
            $intact = self::filterArray($chars, $intact);
            $intactValues = array();
            foreach ($intact as $k => $v) {
                $intactValues[$k] = $obj->format($v);
            }
            //End Intact Keys
            //Changed Keys
            list($year, $month, $day) = array($obj->format('Y'), $obj->format('n'), $obj->format('j'));
            list($jyear, $jmonth, $jday) = self::toJalali($year, $month, $day);
            $keys   = array('d','D','j','l','N','S','w','z','W','F','m','M','n','t','L','o','Y','y','a','A','c','r','e','T');
            $keys   = self::filterArray($chars, $keys, array('z'));
            $values = array();
            foreach ($keys as $k => $key) {
                $v = '';
                switch ($key) {
                    //Day
                    case 'd':
                        $v = sprintf('%02d', $jday);
                        break;
                    case 'D':
                        $v = self::getDayNames($obj->format('D'), true);
                        break;
                    case 'j':
                        $v = $jday;
                        break;
                    case 'l':
                        $v = self::getDayNames($obj->format('l'));
                        break;
                    case 'N':
                        $v = self::getDayNames($obj->format('l'), false, 1, true);
                        break;
                    case 'S':
                        $v = 'ام';
                        break;
                    case 'w':
                        $v = self::getDayNames($obj->format('l'), false, 1, true) - 1;
                        break;
                    case 'z':
                        if ($jmonth > 6) {
                            $v = 186 + (($jmonth - 6 - 1) * 30) + $jday;
                        } else {
                            $v = (($jmonth - 1) * 31) + $jday;
                        }
                        self::$temp['z'] = $v;
                        break;
                    //Week
                    case 'W':
                        $v = is_int(self::$temp['z'] / 7) ? (self::$temp['z'] / 7) : intval(self::$temp['z'] / 7 + 1);
                        break;
                    //Month
                    case 'F':
                        $v = self::getMonthNames($jmonth);
                        break;
                    case 'm':
                        $v = sprintf('%02d', $jmonth);
                        break;
                    case 'M':
                        $v = self::getMonthNames($jmonth, true);
                        break;
                    case 'n':
                        $v = $jmonth;
                        break;
                    case 't':
                        if ($jmonth >= 1 && $jmonth <= 6) {
                            $v = 31;
                        } else if ($jmonth >= 7 && $jmonth <= 11) {
                            $v = 30;
                        } else if ($jmonth == 12 && $jyear % 4 == 3) {
                            $v=30;
                        } else if ($jmonth == 12 && $jyear % 4 != 3) {
                            $v = 29;
                        }
                        break;
                    //Year
                    case 'L':
                        $tmpObj = new DateTime('@'.(time()-31536000));
                        $v = $tmpObj->format('L');
                        break;
                    case 'o':
                    case 'Y':
                        $v = $jyear;
                        break;
                    case 'y':
                        $v = $jyear % 100;
                        break;
                    //Time
                    case 'a':
                        $v = ($obj->format('a') == 'am') ? 'ق.ظ' : 'ب.ظ';
                        break;
                    case 'A':
                        $v = ($obj->format('A') == 'AM') ? 'قبل از ظهر' : 'بعد از ظهر';
                        break;
                    //Full Dates
                    case 'c':
                        $v  = $jyear.'-'.sprintf('%02d', $jmonth).'-'.sprintf('%02d', $jday).'T';
                        $v .= $obj->format('H').':'.$obj->format('i').':'.$obj->format('s').$obj->format('P');
                        break;
                    case 'r':
                        $v  = self::getDayNames($obj->format('D'), true).', '.sprintf('%02d', $jday).' '.self::getMonthNames($jmonth, true);
                        $v .= ' '.$jyear.' '.$obj->format('H').':'.$obj->format('i').':'.$obj->format('s').' '.$obj->format('P');
                        break;
                    //Timezone
                    case 'e':
                        $v = $obj->format('e');
                        break;
                    case 'T':
                        $v = $obj->format('T');
                        break;
                }
                $values[$k] = $v;
            }
            //End Changed Keys
            //Merge
            $keys   = array_merge($intact, $keys);
            $values = array_merge($intactValues, $values);
            //Return
            $ret = strtr($format, array_combine($keys, $values));
            return
                ($convert === false ||
                    ($convert === null && self::$convert === false) ||
                    ( $jalali === false || $jalali === null && self::$jalali === false ))
                    ? $ret : self::convertNumbers($ret);
        }
    }
    /**
     * jDateTimePlus::gDate
     *
     * Same as jDateTimePlus::Date method
     * but this one works as a helper and returns Gregorian Date
     * in case someone doesn't like to pass all those false arguments
     * to Date method.
     *
     * e.g. $obj->gDate("Y-m-d") //Outputs: 2011-05-05
     *      $obj->date("Y-m-d", false, false, false); //Outputs: 2011-05-05
     *      Both return the exact same result.
     *
     * @author Sallar Kaboli
     * @param $format string Acceps format string based on: php.net/date
     * @param $stamp int|bool Unix Timestamp (Epoch Time)
     * @param $timezone string (Optional) forces a different timezone. pass null to use system default
     * @return string Formatted input
     */
    public static function gDate($format, $stamp = false, $timezone = null)
    {
        return self::date($format, $stamp, false, false, $timezone);
    }

    /**
     * jDateTimePlus::Strftime
     *
     * Format a local time/date according to locale settings
     * built in strftime() function.
     * e.g:
     * $obj->strftime("%x %H", time());
     * $obj->strftime("%H", time(), false, false, 'America/New_York');
     *
     * @author Omid Pilevar
     * @param $format string Acceps format string based on: php.net/date
     * @param $stamp int|bool Unix Timestamp (Epoch Time)
     * @param $convert bool (Optional) forces convert action. pass null to use system default
     * @param $jalali bool (Optional) forces jalali conversion. pass null to use system default
     * @param $timezone string (Optional) forces a different timezone. pass null to use system default
     * @return string Formatted input
     */
    public static function strftime($format, $stamp = false, $convert = null, $jalali = null, $timezone = null)
    {
        $str_format_code = array(
            '%a', '%A', '%d', '%e', '%j', '%u', '%w',
            '%U', '%V', '%W',
            '%b', '%B', '%h', '%m',
            '%C', '%g', '%G', '%y', '%Y',
            '%H', '%I', '%l', '%M', '%p', '%P', '%r', '%R', '%S', '%T', '%X', '%z', '%Z',
            '%c', '%D', '%F', '%s', '%x',
            '%n', '%t', '%%'
        );

        $date_format_code = array(
            'D', 'l', 'd', 'j', 'z', 'N', 'w',
            'W', 'W', 'W',
            'M', 'F', 'M', 'm',
            'y', 'y', 'y', 'y', 'Y',
            'H', 'h', 'g', 'i', 'A', 'a', 'h:i:s A', 'H:i', 's', 'H:i:s', 'h:i:s', 'H', 'H',
            'D j M H:i:s', 'd/m/y', 'Y-m-d', 'U', 'd/m/y',
            '\n', '\t', '%'
        );
        //Change Strftime format to Date format
        $format = str_replace($str_format_code, $date_format_code, $format);
        //Convert to date
        return self::date($format, $stamp, $convert, $jalali, $timezone);
    }

    /**
     * jDateTimePlus::Mktime
     *
     * Creates a Unix Timestamp (Epoch Time) based on given parameters
     * works like php's built in mktime() function.
     * e.g:
     * $time = $obj->mktime(0,0,0,2,10,1368);
     * $obj->date("Y-m-d", $time); //Format and Display
     * $obj->date("Y-m-d", $time, false, false); //Display in Gregorian !
     *
     * You can force gregorian mktime if system default is jalali and you
     * need to create a timestamp based on gregorian date
     * $time2 = $obj->mktime(0,0,0,12,23,1989, false);
     *
     * @author Vahid Amiri Motlagh
     * @param $hour int Hour based on 24 hour system
     * @param $minute int Minutes
     * @param $second int Seconds
     * @param $month int Month Number
     * @param $day int Day Number
     * @param $year int Four-digit Year number eg. 1390
     * @param $jalali bool (Optional) pass false if you want to input gregorian time
     * @return int Unix Timestamp (Epoch Time)
     */
    public static function mktime($hour, $minute, $second, $month, $day, $year, $jalali)
    {
        if ($hour == '' and $minute  =='' and $second == '' and $month == '' and $day == '' and $year == '') {
            return mktime();
        } else {
            if ($jalali) {
                list($m_year,$m_month,$m_day) = self::toGregorian($year, $month, $day);
            } else {
                list($m_year,$m_month,$m_day) = [$year, $month, $day];
            }
            return mktime($hour, $minute, $second, $m_month, $m_day, $m_year);
        }
    }

    /**
     * jDateTimePlus::Checkdate
     *
     * Checks the validity of the date formed by the arguments.
     * A date is considered valid if each parameter is properly defined.
     * works like php's built in checkdate() function.
     * Leap years are taken into consideration.
     * e.g:
     * $obj->checkdate(10, 21, 1390); // Return true
     * $obj->checkdate(9, 31, 1390);  // Return false
     *
     * You can force gregorian checkdate if system default is jalali and you
     * need to check based on gregorian date
     * $check = $obj->checkdate(12, 31, 2011, false);
     *
     * @author Omid Pilevar
     * @param $month int The month is between 1 and 12 inclusive.
     * @param $day int The day is within the allowed number of days for the given month.
     * @param $year int The year is between 1 and 32767 inclusive.
     * @param $jalali bool (Optional) pass false if you want to input gregorian time
     * @return bool
     */
    public static function checkdate($month, $day, $year, $jalali = null)
    {
        //Defaults
        $month = (intval($month) == 0) ? self::date('n') : intval($month);
        $day   = (intval($day)   == 0) ? self::date('j') : intval($day);
        $year  = (intval($year)  == 0) ? self::date('Y') : intval($year);

        //Check if its jalali date
        if ($jalali === true || ($jalali === null && self::$jalali === true)) {
            $epoch = self::mktime(0, 0, 0, $month, $day, $year, $jalali);

            if (self::date('Y-n-j', $epoch, false) == "$year-$month-$day") {
                $ret = true;
            } else {
                $ret = false;
            }
        } else { //Gregorian Date
            $ret = checkdate($month, $day, $year);
        }

        //Return
        return $ret;
    }

    /**
     * jDateTimePlus::getdate
     *
     * Like php built-in function, returns an associative array containing the date information of a timestamp, or the current local time if no timestamp is given. .
     *
     * @author Meysam Pour Ganji
     * @param $timestamp int The timestamp that would convert to date information array, if NULL passed, current timestamp will be processed.
     * @return array An associative array of information related to the timestamp. For see elements of the returned associative array see {@link http://php.net/manual/en/function.getdate.php#refsect1-function.getdate-returnvalues}.
     */
    public static function getdate($timestamp = null)
    {
        if ($timestamp === null) {
            $timestamp = time();
        }

        if (is_string($timestamp)) {
            if (ctype_digit($timestamp) || ($timestamp{0} == '-' && ctype_digit(substr($timestamp, 1)))) {
                $timestamp = (int)$timestamp;
            } else {
                $timestamp = strtotime($timestamp);
            }
        }

        $dateString = self::date("s|i|G|j|w|n|Y|z|l|F", $timestamp);
        $dateArray = explode("|", $dateString);

        $result = array(
            "seconds" => $dateArray[0],
            "minutes" => $dateArray[1],
            "hours" => $dateArray[2],
            "mday" => $dateArray[3],
            "wday" => $dateArray[4],
            "mon" => $dateArray[5],
            "year" => $dateArray[6],
            "yday" => $dateArray[7],
            "weekday" => $dateArray[8],
            "month" => $dateArray[9],
            0 => $timestamp
        );

        return $result;
    }
    /**
     * System Helpers below
     * ------------------------------------------------------
     */

    /**
     * Filters out an array
     */
    private static function filterArray($needle, $heystack, $always = array())
    {
        return array_intersect(array_merge($needle, $always), $heystack);
    }

    /**
     * Returns correct names for week days
     */
    private static function getDayNames($day, $shorten = false, $len = 1, $numeric = false)
    {
        $days = array(
            'sat' => array(1, 'شنبه'),
            'sun' => array(2, 'یکشنبه'),
            'mon' => array(3, 'دوشنبه'),
            'tue' => array(4, 'سه شنبه'),
            'wed' => array(5, 'چهارشنبه'),
            'thu' => array(6, 'پنجشنبه'),
            'fri' => array(7, 'جمعه')
        );
        $day = substr(strtolower($day), 0, 3);
        $day = $days[$day];
        return ($numeric) ? $day[0] : (($shorten) ? self::substr($day[1], 0, $len) : $day[1]);
    }
    /**
     * Returns correct names for months
     */
    private static function getMonthNames($month, $shorten = false, $len = 3)
    {
        // Convert
        $months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
        $ret    = $months[$month - 1];
        // Return
        return ($shorten) ? self::substr($ret, 0, $len) : $ret;
    }
    /**
     * Converts latin numbers to farsi script and reverse
     * @param string $matches The string to replace numbers in
     * @param string $toLang The language of input string
     * @return string
     */
    public static function convertNumbers($matches, $toLang = 'fa')
    {
        $farsi_array   = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english_array = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        if ($toLang == 'fa') {
            return str_replace($english_array, $farsi_array, $matches);
        } else {
            return str_replace($farsi_array, $english_array, $matches);
        }
    }
    /**
     * Division
     */
    private static function div($a, $b)
    {
        return (int) ($a / $b);
    }
    /**
     * Substring helper
     */
    private static function substr($str, $start, $len)
    {
        if (function_exists('mb_substr')) {
            return mb_substr($str, $start, $len, 'UTF-8');
        } else {
            return substr($str, $start, $len * 2);
        }
    }

    /**
     * Converts the given Gregorian year/month/day to Jalali
     *
     * @param $gy int Gregorian year
     * @param $gm int Gregorian month
     * @param $gd int Gregorian day
     * @return array
     */
    public static function toJalali($gy, $gm, $gd)
    {
        $g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
        $jy = ($gy <= 1600) ? 0 : 979;
        $gy -= ($gy <= 1600) ? 621 : 1600;
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = (365 * $gy) + self::div($gy2 + 3, 4) - self::div($gy2 + 99, 100) + self::div($gy2 + 399, 400) - 80 + $gd + $g_d_m[$gm-1];
        $jy += 33 * self::div($days, 12053);
        $days %= 12053;
        $jy += 4 * (self::div($days, 1461));
        $days %= 1461;
        $jy += self::div($days - 1, 365);

        if ($days > 365) {
            $days = ($days-1) % 365;
        }

        $jm = ($days < 186) ? 1 + self::div($days, 31) : 7 + self::div($days - 186, 30);
        $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) %30));

        return [$jy, $jm, $jd];
    }

    /**
     * Converts the given Jalali year/month/day to Gregorian
     *
     * @param $jy int Jalali year
     * @param $jm int Jalali month
     * @param $jd int Jalali day
     * @return array
     */
    public static function toGregorian($jy, $jm, $jd)
    {
        $gy = ($jy <= 979) ? 621 : 1600;
        $jy -= ($jy <= 979) ? 0 : 979;
        $days = (365 * $jy) + (self::div($jy, 33) * 8) + self::div(($jy%33)+3, 4) + 78 + $jd + (($jm<7) ? ($jm-1) * 31:(($jm-7)*30) + 186);
        $gy += 400 * self::div($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * self::div(--$days, 36524);
            $days %= 36524;
            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * self::div($days, 1461);
        $days %= 1461;
        $gy += self::div($days - 1, 365);

        if ($days > 365) {
            $days = ($days-1) % 365;
        }

        $gd=$days+1;

        foreach (array(0,31,(($gy%4==0 and $gy%100!=0) or ($gy % 400 == 0)) ? 29:28,31,30,31,30,31,31,30,31,30,31) as $gm => $v) {
            if ($gd <= $v) {
                break;
            }
            $gd -= $v;
        }

        return [$gy, $gm, $gd];
    }

    /**
     * Returns the current DateTime in UTC/GMT
     *
     * @return string
     */
    public static function getDateTimeUtcNow()
    {
        return gmdate('Y-m-d H:i:s');
    }

    /**
     * Converts the given UTC DateTime to the passed timezone
     *
     * @param string $utcDateTime
     * @param string $timezone
     * @param string $inputFormat
     * @param string $outputFormat
     * @return string
     */
    public static function getUTCToLocalDateTime($utcDateTime, $timezone, $inputFormat = 'Y-m-d H:i:s', $outputFormat = 'Y-m-d H:i:s')
    {
        $utc_date = DateTime::createFromFormat(
            $inputFormat,
            $utcDateTime,
            new DateTimeZone('UTC')
        );

        $acst_date = clone $utc_date; // We don't want PHP's default pass object by reference here
        $acst_date->setTimezone(new DateTimeZone($timezone));
        return  $acst_date->format($outputFormat);
    }
}
