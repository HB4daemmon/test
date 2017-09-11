/**
 * Cryozonic
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Single Domain License
 * that is available through the world-wide-web at this URL:
 * http://cryozonic.com/licenses/stripe.html
 * If you are unable to obtain it through the world-wide-web,
 * please send an email to info@cryozonic.com so we can send
 * you a copy immediately.
 *
 * @category   Cryozonic
 * @package    Cryozonic_Stripe
 * @version    2.7.3
 * @build      RC3014
 * @copyright  Copyright (c) Cryozonic Ltd (http://cryozonic.com)
 */

var stripeTokens = {};

var initStripe = function(apiKey, securityMethod)
{
    cryozonic.securityMethod = securityMethod;

    var onLoad = function()
    {
        if (securityMethod == 1)
        {
            Stripe.setPublishableKey(apiKey);
            cryozonic.stripe = Stripe;
        }
        else
            cryozonic.stripe = Stripe(apiKey);

        initLoadedStripe();

        cryozonic.dispatchEvent('stripeJsInitialized');
    };

    // Load Stripe.js dynamically
    if (typeof Stripe == "undefined")
    {
        var resource = document.createElement('script');
        if (securityMethod == 1)
            resource.src = "https://js.stripe.com/v2/";
        else
            resource.src = "https://js.stripe.com/v3/";

        resource.onload = onLoad;
        resource.onerror = function(evt) {
            console.warn("Stripe.js could not be loaded");
            console.error(evt);
        };
        var script = document.getElementsByTagName('script')[0];
        script.parentNode.insertBefore(resource, script);
    }
    else
        onLoad();

    // Disable server side card validation when Stripe.js is enabled
    if (typeof AdminOrder != 'undefined' && AdminOrder.prototype.loadArea && typeof AdminOrder.prototype._loadArea == 'undefined')
    {
        AdminOrder.prototype._loadArea = AdminOrder.prototype.loadArea;
        AdminOrder.prototype.loadArea = function(area, indicator, params)
        {
            if (typeof area == "object" && area.indexOf('card_validation') >= 0)
                area = area.splice(area.indexOf('card_validation'), 0);

            if (area.length > 0)
                this._loadArea(area, indicator, params);
        };
    }

    // Integrate Stripe.js with various One Step Checkout modules
    initOSCModules();
    cryozonic.onWindowLoaded(initOSCModules); // Run it again after the page has loaded in case we missed an ajax based OSC module

    // Integrate Stripe.js with the multi-shipping payment form
    cryozonic.onWindowLoaded(initMultiShippingForm);

    // Integrate Stripe.js with the admin area
    cryozonic.onWindowLoaded(initAdmin); // Needed when refreshing the browser
    initAdmin(); // Needed when the payment method is loaded through an ajax call after adding the shipping address
};

// Initializations that depend on Stripe.js being loaded and ready
var initLoadedStripe = function()
{
    // Enable Apple Pay for supported devices
    initApplePay();
    cryozonic.onWindowLoaded(initApplePay);

    initStripeElements();
    cryozonic.onWindowLoaded(initStripeElements);
};

var initStripeElements = function()
{
    if (cryozonic.securityMethod != 2)
        return;

    if (document.getElementById('card-element') === null)
        return;

    // Custom styling can be passed to options when creating an Element.
    var style = {
        base: {
            // Add your base input styles here. For example:
            fontSize: '16px',
            lineHeight: '24px'
    //         iconColor: '#666EE8',
    //         color: '#31325F',
    //         fontWeight: 300,
    //         fontFamily: '"Helvetica Neue", Helvetica, sans-serif',

    //         '::placeholder': {
    //             color: '#CFD7E0'
    //         }
        }
    };

    var elements = cryozonic.stripe.elements();

    // Create an instance of the card Element
    var card = cryozonic.card = elements.create('card', {style: style});

    // Add an instance of the card Element into the `card-element` <div>
    card.mount('#card-element');

    card.addEventListener('change', function(event)
    {
        if (event.error)
            cryozonic.displayCardError(event.error.message);
        else
            cryozonic.clearCardErrors();
    });
};

