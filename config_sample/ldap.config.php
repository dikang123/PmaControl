<?php

/*
 * Test all configuration to be sure everything correctly installed
 * developement => true
 * production => false
 * default : true
 */

if (!defined('LDAP_URL')) {
    define("LDAP_URL", "ldap.com");
}

if (!defined('LDAP_PORT')) {
    define("LDAP_PORT", 389);
}

if (!defined('LDAP_BIND_DN')) {
    define("LDAP_BIND_DN", "CN=readonly,CN=Users,DC=pws,DC=com");
}

if (!defined('LDAP_BIND_PASSWD')) {
    define("LDAP_BIND_PASSWD", "password");
}


if (!defined('LDAP_CHECK')) {
    define("LDAP_CHECK", false);
}
