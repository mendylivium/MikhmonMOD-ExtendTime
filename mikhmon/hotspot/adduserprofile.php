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
//init_set('display_errors',1);
error_reporting(E_ALL);

include('../flash.php');

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

if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {

  $getallqueue = $API->comm("/queue/simple/print", array(
    "?dynamic" => "false",
  ));
  

  $getpool = $API->comm("/ip/pool/print");

  if (isset($_POST['name'])) {
    $name = (preg_replace('/\s+/', '-',$_POST['name']));
    $sharedusers = ($_POST['sharedusers']);
    $ratelimit = ($_POST['ratelimit']);
    $expmode = ($_POST['expmode']);
    $validity = ($_POST['validity']);
    $graceperiod = ($_POST['graceperiod']);
    $getprice = ($_POST['price']);
    $getsprice = ($_POST['sprice']);
    $addrpool = ($_POST['ppool']);
    if ($getprice == "") {
      $price = "0";
    } else {
      $price = $getprice;
    }
    if ($getsprice == "") {
      $sprice = "0";
    } else {
      $sprice = $getsprice;
    }
    $getlock = ($_POST['lockunlock']);
    if ($getlock == 'Enable') {
      $lock = '; [:local mac $"mac-address"; /ip hotspot user set mac-address=$mac [find where name=$user]]';
    } else {
      $lock = "";
    }

	
    $randstarttime = "0".rand(1,5).":".rand(10,59).":".rand(10,59);
    $randinterval = "00:02:".rand(10,59);

    $parent = ($_POST['parent']);
    
    $record = '; :local mac $"mac-address"; :local time [/system clock get time ]; /system script add name="$date-|-$time-|-$user-|-'.$price.'-|-$address-|-$mac-|-' . $validity . '-|-'.$name.'-|-$comment" owner="$month$year" source=$date comment=mikhmon';
    
    $onlogin = ':put (",'.$expmode.',' . $price . ',' . $validity . ','.$sprice.',,' . $getlock . ',"); {:local date [ /system clock get date ];:local year [ :pick $date 7 11 ];:local month [ :pick $date 0 3 ];:local comment [ /ip hotspot user get [/ip hotspot user find where name="$user"] comment]; :local ucode [:pic $comment 0 2]; :if ($ucode = "vc" or $ucode = "up" or $comment = "") do={ /sys sch add name="$user" disable=no start-date=$date interval="' . $validity . '"; :delay 2s; :local exp [ /sys sch get [ /sys sch find where name="$user" ] next-run]; :local getxp [len $exp]; :if ($getxp = 15) do={ :local d [:pic $exp 0 6]; :local t [:pic $exp 7 16]; :local s ("/"); :local exp ("$d$s$year $t"); /ip hotspot user set comment=$exp [find where name="$user"];}; :if ($getxp = 8) do={ /ip hotspot user set comment="$date $exp" [find where name="$user"];}; :if ($getxp > 15) do={ /ip hotspot user set comment=$exp [find where name="$user"];}; /sys sch remove [find where name="$user"];';
    

    if ($expmode == "rem") {
      $onlogin = $onlogin . $lock . "}}";
      $mode = "remove";
    } elseif ($expmode == "ntf") {
      $onlogin = $onlogin . $lock . "}}";
      $mode = "set limit-uptime=1s";
    } elseif ($expmode == "remc") {
      $onlogin = $onlogin . $record . $lock . "}}";
      $mode = "remove";
    } elseif ($expmode == "ntfc") {
      $onlogin = $onlogin . $record . $lock . "}}";
      $mode = "set limit-uptime=1s";
    } elseif ($expmode == "0" && $price != "") {
      $onlogin = ':put (",,' . $price . ',,,noexp,' . $getlock . ',")' . $lock;
    } else {
      $onlogin = "";
    }

	$onlogin .= '; :local usern $user; :local macadd $"mac-address"; :local voucherUs [:toarray ""]; :local voucherCookies [/ip hotspot cookie find mac-address=$macadd and user!=$usern]; :foreach voucherCookie in=$voucherCookies do={ :local voucherCookieU [/ip hotspot cookie get $voucherCookie user]; :local p [:find $voucherUs $voucherCookieU]; :if ([:type $p] = "nil") do={ :set $voucherUs ( $voucherUs, $voucherCookieU ); :local timeUsed [/ip hotspot user get [/ip hotspot user find where name=$voucherCookieU] uptime]; :local timeBought [/ip hotspot user get [/ip hotspot user find where name=$voucherCookieU] limit-uptime]; :local timeClaim ($timeBought - $timeUsed); :log info message=$timeClaim; :local currTime [/ip hotspot user get [/ip hotspot user find where name=$usern] limit-uptime]; :set currTime ($currTime + $timeClaim); [/ip hotspot user set limit-uptime=$currTime [find where name=$usern]]; [/ip hotspot active remove [find where user=$usern] ]; [/ip hotspot user remove [find where name=$voucherCookieU] ]; [/ip hotspot active remove [find where user=$voucherCookieU] ]; [/ip hotspot cookie remove [find where user=$voucherCookieU] ]; }; };';
	
    $bgservice = ':local dateint do={:local montharray ( "jan","feb","mar","apr","may","jun","jul","aug","sep","oct","nov","dec" );:local days [ :pick $d 4 6 ];:local month [ :pick $d 0 3 ];:local year [ :pick $d 7 11 ];:local monthint ([ :find $montharray $month]);:local month ($monthint + 1);:if ( [len $month] = 1) do={:local zero ("0");:return [:tonum ("$year$zero$month$days")];} else={:return [:tonum ("$year$month$days")];}}; :local timeint do={ :local hours [ :pick $t 0 2 ]; :local minutes [ :pick $t 3 5 ]; :return ($hours * 60 + $minutes) ; }; :local date [ /system clock get date ]; :local time [ /system clock get time ]; :local today [$dateint d=$date] ; :local curtime [$timeint t=$time] ; :foreach i in [ /ip hotspot user find where profile="'.$name.'" ] do={ :local comment [ /ip hotspot user get $i comment]; :local name [ /ip hotspot user get $i name]; :local gettime [:pic $comment 12 20]; :if ([:pic $comment 3] = "/" and [:pic $comment 6] = "/") do={:local expd [$dateint d=$comment] ; :local expt [$timeint t=$gettime] ; :if (($expd < $today and $expt < $curtime) or ($expd < $today and $expt > $curtime) or ($expd = $today and $expt < $curtime)) do={ [ /ip hotspot user '.$mode.' $i ]; [ /ip hotspot active remove [find where user=$name] ];}}}';
	
	
    $API->comm("/ip/hotspot/user/profile/add", array(
			  		  /*"add-mac-cookie" => "yes",*/
      "name" => "$name",
      "address-pool" => "$addrpool",
      "rate-limit" => "$ratelimit",
      "shared-users" => "$sharedusers",
      "status-autorefresh" => "1m",
      "transparent-proxy" => "yes",
      "on-login" => "$onlogin",
      "parent-queue" => "$parent",
    ));

    if($expmode != "0"){
      if (empty($monid)){
        $API->comm("/system/scheduler/add", array(
          "name" => "$name",
          "start-time" => "$randstarttime",
          "interval" => "$randinterval",
          "on-event" => "$bgservice",
          "disabled" => "no",
          "comment" => "Monitor Profile $name",
          ));
      }else{
      $API->comm("/system/scheduler/set", array(
        ".id" => "$monid",
        "name" => "$name",
        "start-time" => "$randstarttime",
        "interval" => "$randinterval",
        "on-event" => "$bgservice",
        "disabled" => "no",
        "comment" => "Monitor Profile $name",
        ));
      }}else{
        $API->comm("/system/scheduler/remove", array(
          ".id" => "$monid"));
      }

    $getprofile = $API->comm("/ip/hotspot/user/profile/print", array(
      "?name" => "$name",
    ));
	
	$getprofiles = $API->comm("/ip/hotspot/user/profile/print");
	$arr_rates = array();
	for($i=1;$i<count($getprofiles);$i++) {
		
		$arr_rates[$i-1]['rate_name'] = $currency;
		
		$arr_rates[$i-1]['rate'] = explode($currency,explode("-",$getprofiles[$i]['name'])[1])[0]	;
		
		$time_arr = dateTimeConverter(explode("-",$getprofiles[$i]['name'])[0]);
		$arr_rates[$i-1]['time'] = ($time_arr['day'] ? $time_arr['day'] . ' Day' . ($time_arr['day'] > 1 ? 's' : '') : '') . ($time_arr['hour'] ? $time_arr['hour'] . ' Hour' . ($time_arr['hour'] > 1 ? 's' : '') : '') . ($time_arr['minutes'] ? $time_arr['minutes'] . ' Minute' . ($time_arr['minutes'] > 1 ? 's' : '') : '');
	}	
	$API->comm("/file/print",array("file"=>($has_flash ? 'flash/' : '') . "hotspot/rates"));
	$API->comm("/file/set",array("numbers"=>($has_flash ? 'flash/' : '') . "hotspot/rates.txt","contents"=>"var rates = " . json_encode($arr_rates,false) . ";"));
	
	//echo json_encode($arr_rates,false);
	
    $pid = $getprofile[0]['.id'];
    echo "<script>window.location='./?user-profile=" . $pid . "&session=" . $session . "'</script>";
  }
   
   //var_dump($API->comm("/ip/hotspot/cookie/print",array("?mac-address"=>"88:46:04:52:BD:C0")));
   

}
?>
<div class="row">
<div class="col-8">
<div class="card box-bordered">
  <div class="card-header">
    <h3><i class="fa fa-plus"></i> Add Voucher Profile <small id="loader" style="display: none;" ><i><i class='fa fa-circle-o-notch fa-spin'></i> Processing... </i></small></h3>
  </div>
  <div class="card-body">
