<?php
/**
 * DokuWiki Plugin autologoff (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_autologoff extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler &$controller) {

        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'handle_dokuwiki_started');
        $controller->register_hook('DETAIL_STARTED', 'BEFORE', $this, 'handle_dokuwiki_started');
        $controller->register_hook('MEDIAMANAGER_STARTED', 'BEFORE', $this, 'handle_dokuwiki_started');

        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax');
    }

    /**
     * Check for timeouts, remember last activity
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_dokuwiki_started(Doku_Event &$event, $param) {
        global $ID;
        global $JSINFO;

        $time = $this->usertime();
        if(!$time) return;

        // check if the time has expired meanwhile
        if(isset($_SESSION[DOKU_COOKIE]['autologoff'])) {
            if(time() - $_SESSION[DOKU_COOKIE]['autologoff'] > $time * 60) {
                msg(sprintf($this->getLang('loggedoff'), hsc($_SERVER['REMOTE_USER'])));

                unset($_SESSION[DOKU_COOKIE]['autologoff']);
                auth_logoff();
                send_redirect(wl($ID, '', true, '&'));
            }
        }

        // update the time
        $_SESSION[DOKU_COOKIE]['autologoff'] = time();
        $JSINFO['autologoff'] = $time;
    }

    /**
     * Ajax function returning the remaining time
     *
     * @param Doku_Event $event
     * @param $param
     */
    public function handle_ajax(Doku_Event &$event, $param) {
        if($event->data != 'autologoff') return;
        $event->preventDefault();

        header('Content-Type: text/plain');

        $time = $this->usertime();
        if(!$time){
            echo 0;
            exit;
        }

        // user hit button to stay logged in
        if(isset($_REQUEST['refresh'])){
            session_start();
            $_SESSION[DOKU_COOKIE]['autologoff'] = time();
            session_write_close();
        }

        echo(($time * 60) - (time() - $_SESSION[DOKU_COOKIE]['autologoff']));
    }

    /**
     * Returns the configured time for the current user (in minutes)
     *
     * @return int
     */
    private function usertime(){
        if(!$_SERVER['REMOTE_USER']) return 0;

        return 3; //FIXME read from config
    }
}

// vim:ts=4:sw=4:et:
