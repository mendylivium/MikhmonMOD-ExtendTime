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

error_reporting(0);

ob_start("ob_gzhandler");

if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
  
  date_default_timezone_set($_SESSION['timezone']);
  
// load session MikroTik
  $session = $_GET['session'];

// load config
  include('../include/config.php');
  include('../include/readcfg.php');

  include('../lib/formatbytesbites.php');

  $id = $_GET['id'];
  $qr = $_GET['qr'];
  $small = $_GET['small'];
  $userp = $_GET['user'];

  require('../lib/routeros_api.class.php');
  $API = new RouterosAPI();
  $API->debug = false;
  $API->connect($iphost, $userhost, decrypt($passwdhost));

  

  if ($userp != "") {
    $usermode = explode('-', $userp)[0];
    $pulluser = explode('-', $userp);
    $iuser = count($pulluser);
    $prefix = explode('-', $userp)[$iuser - 2];
    $user = explode('-', $userp)[$iuser - 1];
    if ($iuser == 3) {
      $user = $prefix . "-" . $user;
    } else {
      $user = $user;
    }
    $getuser = $API->comm("/ip/hotspot/user/print", array("?name" => "$user"));
    $TotalReg = count($getuser);
  } elseif ($id != "") {
    $usermode = explode('-', $id)[0];
    $getuser = $API->comm('/ip/hotspot/user/print', array("?comment" => "$id", "?uptime" => "0s"));
    $TotalReg = count($getuser);
  }
  $getuprofile = $getuser[0]['profile'];


  $getprofile = $API->comm("/ip/hotspot/user/profile/print", array("?name" => "$getuprofile"));
  $getsharedu = $getprofile[0]['shared-users'];
  $ponlogin = $getprofile[0]['on-login'];
  $validity = explode(",", $ponlogin)[3];
  $getprice = explode(",", $ponlogin)[2];
  $getsprice = explode(",", $ponlogin)[4];

 
  
    if($getsprice == "0" && $getprice != "0"){
      if ($currency == in_array($currency, $cekindo['indo'])) {
        $price = $currency . " " . number_format($getprice, 0, ",", ".");
      } else {
        $price = $currency . " " . number_format($getprice, 2);
      }
    }else if($getsprice != "0"){
      if ($currency == in_array($currency, $cekindo['indo'])) {
        $price = $currency . " " . number_format($getsprice, 0, ",", ".");
      } else {
        $price = $currency . " " . number_format($getsprice, 2);
      }
    }else if ($getsprice == "0") {
      $price = "";
    }

    
  

  $logo = "../img/logo-" . $session . ".png";
  if (file_exists($logo)) {
    $logo = "../img/logo-" . $session . ".png?t=". str_replace(" ","_",date("Y-m-d H:i:s"));
  } else {
    $logo = "../img/logo.png?t=". str_replace(" ","_",date("Y-m-d H:i:s"));
  }

}

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

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Voucher-<?= $hotspotname . "-" . $getuprofile . "-" . $id; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="pragma" content="no-cache" />
		<link rel="icon" href="../img/favicon.png" />
		<script src="../js/qrious.min.js"></script>
		<style>
body {
  color: #000000;
  background-color: #FFFFFF;
  font-size: 14px;
  font-family:  'Helvetica', arial, sans-serif;
  margin-top: 30px;
}
		</style>
	</head>
	<body onunload="self.opener.location.reload();">

<?php for ($i = 0; $i < $TotalReg; $i++) {;
  $regtable = $getuser[$i];
  $uid = str_replace("=","",base64_encode($regtable['.id']));
  $idqr = str_replace("=","",base64_encode(($regtable['.id']."qr")));
  $username = $regtable['name'];
  $password = $regtable['password'];
  $profile = $regtable['profile'];
  $timelimit = $regtable['limit-uptime'];
  $getdatalimit = $regtable['limit-bytes-total'];
  $comment = $regtable['comment'];
  if ($getdatalimit == 0) {
    $datalimit = "";
  } else {
    $datalimit = formatBytes($getdatalimit, 2);
  }
  
  $urilogin = "http://$dnsname/login?username=$username&password=$password";
  $qrcode = "
	<canvas class='qrcode' id='".$uid."'></canvas>
    <script>
      (function() {
        var ".$uid." = new QRious({
          element: document.getElementById('".$uid."'),
          value: '".$urilogin."',
          size:'256'
        });

      })();
    </script>
	";
 
  $num = $i + 1;
  ?>
  
  
 <?php
 /*
 $time = $timelimit;

$result = array_combine(
    ['hour', 'minutes'],
    sscanf($time, '%dh%dm')
);

print_r($result);
 */
 
$time_arr = dateTimeConverter($timelimit);
$valid_arr = dateTimeConverter($validity);
 ?>
 <center>
<table style="border:1px solid;max-width:256px;">
	<tr>
		<td align="center">
			Welcome to <?= $hotspotname; ?>
		</td>
	</tr>
	<tr>
		<td>
			<?= $qrcode; ?><br/>
			<center>Scan QR Code to Connect<br/>
			<br/>
			<small>Or visit this link <span style="color:blue;">http://<?= $dnsname; ?></span> and connect with this code:<br/><br/></small>
			<b style="font-size: 20px;"><?= $username; ?></b>
			<br/><br/>
			You must pay <?= $price; ?> first to the teller<br/></br><hr style="border-top: dashed 1px;"/>
			<b>Time Limit</b>: <?= $time_arr['day'] ? $time_arr['day'] . ' Day' . ($time_arr['day'] > 1 ? 's' : '') : ''?> <?= $time_arr['hour'] ? $time_arr['hour'] . ' Hour' . ($time_arr['hour'] > 1 ? 's' : '') : ''?> <?= $time_arr['minutes'] ? $time_arr['minutes'] . ' Minute' . ($time_arr['minutes'] > 1 ? 's' : '') : ''?><br/>
			<b>Validity</b>: <?= $valid_arr['day'] ? $valid_arr['day'] . ' Day' . ($valid_arr['day'] > 1 ? 's' : '') : ''?> <?= $valid_arr['hour'] ? $valid_arr['hour'] . ' Hour' . ($valid_arr['hour'] > 1 ? 's' : '') : ''?> <?= $valid_arr['minutes'] ? $valid_arr['minutes'] . ' Minute' . ($valid_arr['minutes'] > 1 ? 's' : '') : ''?>
			<hr style="border-top: dashed 1px;"/>
			<sup>Printer Less Voucher by Mendy</sup>
			</center>
		</td>
	</tr>
</table>
  <center>
<?php
/*include('./template-small.php');
if ($userp != "") {
  include('./template-thermal.php');
} else {
  if ($small == "yes") {
    include('./template-small.php');
  } else {
    include('./template.php');
  }
}
*/ ?>
<?php 
} ?>

	
</body>
</html>
