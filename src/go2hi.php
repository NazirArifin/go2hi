<?php
namespace go2hi;

/**************************************************************************
 * GO2HI - Gregorian To Hijri Converter
 * 
 *  GO2HI is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *************************************************************************/


/**
 * go2hi::date('d-m-Y'); #---> the result is same with date() in PHP
 * go2hi::date('d-m-Y', go2hi::GO2HI_HIJRI); #-------or-------- 
 * go2hi::date('d-m-Y', 1); #---> output will be converted to hijri
 * go2hi::date('d-m-Y', 1, mktime(0,0,0,1,1,2000)); #---> hijri date of 1 Januari 2000
 *
 */

class go2hi {
    const GO2HI_GREG        = 0;
    const GO2HI_HIJRI       = 1;
    const GO2HI_ENGLISH     = 0;
    const GO2HI_INDONESIAN  = 1;

    protected static $monthA = array('\M\u\h\a\r\r\a\m','\S\af\a\r','R\ab\i\'\u\l \A\w\a\l','R\ab\i\'\u\l \Ak\h\i\r','J\u\m\a\d\i\l \A\w\a\l','J\u\m\a\d\i\l \Ak\h\i\r','R\a\j\ab','\S\y\a\'b\a\n','R\a\m\a\d\h\a\n','\S\y\a\w\a\l','\Dz\u\l Q\a\'\d\a\h','\Dz\u\l \H\i\j\j\a\h');
    protected static $acr_monthA = array( 
'\M\u\h','\S\a\f','\R\'\A\w','\R\'\A\k','\J\'\A\w','\J\'\A\k','\R\a\j','\S\y\a','\R\a\m','\S\a\w','\D\'\Q\d','\D\'\H\j');
    protected static $monthI = array('J\a\n\u\a\r\i','\F\eb\r\u\a\r\i','\M\a\r\e\t','\Ap\r\i\l','\M\e\i','J\u\n\i','J\u\l\i','\A\g\u\s\t\u\s','\S\ep\t\e\mb\e\r','\Ok\t\ob\e\r','\N\ov\e\mb\e\r','\D\e\s\e\mb\e\r');
    protected static $monthE = array('J\a\n\u\a\r\y','\F\eb\r\u\a\r\y','\M\a\r\c\h','\Ap\r\i\l','\M\a\y','J\u\n\e','J\u\l\y','\A\u\g\u\s\t','\S\ep\t\e\mb\e\r','\O\c\t\ob\e\r','\N\ov\e\mb\e\r','\D\e\c\e\mb\e\r');
    protected static $dayA = array('\A\l-\H\a\m\i\s','\A\l-J\u\m\'\a','\A\s-\S\ab\t','\A\l-\A\h\a\d','\A\l-\I\t\s\n\a\y\n\a','\A\t\s-\T\s\a\l\a\t\s\a\'','\A\l-\A\rb\a\'\a\'');
    protected static $dayI = array('K\a\m\i\s','J\u\m\a\t','\S\ab\t\u','\M\i\n\g\g\u','\S\e\n\i\n','\S\e\l\a\s\a','R\ab\u');
    protected static $dayE = array('\T\h\u\r\s\d\a\y','\F\r\i\d\a\y','\S\a\t\u\r\d\a\y','\S\u\n\d\a\y','\M\o\n\d\a\y','\T\u\e\s\d\a\y','W\e\d\n\e\s\d\a\y');
    protected static $dayJ = array('\W\a\g\e','K\l\i\w\o\n','\L\e\g\i','\P\a\h\i\n\g','\P\o\n');
    
    private static $hijri_date_correction = -1;
    
    private static $dateH, $monthH, $yearH;
    private static $dateM, $monthM, $yearM, $hourM, $minuteM, $secondM;
    private static $dchars = array('A','B','D','F','G','H','I','L','M','N','O','P','S','T','U','W','Y','Z','a','c','d','e','g','h','i','j','k','l','m','n','o','r','s','t','u','w','y','z');
    
    private static $cformat, $ctype, $clang, $time = 0, $wday;
    
