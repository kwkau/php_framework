<?php

class date_time {

    private $date;
    private $time;
    
    function __construct() {
        
    }

    /**
     * this is a function to format raw datetime values produce a more user friendly date
     * with the name of the months present and a 12 hour time format
     * @param $datetime string the raw datetime string value
     * @return string a formatted date with the name of the month and 12 hour time format
     */
    public function format($datetime) {
        list($this->date, $this->time) = explode(' ', $datetime);
        $ymd = explode('-', $this->date);
        switch ($ymd[1]) {
            case 1:
                $month = 'January';
                break;
            case 2:
                $month = 'February';
                break;
            case 3:
                $month = 'March';
                break;
            case 4:
                $month = 'April';
                break;
            case 5:
                $month = 'May';
                break;
            case 6:
                $month = 'June';
                break;
            case 7:
                $month = 'Jully';
                break;
            case 8:
                $month = 'August';
                break;
            case 9:
                $month = 'September';
                break;
            case 10:
                $month = 'October';
                break;
            case 11:
                $month = 'November';
                break;
            case 12:
                $month = 'Decmber';
                break;
            default:
                $month = 'no month';
                break;
        }
        
        $this->date = $ymd[2] . '-' . $month . '-' . $ymd[0];
        $times = explode(':', $this->time);
        if ($times[0] > 12) {
            $times[0] = ($times[0] % 12);
            $this->time = $times[0].':'.$times[1].'pm';
        }
        elseif($times[0] == 0){
            $this->time = '12'.':'.$times[1].'am';
        }elseif($times[0] <= 12){
            $this->time = $times[0].':'.$times[1].'am';
        }
        $fulldate = $this->time.' '.  $this->date;
        return $fulldate;
    }
    
public function time_lnth($latter,$current){
    list($date,$time) = explode(' ',$latter);
    echo $date.$time;
}


    /**
     * "date.timezone"
     * @param $timezone
     * @return string
     */
    public  function get_date($timezone) {
        $tz = ini_get($timezone);
        $dtz = new DateTimeZone($tz);
        $dt = new DateTime("now", $dtz);
        return $dt->format("Y-m-d H:i:s.u");
    }
    
    
}