var cryozonic = {
    // Properties
    billingInfo: null,
    multiShippingFormInitialized: false,
    oscInitialized: false,
    applePayButton: null,
    applePaySuccess: false,
    applePayResponse: null,
    securityMethod: 0,
    card: null,
    paymentFormValidator: null,

    // Methods
    onWindowLoaded: function(callback)
    {
        if (window.attachEvent)
            window.attachEvent("onload", callback); // IE
        else
            window.addEventListener("load", callback); // Other browsers
    },
    validatePaymentForm: function(callback)
    {
        if (!this.paymentFormValidator)
            this.paymentFormValidator = new Validation('payment_form_cryozonic_stripe');

        if (!this.paymentFormValidator.form)
            this.paymentFormValidator = new Validation('new-card');

        if (!this.paymentFormValidator.form)
            return true;

        this.paymentFormValidator.reset();

        return this.paymentFormValidator.validate();
    },
    placeAdminOrder: function(e)
    {
        var radioButton = document.getElementById('p_method_cryozonic_stripe');
        if (radioButton && !radioButton.checked)
            return order.submit();

        createStripeToken(function(err)
        {
            if (err)
                alert(err);
            else
                order.submit();
        });
    },
    addAVSFieldsTo: function(cardDetails)
    {
        var owner = cryozonic.getSourceOwner();
        cardDetails.name = owner.name;
        cardDetails.address_line1 = owner.address.line1;
        cardDetails.address_line2 = owner.address.line2;
        cardDetails.address_zip = owner.address.postal_code;
        cardDetails.address_city = owner.address.city;
        cardDetails.address_state = owner.address.state;
        cardDetails.address_zip = owner.address.postal_code;
        cardDetails.address_country = owner.address.country;
        return cardDetails;
    },
    getSourceOwner: function()
    {
        // Format is
        var owner = {
            name: null,
            email: null,
            phone: null,
            address: {
                city: null,
                country: null,
                line1: null,
                line2: null,
                postal_code: null,
                state: null
            }
        };

        // If there is an address select dropdown, don't read the address from the input fields in case
        // the customer changes the address from the dropdown. Dropdown value changes do not update the
        // plain input fields
        if (!document.getElementById('billing-address-select'))
        {
            // Scenario 1: We are in the admin area creating an order for a guest who has no saved address yet
            var line1 = document.getElementById('order-billing_address_street0');
            var postcode = document.getElementById('order-billing_address_postcode');
            var email = document.getElementById('order-billing_address_email');

            // Scenario 2: Checkout page with an OSC module and a guest customer
            if (!line1)
                line1 = document.getElementById('billing:street1');

            if (!postcode)
                postcode = document.getElementById('billing:postcode');

            if (!email)
                email = document.getElementById('billing:email');

            if (line1)
                owner.address.line1 = line1.value;

            if (postcode)
                owner.address.postal_code = postcode.value;

            if (email)
                owner.email = email.value;

            // New fields
            if (document.getElementById('billing:firstname'))
                owner.name = document.getElementById('billing:firstname').value + ' ' + document.getElementById('billing:lastname').value;

            if (document.getElementById('billing:telephone'))
                owner.phone = document.getElementById('billing:telephone').value;

            if (document.getElementById('billing:city'))
                owner.address.city = document.getElementById('billing:city').value;

            if (document.getElementById('billing:country_id'))
                owner.address.country = document.getElementById('billing:country_id').value;

            if (document.getElementById('billing:street2'))
                owner.address.line2 = document.getElementById('billing:street2').value;

            if (document.getElementById('billing:region'))
                owner.address.state = document.getElementById('billing:region').value;
        }

        // Scenario 3: Checkout or admin area and a registered customer already has a pre-loaded billing address
        if (cryozonic.billingInfo !== null)
        {
            if (owner.email === null && cryozonic.billingInfo.email !== null)
                owner.email = cryozonic.billingInfo.email;

            if (owner.address.line1 === null && cryozonic.billingInfo.line1 !== null)
            {
                owner.address.line1 = cryozonic.billingInfo.line1;
                owner.address.postal_code = cryozonic.billingInfo.postcode;
            }

            // New fields
            if (owner.name === null && cryozonic.billingInfo.name !== null)
                owner.name = cryozonic.billingInfo.name;

            if (owner.phone === null && cryozonic.billingInfo.phone !== null)
                owner.phone = cryozonic.billingInfo.phone;

            if (owner.address.city === null && cryozonic.billingInfo.city !== null)
                owner.address.city = cryozonic.billingInfo.city;

            if (owner.address.country === null && cryozonic.billingInfo.country !== null)
                owner.address.country = cryozonic.billingInfo.country;

            if (owner.address.line2 === null && cryozonic.billingInfo.line2 !== null)
                owner.address.line2 = cryozonic.billingInfo.line2;

            if (owner.address.state === null && cryozonic.billingInfo.state !== null)
                owner.address.state = cryozonic.billingInfo.state;
        }

        return owner;
    },
    displayCardError: function(message)
    {
        // Some OSC modules have the Place Order button away from the payment form
        if (cryozonic.oscInitialized)
        {
            alert(message);
            return;
        }

        // When we use a saved card, display the message as an alert
        var newCardRadio = document.getElementById('new_card');
        if (newCardRadio && !newCardRadio.checked)
        {
            alert(message);
            return;
        }

        var box = $('cryozonic-stripe-card-errors');

        if (box)
        {
            box.update(message);
            box.addClassName('populated');
        }
        else
            alert(message);
    },
    clearCardErrors: function()
    {
        var box = $('cryozonic-stripe-card-errors');

        if (box)
        {
            box.update("");
            box.removeClassName('populated');
        }
    },
    dispatchEvent: function(eventName)
    {
        var event = new Event(eventName);
        document.dispatchEvent(event);
    },
    is3DSecureEnabled: function()
    {
        return (typeof params3DSecure != 'undefined' && params3DSecure && !isNaN(params3DSecure.amount));
    }
};