    /**
     * Returning formatting date based on format, calender type, and language
     *
     * @param str $format. String date format that has same pattern like PHP date format.
     * Optional @param int $ctype. Calendar type, 0 for Gregorian, 1 for Hijri, -- see GO2HI constants.
     * Optional @param int $timestamp. Unix timestamp, if empty it will be set current time.
     * Optional @param int $lang. Language that used in result, 0 for english, 1 for indonesian
     * @return string. Formatted date format
     */
    public static function date($format, $ctype=0, $timestamp=0, $lang=0) {
        self::$cformat = (string) $format;
        $ctype = intval($ctype);
        switch($ctype) {
            case 0: case 1: self::$ctype = $ctype; break;
            default: trigger_error('Calender type doesnt match in go2hi::date()', E_USER_ERROR);
        }
        $lang = intval($lang);
        switch($lang) { 
            case 0: case 1: self::$clang = $lang; break;
            default: trigger_error('Language doesnt exists in go2hi::date()', E_USER_ERROR);
        }
        self::reset_vars();
        
        $time = intval($timestamp);
        if(!empty($timestamp)): self::$time = $time; else: self::$time = time(); endif;
        
        $wday = date('w', self::$time)+3;
        self::$wday = ($wday > 6 ? $wday-7 : $wday);
        self::calculate_date(); 
        
        //formatting and returning result
        $arrchars = implode('|', self::$dchars);
        $format = preg_replace("/\\\(?!(?:$arrchars))/", '\\\\\\' , self::$cformat);
        
        for($i = 0; $i < count(self::$dchars); $i++) {
            $format = self::replacef(self::$dchars[$i], $format);
        }
        
        return stripslashes($format);
    }
    
    protected static function calculate_date() {
        self::$dateM = date('j', self::$time);
        self::$monthM = date('n', self::$time);
        self::$yearM = date('Y', self::$time);
        self::$hourM = date('G', self::$time);
        self::$minuteM = preg_replace('/^0/', '', date('i', self::$time));
        self::$secondM = preg_replace('/^0/', '', date('s', self::$time));
        if(self::$ctype === 1) { self::calculate_hijri(); }
    }
    
    protected static function calculate_hijri($d=0, $m=0, $y=0) {
        $month = (empty($m) ? self::$monthM : $m);
        $date = (empty($d) ? self::$dateM : $d);
        $year = (empty($y) ? self::$yearM : $y);
        
        $mPart = ($month-13)/12; 
        $jd = self::int_part((1461*($year+4800+self::int_part($mPart)))/4)+self::int_part((367*($month-1-12*(self::int_part($mPart))))/12)-self::int_part((3*(self::int_part(($year+4900+self::int_part($mPart))/100)))/4)+$date-32075; 
        $l = $jd-1948440+10632; 
        $n = self::int_part(($l-1)/10631); 
        $l = $l-10631*$n+354; 
        $j = (self::int_part((10985-$l)/5316))*(self::int_part((50*$l)/17719))+(self::int_part($l/5670))*(self::int_part((43*$l)/15238)); 
        $l = $l-(self::int_part((30-$j)/15))*(self::int_part((17719*$j)/50))-(self::int_part($j/16))*(self::int_part((15238*$j)/43))+29;
        $l += self::$hijri_date_correction;
        
        $monthR = self::int_part((24*$l)/709);
        $dateR = $l-self::int_part((709*$monthR)/24);
        $yearR = 30*$n+$j-30;
        $monthR -= 1;
        
        if(empty($d) && empty($m) && empty($y)) {
            self::$dateH = $dateR;
            self::$monthH = $monthR;
            self::$yearH = $yearR;
        } else {
            return $dateR.'-'.$monthR.'-'.$yearR;
        }
    }
    
    protected static function esc_char($text) {
        $arrchars = implode('|',self::$dchars);
        return preg_replace("/($arrchars)/",'\\\\\1',$text);
    }
    
    protected static function hijri_month_num_days($dateH=0, $time=0) {
        if(empty($dateH)) { $dateH = self::$dateH; }
        if(empty($time)) { $time = self::$time; }
        
        $m_date = $time + ((29-$dateH)*24*60*60);
        list($d, $m, $y) = explode('-', date('j-n-Y', $m_date));
        list($date_h) = explode('-', $h_date = self::calculate_hijri($d, $m, $y));
        return ($date_h == 29 ? 30 : 29);
    }
    
    protected static function hijri_num_days() {
        //hari ini
        if(self::$dateH <= 15) {
            $dist = (15-self::$dateH)*24*60*60;
        } else {
            $dist = 0;
        }
        
        $mktime = date('U', self::$time+$dist);
        for($i = self::$monthH; $i >= 1; $i--) {
            
        }
    }
    
    protected static function int_part($float) {
        return ($float<-0.0000001 ? ceil($float-0.0000001) : floor($float+0.0000001));
    }
    
