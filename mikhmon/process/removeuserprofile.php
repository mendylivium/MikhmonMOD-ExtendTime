<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);

function dateTimeConverter($inputs) {
    // here you can customize how the function interprets input data and how it should return the result
    // example : you can add "y"    => "year"
    //                       "s"    => "seconds"
    //                       "u"    => "microsecond"
    // key, can be also a string
    // example                "us"  => "microsecond"
    $dateTimeIndex  = array("d" => "day",
                               "h" => "hour",
                               "m" => "minutes");

    $pattern        = "#(([0-9]+)([a-z]+))#";
    $r              = preg_match_all($pattern, $inputs, $matches);
    if ($r === FALSE) {
        throw new Exception("can not parse input data");
    }
    if (count($matches) != 4) {
        throw new Exception("something wrong with input data");
    }
    $datei      = $matches[2]; // contains number
    $dates      = $matches[3]; // contains char or string
    $result    = array();
    for ($i=0 ; $i<count ($dates) ; $i++) {
        if(!array_key_exists($dates[$i], $dateTimeIndex)) {
            throw new Exception ("dateTimeIndex is not configured properly, please add this index : [" . $dates[$i] . "]");
        }
        $result[$dateTimeIndex[$dates[$i]]] = (int)$datei[$i];
    }
    return $result;
}

$pid = $removeuserprofile;
$pname = $_GET['pname'];

$getmonid = $API->comm("/system/scheduler/print", array(
    "?name" => "$pname",
));
$monid = $getmonid[0]['.id'];

$API->comm("/ip/hotspot/user/profile/remove", array(
    ".id" => "$pid",
));
$API->comm("/system/scheduler/remove", array(
    ".id" => "$monid",
));

$getprofiles = $API->comm("/ip/hotspot/user/profile/print");
$arr_rates = array();
for($i=1;$i<count($getprofiles);$i++) {
	
	$arr_rates[$i-1]['rate_name'] = $currency;
	
	$arr_rates[$i-1]['rate'] = explode($currency,explode("-",$getprofiles[$i]['name'])[1])[0]	;
	
	$time_arr = dateTimeConverter(explode("-",$getprofiles[$i]['name'])[0]);
	$arr_rates[$i-1]['time'] = ($time_arr['day'] ? $time_arr['day'] . ' Day' . ($time_arr['day'] > 1 ? 's' : '') : '') . ($time_arr['hour'] ? $time_arr['hour'] . ' Hour' . ($time_arr['hour'] > 1 ? 's' : '') : '') . ($time_arr['minutes'] ? $time_arr['minutes'] . ' Minute' . ($time_arr['minutes'] > 1 ? 's' : '') : '');
}	
$API->comm("/file/print",array("file"=>"hotspot/rates"));
$API->comm("/file/set",array("numbers"=>"hotspot/rates.txt","contents"=>"var rates = " . json_encode($arr_rates,false) . ";"));
echo "<script>window.location='./?hotspot=user-profiles&session=" . $session . "'</script>";
?>