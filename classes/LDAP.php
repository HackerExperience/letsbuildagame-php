<?php

/**
 * Created by PhpStorm.
 * User: taiga
 * Date: 4/6/16
 * Time: 5:48 PM
 */
class LDAP {
    private $_ds;
    private $_dn;
    private $_rdn;
    private $_pass;

    public function __construct() {
        $this->_ds = ldap_connect("ldap://198.27.82.221:389");
        $this->_dn = "dc=example,dc=org";
        $this->_rdn = "cn=admin," . $this->_dn;
        $this->_pass = "admin";
        ldap_set_option($this->_ds, LDAP_OPT_PROTOCOL_VERSION, 3);
    }


    public function createUser($username, $email, $password) {
        $r = ldap_bind($this->_ds, $this->_rdn, $this->_pass);
        if ($r) {
            $user['objectClass'][0] = "top";
            $user['objectClass'][1] = "person";
            $user['objectClass'][2] = "inetOrgPerson";

            $user['cn'] = $username;
            $user['sn'] = $username;
            $user['mail'] = $email;
            $user['userPassword'] = "{SHA}" . base64_encode(pack("H*", sha1($password)));

            $user_rdn = 'cn=' . $user['cn'] . ',' . $this->_dn;

            $lr = ldap_add($this->_ds, $user_rdn, $user);

            ldap_close($this->_ds);

            if ($lr) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
}
