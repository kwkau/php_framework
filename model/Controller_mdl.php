<?php
class Controller_mdl extends Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * function to verify that session of the current user is valid
     * @param $uid int the user id of the user
     * @param $lg_strng string a unique string value created for each user when they logon
     * @param $uagnt string
     * @return bool true if the users session is valid false if not valid
     */
    public function l_verf($uid, $lg_strng, $uagnt) {
        $lver_sth = $this->db->prepare('select hcms.hcms_user_login.password from hcms.hcms_user_login where hcms.hcms_user_login.user_id = ? limit 1');
        $lver_sth->execute(array($uid));

        if ($lver_sth->rowCount() == 1) {
            $user = $lver_sth->fetch(PDO::FETCH_ASSOC);
            $login_check = hash('sha512', $user['password'] . $uagnt);
            if ($lg_strng == $login_check) { return true; } else return false;
        } else return false;
    }
}