<form autocomplete="off" method="post" action="" onsubmit="return subm();">
  <div>
    <a class="btn bg-warning" href="./?hotspot=user-profiles&session=<?= $session; ?>"> <i class="fa fa-close btn-mrg"></i> <?= $_close ?></a>
    <button type="submit" name="save" class="btn bg-primary btn-mrg" ><i class="fa fa-save btn-mrg"></i> <?= $_save ?></button>
  </div>
<table class="table">
	<tr>
		<td colspan="2">
			<hr/>
			<center>
				Basic
			</center>
		</td>
	</tr>
  <tr>
	<input type="hidden" name="name" value=""/>
  </tr>  
  
  <tr>
    <td class="align-middle">Bandwidth (Internet Speed)</td>
	<td>
	
		<div class="row">
			<div class="col-6">
				<table   style="width:100%;">
					<tr>
						<td style="width:30%;"><center>Upload</center></td>
						<td style="width:70%;">
							<div class="input-group">
								<div class="input-group-9 col-box-8">
									<input class="group-item group-item-l" type="number" min="0" max="9999" id="speed_up" value="512">
								</div>
								<div class="input-group-3 col-box-4">
								  <select style="padding:4.2px;" class="group-item group-item-r" id="speed_up_post" required="1">
											<option selected="selected" value="k">kb</option>
											<option value="m">mb</option>
										</select>
								</div>						
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div class="col-6">
				<table   style="width:100%;">
					<tr>
						<td style="width:30%;"><center>Download</center></td>
						<td style="width:70%;">
							<div class="input-group">
								<div class="input-group-9 col-box-8">
									<input class="group-item group-item-l" type="number" min="0" max="9999" id="speed_down" value="1">
								</div>
								<div class="input-group-3 col-box-4">
								  <select style="padding:4.2px;" class="group-item group-item-r" id="speed_down_post" required="1">
									<option value="k">kb</option>
									<option selected="selected" value="m">mb</option>
								</select>
								</div>						
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
	  
    </td>
  </tr>
  
  <tr style="display:none;">
    <td class="align-middle">Bandwith [upload/download]</td><td><input class="form-control" type="text" name="ratelimit" autocomplete="off" value="512k/1m" placeholder="Example : 512k/1M" ></td>
  </tr>
  <tr>
    <td class="align-middle">Time limit</td>
	<td>
		<div class="row">
			<div class="col-6">
				<table align="left" style="width:100%;">
					<tr>
						<td style="width:30%;"><center>Hour</center></td>
						<td style="width:70%;"><input class="form-control" type="number" id="timelimh" autocomplete="off" value="0"/></td>
						
					</tr>
				</table>
			</div>
			<div class="col-6">
				<table align="left"  style="width:100%;">
					<tr>
						<td style="width:30%;"><center>Minutes</center></td>
						<td style="width:70%;"><input class="form-control" type="number" id="timelimm" autocomplete="off" value="0"/></td>
						
					</tr>
				</table>
			</div>
		</div>
	</td>
	<input type="hidden" name="expmode" value="remc"/>
  </tr>
  <?php /*
  <tr>
    <td class="align-middle"><?= $_expired_mode ?></td><td>
      <select class="form-control" onchange="RequiredV();" id="expmode" name="expmode" required="1">
        <option value="">Select...</option>
        <option value="0">None</option>
        <option value="rem">Remove</option>
        <option value="ntf">Notice</option>
        <option value="remc">Remove & Record</option>
        <option value="ntfc">Notice & Record</option>
      </select>
    </td>
  </tr>
  */ ?>
  <tr id="graceperiod" style="display:none;">
    <td class="align-middle"><?= $_grace_period ?></td><td><input class="form-control" type="text" id="gracepi" size="4" autocomplete="off" name="graceperiod" placeholder="5m" value="5m" required="1"></td>
  </tr>
  <tr>
    <td class="align-middle"><?= $_price.' '.$currency; ?></td><td><input class="form-control" type="number" size="10" min="0" name="price" value="" ></td>
	<input type="hidden" name="sprice" />
  </tr>
  
  

