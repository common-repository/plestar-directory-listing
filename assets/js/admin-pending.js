jQuery(function($) {
	var rows = [];
	var indexs = [];
	var cid = [];
	$( '.pending-approve' ).click(function(e) {
        e.preventDefault();
        rows = [$(this).closest('tr')];
        row = rows[0];
        indexs = [$(row).index()];
        cid = [$(row).find(".check-column input[type='checkbox'] ").val()];
        approve_directory();
    });
	
	function approve_directory(){
		$.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'pdl-approve-pending-directory',
                cid: indexs,
                pendings: cid
            },
            success: function( res ) {
                if ( ! res.success )
                    return;
                $.each(rows,function(key, value){
                	$(value).find('.button-primary').removeClass('button-primary').addClass('button-secondary').text("Approved");
                })               
            }
        });
	}
})