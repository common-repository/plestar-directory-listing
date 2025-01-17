jQuery(function($) {

    var $pdl_debugging = $('#pdl-debugging');
    var $tab_selector = $('.tab-selector', $pdl_debugging);

	$('#wpbody .wrap').before('<div id="pdl-debugging-placeholder"></div>');
	$('#pdl-debugging-placeholder').replaceWith($('#pdl-debugging'));

    $tab_selector.find('li a').click(function(e) {
        e.preventDefault();

        var dest = '#pdl-debugging-tab-' + $(this).attr('href').replace('#', '');

        $tab_selector.find('li').removeClass('active');
        $(this).parent('li').addClass('active');
        $pdl_debugging.find('.tab').hide();
        $(dest).show();
    }).first().click();

    $pdl_debugging.find('table tr').click(function(e) {
        var $extradata = $(this).find('.extradata');

        if ( $extradata.length > 0 )
            $extradata.toggle();
    });

});
