// For IE8 and earlier version.
// http://afuchs.tumblr.com/post/23550124774/date-now-in-ie8
Date.now = Date.now || function() { return +new Date; };

jQuery(function () {
    if (!JSINFO.autologoff) return;


    var autologofftimer = window.setTimeout(autologoff_check, (JSINFO.autologoff - 1) * 60 * 1000);
    var autologoffrefresh = Date.now();

    jQuery('body').keypress(function(){
        if((Date.now() - autologoffrefresh) < 60*1000) return;
        autologoffrefresh = Date.now();
        autologoff_refresh();
    });

    function autologoff_check() {

        jQuery.post(DOKU_BASE + 'lib/exe/ajax.php',
            {call: 'autologoff'},
            function (timeremains) {
                if (timeremains <= 0) {
                    // remove any onunload handlers
                    window.onbeforeunload = function(){};
                    window.onunload = function(){};
                    // log off
                    window.location.reload();
                } else {
                    timeremains -= 65;
                    if (timeremains <= 0) {
                        var $dialog = jQuery('<div>' + LANG.plugins.autologoff.warn + '</div>');
                        $dialog.attr('title', LANG.plugins.autologoff.title);
                        $dialog.appendTo(document.body);


                        var buttons = {};
                        buttons[LANG.plugins.autologoff.stillhere] = function () {
                            autologoff_refresh();
                            jQuery(this).dialog('close');
                        };


                        $dialog.dialog({
                            modal: true,
                            buttons: buttons
                        });

                        timeremains = 60;
                    }

                    window.clearTimeout(autologofftimer);
                    autologofftimer = window.setTimeout(autologoff_check, timeremains * 1000);
                }
            }
        );
    }

    function autologoff_refresh() {
        jQuery.post(DOKU_BASE + 'lib/exe/ajax.php',
            {call: 'autologoff', refresh: 1},
            function(timeremains){
                window.clearTimeout(autologofftimer);
                autologofftimer = window.setTimeout(autologoff_check, (timeremains - 60) * 1000);
            }
        );
    }

});
