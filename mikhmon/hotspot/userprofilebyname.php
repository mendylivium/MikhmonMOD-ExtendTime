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
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {


  if (substr($userprofile, 0, 1) == "*") {
    $userprofile = $userprofile;
  } elseif (substr($userprofile, 0, 1) != "") {
    $getprofile = $API->comm("/ip/hotspot/user/profile/print", array(
      "?name" => "$userprofile",
    ));
    $userprofile = $getprofile[0]['.id'];
    if ($userprofile == "") {
      echo "<b>User Profile not found</b>";
    }
  }

  $getpool = $API->comm("/ip/pool/print");

  $getprofile = $API->comm("/ip/hotspot/user/profile/print", array(
    "?.id" => "$userprofile"
  ));
  $profiledetalis = $getprofile[0];
  $pid = $profiledetalis['.id'];
  $pname = $profiledetalis['name'];
  $psharedu = $profiledetalis['shared-users'];
  $pratelimit = $profiledetalis['rate-limit'];
  $ponlogin = $profiledetalis['on-login'];
  $ppool = $profiledetalis['address-pool'];
  $sparent = $profiledetalis['parent-queue'];

  if(empty($ppool)){$ppool = "none";}
  if(empty($sparent)){$sparent = "none";}

  $getexpmode = explode(",", $ponlogin)[1];

  if ($getexpmode == "rem") {
    $getexpmodet = "Remove";
  } elseif ($getexpmode == "ntf") {
    $getexpmodet = "Notice";
  } elseif ($getexpmode == "remc") {
    $getexpmodet = "Remove & Record";
  } elseif ($getexpmode == "ntfc") {
    $getexpmodet = "Notice & Record";
  } else {
    $getexpmode = "0";
    $getexpmodet = "None";
  }

  $getprice = explode(",", $ponlogin)[2];
  if ($getprice == "0") {
    $getprice = "";
  } else {
    $getprice = $getprice;
  }

  $getsprice = explode(",", $ponlogin)[4];
  if ($getsprice == "0") {
    $getsprice = "";
  } else {
    $getsprice = $getsprice;
  }

  $getvalid = explode(",", $ponlogin)[3];

  $getgracep = explode(",", $ponlogin)[4];

  $getlocku = explode(",", $ponlogin)[6];
  if ($getlocku == "") {
    $getlocku = "Disable";
  } else {
    $getlocku = $getlocku;
  }

  $getallqueue = $API->comm("/queue/simple/print", array(
    "?dynamic" => "false",
  ));

  $getmonexpired = $API->comm("/system/scheduler/print", array(
    "?name" => "$pname",
  ));
  $monexpired = $getmonexpired[0];
  $monid = $monexpired['.id'];
	$pmon = $monexpired['name'];
	$chkpmon = $monexpired['disabled'];
	if(empty($pmon) || $chkpmon == "true"){$moncolor = "text-orange";}else{$moncolor = "text-green";}

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
    if ($getlock == Enable) {
      $lock = '; [:local mac $"mac-address"; /ip hotspot user set mac-address=$mac [find where name=$user]]';
    } else {
      $lock = "";
    }

    $randstarttime = "0".rand(1,5).":".rand(10,59).":".rand(10,59);
    $randinterval = "00:02:".rand(10,59);

    $parent = ($_POST['parent']);

    $record = '; :local mac $"mac-address"; :local time [/system clock get time ]; /system script add name="$date-|-$time-|-$user-|-'.$price.'-|-$address-|-$mac-|-' . $validity . '-|-'.$name.'-|-$comment" owner="$month$year" source=$date comment=mikhmon';
    
    $onlogin = ':put (",'.$expmode.',' . $price . ',' . $validity . ','.$sprice.',,' . $getlock . ',"); {:local date [ /system clock get date ];:local year [ :pick $date 7 11 ];:local month [ :pick $date 0 3 ];:local comment [ /ip hotspot user get [/ip hotspot user find where name="$user"] comment]; :local ucode [:pic $comment 0 2]; :if ($ucode = "vc" or $ucode = "up" or $comment = "") do={ /sys sch add name="$user" disable=no start-date=$date interval="' . $validity . '"; :delay 2s; :local exp [ /sys sch get [ /sys sch find where name="$user" ] next-run]; :local getxp [len $exp]; :if ($getxp = 15) do={ :local d [:pic $exp 0 6]; :local t [:pic $exp 7 16]; :local s ("/"); :local exp ("$d$s$year $t"); /ip hotspot user set comment=$exp [find where name="$user"];}; :if ($getxp = 8) do={ /ip hotspot user set comment="$date $exp" [find where name="$user"];}; :if ($getxp > 15) do={ /ip hotspot user set comment=$exp [find where name="$user"];}; /sys sch remove [find where name="$user"]';
    

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

    $bgservice = ':local dateint do={:local montharray ( "jan","feb","mar","apr","may","jun","jul","aug","sep","oct","nov","dec" );:local days [ :pick $d 4 6 ];:local month [ :pick $d 0 3 ];:local year [ :pick $d 7 11 ];:local monthint ([ :find $montharray $month]);:local month ($monthint + 1);:if ( [len $month] = 1) do={:local zero ("0");:return [:tonum ("$year$zero$month$days")];} else={:return [:tonum ("$year$month$days")];}}; :local timeint do={ :local hours [ :pick $t 0 2 ]; :local minutes [ :pick $t 3 5 ]; :return ($hours * 60 + $minutes) ; }; :local date [ /system clock get date ]; :local time [ /system clock get time ]; :local today [$dateint d=$date] ; :local curtime [$timeint t=$time] ; :foreach i in [ /ip hotspot user find where profile="'.$name.'" ] do={ :local comment [ /ip hotspot user get $i comment]; :local name [ /ip hotspot user get $i name]; :local gettime [:pic $comment 12 20]; :if ([:pic $comment 3] = "/" and [:pic $comment 6] = "/") do={:local expd [$dateint d=$comment] ; :local expt [$timeint t=$gettime] ; :if (($expd < $today and $expt < $curtime) or ($expd < $today and $expt > $curtime) or ($expd = $today and $expt < $curtime)) do={ [ /ip hotspot user '.$mode.' $i ]; [ /ip hotspot active remove [find where user=$name] ];}}}';
    

    $API->comm("/ip/hotspot/user/profile/set", array(
			  		  /*"add-mac-cookie" => "yes",*/
      ".id" => "$pid",
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

    echo "<script>window.location='./?user-profile=" . $pid . "&session=" . $session . "'</script>";
  }
}
?>
<div class="row">
<div class="col-8">
<div class="card">
<div class="card-header">
    <h3><i class="fa fa-edit"></i> <?= $_edit." ".$_user_profile ?> </h3>
</div>
<div class="card-body">
<form autocomplete="off" method="post" action="">
  <div>
    <a class="btn bg-warning" href="./?hotspot=user-profiles&session=<?= $session; ?>"> <i class="fa fa-close"></i> <?= $_close?></a>
    <button type="submit" name="save" class="btn bg-primary" ><i class="fa fa-save"></i> <?= $_save ?></button>
  </div>
<table class="table">
  <tr>
    <td><?= $_name ?> <i class="fa fa-ci fa-circle <?= $moncolor ?>"></i></td><td><input class="form-control" type="text" onchange="remSpace();" autocomplete="off" name="name" value="<?= $pname; ?>" required="1" autofocus></td>
  </tr>
  <tr>
    <td class="align-middle">Address Pool</td>
    <td>
    <select class="form-control " name="ppool">
      <option><?= $ppool; ?></option>
      <option>none</option>
        <?php $TotalReg = count($getpool);
        for ($i = 0; $i < $TotalReg; $i++) {

          echo "<option>" . $getpool[$i]['name'] . "</option>";
        }
        ?>
    </select>
    </td>
  </tr>
  <tr>
    <td>Shared Users</td><td><input class="form-control" type="text" size="4" autocomplete="off" name="sharedusers" value="<?= $psharedu; ?>" required="1"></td>
  </tr>
  <tr>
    <td>Rate limit [up/down]</td><td><input class="form-control" type="text" name="ratelimit" autocomplete="off" value="<?= $pratelimit; ?>" placeholder="Example : 512k/1M" ></td>
  </tr>
  <tr>
    <td><?= $_expired_mode ?></td><td>
      <select class="form-control" onchange="RequiredV();" id="expmode" name="expmode" required="1">
        <option value="<?= $getexpmode; ?>"><?= $getexpmodet; ?></option>
        <option value="0">None</option>
        <option value="rem">Remove</option>
        <option value="ntf">Notice</option>
        <option value="remc">Remove & Record</option>
        <option value="ntfc">Notice & Record</option>
      </select>
    </td>
  </tr>
  <tr id="validity" <?php if ($getexpmodet == "None") {echo 'style="display:none;"';}?>>
    <td><?= $_validity ?></td><td><input class="form-control" type="text" id="validi" size="4" autocomplete="off" name="validity" value="<?= $getvalid; ?>" required="1"></td>
  </tr>
  <tr>
    <td><?= $_price." ". $currency; ?></td><td><input class="form-control" type="text" min="0" name="price" value="<?= $getprice; ?>" ></td>
  </tr>
  <tr>
    <td class="align-middle"><?= $_selling_price.' '.$currency; ?></td><td><input class="form-control" type="text" size="10" min="0" name="sprice" value="<?= $getsprice; ?>" ></td>
  </tr>
  <tr>
    <td><?= $_lock_user ?></td><td>
      <select class="form-control" id="lockunlock" name="lockunlock" required="1">
        <option value="<?= $getlocku; ?>"><?= $getlocku; ?></option>
        <option value="Enable">Enable</option>
        <option value="Disable">Disable</option>
      </select>
    </td>
  </tr>
  <tr>
    <td class="align-middle">Parent Queue</td>
    <td>
    <select class="form-control " name="parent">
      <option><?= $sparent; ?></option>
      <option>none</option>
        <?php $TotalReg = count($getallqueue);
        for ($i = 0; $i < $TotalReg; $i++) {

          echo "<option>" . $getallqueue[$i]['name'] . "</option>";
        }
        ?>
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
        <?= $_details_user_profile ?>
      </p>
      <p style="padding:0px 5px;">
        <?= $_format_validity ?>
      </p>
    </td>
  </tr>
</table>
</div>
</div>
</div>
</div>