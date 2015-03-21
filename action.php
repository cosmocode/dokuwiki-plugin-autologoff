<?php
/**
 * DokuWiki Plugin autologoff (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_autologoff extends DokuWiki_Action_Plugin {
    /** @var  helper_plugin_autologoff */
    private $helper;

    public function __construct(){
        $this->helper = $this->loadHelper('autologoff', false);
    }

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

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

        $time = $this->helper->usertime();
        if(!$time) return;

        // check if the time has expired meanwhile
        if(isset($_SESSION[DOKU_COOKIE]['autologoff'])) {
            /** @var int $idle_time */
            $idle_time = time() - $_SESSION[DOKU_COOKIE]['autologoff'];
            if( $idle_time > $time * 60) {
                msg(sprintf($this->getLang('loggedoff'), hsc($_SERVER['REMOTE_USER'])));
                unset($_SESSION[DOKU_COOKIE]['autologoff']);
                $event = new Doku_Event('ACTION_AUTH_AUTOLOGOUT', $idle_time);
                $event->advise_before(false);
                auth_logoff();
                $event->advise_after();
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

        $time = $this->helper->usertime();
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
}

// vim:ts=4:sw=4:et:
