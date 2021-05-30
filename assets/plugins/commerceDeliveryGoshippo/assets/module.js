var goshippo = {
    goshippoFieldChangeTimer: null,
    fullNameFieldName: null,
    deliveryMethodKey: null,
    rates: [],

    init: function () {
        let $ = jQuery;
        this.fullNameFieldName = goshippoConfig.fullNameField;
        this.deliveryMethodKey = goshippoConfig.deliveryMethodKey;


        $(document).on('change', '#delivery_goshippo_rate_id', () => {
            var rateId = $('#delivery_goshippo_rate_id').val();

            console.log(rateId);

            for (var i = 0; i < this.rates.length; i++) {

                if (rateId == this.rates[i]['object_id']) {
                    $('#delivery_goshippo_rate').val(JSON.stringify(this.rates[i]));
                }
            }
        });

        $(document).ready(() => this.updateStateVisibility());

        $(document).ready(() => this.updateFieldsVisibility());
        $(document).on('change', '[name="order[fields][delivery_method]"]', () => this.updateFieldsVisibility());


        $(document).on('click', '#update-rates', () => {
            this.goshippoUpdateRates(true);
        });
        $(document).on('change', '#delivery_goshippo_country', () => {
            var $option = $('#delivery_goshippo_country option:selected');

            this.updateStateVisibility();

            var country = $('#delivery_goshippo_country').val();

            $('#delivery_goshippo_state option:gt(0)').remove();

            if (parseInt($option.data('state')) === 1) {

                $.post('/ajax/commerce/delivery/goshippo/states', {
                    country: country
                }, function (response) {

                    for (var i = 0; i < response.states.length; i++) {
                        var item = response.states[i];
                        $('#delivery_goshippo_state').append($('<option></option>').val(item['iso']).text(item['title']))
                    }

                })
            }
        });

        $(document)
            .on('keyup', '#order_form [name="' + this.fullNameFieldName + '"]', () => this.goshippoUpdateRates())
            .on('change', '#delivery_goshippo_country', () => this.goshippoUpdateRates())
            .on('change', '#delivery_goshippo_state', () => this.goshippoUpdateRates())

            .on('keyup', '#delivery_goshippo_zip', () => this.goshippoUpdateRates())
            .on('keyup', '#delivery_goshippo_city', () => this.goshippoUpdateRates())
            .on('keyup', '#delivery_goshippo_street', () => this.goshippoUpdateRates())
        ;


    },
    canRequestRates: function () {
        let $ = jQuery;

        let fullNameFieldName = this.fullNameFieldName;
        let deliveryMethod = $('#order_form [name="order[fields][delivery_method]"]').val();

        let $countryOption = $('#delivery_goshippo_country option:selected');

        let name = $('#order_form [name="' + fullNameFieldName + '"]').val();

        let country = $countryOption.val();
        let state = $('#delivery_goshippo_state').val();
        let requireState = parseInt($countryOption.data('state'));

        let zip = $('#delivery_goshippo_zip').val();
        let city = $('#delivery_goshippo_city').val();
        let street = $('#delivery_goshippo_street').val();


        var result = deliveryMethod === this.deliveryMethodKey && name && country && (requireState === 0 || state) && zip && city && street;

        return result;
    },
    goshippoUpdateRates: function (immediately) {
        let $ = jQuery;
        if (!this.canRequestRates()) {
            console.log('can not requestRates');
            return;
        }

        let timer = !immediately ? 1000 : 0;


        clearTimeout(this.goshippoFieldChangeTimer);
        this.goshippoFieldChangeTimer = setTimeout(() => {

            $('[data-delivery="goshippo"]').prop('readonly', true)
            $('#delivery_goshippo_rate_id option:gt(0)').remove();

            var cart = [];


            $('#products tr').each(function (ind, elem) {

                var iteration = ind + 1;

                cart.push({
                    id: $('[name="order[cart][' + iteration + '][id]"]').val(),
                    count: $('[name="order[cart][' + iteration + '][count]"]').val(),
                })

            })

            this.rates = [];


            $.post('/ajax/commerce/delivery/goshippo/rates-calculate', {
                name: $('#order_form [name="' + this.fullNameFieldName + '"]').val(),
                delivery_goshippo_country: $('#delivery_goshippo_country').val(),
                delivery_goshippo_state: $('#delivery_goshippo_state').val(),
                delivery_goshippo_zip: $('#delivery_goshippo_zip').val(),
                delivery_goshippo_city: $('#delivery_goshippo_city').val(),
                delivery_goshippo_street: $('#delivery_goshippo_street').val(),
                cart: cart

            }, (response) => {
                if (response.status) {

                    console.log(this)

                    this.rates = response.rates;

                    for (var i = 0; i < response.rates.length; i++) {
                        var rate = response.rates[i];
                        $('#delivery_goshippo_rate_id').append($('<option></option>').val(rate.object_id).text(rate.title))
                    }
                } else {
                    alert(response.message)
                }

                $('[data-delivery="goshippo"]').prop('readonly', false);

            })


        }, timer);
    },

    updateStateVisibility: function () {
        let $ = jQuery;

        var deliveryMethod = $('#delivery_goshippo_country').val();
        if (deliveryMethod !== this.deliveryMethodKey) {
            return;
        }
        var $option = $('#delivery_goshippo_country option:selected');

        var $tr = $("#delivery_goshippo_state").closest('tr');


        if (parseInt($option.data('state')) === 1) {
            $tr.show();
        } else {
            $tr.hide();
        }
    },
    updateFieldsVisibility: function () {
        let $ = jQuery;
        var deliveryMethod = $('[name="order[fields][delivery_method]"]').val();

        console.log(deliveryMethod)
        console.log(this.deliveryMethodKey)

        if (deliveryMethod === this.deliveryMethodKey) {
            $('[data-delivery="goshippo"]').closest('tr').show();
        } else {
            $('[data-delivery="goshippo"]').closest('tr').hide();
        }
    },


};

goshippo.init();




