<?php
/**
 * DokuWiki Plugin autologoff (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class admin_plugin_autologoff extends DokuWiki_Admin_Plugin {
    /** @var  helper_plugin_autologoff */
    private $helper;

    public function __construct(){
        $this->helper = $this->loadHelper('autologoff', false);
    }


    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort() {
        return 500;
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly() {
        return false;
    }

    /**
     * Should carry out any processing required by the plugin.
     */
    public function handle() {
        if(isset($_REQUEST['remove']) && checkSecurityToken()){
            $this->helper->remove_entry($_REQUEST['remove']);
        }

        if(isset($_REQUEST['usergroup']) && checkSecurityToken()){
            $this->helper->add_entry($_REQUEST['usergroup'], $_REQUEST['time']);
        }


    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        echo $this->locale_xhtml('intro');

        $config = $this->helper->load_config();

        echo '<form action="'.script().'" method="post">';
        echo '<input type="hidden" name="do" value="admin" />';
        echo '<input type="hidden" name="page" value="autologoff" />';
        echo '<input type="hidden" name="sectok" value="'.getSecurityToken().'" />';

        echo '<table class="inline">';
        echo '<tr>';
        echo '<th>'.$this->getLang('usergroup').'</th>';
        echo '<th>'.$this->getLang('time').'</th>';
        echo '<th></th>';
        echo '</tr>';

        foreach($config as $usergroup => $time){

            $url = wl('',array(
                              'do' => 'admin',
                              'page' => 'autologoff',
                              'remove' => $usergroup,
                              'sectok' => getSecurityToken()
                         ));

            echo '<tr>';
            echo '<td>'.hsc($usergroup).'</td>';
            echo '<td>'.hsc($time).'</td>';
            echo '<td><a href="'.$url.'">'.$this->getLang('remove').'</a></td>';
            echo '</tr>';
        }

        echo '<tr>';
        echo '<td><input type="text" name="usergroup" class="edit" /></td>';
        echo '<td><input type="text" name="time" class="edit" /></td>';
        echo '<td><input type="submit" class="button" value="'.$this->getLang('save').'" /></td>';
        echo '</tr>';


        echo '</table>';
        echo '</form>';

    }
}

// vim:ts=4:sw=4:et:
