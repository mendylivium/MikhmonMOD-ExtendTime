	            	              	            	              
<table class="voucher" style=" width: 160px;">
  <tbody>
    <tr>
      <td style="text-align: left; font-size: 14px; font-weight:bold; border-bottom: 1px black solid;"><?= $hotspotname; ?><span id="num"><?= " [$num]"; ?></span></td>
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
          <td >Kode Voucher</td>
        </tr>
        <tr style="color: black; font-size: 14px;">
          <td style="width:100%; border: 1px solid black; font-weight:bold;"><?= $username; ?></td>
        </tr>
        <tr>
          <td colspan="2" style="border: 1px solid black; font-weight:bold;"><?= $validity; ?> <?= $timelimit; ?> <?= $datalimit; ?> <?= $price; ?></td>
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