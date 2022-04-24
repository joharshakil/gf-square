jQuery(document).ready(function(){
    
    // on click .gfsr_refund
    jQuery(".gfsr_refund").on('click', function(e){
        
        e.preventDefault();
        var This = jQuery(this);

        var action = 'gfsr_add_refund_payment_ajax';        
        var entry_id = This.data('entry-id');
        var transaction_id = This.data('transaction-id');
        var type = This.data('type');
        var reason = This.siblings('.refund_reason').val();

        check = confirm('Are you sure, you want to refund this transaction ? This action cannot be reversible.');
        if ( check ) {

            jQuery.ajax({
                url: gfsr_refund.ajaxUrl,
                type: "POST",
                dataType: "json",
                data: {
                    action: action,                
                    form_id: gfsr_refund.form_id,
                    hdnAction: type,
                    entry_id: entry_id,
                    transaction_id: transaction_id,
                    reason: reason,
                },
                beforeSend: function () {
                    This.attr('disabled', true);
                    This.text('Refunding...');                
                },
                success: function (response) { 

                    //print response in console log for test only
                    //console.log(response);

                    if ( response.status == 'FAILED' ) {
                        This.removeAttr('disabled');
                        This.text('Make Refund');
                        This.siblings('.refund_result').html(`<span class="refund_failed">${response.message}</span>`);
                    }

                    if ( response.status == 'PENDING' ) {
                        This.siblings('.refund_reason').attr('disabled', true);
                        This.siblings('.refund_result').html(`<span class="refund_success">${response.message}</span>`);
                        This.remove();
                        //location.reload();
                    }
                    
                }
            });
        }
    });

    // on click .gfsr_proccess_transaction
    jQuery(".gfsr_proccess_transaction").on('click', function(e) {
        
        e.preventDefault();
        var This = jQuery(this);

        var action = 'gfsr_process_payment_ajax';        
        var entry_id = This.data('entry-id');
        var transaction_id = This.data('transaction-id');
        var type = This.data('type');
        if ( type == 'complete' ) {
            check = confirm('Are you sure, you want to complete this transaction ? This action will charge amount from user\'s account.');
        } else {
            check = confirm('Are you sure, you want to cancel this transaction ? This action cannot be reversible.');
        }
        if ( check ) {
            jQuery.ajax({
                url: gfsr_refund.ajaxUrl,
                type: "POST",
                dataType: "json",
                data: {
                    action: action,                
                    form_id: gfsr_refund.form_id,
                    hdnAction: type,
                    entry_id: entry_id,
                    transaction_id: transaction_id,
                },
                beforeSend: function () {
                    This.attr('disabled', true);
                    This.text('Completing...');                
                },
                success: function (response) { 

                    //print response in console log for test only
                    //console.log(response);

                    if ( response.status == 'FAILED' ) {
                        This.removeAttr('disabled');
                        if ( 'complete' == type ) {
                            This.text('Complete Transaction');
                        } else {
                            This.text('Cancel Transaction');
                        }
                        This.siblings('.refund_result').html(`<span class="refund_failed">${response.message}</span>`);
                    }

                    if ( response.status == 'COMPLETED' || response.status == 'CANCELED' ) {
                        This.parents('.gfsr_processing_payment_container').siblings('.refund_result').html(`<span class="refund_success">${response.message}</span>`);
                        This.parents('.gfsr_processing_payment_container').remove();                    
                        //location.reload();
                    }
                    
                }
            });
        }
    });

});