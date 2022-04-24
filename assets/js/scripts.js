

var totalAmount = 0;
var orderID = Math.floor(Math.random() * (1000 - 0 + 1) + 0);
var giftCardForm;
var save_card ;
var wcSquarePaymentForm = {};

function run_gravity_square(id) {
    let me;
    var application_id = jQuery('form#gform_'+id+' .application_id').val();
    var location_id = jQuery('form#gform_'+id+' .location_id').val();
    var currency_charge = jQuery('form#gform_'+id+' .currency_charge').val();
    var square_cof_mode = jQuery('form#gform_'+id+' .square_cof_mode').val();
    var is_recurring = jQuery('form#gform_'+id+' .is_recurring').val();
    var is_applepay = jQuery('form#gform_'+id+' .is_applepay').val();
    var is_googlepay = jQuery('form#gform_'+id+' .is_googlepay').val();
    var is_masterpass = jQuery('form#gform_'+id+' .is_masterpass').val();
    var is_giftcard = jQuery('form#gform_'+id+' .is_giftcard').val();
    var save_card = false;
    if (jQuery('form#gform_'+id+' .gf-multiple').length > 0) {
        save_card = true;
        //alert(save_card);
    }
        
    if (gfsqs.fname!=''){
        var billing_fname = gfsqs.fname;
    } else {
        var billing_fname = "Jane";
    }
    
    if (gfsqs.lname!='') {
        var billing_lname = gfsqs.lname;    
    } else {
        var billing_lname = "Doe";    
    }

    if (gfsqs.email!='') {
        var billing_email = gfsqs.email;    
    } else {
        var billing_email = "john.doe@gmail.com";    
    }

	wcSquarePaymentForm[id] = new SqPaymentForm({
        applicationId: application_id,
        locationId: location_id,
        inputClass: 'gfsq-input',
        inputStyles: jQuery.parseJSON(gfsqs.payment_form_input_styles),
        cardNumber: {
            elementId: 'gfsq-card-number-' + id,
            placeholder: gfsqs.placeholder_card_number
        },
        cvv: {
            elementId: 'gfsq-cvv-' + id,
            placeholder: gfsqs.placeholder_card_cvv
        },
        expirationDate: {
            elementId: 'gfsq-expiration-date-' + id,
            placeholder: gfsqs.placeholder_card_expiration
        },
        postalCode: {
            elementId: 'gfsq-postal-code-' + id,
            placeholder: gfsqs.placeholder_card_postal_code
        },
        googlePay: { // Initialize Google Pay button ID
            elementId: 'gfsq-google-pay-' + id
        },
        applePay: {
            elementId: 'gfsq-apple-pay-' + id
        },
        /*masterpass: {
            elementId: 'gfsq-masterpass-' + id
        },*/
        callbacks: {
            cardNonceResponseReceived: function (errors, nonce, cardData) {
                //alert(nonce);
                //debugger;
                gfsqs_pay_from_nonce(errors, nonce, id, square_cof_mode, is_recurring, currency_charge, billing_fname, billing_lname );                
            },
			createPaymentRequest: function() {
                
            },
            paymentFormLoaded: function () {

            },
            methodsSupported: function (methods, unsupportedReason) {

                console.log(methods);
                console.log(unsupportedReason);

                // Only show the button if Google Pay on the Web is enabled
                if ( is_googlepay === 'enabled' && is_recurring === 'disabled' ) {
                    //console.log('google pay is '+ is_googlepay + ' and is recurring '+ is_recurring);
                    var googlePayBtn = document.getElementById('gfsq-google-pay-' + id);                    
                    if (methods.googlePay === true) {
                        googlePayBtn.style.display = 'inline-block';
                        //console.log(methods);
                    } else if(methods.googlePay === false) {
                        jQuery('#gfsq-google-pay-' + id +'-wrapper').html('<span style="color:red;font-size:14px;">'+ unsupportedReason.type + ' - ' + unsupportedReason.message +'</span>');
                        //console.log(unsupportedReason);
                    }
                }

                // Only show the button if Apple Pay on the Web is enabled
                if ( is_applepay === 'enabled' && is_recurring === 'disabled' ) {
                    //console.log('Apple pay is '+ is_applepay + ' and is recurring '+ is_recurring);
                    var applePayBtn = document.getElementById('gfsq-apple-pay-' + id);                    
                    if (methods.applePay === true) {
                        applePayBtn.style.display = 'inline-block';
                        //console.log(methods);
                    } else if(methods.applePay === false) {
                        jQuery('#gfsq-apple-pay-' + id +'-wrapper').html('<span style="color:red;font-size:14px;">'+ unsupportedReason.type + ' - ' + unsupportedReason.message +'</span>');
                        //console.log(unsupportedReason);
                    }
                }

                // Only show the button if masterpass on the Web is enabled
                /*if ( is_masterpass === 'enabled' && is_recurring === 'disabled' ) {
                    //console.log('masterpass is '+ is_masterpass + ' and is recurring '+ is_recurring);
                    var masterpassBtn = document.getElementById('gfsq-masterpass-' + id);  
                                    
                    if (methods.masterpass === true) {
                        masterpassBtn.style.display = 'inline-block';
                        masterpassBtn.style.backgroundImage =`url(${wcSquarePaymentForm[id].masterpassImageUrl()})`;  
                        //console.log(methods);
                    } else if (methods.masterpass === false) {
                        jQuery('#gfsq-masterpass-' + id +'-wrapper').html('<span style="color:red;font-size:14px;">'+ unsupportedReason.type + ' - ' + unsupportedReason.message +'</span>');
                        //console.log(unsupportedReason);
                    }
                }*/

            },
            inputEventReceived: function (inputEvent) {
                switch (inputEvent.eventType) {
                    case 'focusClassAdded':
                        /* HANDLE AS DESIRED */
                        if (inputEvent.field == "cvv") {
                            jQuery('#' + inputEvent.elementId).parent('.element-toRight').siblings('.element-toLeft').find('.gfsq-ccard-container .gfsq-card').addClass('rotate');
                        } else {
                            jQuery('#' + inputEvent.elementId).parent('.element-toRight').siblings('.element-toLeft').find('.gfsq-ccard-container .gfsq-card').removeClass('rotate');
                            jQuery('#' + inputEvent.elementId).parent('.element-toLeft').find('.gfsq-ccard-container .gfsq-card').removeClass('rotate');
                        }
                        //console.log(inputEvent);
                        jQuery('#' + inputEvent.elementId).siblings('.gfsq-ccard-container').find('.gfsq-front').attr('class', 'gfsq-front');
                        jQuery('#' + inputEvent.elementId).siblings('.gfsq-ccard-container').find('.gfsq-back').attr('class', 'gfsq-back');
                        
                    case 'focusClassRemoved':
                        /* HANDLE AS DESIRED */
                        
                    case 'errorClassAdded':
                        /* HANDLE AS DESIRED */
                        
                    case 'errorClassRemoved':
                        /* HANDLE AS DESIRED */
                        
                    case 'cardBrandChanged':
                        /* HANDLE AS DESIRED */
                        jQuery('#' + inputEvent.elementId).siblings('.gfsq-ccard-container').find('.gfsq-front').attr('class', 'gfsq-front');
                        jQuery('#' + inputEvent.elementId).siblings('.gfsq-ccard-container').find('.gfsq-back').attr('class', 'gfsq-back');
                        //console.log(inputEvent);
                        var cardType = inputEvent.cardBrand
                        jQuery('#' + inputEvent.elementId).siblings('.gfsq-ccard-container').find('.gfsq-front').addClass(cardType);
                        jQuery('#' + inputEvent.elementId).siblings('.gfsq-ccard-container').find('.gfsq-back').addClass(cardType);
                        
                    case 'postalCodeChanged':
                        /* HANDLE AS DESIRED */
                }
            },
            unsupportedBrowserDetected: function () {

            }
        }
    });
	console.log(jQuery('.gf_square_payment_toggler').length);
    if (jQuery('.gf_square_payment_toggler').length > 0) {
		
		
        wcSquarePaymentForm[id].build();
    } else if (jQuery('#gfsq-card-number-'+id).length > 0) {
        wcSquarePaymentForm[id].build();
    }

    jQuery('form#gform_' + id + ' .sqgf_square_nonce').remove();
	
} // end of run_gravity_square function

function gfsqs_pay_from_nonce( errors, nonce, id, square_cof_mode, is_recurring, currency_charge, billing_fname, billing_lname ) {
    if (errors) {
        var html = '';
        jQuery('#gform_submit_button_' + id).prop('disabled', false);
        html += '<ul class="sqgf-errors gfield_error">';

        // handle errors
        jQuery(errors).each(function (index, error) {
            html += '<li class="gfield_description validation_message">' + error.message + '</li>';
        });

        html += '</ul>';

        // append it to DOM
        //me.closest('.gform_wrapper form').find('.messages').html(html);
        jQuery('form#gform_' + id).find('.messages').html(html);

    } else {
        if( jQuery.trim(nonce) && typeof nonce != 'undefined' ){
            
            /*gform.addFilter( 'gform_product_total', function(total, formId){
                totalAmount = total;
                //alert(totalAmount);
            });*/

            totalAmount = jQuery('#gform_' + id + ' .ginput_container_total .ginput_total').val();

            //alert('amout '+ totalAmount);

            //debugger;
            
            if ( jQuery('#save_card_for_future_'+ id).is(':checked') ) {
                save_card = true;
            }
            
            if ( (square_cof_mode=="enabled" && save_card==true) || is_recurring=="enabled" ) {
                //debugger;
                const verificationDetails = { 
                    intent: 'STORE', 
                    billingContact: {
                        familyName: billing_lname,
                        givenName: billing_fname
                    }
                };

                wcSquarePaymentForm[id].verifyBuyer(
                    nonce,
                    verificationDetails,
                    function(err, verificationResult) {
                        if (err == null) { 
                            jQuery('.gform_wrapper form#gform_'+ id).find('.gform_body').append('<input type="hidden" class="sqgf_square_nonce" name="sqgf_square_nonce" value="' + nonce + '" />');
                            jQuery('.gform_wrapper form#gform_'+ id).find('.gform_body').append('<input type="hidden" class="sqgf_square_nonce" name="sqgf_square_verify" value="' + verificationResult.token + '" />');
                            jQuery('#gform_submit_button_' + id).prop('disabled', false);
                            jQuery('.gform_wrapper form#gform_'+ id).submit();
                            jQuery('#gform_submit_button_' + id).prop('disabled', true);
                        }
                    }
                );
            } else {
                totalAmount = parseFloat( extractFloat( totalAmount ) ).toFixed(2);//totalAmount.toFixed(2);
                const verificationDetails = {
                    intent: 'CHARGE',
                    amount: totalAmount.toString(),
                    currencyCode: currency_charge,
                    billingContact: {
                        familyName: billing_lname,
                        givenName: billing_fname
                    }
                };

                wcSquarePaymentForm[id].verifyBuyer(
                    nonce,
                    verificationDetails,
                    function(err, verificationResult) {
                        if (err == null) {
                            jQuery('.gform_wrapper form#gform_'+ id).find('.gform_body').append('<input type="hidden" class="sqgf_square_nonce" name="sqgf_square_nonce" value="' + nonce + '" />');
                            jQuery('.gform_wrapper form#gform_'+ id).find('.gform_body').append('<input type="hidden" class="sqgf_square_nonce" name="sqgf_square_verify" value="' + verificationResult.token + '" />');
                            jQuery('#gform_submit_button_' + id).prop('disabled', false);
                            jQuery('.gform_wrapper form#gform_'+ id).submit();
                            jQuery('#gform_submit_button_' + id).prop('disabled', true);
                        }
                    }
                );
            }    
        } else {
            jQuery('#gform_submit_button_' + id).prop('disabled', false);
            var html = '';
            html += '<ul class="sqgf-errors gfield_error">';
            // handle errors
            html += '<li class="gfield_description validation_message">Credit card nonce not found contact system admin</li>';
            html += '</ul>';
            // append it to DOM
            jQuery('.gform_wrapper form#gform_'+ id).find('.messages').html(html);
            return false;

        }
    }
}

jQuery(document).ready(function ($) {
    // jQuery('.gform_wrapper form').each(function () {        
    //     var form_id = jQuery(this).find('input.form_id').val();
    //     run_gravity_square(form_id);
    // });

    jQuery('input[name="gfsqs-other-payments"]').on('click', function(){
        me = jQuery(this);
        parent = me.parents('form');
        id = parent.find('.form_id').val();
        if( jQuery('input.gift-card-radio').is(':checked') ) {
            console.log('gift card checkbox with form id ' + id);
            giftCardForm = createGiftCardForm(id);
            giftCardForm.build();
        }

        //hide submit button if google, apple or masterpass is showing
        if( me.is(':checked') && ('google'===me.data('type') || 'apple'===me.data('type') || 'masterpass'===me.data('type')) ) {
            parent.find('.gform_button').css('visibility', 'hidden');
        } else {
            parent.find('.gform_button').css('visibility', 'visible');
        }
        
        var val = me.val();
        jQuery('.gfsqs-digital-pay-wrapper').hide();
        me.parents('label').siblings('label').find('input[name="gfsqs-other-payments"]').prop('checked', false);
        //jQuery(this).prop('checked', true);
        if( me.is(':checked') ) {
            jQuery('#'+val).show();
        }
    });

    jQuery('.gform_wrapper .button-google-pay').on('click', function () {
        me = jQuery(this);
        parent = me.parents('form');
        id = parent.find('.form_id').val();
        return false;
        //debugger;
    });

    jQuery('.gform_wrapper .apple-pay-button').on('click', function () {
        me = jQuery(this);
        parent = me.parents('form');
        id = parent.find('.form_id').val();
        return false;
        //debugger;
    });

    jQuery('.gform_wrapper .button-masterpass').on('click', function () {
        me = jQuery(this);
        parent = me.parents('form');
        id = parent.find('.form_id').val();
        return false;
        //debugger;
    });

    jQuery('.gform_wrapper .gform_button').on('click', function (event) {
        event.preventDefault();
       
        me = jQuery(this);
        parent = me.parents('form');
        id = parent.find('.form_id').val();

        if ( parent.find('.gf-multiple').length > 0 && !(parent.find('.gf-multiple').is(":visible")) ) {
            jQuery('.gform_wrapper form#gform_'+ id).find('.gform_body').append('<input type="hidden" name="sqgf_no_square" value="yes" />');
            me.closest('.gform_wrapper form').submit();
            return false;
        } else {

            if ( jQuery(parent).find('input.gift-card-radio').is(':checked') ) {
                
                nonceGiftCard(event);
            }else {
                if ( jQuery(parent).find('input[data-id="existing_card_paymment"]').is(':checked') ) {
                    //alert('existing card!');
                    me.closest('.gform_wrapper form').submit();
                } else {
                    if (me.closest('form').find('#gfsq-card-number-'+id).length > 0){
                        if (jQuery(parent).find('.gf_sqquare_container').is(':visible')) {
                            // remove any error messages first
                            jQuery('form#gform_' + id + ' .messages').find('.sqgf-errors').remove();
                            me.prop('disabled', true);
                            wcSquarePaymentForm[id].requestCardNonce();
                            //run_gravity_square(id);
                            return false;
                        } else {
                            me.closest('.gform_wrapper form').submit(); 
                        }
                    }
                }
            }
        }
    });

    jQuery(document).on('click', '.gf_square_payment_toggler', function(){
        var parent = jQuery(this).parents('form');
        if ( jQuery(this).is(':checked') ) {
            var val = jQuery(this).val();
            jQuery(parent).find('.gf-payment-activated').removeClass('gf-payment-activated');
            jQuery('#' + val).addClass('gf-payment-activated');
        }
    });

    jQuery(document).on('click', 'div.gf-stored-cards > div.credit-card', function(){
        var parent = jQuery(this).parents('form');
        var val = jQuery(this).attr('card-id');
        jQuery(parent).find('.gf-selected').removeClass('gf-selected');
        jQuery(this).addClass('gf-selected');
        jQuery(this).find('input[type="radio"]').prop('checked', true);
    });
});