var initAdmin = function()
{
    var btn = document.getElementById('order-totals');
    if (btn) btn = btn.getElementsByTagName('button');
    if (btn && btn[0]) btn = btn[0];
    if (btn) btn.onclick = cryozonic.placeAdminOrder;

    var topBtns = document.getElementsByClassName('save');
    for (var i = 0; i < topBtns.length; i++)
    {
        topBtns[i].onclick = cryozonic.placeAdminOrder;
    }
};

var shouldUse3DSecure = function(response)
{
    return (cryozonic.is3DSecureEnabled() &&
            typeof response.card.three_d_secure != 'undefined' &&
            ['optional', 'required'].indexOf(response.card.three_d_secure) >= 0);
};

var cryozonicSetLoadWaiting = function(section)
{
    // Check if defined first in case of an OSC module rewriting the whole thing
    if (typeof checkout != 'undefined' && checkout && checkout.setLoadWaiting)
    {
        try
        {
            // OSC modules may also cause crashes if they have stripped away the html elements
            checkout.setLoadWaiting(section);
        }
        catch (e) {}
    }
    else
        cryozonicToggleAdminSave(section);
};

var cryozonicToggleAdminSave = function(disable)
{
    if (typeof disableElements != 'undefined' && typeof enableElements != 'undefined')
    {
        if (disable)
            disableElements('save');
        else
            enableElements('save');
    }
};

var initApplePay = function()
{
    // Some OSC modules will refuse to reload the payment method when the billing address is changed for a customer.
    // We can't use Apple Pay without a billing address
    if (typeof paramsApplePay == "undefined" || !paramsApplePay)
        return;

    var payButton = document.getElementById('apple-pay-button');
    if (!payButton)
        return;
    // For OSC modules that do reload the payment form, more than once, we deduplicate event binding here
    else if (payButton == cryozonic.applePayButton)
        return;
    else
        cryozonic.applePayButton = payButton;

    // Binding the event from javascript instead of markup can capture both clicks and touch events
    payButton.addEventListener('click', beginApplePay);

    var resetButton = document.getElementById('apple-pay-reset');
    resetButton.addEventListener('click', resetApplePayToken);

    Stripe.applePay.checkAvailability(function(available)
    {
        if (available)
            $('payment_form_cryozonic_stripe').addClassName('apple-pay-supported');
    });
};

var beginApplePay = function()
{
    var paymentRequest = paramsApplePay;

    var session = Stripe.applePay.buildSession(paymentRequest, function(result, completion)
    {
        setStripeToken(result.token.id);

        completion(ApplePaySession.STATUS_SUCCESS);

        setApplePayToken(result.token);
    },
    function(error)
    {
        alert(error.message);
    });

    session.begin();
};

