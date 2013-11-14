jQuery(function () {
    if (!JSINFO.autologoff) return;

    window.setTimeout(autologoff_check, (JSINFO.autologoff - 1) * 60 * 1000);


    function autologoff_check() {

        jQuery.post(DOKU_BASE + 'lib/exe/ajax.php',
            {call: 'autologoff'},
            function (timeremains) {
                if (timeremains <= 0) {
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
                            jQuery.post(DOKU_BASE + 'lib/exe/ajax.php',
                                {call: 'autologoff', refresh: 1}
                            );

                            jQuery(this).dialog('close');
                        };


                        $dialog.dialog({
                            modal: true,
                            buttons: buttons
                        });

                        timeremains = 60;
                    }

                    window.setTimeout(autologoff_check, timeremains * 1000);
                }
            }
        );

    }
});