    protected static function replacef($char, $format) {
        $tda = '';
        switch($char) {
            case 'k': // (Nama hari Jawa) What if PHP use this char??
                # naikkan jam menjadi jam 12
                $hrj = (mktime(12, 0, 0, self::$monthM, self::$dateM, self::$yearM)/60/60/24)%5;
                return preg_replace('/(?<!\\\)k/U', self::$dayJ[$hrj], $format);
            case 'd': // (Tanggal dengan leading zero)
                if(self::isHijri()) {
                    $tda = (strlen(self::$dateH) < 2 ? '0'.self::$dateH : self::$dateH);
                } else {
                    $tda = (strlen(self::$dateM) < 2 ? '0'.self::$dateM : self::$dateM);
                }
                return preg_replace('/(?<!\\\)d/U', $tda, $format);
            case 'D': // (Singkatan nama hari)
                if(self::isHijri()) {
                    $c = explode('-', self::$dayA[self::$wday]);
					$tda = end($c);
                } else {
                    //take 3 first char
                    if(!self::isEnglish()) {
                        $tda = self::esc_char(substr(stripslashes(self::$dayI[self::$wday]), 0, 3));
                    } else {
                        $tda = self::esc_char(substr(stripslashes(self::$dayE[self::$wday]), 0, 3));
                    }
                }
                return preg_replace('/(?<!\\\)D/U', $tda, $format);
            case 'j': // (Tangggal tanpa leading zero)
                return (self::isHijri() ? preg_replace('/(?<!\\\)j/U', self::$dateH, $format) : preg_replace('/(?<!\\\)j/U', self::$dateM, $format));
            case 'l': // (Nama hari penuh)
                if(self::isHijri()) {
                    $tda = self::$dayA[self::$wday];
                } else {
                    if(!self::isEnglish()) {
                        $tda = self::$dayI[self::$wday];
                    } else {
                        $tda = self::$dayE[self::$wday];
                    }
                }
                return preg_replace('/(?<!\\\)l/U', $tda, $format);
            case 'S': // (Suffix tanggal dalam bahasa Inggris)
                if(!self::isEnglish() && !self::isHijri()) {
                    return preg_replace('/(?<!\\\)S/U', '', $format);
                } else {
                    return preg_replace('/(?<!\\\)S/U', self::esc_char(date('S', self::$time)), $format);
                }
            case 'z': // (Angka hari dalam satu tahun, dimulai 0)
                if(self::isHijri()) {
                    return preg_replace('/(?<!\\\)z/U', self::hijri_num_days(), $format);
                } else {
                    return preg_replace('/(?<!\\\)z/U', date('z', self::$time), $format);
                }
            case 'W': // (Angka minggu dalam satu tahun)
                if(self::isHijri()) {
                    /*___________________________HOW??? ____________________*/
                    return $format;
                    
                } else {
                    return preg_replace('/(?<!\\\)W/U', date('W', self::$time), $format);
                }
            case 'F': // (Nama bulan lengkap)
                if(self::isHijri()) {
                    $tda = self::$monthA[self::$monthH-1];
                } else {
                    if(self::isEnglish()) {
                        $tda = self::$monthE[self::$monthM-1];
                    } else {
                        $tda = self::$monthI[self::$monthM-1];
                    }
                }
                return preg_replace('/(?<!\\\)F/U', self::esc_char($tda), $format);
            case 'm': // (Angka bulan dengan leading zero)
                if(self::isHijri()) {
                    $tda = (strlen(self::$monthH) < 2 ? '0'.self::$monthH : self::$monthH);
                } else {
                    $tda = (strlen(self::$monthM) < 2 ? '0'.self::$monthM : self::$monthM);
                }
                return preg_replace('/(?<!\\\)m/U', $tda, $format);
            case 'M': // (Nama bulan singkat)
                if(self::isHijri()) {
                    $tda = self::$acr_monthA[self::$monthH-1];
                } else {
                    if(self::isEnglish()) {
                        $tda = self::esc_char(substr(stripslashes(self::$monthE[self::$monthM-1]), 0, 3));
                    } else {
                        $tda = self::esc_char(substr(stripslashes(self::$monthI[self::$monthM-1]), 0, 3));
                    }
                }
                $format = preg_replace('/(?<!\\\)M/U', $tda, $format);
            case 'n': // (Angka bulan tanpa leading zero)
                return preg_replace('/(?<!\\\)n/U', (self::isHijri() ? self::$monthH : self::$monthM), $format);
            case 't': // (Jumlah hari pada bulan tersebut)
                if(self::isHijri()) {
                    return preg_replace('/(?<!\\\)t/U', self::hijri_month_num_days(), $format);
                } else {
                    return preg_replace('/(?<!\\\)t/U', date('t', self::$time), $format);
                }
            case 'L': // (Tahun kabisat 1 jika iya, 0 jika bukan)
                if(self::isHijri()) {
                    //adakah tahun kabisat di hijri?
                    preg_replace('/(?<!\\\)L/U', 0, $format);
                } else {
                    return preg_replace('/(?<!\\\)L/U', date('L', self::$time), $format);
                }
            case 'o': // (Sama dengan Y tapi ISO-8601)
                return $format;
            case 'Y': // (Angka tahun empat digit)
                return preg_replace('/(?<!\\\)Y/U', (self::isHijri() ? self::$yearH : self::$yearM), $format);
            case 'y': // (Angka tahun dua digit)
                return preg_replace('/(?<!\\\)y/U', substr(self::isHijri() ? self::$yearH : self::$yearM, -2), $format);
            /**
             * Next chars is same with PHP date() function
             */
            case 'N': // (Angka hari)
                return preg_replace('/(?<!\\\)N/U', date('N', self::$time), $format);
            case 'w': // (Angka hari)
                return preg_replace('/(?<!\\\)w/U', date('w' ,self::$time), $format);
            case 'a': // (Penunjuk am atau pm)
                return preg_replace('/(?<!\\\)a/U', self::esc_char(date('a', self::$time)), $format);
            case 'A': // (Penunjuk am atau pm UPPERCASE)
                return preg_replace('/(?<!\\\)A/U', self::esc_char(date('A', self::$time)), $format);
            case 'B': // (Swatch Internet)
                return preg_replace('/(?<!\\\)B/U', date('B', self::$time), $format);
            case 'g': // (Angka jam mode 12 jam tanpa leading zero)
                return preg_replace('/(?<!\\\)g/U', date('g', self::$time), $format);
            case 'G': // (Angka jam mode 24 jam tanpa dengan zero)
                return preg_replace('/(?<!\\\)G/U', date('G', self::$time), $format);
            case 'h': // (Angka jam mode 12 jam dengan leading zero)
                return preg_replace('/(?<!\\\)h/U', date('h', self::$time), $format);
            case 'H': // (Angka jam mode 24 jam dengan leading zero)
                return preg_replace('/(?<!\\\)H/U', date('H', self::$time), $format);
            case 'i': // (Angka menit dengan leading zero)
                return preg_replace('/(?<!\\\)i/U', date('i', self::$time), $format);
            case 's': // (Angka detik dengan leading zero)
                return preg_replace('/(?<!\\\)s/U', date('s', self::$time), $format);
            case 'u': //(Miliseconds)
                return preg_replace('/(?<!\\\)u/U', date('u', self::$time), $format);
            case 'e': // (Timezone Identifier)
                return preg_replace('/(?<!\\\)e/U', self::esc_char(date('e', self::$time)), $format);
            case 'I': // (Daylight Saving, 1 jika iya, 0 jika tidak)
                return preg_replace('/(?<!\\\)I/U', date('I', self::$time), $format);
            case 'O': // (Beda dengan GMT dalam jam, +0200)
                return preg_replace('/(?<!\\\)O/U', date('O', self::$time), $format);
            case 'P': // (Beda dengan GMT dalam jam ditambah tanda :, +02:00)
                return preg_replace('/(?<!\\\)P/U', date('P', self::$time), $format);
            case 'T': // (Singkatan Waktu, EST, MDT, dsb)
                return preg_replace('/(?<!\\\)T/U', self::esc_char(date('T', self::$time)), $format);
            case 'Z': // (Beda zona waktu UTC dalam detik)
                return preg_replace('/(?<!\\\)Z/U', date('Z', self::$time), $format);
            case 'c': // (Tanggal ISO 8601 dan W3C, 2004-02-12T15:19:21+00:00)
                return preg_replace('/(?<!\\\)c/U', self::esc_char(date('c', self::$time)), $format);
            case 'r': // (Tanggal RFC 2822, Thu, 21 Dec 2000 16:01:07 +0200)
                return preg_replace('/(?<!\\\)r/U', self::esc_char(date('r', self::$time)), $format);
            case 'U': // (Detik dari Unix Epoch)
                return preg_replace('/(?<!\\\)U/U', self::$time, $format);  
            default:
                return $format;
        }
    }
    
    private static function isEnglish() {
        return (self::$clang == 0 ? true : false);
    }
    
    private static function isHijri() {
        return (self::$ctype == 1 ? true : false);
    }
    
    private static function reset_vars() {
        self::$dateH = self::$monthH = self::$yearH = null;
        self::$dateM = self::$monthM = self::$yearM = self::$hourM = self::$minuteM = self::$secondM = null;
    }
}
