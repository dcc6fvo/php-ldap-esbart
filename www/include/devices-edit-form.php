<?php

if ($pd = getPersonalData($_GET['object'])) {
  
?>
      <h2><?=edit?> <?=user?></h2>
      <form id="user" enctype="application/x-www-form-urlencoded" method="post" action="index.php?module=devices&action=edit&object=<?=$pd[0]['uid'][0]?>">
      <?php
        echo "<div>".login."</div>";
        echo "<div class='not-editable-form-item'>".$pd[0]['uid'][0]."</div>";

        echo "<div>".dn."</div>";
        echo "<div class='not-editable-form-item'>".$pd[0]['dn']."</div>";
              
        for ($x = 0; $x < $pd[0]['count']; $x++) {
            $attr_name = $pd[0][$x];
            if($attr_name != "uid" && $attr_name != "cn"){
                echo "<div><label for=".$attr_name.">".$attr_name."</label></div>";
                echo "<div><input name=".$attr_name." type='text' value=\"".$pd[0][$attr_name][0]."\" /></div>";
            }
        }
        
        echo "<div><label for=\"groups\">".member_of.":</label></div>\n";
        echo "<div><select name=\"groups[]\" multiple>\n";

        $current_groups = getUserMembership($pd[0]['uid'][0]);
        $groups = getAssignableGroups();
        foreach ($groups as $group) {
          if (array_search($group,$current_groups) !== false) {
            echo "<option value=\"$group\" selected>$group</option>\n";
          }
          else {
            echo "<option value=\"$group\">$group</option>\n";
          }
        }
        echo "</select></div>\n";
        ?>    
        <input name="submit" type="submit" value="<?=save?>" />
        <?php
          printMessages($err);
          echo "</form>\n";
}
else {
  echo "<p>".user_does_not_exist."</p>";
}

?>