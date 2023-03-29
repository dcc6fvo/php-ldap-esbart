<?php

//prepare entry
$uid = $_GET['object'];

  if ($pd = getEntryData($uid)) {

?>
      <h2><?=set_reset_password_header?></h2>

      <form id="device" enctype="application/x-www-form-urlencoded" method="post" action="index.php?module=devices&action=password&object=<?=$_GET['object']?>">
        <div><?=username?></div>
        <div class="not-editable-form-item"><?=$_GET['object']?></div>

        <div><?=dn?></div>
        <div class='not-editable-form-item'><?=$pd[0]['dn']?></div>
        
        <div><label for="password"><?=password?></label></div>
        <div><input name="password" id="password" type="password" class="short" value="" /></div>
        
        <div><label for="password_c"><?=repeat?> <?=password?></label></div>
        <div><input name="password_c" id="password_c" type="password" class="short" onfocusout="comparePasswords(event)" value="" /></div>
        <span id='mensagempassword_c' class='validation-message'></span>
      
        <input name="submit" type="submit" value="<?=create?>" />
<?php
      printMessages($err);
      echo "      </form>\n";
        
  }
  else {
    echo "<p>".user_does_not_exist."</p>";
  }

?>

<script src="../scripts/devices-add.js"></script>