<tr>
	<td colspan="2">
		<hr/>
		<center>Advance<br/>
		<small><i>Magtanung muna kung di alam ang gagawin</i></small><center><br/>
	</td>
</tr>
  
  <tr>
    <td class="align-middle">Piso Wifi Address Pool</td>
    <td>
    <select class="form-control " name="ppool">
      <option id="pool-PisoWifi">none</option>
        <?php $TotalReg = count($getpool);
        for ($i = 0; $i < $TotalReg; $i++) {
		
		  if($getpool[$i]['name']=="pool-PisoWifi") {
			echo '<option selected="selected">' . $getpool[$i]['name'] . "</option>";
		  } else {
			echo "<option>" . $getpool[$i]['name'] . "</option>";
		  }
		  
        }
		
	
        ?>
    </select>
    </td>
  </tr>
  <tr>
    <td class="align-middle">Parent Queue</td>
    <td>
    <select class="form-control " name="parent">
      <option>none</option>
        <?php $TotalReg = count($getallqueue);
        for ($i = 0; $i < $TotalReg; $i++) {
			
			if($getallqueue[$i]['name']=="PisoWifi") {
				echo '<option selected="selected">' . $getallqueue[$i]['name'] . "</option>";
			} else {
				echo "<option>" . $getallqueue[$i]['name'] . "</option>";
			}
		}
        ?>
    </select>
  </td>
  </tr>
  <tr>
    <td class="align-middle">Shared Users</td><td><input class="form-control" type="text" size="4" autocomplete="off" name="sharedusers" value="1" required="1" readonly></td>
  </tr>
  <tr id="validity">
    <td class="align-middle"><?= $_validity ?></td><?php /* <input class="form-control" type="text" id="validi" size="4" autocomplete="off" name="validity" value="1d" required="1" readonly> */ ?>
		<td>
		<input type="hidden" name="validity" />
		<div class="row">
		<div class="col-4">
			<table align="left" style="width:100%;">
				<tr>
					<td style="width:30%;"><center>Day</center></td>
					<td style="width:70%;"><input class="form-control" type="number" id="validtimd" autocomplete="off" value="1"/></td>
					
				</tr>
			</table>
		</div>
		<div class="row">
			<div class="col-4">
				<table align="left" style="width:100%;">
					<tr>
						<td style="width:30%;"><center>Hour</center></td>
						<td style="width:70%;"><input class="form-control" type="number" id="validtimh" autocomplete="off" value="0"/></td>
						
					</tr>
				</table>
			</div>
			<div class="col-4">
				<table align="left"  style="width:100%;">
					<tr>
						<td style="width:30%;"><center>Minutes</center></td>
						<td style="width:70%;"><input class="form-control" type="number" id="validtimm" autocomplete="off" value="0"/></td>
						
					</tr>
				</table>
			</div>
			
		</div></td>
 </tr>
   <tr>
    <td><?= $_lock_user ?></td><td>
      <select class="form-control" id="lockunlock" name="lockunlock" required="1">
        <option selected="selected" value="Disable">Disable</option>
        <option value="Enable">Enable</option>
      </select>
    </td>
  </tr>
  
  