var setApplePayToken = function(token)
{
    var radio = document.querySelector('input[name="payment[cc_saved]"]:checked');
    if (!radio || (radio && radio.value == 'new_card'))
        disablePaymentFormValidation();

    if ($('new_card'))
        $('new_card').removeClassName('validate-one-required-by-name');

    $('apple-pay-result-brand').update(token.card.brand);
    $('apple-pay-result-last4').update(token.card.last4);
    $('payment_form_cryozonic_stripe').addClassName('apple-pay-success');
    $('apple-pay-result-brand').className = "type " + token.card.brand;
    cryozonic.applePaySuccess = true;
    cryozonic.applePayToken = token;
};

var resetApplePayToken = function()
{
    var radio = document.querySelector('input[name="payment[cc_saved]"]:checked');
    if (!radio || (radio && radio.value == 'new_card'))
        enablePaymentFormValidation();

    if ($('new_card'))
        $('new_card').addClassName('validate-one-required-by-name');

    $('payment_form_cryozonic_stripe').removeClassName('apple-pay-success');
    $('apple-pay-result-brand').update();
    $('apple-pay-result-last4').update();
    $('apple-pay-result-brand').className = "";
    deleteStripeToken();
    cryozonic.applePaySuccess = false;
    cryozonic.applePayToken = null;
};

var getCardDetails = function()
{
    // Validate the card
    var cardName = document.getElementById('cryozonic_stripe_cc_owner');
    var cardNumber = document.getElementById('cryozonic_stripe_cc_number');
    var cardCvc = document.getElementById('cryozonic_stripe_cc_cid');
    var cardExpMonth = document.getElementById('cryozonic_stripe_expiration');
    var cardExpYear = document.getElementById('cryozonic_stripe_expiration_yr');

    var isValid = cardName && cardName.value && cardNumber && cardNumber.value && cardCvc && cardCvc.value && cardExpMonth && cardExpMonth.value && cardExpYear && cardExpYear.value;

    if (!isValid) return null;

    var cardDetails = {
        name: cardName.value,
        number: cardNumber.value,
        cvc: cardCvc.value,
        exp_month: cardExpMonth.value,
        exp_year: cardExpYear.value
    };

    cardDetails = cryozonic.addAVSFieldsTo(cardDetails);

    return cardDetails;
};

var createStripeToken = function(callback)
{
    cryozonic.clearCardErrors();

    if (!cryozonic.validatePaymentForm())
        return;

    cryozonicSetLoadWaiting('payment');
    var done = function(err)
    {
        cryozonicSetLoadWaiting(false);
        return callback(err);
    };

    if (cryozonic.applePaySuccess)
        return done();

    // First check if the "Use new card" radio is selected, return if not
    var cardDetails, newCardRadio = document.getElementById('new_card');
    if (newCardRadio && !newCardRadio.checked)
        return done();

    // Stripe.js v3 + Stripe Elements
    if (cryozonic.securityMethod == 2)
    {
        cryozonic.stripe.createSource(cryozonic.card, { owner: cryozonic.getSourceOwner() }).then(function(result)
        {
            if (result.error)
                return done(result.error.message);

            var cardKey = result.source.id;
            var token = result.source.id + ':' + result.source.card.brand + ':' + result.source.card.last4;
            stripeTokens[cardKey] = token;
            setStripeToken(token);

            return done();
        });
    }
    // Stripe.js v2
    else
    {
        cardDetails = getCardDetails();

        if (!cardDetails)
            return done('Invalid card details');

        var cardKey = JSON.stringify(cardDetails);

        if (stripeTokens[cardKey])
        {
            setStripeToken(stripeTokens[cardKey]);
            return done();
        }
        else
            deleteStripeToken();

        Stripe.card.createToken(cardDetails, function (status, response)
        {
            if (response.error)
                return done(response.error.message);

            var token = response.id + ':' + response.card.brand + ':' + response.card.last4;
            stripeTokens[cardKey] = token;
            setStripeToken(token);
            return done();
        });
    }
};