jQuery(document).on('gform_post_render', function(event, form_id, current_page){
    //alert('test');
    var isHidden = document.getElementById("gform_wrapper_" + form_id).style.display == "none";
    
    if (isHidden) {
        jQuery('#gform_wrapper_' + form_id).show();
    }
    if ( gfsqs.application_id  ) {
		setTimeout(function(){
			
			
            run_gravity_square(form_id);
        }, 1000);
    }

    if (gfsqs.fname!='') {
        jQuery('#gform_fields_'+ form_id + ' .name_first').find('input').val(gfsqs.fname);
    }

    if (gfsqs.lname!='') {
        jQuery('#gform_fields_'+ form_id + ' .name_last').find('input').val(gfsqs.lname);
    }
    
    if (gfsqs.email!='') {
        jQuery('#gform_fields_'+ form_id + ' .ginput_container_email').find('input').val(gfsqs.email);
    }
    

    if ( jQuery('#gform_wrapper_'+form_id).hasClass('gform_validation_error') ) {
        jQuery('html, body').animate({
            scrollTop: jQuery('#gform_wrapper_'+form_id).offset().top
        }, 2000);    
    }

    //var toggler_value = jQuery('form#gform_' + form_id + ' .gf_square_payment_toggler:checked').val();
    if ( ( jQuery('.validation_message').is(":visible") || jQuery('.gf-multiple').is(":visible") ) && !jQuery('.saved_card_wrapper').is(":visible")	) {
        jQuery('#gf_sqquare_container_' + form_id).addClass('gf-payment-activated');
        jQuery('.gf_square_payment_toggler').prop('checked', true);
    }

    /*if ( ( jQuery('.validation_message').is(":visible") || jQuery('.gf-multiple').is(":visible") ) && jQuery('.saved_card_wrapper').is(":visible")	) {
        jQuery('#gf_sqquare_saved_card_container_' + form_id).addClass('gf-payment-activated');
        jQuery('#gf_sqquare_saved_card_container_' + form_id).siblings('input.gf_square_payment_toggler').prop('checked', true);
    }*/
   
    if ( !(jQuery('.gf_square_payment_toggler').length > 0) ) {
        jQuery('#gf_sqquare_container_' + form_id).addClass('gf-payment-activated');
        jQuery('.gf_square_payment_toggler').prop('checked', true);
    }

});

/**
 * gift card
 */



function nonceGiftCard(event) {
    event.preventDefault();
    giftCardForm.requestCardNonce();
}

function createGiftCardForm(id) {
    var application_id = jQuery('form#gform_'+id+' .application_id').val();
    //Initialize the payment form for gift cards
    return new SqPaymentForm({
        applicationId: application_id,
        inputClass: 'gfsq-input',
        inputStyles: jQuery.parseJSON(gfsqs.payment_form_input_styles),
        giftCard: {
            elementId: 'gfsq-giftcard-' + id,
            placeholder: gfsqs.placeholder_card_number
        },
        callbacks: {
        cardNonceResponseReceived: function(errors, nonce, paymentData, contacts) {
            
                postData(nonce, orderID, id ).then( function(response) {
        
                    if (response.status !== undefined && response.status === "FAILED") {
                        //alert('Card denied:' + response[0].code + ' - ' + response[0].detail);
                        var html = '';
                        html += '<ul class="sqgf-errors gfield_error" style="margin:0">';
                        // handle errors
                        html += '<li class="gfield_description validation_message" style="margin:0;padding:0">' + 'Card denied:' + response[0].code + ' - ' + response[0].detail + '</li>';
                        html += '</ul>'; 
                        // append it to DOM
                        jQuery('.gform_wrapper form#gform_'+ id).find('.digital-message').html(html);
                        return;
                    }

                    if ( response.status !== undefined && response.status === "PRICE_0") {
                        //alert(response.detail);
                        var html = '';
                        html += '<ul class="sqgf-errors gfield_error" style="margin:0">';
                        // handle errors
                        html += '<li class="gfield_description validation_message" style="margin:0;padding:0">' + response.detail + '</li>';
                        html += '</ul>'; 
                        // append it to DOM
                        jQuery('.gform_wrapper form#gform_'+ id).find('.digital-message').html(html);
                        return;
                    }

                    if ( response.status !== undefined && response.status == "COMPLETED" ) {
                        
                        jQuery('.gform_wrapper form#gform_'+ id).find('.gform_body').append('<input type="hidden" name="pay_with_giftcard" value="yes" />');
                        jQuery('.gform_wrapper form#gform_'+ id).find('.gform_body').append("<input type='hidden' name='giftcard_nonce' value='"+ JSON.stringify(response.nonce) +"'  />");
                        jQuery( "form#gform_"+id ).submit();
                    }
                });
        }
        }
    });
}

//POST the Gift Card nonce to a backend service to be processed as a payment
function postData(nonce, order_id, form_id) {
    if (nonce === 'undefined' ) {
      throw new TypeError('`nonce` is required');
    }
    if (order_id === 'undefined') {
      throw new TypeError('`order_id` is required');
    }

    let data = {
      nonce: nonce,
      orderID : order_id,
      form_id : form_id,
      action: "gfsqs_process_giftcard",
      totalAmount : jQuery('.ginput_container_total').find('input.ginput_total').val(),
    };

    return fetch(gfsqs.ajaxUrl, {
        method: 'POST',      
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cache-Control': 'no-cache',
        },
        redirect: 'follow',
        referrer: 'no-referrer',
        body: new URLSearchParams(data),
    }).then(
        response => response.json()
    );
}

//delete card on file function
jQuery('.gfsqs-trigger-btn').on('click', function(e){
    e.preventDefault();
    var current = jQuery(this);
    var card_id = current.parent('div.credit-card').data('card-id');    
    var form_id = current.parents('form').find('.form_id').val();
    jQuery('#gfsqs-confirmation').find('.credit-card-delete').attr('data-card-id', card_id);
    jQuery('#gfsqs-confirmation').find('.credit-card-delete').attr('data-form-id', form_id);
    var template = '<button type="button" class="btn btn-secondary" data-dismiss="modal">'+ gfsqs.cancel_text +'</button><button type="button" data-form-id="'+ form_id +'" data-card-id="'+ card_id +'" class="btn btn-danger credit-card-delete">'+ gfsqs.delete_text +'</button>';
    jQuery('.modal-footer').html(template);
    jQuery('.modal-body p').show();
    jQuery('.modal-body p.in-process').hide();
    jQuery('.modal-body p.alert-danger').hide();
    jQuery('.modal-body p.alert-success').hide();
    jQuery('#gfsqs-confirmation').modal('show');
});

jQuery(document).on('click', '.credit-card-delete', function(e){
    e.preventDefault();
    var current = jQuery(this);
    var card_id = current.data('card-id');    
    var form_id = current.data('form-id');
    
    jQuery.ajax({
		url: gfsqs.ajaxUrl,
		type: "POST",
		dataType: "json",
		data: {
            action: 'cof_delete_card',
            card_id: card_id,
            form_id: form_id,
		},
		beforeSend: function () {
            current.parents('.modal').find('.modal-body p').hide();
            current.parents('.modal').find('.modal-body p.in-process').show();
        },
		success: function (response) {
            var stringified = JSON.stringify(response);
            var parsedObj = JSON.parse(stringified);
            
            
			if (!parsedObj['errors']) {
                current.parents('.modal').find('.modal-body p').hide();
                current.parents('.modal').find('.modal-body p.alert-success').show();              
                //removing card element from DOM
                jQuery('div.credit-card[data-card-id="'+ card_id +'"]').remove();
                current.parents('.modal').find('.modal-footer').html('');
            } else {
                current.parents('.modal').find('.modal-body p').hide();
                current.parents('.modal').find('.modal-body p.alert-danger').html(parsedObj[0].code + ' - ' + parsedObj[0].detail);
            }
		}
	});
});

function extractFloat(text) {
    const match = text.match(/\d+((\.|,)\d+)?/)
    return match && match[0]
}