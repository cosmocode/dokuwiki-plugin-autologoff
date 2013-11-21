<?php
/**
 * DokuWiki Plugin autologoff (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_autologoff extends DokuWiki_Plugin {

    private $configfile;

    public function __construct() {
        $this->configfile = DOKU_CONF . '/autologoff.conf';
    }

    /**
     * Loads the configuration from config file
     *
     * @return array
     */
    public function load_config() {
        $conf = array();
        foreach((array) confToHash($this->configfile) as $usergroup => $time) {
            $conf[rawurldecode($usergroup)] = (int) $time;
        }
        ksort($conf);
        return $conf;
    }

    /**
     * Adds another entry to tend of the config file
     *
     * @param $usergroup
     * @param $time
     */
    public function add_entry($usergroup, $time) {
        $time = (int) $time;
        if($time !== 0 && $time < 2) {
            msg($this->getLang('mintime'), -1);
            $time = 2;
        }
        $usergroup = auth_nameencode($usergroup, true);

        io_saveFile($this->configfile, "$usergroup\t$time\n", true);
    }

    /**
     * Removes an entry for the given group or user from config file
     *
     * @param $usergroup
     */
    public function remove_entry($usergroup){
        $grep = preg_quote(auth_nameencode($usergroup, true), '/');
        $grep = '/^'.$grep.'\\t/';

        io_deleteFromFile($this->configfile, $grep, true);
    }

    /**
     * Returns the configured time for the current user (in minutes)
     *
     * @return int
     */
    public function usertime() {
        global $INFO;
        global $auth;
        if(!$_SERVER['REMOTE_USER']) return 0;

        // make sure we have group info on the current user
        if(isset($INFO) && isset($INFO['userinfo'])){
            $groups = $INFO['userinfo']['grps'];
        }else{
            $info = $auth->getUserData($_SERVER['REMOTE_USER']);
            $groups = $info['grps'];
        }

        $config = $this->load_config();
        $maxtime = 0;
        foreach($config as $usergroup => $time) {
            if(!auth_isMember($usergroup, $_SERVER['REMOTE_USER'], (array) $groups)) continue;
            if($time == 0) return 0;
            if($time > $maxtime) $maxtime = $time;
        }

        return $maxtime;
    }
}

// vim:ts=4:sw=4:et:
