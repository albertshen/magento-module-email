/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {

    var formSubmit = function (config) {
        var postData = {
            form_key: FORM_KEY
        };

        /** global var configForm **/
        configForm.find('[id^=albert_email_emailsmtp]').find(':input').serializeArray().map(function (field) {
            var name = field.name.match(/groups\[emailsmtp\]?(\[groups\]\[debug\])?\[fields\]\[(.*)\]\[value]/);

            /**
             * groups[emailsmtp][groups][debug][fields][email][value]
             * groups[emailsmtp][fields][password][value]
             */

            if (name && name.length === 3) {
                postData[name[2]] = field.value;
            }
        });

        $.ajax({
            url: config.postUrl,
            type: 'post',
            dataType: 'html',
            data: postData,
            showLoader: true
        }).done(function (response) {
            if (typeof response === 'object') {
                if (response.error) {
                    alert({ title: 'Error', content: response.message });
                } else if (response.ajaxExpired) {
                    window.location.href = response.ajaxRedirect;
                }
            } else {
                alert({
                    title:'',
                    content:response,
                    buttons: []
                });
            }
        });
    };

    return function (config, element) {
        $(element).on('click', function () {
            formSubmit(config);
        });
    }
});