function setStripeToken(token)
{
    try
    {
        var input, inputs = document.getElementsByClassName('cryozonic-stripejs-token');
        if (inputs && inputs[0]) input = inputs[0];
        else input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "payment[cc_stripejs_token]");
        input.setAttribute("class", 'cryozonic-stripejs-token');
        input.setAttribute("value", token);
        input.disabled = false; // Gets disabled when the user navigates back to shipping method
        var form = document.getElementById('payment_form_cryozonic_stripe');
        if (!form) form = document.getElementById('co-payment-form');
        if (!form) form = document.getElementById('order-billing_method_form');
        if (!form) form = document.getElementById('onestepcheckout-form');
        if (!form && typeof payment != 'undefined') form = document.getElementById(payment.formId);
        if (!form)
        {
            form = document.getElementById('new-card');
            input.setAttribute("name", "newcard[cc_stripejs_token]");
        }
        form.appendChild(input);
    } catch (e) {}
}

function deleteStripeToken()
{
    var input, inputs = document.getElementsByClassName('cryozonic-stripejs-token');
    if (inputs && inputs[0]) input = inputs[0];
    if (input && input.parentNode) input.parentNode.removeChild(input);
}

// Multi-shipping form support for Stripe.js
var multiShippingForm = null, multiShippingFormSubmitButton = null;

function submitMultiShippingForm(e)
{
    if (payment.currentMethod != 'cryozonic_stripe')
        return true;

    if (e.preventDefault) e.preventDefault();

    if (!multiShippingFormSubmitButton) multiShippingFormSubmitButton = document.getElementById('payment-continue');
    if (multiShippingFormSubmitButton) multiShippingFormSubmitButton.disabled = true;

    createStripeToken(function(err)
    {
        if (multiShippingFormSubmitButton) multiShippingFormSubmitButton.disabled = false;

        if (err && err == three_d_secure_canceled)
            console.error(three_d_secure_canceled);
        else if (err)
            cryozonic.displayCardError(err);
        else
            multiShippingForm.submit();
    });

    return false;
}

// Multi-shipping form
var initMultiShippingForm = function()
{
    if (typeof payment == 'undefined' ||
        payment.formId != 'multishipping-billing-form' ||
        cryozonic.multiShippingFormInitialized)
        return;

    multiShippingForm = document.getElementById(payment.formId);
    if (!multiShippingForm) return;

    if (multiShippingForm.attachEvent)
        multiShippingForm.attachEvent("submit", submitMultiShippingForm);
    else
        multiShippingForm.addEventListener("submit", submitMultiShippingForm);

    cryozonic.multiShippingFormInitialized = true;
};

var isCheckbox = function(input)
{
    return input.attributes && input.attributes.length > 0 &&
        (input.type === "checkbox" || input.attributes[0].value === "checkbox" || input.attributes[0].nodeValue === "checkbox");
};

var disablePaymentFormValidation = function()
{
    var i, inputs = document.querySelectorAll(".stripe-input");
    var parentId = 'payment_form_cryozonic_stripe';

    $(parentId).removeClassName("stripe-new");
    for (i = 0; i < inputs.length; i++)
    {
        if (isCheckbox(inputs[i])) continue;
        $(inputs[i]).removeClassName('required-entry');
    }
};

var enablePaymentFormValidation = function()
{
    var i, inputs = document.querySelectorAll(".stripe-input");
    var parentId = 'payment_form_cryozonic_stripe';

    $(parentId).addClassName("stripe-new");
    for (i = 0; i < inputs.length; i++)
    {
        if (isCheckbox(inputs[i])) continue;
        $(inputs[i]).addClassName('required-entry');
    }
};

// Triggered when the user clicks a saved card radio button
var useCard = function(evt)
{
    // User wants to use a new card
    if (this.value == 'new_card')
        enablePaymentFormValidation();
    // User wants to use a saved card
    else
    {
        deleteStripeToken();
        disablePaymentFormValidation();
    }
};

var toggleValidation = function(evt)
{
    $('new_card').removeClassName('validate-one-required-by-name');
    if (evt.target.value == 'cryozonic_stripe')
        $('new_card').addClassName('validate-one-required-by-name');
};

