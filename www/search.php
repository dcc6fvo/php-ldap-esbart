<?php
    require 'include/functions.php';
    require 'config.php';
    require 'locale/'.LOCALE.'.php';

    // Retrieve search query from GET request
    $uid = $_GET['q'];
    $con = LDAPconnect();

    if( strlen($uid) > 3 ){
        $result = ldap_search($con[0],LDAP_SEARCH_DN,"(uid=*$uid*)",array('sn','uid',LDAP_USER_EMAIL_ATTR,'radiuscallingstationid','radiusclientipaddress','description'));
        $entries = ldap_get_entries($con[0],$result);  
        echo json_encode($entries);
        
    }else{
        $results = null;
        echo json_encode($results);
    }

    ldap_close($con[0]);
?>