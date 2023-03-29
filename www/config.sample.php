<?php

//esbart
define('URL','http://localhost/');
define('TITLE','php-ldap-esbart');
define('LOCALE','pt');
define('MODE','dark');
define('LOGGING',true);
define('MIN_PASSWORD_LENGTH',10);
define('HIDE_SECOND_SURNAME',true);
define('TOKEN_EXPIRES_H',24);
define('FROM_NAME','My domain');
define('FROM_ADDR','noreply@example.com');
define('FROM_REPLYTO','replyto@example.com');
define('FOOTER_IMAGE_P','include/template/images/logo.png');
define('FOOTER_PRIVACY_POLICY_URL','');

//MariaDB
define('DB_HOST','mysql');
define('DB_PORT', 3306);
define('DB_NAME','esbart');
define('DB_USER','esbart');
define('DB_PASS','xxxxxxx');

//LDAP
define('LDAP_TREE','dc=branch,dc=my,dc=domain');
define('LDAP_SEARCH_DN','ou=people,'.LDAP_TREE);
define('LDAP_SEARCH_FILTER','(objectclass=*)');
define('LDAP_USER','cn=admin,'.LDAP_TREE);
define('LDAP_HOST','branch.mydomain.com');
define('LDAP_PASS','xxxxxxxx');

define('LDAP_USER_ATTRS', 'givenname,cn,uid,mail,telephoneNumber,radiusSimultaneousUse,radiusTunnelPrivateGroupId,radiusExpiration,brPersonCpf');
define('LDAP_USER_OBJ_CLASSES','brPerson,inetOrgPerson,person,radiusprofile,sambaSamAccount,schacPersonalCharacteristics');

define('LDAP_DEVICES_ATTRS', 'givenname,cn,uid,macaddress,radiusSimultaneousUse,radiusTunnelPrivateGroupId,radiusExpiration');
define('LDAP_DEVICES_OBJ_CLASSES','ieee802Device,inetOrgPerson,radiusprofile,sambaSamAccount');
define('LDAP_DEVICES_DN','ou=dispositivos,'.LDAP_TREE);

define('LDAP_PRIMARY_GROUP_ID','1');
define('LDAP_USER_EMAIL_ATTR','mail');
define('LDAP_GROUP_EXCLUSIONS','everybody,workshop');
define('LDAP_GROUP_ATTR','member');
define('LDAP_GROUP_FILTER','groupOfNames');
define('LDAP_GROUPS_DN','ou=grupos,'.LDAP_TREE);
define('LDAP_AUTH_GROUP','cn=cti,'.LDAP_GROUPS_DN);
define('LDAP_NOPASSWD_CHANGE_OU','ou=servidor,ou=people,'.LDAP_TREE);
define('LDAP_SAMBA_SID','1');

define('LDAP_SYNC_TREE','dc=my,dc=domain');
define('LDAP_SYNC_SEARCH_DN','ou=people,'.LDAP_SYNC_TREE);
define('LDAP_SYNC_SEARCH_FILTER','(objectclass=*)');
define('LDAP_SYNC_USER','cn=branch_user,'.LDAP_SYNC_TREE);
define('LDAP_SYNC_HOST','hq.mydomain.com');
define('LDAP_SYNC_PASS','xxxx');
define('LDAP_SYNC_ATTR','mail');
define('LDAP_SYNC_USER_ATTRS', 'sn,uid,mail,telephoneNumber,brPersonCpf,userPassword,sambaNTPassword');

//PHP
define('DISPLAY_ERRORS',true);
define('ERROR_REPORTING',E_ALL);
define('TIME_LIMIT',30);

?>