var initSavedCards = function(isAdmin)
{
    var inputs = document.querySelectorAll('#saved-cards input');
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].onclick = useCard;
    }

    // If there are no saved cards, display the new card form
    if (inputs.length === 0)
        $('payment_form_cryozonic_stripe').addClassName('stripe-new');

    if (isAdmin)
    {
        // Adjust validation if necessary
        var newCardRadio = document.getElementById('new_card');
        if (newCardRadio)
        {
            var methods = document.getElementsByName('payment[method]');
            for (var j = 0; j < methods.length; j++)
                methods[j].addEventListener("click", toggleValidation);
        }
    }
};

var saveNewCard = function()
{
    var saveButton = document.getElementById('cryozonic-savecard-button');
    var wait = document.getElementById('cryozonic-savecard-please-wait');
    saveButton.style.display = "none";
    wait.style.display = "block";

    if (typeof Stripe != 'undefined')
    {
        createStripeToken(function(err)
        {
            saveButton.style.display = "block";
            wait.style.display = "none";

            if (err)
                cryozonic.displayCardError(err);
            else
                document.getElementById("new-card").submit();

        });
        return false;
    }

    return true;
};

var initOSCModules = function()
{
    if (cryozonic.oscInitialized) return;

    // Front end bindings
    if (typeof IWD != "undefined" && typeof IWD.OPC != "undefined")
    {
        // IWD OnePage Checkout override, which is a tad of a mess
        var proceed = function()
        {
            if (typeof $j == 'undefined') // IWD 4.0.4
                $j = $j_opc; // IWD 4.0.8

            var form = $j('#co-payment-form').serializeArray();
            IWD.OPC.Checkout.xhr = $j.post(IWD.OPC.Checkout.config.baseUrl + 'onepage/json/savePayment',form, IWD.OPC.preparePaymentResponse,'json');
        };

        IWD.OPC.savePayment = function()
        {
            if (!IWD.OPC.saveOrderStatus)
                return;

            if (IWD.OPC.Checkout.xhr !== null)
                IWD.OPC.Checkout.xhr.abort();

            IWD.OPC.Checkout.lockPlaceOrder();

            if (payment.currentMethod != 'cryozonic_stripe')
                return proceed();

            IWD.OPC.Checkout.hideLoader();

            createStripeToken(function(err)
            {
                IWD.OPC.Checkout.xhr = null;
                IWD.OPC.Checkout.unlockPlaceOrder();

                if (err)
                    cryozonic.displayCardError(err);
                else
                    proceed();
            });
        };
        cryozonic.oscInitialized = true;
    }
    else if ($('onestepcheckout-form'))
    {
        // MageBay OneStepCheckout 1.1.5
        // Idev OneStepCheckout 4.5.4
        var initIdevOSC = function()
        {
            if (typeof $('onestepcheckout-form').proceed != 'undefined')
                return;

            $('onestepcheckout-form').proceed = $('onestepcheckout-form').submit;
            $('onestepcheckout-form').submit = function(e)
            {
                if (typeof payment != 'undefined' && payment.currentMethod != 'cryozonic_stripe' && payment.currentMethod !== "")
                    return $('onestepcheckout-form').proceed();

                // MageBay OneStepCheckout 1.1.5 does not have a payment instance defined
                if ($('p_method_cryozonic_stripe') && !$('p_method_cryozonic_stripe').checked)
                    return $('onestepcheckout-form').proceed();

                createStripeToken(function(err)
                {
                    if (err && err == three_d_secure_canceled)
                        console.error(three_d_secure_canceled);
                    else if (err)
                        cryozonic.displayCardError(err);
                    else
                        $('onestepcheckout-form').proceed();
                });
            };
        };

        // This is triggered when the billing address changes and the payment method is refreshed
        window.addEventListener("load", initIdevOSC);

        cryozonic.oscInitialized = true;
    }
    else if (typeof AWOnestepcheckoutForm != 'undefined')
    {
        // AheadWorks OneStepCheckout 1.3.5
        AWOnestepcheckoutForm.prototype.__sendPlaceOrderRequest = AWOnestepcheckoutForm.prototype._sendPlaceOrderRequest;
        AWOnestepcheckoutForm.prototype._sendPlaceOrderRequest = function()
        {
            if (payment.currentMethod != 'cryozonic_stripe')
                return this.__sendPlaceOrderRequest();

            var me = this;
            createStripeToken(function(err)
            {
                if (err)
                    cryozonic.displayCardError(err);
                else
                    me.__sendPlaceOrderRequest();
            });
        };
        AWOnestepcheckoutPayment.prototype._savePayment = AWOnestepcheckoutPayment.prototype.savePayment;
        AWOnestepcheckoutPayment.prototype.savePayment = function() {
            if (payment.currentMethod == 'cryozonic_stripe')
                return;
            else
                return this._savePayment();
        };
        cryozonic.oscInitialized = true;
    }
    // NextBits OneStepCheckout 1.0.3
    else if (typeof checkoutnext != 'undefined' && typeof Review.prototype.proceed == 'undefined')
    {
        Review.prototype.proceed = Review.prototype.save;
        Review.prototype.save = function()
        {
            if (payment.currentMethod != 'cryozonic_stripe')
                return this.proceed();

            var me = this;
            createStripeToken(function(err)
            {
                if (err)
                    cryozonic.displayCardError(err);
                else
                    me.proceed();
            });
        };

        if (typeof review != 'undefined')
            review.save = Review.prototype.save;

        cryozonic.oscInitialized = true;
    }
    // Magecheckout OSC 2.2.1
    else if (typeof MagecheckoutSecuredCheckoutPaymentMethod != 'undefined')
    {
        // Disable saving of payment details
        MagecheckoutSecuredCheckoutPaymentMethod.prototype._savePaymentMethod = MagecheckoutSecuredCheckoutPaymentMethod.prototype.savePaymentMethod;
        MagecheckoutSecuredCheckoutPaymentMethod.prototype.savePaymentMethod = function()
        {
            if (this.currentMethod != 'cryozonic_stripe')
                return this._savePaymentMethod();
        };

        if (typeof securedCheckoutPaymentMethod != 'undefined')
        {
            securedCheckoutPaymentMethod._savePaymentMethod = MagecheckoutSecuredCheckoutPaymentMethod.prototype._savePaymentMethod;
            securedCheckoutPaymentMethod.savePaymentMethod = MagecheckoutSecuredCheckoutPaymentMethod.prototype.savePaymentMethod;
        }

        MagecheckoutSecuredCheckoutForm.prototype._placeOrderProcess = MagecheckoutSecuredCheckoutForm.prototype.placeOrderProcess;
        MagecheckoutSecuredCheckoutForm.prototype.placeOrderProcess = function ()
        {
            var self = this;

            if (payment.currentMethod && payment.currentMethod != 'cryozonic_stripe')
                return this._placeOrderProcess();

            createStripeToken(function(err)
            {
                if (err)
                    cryozonic.displayCardError(err);
                else
                    self._placeOrderProcess();
            });
        };

        if (typeof securedCheckoutForm != 'undefined')
        {
            securedCheckoutForm._placeOrderProcess = MagecheckoutSecuredCheckoutForm.prototype._placeOrderProcess;
            securedCheckoutForm.placeOrderProcess = MagecheckoutSecuredCheckoutForm.prototype.placeOrderProcess;
        }

        cryozonic.oscInitialized = true;
    }
    // FireCheckout 3.2.0
    else if ($('firecheckout-form'))
    {
        var fireCheckoutPlaceOrder = function()
        {
            var self = this;

            if (payment.currentMethod != 'cryozonic_stripe' && payment.currentMethod !== "")
                return self.proceed();

            createStripeToken(function(err)
            {
                if (err)
                    cryozonic.displayCardError(err);
                else
                    self.proceed();
            });
        };

        window.addEventListener("load", function()
        {
            var btnCheckout = document.getElementsByClassName('btn-checkout');
            if (btnCheckout && btnCheckout.length)
            {
                for (var i = 0; i < btnCheckout.length; i++)
                {
                    var button = btnCheckout[i];
                    button.proceed = button.onclick;
                    button.onclick = fireCheckoutPlaceOrder;
                }
            }
        });
        cryozonic.oscInitialized = true;
    }
    else if (typeof MagegiantOneStepCheckoutForm != 'undefined')
    {
        // MageGiant OneStepCheckout 4.0.0
        MagegiantOneStepCheckoutForm.prototype.__placeOrderRequest = MagegiantOneStepCheckoutForm.prototype._placeOrderRequest;
        MagegiantOneStepCheckoutForm.prototype._placeOrderRequest = function()
        {
            if (payment.currentMethod != 'cryozonic_stripe')
                return this.__placeOrderRequest();

            var me = this;
            createStripeToken(function(err)
            {
                if (err)
                    cryozonic.displayCardError(err);
                else
                    me.__placeOrderRequest();
            });
        };

        // Disable savePaymentMethod ajax call
        MagegiantOnestepcheckoutPaymentMethod.prototype._savePaymentMethod = MagegiantOnestepcheckoutPaymentMethod.prototype.savePaymentMethod;
        MagegiantOnestepcheckoutPaymentMethod.prototype.savePaymentMethod = function()
        {
            if (payment.currentMethod != 'cryozonic_stripe')
                return this._savePaymentMethod();
        };
        cryozonic.oscInitialized = true;
    }
    else if (typeof oscPlaceOrder != 'undefined')
    {
        // Magestore OneStepCheckout 3.5.0
        var proceed = oscPlaceOrder;
        oscPlaceOrder = function(element)
        {
            var payment_method = $RF(form, 'payment[method]');
            if (payment_method != 'cryozonic_stripe')
                return proceed(element);

            createStripeToken(function(err)
            {
                if (err)
                    cryozonic.displayCardError(err);
                else
                    proceed(element);
            });
        };
        cryozonic.oscInitialized = true;
    }
    // Amasty OneStepCheckout 3.0.5
    else if ($('amscheckout-submit') && typeof completeCheckout != 'undefined')
    {
        $('amscheckout-submit').onclick = function(el)
        {
            if (payment.currentMethod != 'cryozonic_stripe')
                return completeCheckout();

            showLoading();
            createStripeToken(function(err)
            {
                hideLoading();
                if (err)
                    cryozonic.displayCardError(err);
                else
                    completeCheckout();
            });
        };
        cryozonic.oscInitialized = true;
    }
    // Magesolution Athlete Ultimate Magento Theme v1.1.2
    else if ($('oscheckout-form') && typeof Review.prototype.proceed == 'undefined')
    {
        Review.prototype.proceed = Review.prototype.save;

        Review.prototype.save = function()
        {
            if (payment.currentMethod != 'cryozonic_stripe')
                return review.proceed();

            createStripeToken(function(err)
            {
                if (err)
                    cryozonic.displayCardError(err);
                else
                    review.proceed();
            });
        };

        if (typeof review != 'undefined')
            review.save = Review.prototype.save;
    }
    else if (typeof OSCheckout != 'undefined' && typeof OSCheckout.prototype.proceed == 'undefined')
    {
        // AdvancedCheckout OSC 2.5.0
        OSCheckout.prototype.proceed = OSCheckout.prototype.placeOrder;
        OSCheckout.prototype.placeOrder = function()
        {
            // Payment is not defined
            // if (payment.currentMethod != 'cryozonic_stripe')
            //     return this.proceed();

            var me = this;
            createStripeToken(function(err)
            {
                if (err)
                    cryozonic.displayCardError(err);
                else
                    me.proceed();
            });
        };

        if (typeof oscheckout != 'undefined')
        {
            oscheckout.proceed = OSCheckout.prototype.proceed;
            oscheckout.placeOrder = OSCheckout.prototype.placeOrder;
        }

        cryozonic.oscInitialized = true;
    }
    else if (typeof Payment != 'undefined' && typeof Payment.prototype.proceed == 'undefined')
    {
        // Default Magento Onepage checkout
        // Awesome Checkout 1.5.0
        // Other OSC modules

        /* The Awesome Checkout 1.5.0 configuration whitelist files are:
         *   cryozonic_stripe/js/cryozonic_stripe.js
         *   cryozonic_stripe/js/cctype.js
         *   cryozonic_stripe/css/cctype.css
         *   cryozonic_stripe/css/savedcards.css
         *   prototype/window.js
         *   prototype/windows/themes/default.css
        */

        Payment.prototype.proceed = Payment.prototype.save;

        Payment.prototype.save = function()
        {
            if (payment.currentMethod != 'cryozonic_stripe')
                return payment.proceed();

            createStripeToken(function(err)
            {
                if (err)
                    cryozonic.displayCardError(err);
                else
                    payment.proceed();
            });
        };

        if (typeof payment != 'undefined')
            payment.save = Payment.prototype.save;
    }
};
