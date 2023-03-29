<?php

$con = LDAPconnect()[0];
$result = ldap_search($con,LDAP_SEARCH_DN,"(uid=*)",array('cn',LDAP_USER_EMAIL_ATTR,'uid'));
$entries = ldap_get_entries($con,$result);
//sort alphabetically
usort($entries,"sortByName");
//prepare table
echo "      <table>\n";
echo "        <tr>\n";
echo "          <th>".name."</th>\n";
echo "          <th>".login."</th>\n";
echo "          <th>".email."</th>\n";
echo "          <th>".member_of."</th>\n";
echo "          <th align=\"right\">".actions."</th>\n";
echo "        </tr>\n";
for ($i = 1; $i < count($entries); $i++) {
  
  $uid=$entries[$i]['uid'][0];
  $cn=$entries[$i]['cn'][0];
  
  if (isset($entries[$i]['mail'][0]))
    $mail = $entries[$i]['mail'][0];

  $password = '';
  $sync = '';
  $disable = '';
  $reinvite = '';

  if(isset($mail) && !empty($mail)){
    $password = '<a href="?module=users&action=password&object='.$uid.'">'.password.'</a>';
    $sync = '&nbsp;&nbsp;<a href="?module=users&action=sync&object='.$uid.'">'.sync.'</a>';
  }
  else{
    $password = '<a href="?module=users&action=password&object='.$uid.'">'.password.'</a>';
  }
  
  $edit = '&nbsp;&nbsp;<a href="?module=users&action=edit&object='.$uid.'">'.edit.'</a>';
  #$remove = '&nbsp;&nbsp;<a href="?module=users&action=remove&object='.$uid.'" onclick="return confirm(\''.remove_user_confirmation.'\')">'.remove.'</a>'; 
    
  echo "<tr>";
  
  if(isset($cn))
    echo '<td width=\'200\'>'.$cn.'</td>';
  else
    echo '<td width="200"></td>';

  if(isset($uid))
    echo '<td width=\'100\'>'.$uid.'</td>';
  else
    echo "<td width='100'></td>";
  
  if(isset($entries[$i][LDAP_USER_EMAIL_ATTR][0]))
    echo '<td width=\'300\'>'.$entries[$i][LDAP_USER_EMAIL_ATTR][0].'</td>';
  else
    echo "<td width='300'></td>";

  echo '<td>'.implode(', ',getUserMembership($uid))."</td>";
  echo '<td align="right" width="200">'.$password.$disable.$reinvite.$edit.$sync."</td>";
  echo "</tr>";

  $mail = '';
}
echo "      </table>";
echo "      <p>".there_are." ".ldap_count_entries($con,$result)." ".users."</p>";
ldap_close($con);

?>

