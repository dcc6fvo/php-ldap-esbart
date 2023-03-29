      <h2><?=add?> <?=devices?></h2>

      <form name="device" id="device" enctype="application/x-www-form-urlencoded" method="post" action="index.php?module=devices&action=add">
        <div><label for="name_1"><?=name?>*</label></div>
        <div><input name="name_1" id="name_1" type="text" value="" /></div>

        <div><label for="login"><?=login?>*</label></div>
        <div><input name="login" id="login" type="text" value=""/></div>

        <div><label for="password"><?=password?></label></div>
        <div><input name="password" id="password" type="password" class="short" value="" /></div>
        
        <div><label for="password_c"><?=repeat?> <?=password?></label></div>
        <div><input name="password_c" id="password_c" type="password" class="short" value="" onfocusout="comparePasswords(event)"/></div>
        <span id='mensagempassword_c' class='validation-message'></span>

        <?php
       
        $user_attr_array=preg_split ("/\,/", LDAP_DEVICES_ATTRS);
        $common_attrs = array("mail", "email", "cn", "givenname", "sn", "uid");
        foreach ($user_attr_array as $attr) {
          $action = '';
          if(in_array($attr, array('radiusSimultaneousUse','radiusTunnelPrivateGroupId','radiusExpiration'))){         
            $action = "onkeypress='return isNumber(event)'";
          }

          if(!in_array($attr, $common_attrs)){
            echo "<div><label for='$attr'>$attr</label></div>";
            echo "<div><input name='$attr' id='$attr' type='text' value='' $action /></div>";
            echo "<span id='mensagem$attr' class='validation-message'></span>";
          }
        }

        echo "<div>".ou."</div>";
        echo "<div class='not-editable-form-item'>".LDAP_DEVICES_DN."</div>";
        
?>      
        <input name="submit" id="submit"  type="submit" value="<?=add?>" />
<?php
        printMessages($err);
        echo "</form>";
?>
        <script src="../scripts/devices-add.js"></script>