</table>
</form>
</div>
</div>
</div>
<div class="col-4">
  <div class="card">
    <div class="card-header">
      <h3><i class="fa fa-book"></i> <?= $_readme ?></h3>
    </div>
    <div class="card-body">
<table class="table">
    <tr>
    <td colspan="2">
      <p style="padding:0px 5px;">
        <b>Piso Wifi Address Pool</b> this is the Address Pool Assigned for your PisoWifi.<br/>
		Maghanap ng technician kung hindi alam.<br/><br/>
		<b>Bandwith,</b> Speed of the Internet, eto yung Bilis ng Internet ng User.<br/><br/>
		
		<b>Time Limit</b>, Eto naman yung Oras ng Wifi. <br/><br/>
		
		<b>Price and Selling Price,</b> kayu na bahala dito usually parehas lang value dapat nyan.<br/><br/>
		
		<b>Parent Queue</b>, This is Optional, pero kung gusto nyu macontrol yung Bandwith sa PisoWifi, kakailangan mo ito
		</p>      
    </td>
  </tr>
</table>
</div>
</div>
</div>
</div>
<script type="text/javascript">

function isAlphaOrParen(str) {
  return /^[a-zA-Z()]+$/.test(str);
}

function subm(){
	
	var price = document.getElementsByName("price")[0].value;
	document.getElementsByName("sprice")[0].value = price;	
	var vc_timeh = document.getElementById("timelimh").value;
	var vc_timem = document.getElementById("timelimm").value;
	
	var vc_vald	=	document.getElementById("validtimd").value;
	var vc_valh	=	document.getElementById("validtimh").value;
	var vc_valm	=	document.getElementById("validtimm").value;
	
	if(vc_vald == 0) {
		if(vc_valh < vc_timeh) {
			alert("Validity must be equal or greater than Time Limit");
			return false;
		} else if(vc_valh == 0) {
			if(vc_valm < vc_timeh) {
				alert("Validity must be equal or greater than Time");
				return false;
			}
		}
	}
	
	if(vc_valh > 60) {
		alert("Validity Hour must below or equal to 60");
		return false;
	}
	
	if(vc_valm > 60) {
		alert("Validity Minute must below or equal to 60");
		return false;
	}
	
	if(vc_timeh > 23) {
		alert("Max Hour is 23");
		return false;
	}
	
	if(vc_timeh > 60) {
		alert("Max minute is 60");
		return false;
	}
	
	var vc_val 	= "";
	
	if(vc_vald > 0) {
		vc_val = vc_val + vc_vald + "d";
	}
	
	if(vc_valh > 0) {
		vc_val = vc_val + vc_valh + "h";
	}
	
	if(vc_valm > 0) {
		vc_val = vc_val + vc_valm + "m";
	}
	
	var vc_time = "";
	
	if(vc_timeh > 0) {
		vc_time = vc_time + vc_timeh + "h";
	}
	
	if(vc_timem > 0) {
		vc_time = vc_time + vc_timem + "m";
	}
	
	if(vc_timeh == 0 && vc_timem == 0 )  {
		alert("Please Provide a valid Time");
		return false;
	}
	
	document.getElementsByName("validity")[0].value	= vc_val;
	document.getElementsByName("ratelimit")[0].value = document.getElementById("speed_up").value + document.getElementById("speed_up_post").value + "/" + document.getElementById("speed_down").value + document.getElementById("speed_down_post").value;
	
	document.getElementsByName("name")[0].value = vc_time  + "-" + price + "<?= $currency ?>";
}



function remSpace() {
  var upName = document.getElementById("namee");
  var newUpName = upName.value.replace(/\s/g, "-");
  //alert("<?php if ($currency == in_array($currency, $cekindo['indo'])) {
            echo "Nama Profile tidak boleh berisi spasi";
          } else {
            echo "Profile name can't containing white space!";
          } ?>");
  upName.value = newUpName;
  upName.focus();
}
</script>
