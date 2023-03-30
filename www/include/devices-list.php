<?php

$con = LDAPconnect();
$ldapconn = $con[0];
$ldapbind = $con[1];

//$results = ldap_search($ldapconn,LDAP_SEARCH_DN,"(cn=*)",array('cn',LDAP_USER_EMAIL_ATTR,'uid'),$pageSize);
$results = ldap_search($ldapconn,LDAP_DEVICES_DN,"(cn=*)",array('cn','uid','macaddress','radiusCallingStationId','radiusClientIpAddress','description'));
$entries = ldap_get_entries($ldapconn, $results);

echo "      <table>\n";
echo "        <tr>\n";
echo "          <th>".uid."</th>\n";
echo "          <th>".macaddress."</th>\n";
echo "          <th>".last_mac."</th>\n";
echo "          <th>".last_ip."</th>\n";
echo "          <th>".info."</th>\n";
echo "          <th align=\"right\">".actions."</th>\n";
echo "        </tr>\n";

for ($i=0; $i< $entries['count']; $i++) {

    if(isset($entries[$i]['uid'][0]))
        $uid = $entries[$i]['uid'][0]; 
    
    $password = '<a href="?module=devices&action=password&object='.$uid.'">'.password.'</a>';
    $edit = '&nbsp;&nbsp;<a href="?module=devices&action=edit&object='.$uid.'">'.edit.'</a>';
    echo "<tr>";

    if(isset($uid))
        echo '<td width=\'100\'>'.$uid.'</td>';
    else
        echo "<td width='100'></td>";

    if(isset($entries[$i]['macaddress'][0])){
        $macaddress = $entries[$i]['macaddress'][0];
        echo '<td width=\'200\'>'.$macaddress.'</td>';
    }
    else
        echo '<td width="200"></td>';

    if(isset($entries[$i]['radiuscallingstationid'][0])){
        $radiusCallingStationId = $entries[$i]['radiuscallingstationid'][0];
        echo '<td width=\'200\'>'.$radiusCallingStationId.'</td>';
    }
    else
        echo '<td width="200"></td>';

    if(isset($entries[$i]['radiusclientipaddress'][0])){
        $radiusClientIpAddress = $entries[$i]['radiusclientipaddress'][0];
        echo '<td width=\'200\'>'.$radiusClientIpAddress.'</td>';
    }
    else
        echo '<td width="200"></td>';

    if(isset($entries[$i]['description'][0])){
        $desc = $entries[$i]['description'][0];
        echo '<td width=\'200\'>'.$desc.'</td>';
    }
    else
        echo '<td width="200"></td>';

    //echo '<td>'.implode(', ',getUserMembership($uid))."</td>";
    echo '<td align="right" width="200">'.$password.$edit."</td>";
    echo "</tr>";
}
echo "      </table>";
echo "      <p>".there_are." ".ldap_count_entries($con[0],$results)." ".devices."</p>";
ldap_close($con[0]);


?>
