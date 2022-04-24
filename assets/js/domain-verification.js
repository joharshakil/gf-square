jQuery(document).ready(function(){

    //direct ajax call on load
    jQuery.ajax({
        url: gfsqsadmin.ajaxUrl,
        type: "POST",
        dataType: "json",
        data: {
            action: 'gfsqs_domain_verification',
            nonce: gfsqsadmin.nonce,
            form_id: gfsqsadmin.form_id,
            reference: 'direct',
        },
        success: function (response) { 

            //print response in console log for test only
            console.log(response);
            //debugger;
            if ( response.status == 'VERIFIED') { //if success

                jQuery('label[for="enable_applepay"]').addClass('gfsq-domain-verified');
                jQuery('.verify-btn-wrapper').remove();

            }
            
            if ( response.status == 'NOT_VERIFIED') { //if success

                jQuery('label[for="enable_applepay"]').addClass('gfsq-domain-not-verified');
                
            }
            
        }
    });

    // on click #domain-verification button
    jQuery("#domain-verification").on('click', function(e){
        e.preventDefault();
        jQuery.ajax({
            url: gfsqsadmin.ajaxUrl,
            type: "POST",
            dataType: "json",
            data: {
                action: 'gfsqs_domain_verification',
                nonce: gfsqsadmin.nonce,
                form_id: gfsqsadmin.form_id,
                reference: 'click',
            },
            beforeSend: function () {
                jQuery('.verify-btn-wrapper > button').text('Verifying...');
                jQuery('label[for="enable_applepay"]').removeClass('gfsq-domain-not-verified');
            },
            success: function (response) { 

                //print response in console log for test only
                console.log(response);
                //debugger;
                if ( response.status == 'VERIFIED') { //if success

                    jQuery('label[for="enable_applepay"]').addClass('gfsq-domain-verified');
                    jQuery('.verify-btn-wrapper').remove();

                } 
                
                if ( response.status == 'error' ) {
                    jQuery('.verify-btn-wrapper > button').text('Verify Domain');
                    jQuery('.verify-btn-wrapper').append('<span class="gfsq-domain-failed">' + response[0].code + ' - ' + response[0].detail + '</span>');
                }

                if ( response.status == 'not_moved' ) {
                    jQuery('.verify-btn-wrapper > button').text('Verify Domain');
                    jQuery('.verify-btn-wrapper').append('<span class="gfsq-domain-failed">' + response.code + ' - ' + response.detail + '</span>');
                }
                
                if ( response.status == 'not_permission' ) {
                    jQuery('.verify-btn-wrapper > button').text('Verify Domain');
                    jQuery('.verify-btn-wrapper').append('<span class="gfsq-domain-failed">' + response.code + ' - ' + response.detail + '</span>');
                }

                
            }
        });
    });
});