							            	              	            	              
<table class="voucher" style=" width: 160px;">
  <tbody>
    <tr style="background-color:<?php 
	
	switch($price) {
		case "Php 50.00":
			echo "red";
		break;
		case "Php 30.00":
			echo "blue";
		break;
		case "Php 10.00":
			echo "gray";
		break;
		case "Php 5.00":
		default:
			echo "black";
		break;
	}
	
	?>;">
      <td style="text-align: left; font-size: 14px; font-weight:bold;color: #fff;"><?= $hotspotname; ?><span id="num"><?= " [$num]"; ?></span></td>
    </tr>
    <tr>
      <td>
    <table style=" text-align: center; width: 150px;">
  <tbody>
    <tr style="color: black; font-size: 11px;">
      <td>
        <table style="width:100%;">
<!-- Username = Password    -->
<?php if ($usermode == "vc") { ?>
        <tr>
          <td >VOUCHER CODE</td>
        </tr>
        <tr style="color: black; font-size: 14px;">
          <td style="width:100%; border: 1px solid black; font-weight:bold;"><?= $username; ?></td>
        </tr>
        <tr>
		  <?php 
			if($timelimit != "") {
				$numerical = substr($timelimit,0,strlen($timelimit)-1);
				$pref = $numerical > 1 ? 's' : '';
				
				if(strpos($timelimit,'h')!=FALSE) {
					$timelimit = str_replace('h',' Hour' . $pref ,$timelimit);
				} elseif (strpos($timelimit,'d')!=FALSE) {
					$timelimit = str_replace('d',' Day' . $pref ,$timelimit);
				} elseif (strpos($timelimit,'w')!=FALSE) {
					$timelimit = str_replace('w',' Week' . $pref ,$timelimit);
				} elseif (strpos($timelimit,'m')!=FALSE) {
					$timelimit = str_replace('m',' Minutes' . $pref ,$timelimit);
				}
			}
		  
		  ?>
          <td colspan="2" style="border: 1px solid black; font-weight:bold;"><?= $timelimit; ?> <?= $datalimit; ?> [<?= $price; ?>]</td>
        </tr>
		<tr>
			<td colspan="2">
				<center>
					Login @ http://<?= $dnsname; ?>/
				</center>
			</td>
		</tr>
<!-- /  -->
<!-- Username & Password  -->
<?php 
} elseif ($usermode == "up") { ?>
          <tr>
          <td style="width: 50%">Username</td>
          <td>Password</td>
        </tr>
        <tr style="color: black; font-size: 14px;">
          <td style="border: 1px solid black; font-weight:bold;"><?= $username; ?></td>
          <td style="border: 1px solid black; font-weight:bold;"><?= $password; ?></td>
        </tr>
        <tr>
          <td colspan="2" style="border: 1px solid black; font-weight:bold;"><?= $validity; ?> <?= $timelimit; ?> <?= $datalimit; ?> <?= $price; ?></td>
        </tr>
<?php 
} ?>
<!-- /  -->
        </table>
      </td>
    </tr>
  </tbody>
    </table>
      </td>
    </tr>
  </tbody>
</table>	        