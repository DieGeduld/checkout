/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
// import './styles/app.css';
// import bootstrap

const $ = require('jquery');
require('bootstrap');

require('./styles/app.scss');
require('bootstrap/dist/css/bootstrap.min.css');


$(function () {

    // Via Ajax:

    let addressField = document.getElementById('address_country');
    let taxContainer = document.getElementById('tax-container');

    if (addressField) {

        addressField.addEventListener('change', function() {

            data = new FormData();
            data.append('countryId', $(this).val());

            fetch("/address/check-country", {
                method: 'POST',
                body: data
            }).then(function(response) {
                return response.text();
            }).then(function(html) {
                if (JSON.parse(html).isEu) {
                    taxContainer.style.display = 'block';
                } else {
                    taxContainer.style.display = 'none';
                }
            });
        });
    }


    $(document).on("click change", ".ajax", (e) => {
        e.preventDefault();
        $this = $(e.currentTarget);
        $replace = $(".editableShoppingCartWrapper");

        let url = "";
        if ($this.is("a")) {
            url = $this.attr('href');
        } else {
            url = $this.closest("form").attr('action');
            url += "?" + $this.closest("form").serialize();
        }
    
        $.ajax({
            url: url,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (data) {
                $replace.html(data.editableShoppingCartHtmlWrapper);
                console.log(data.products);
                if (data.products.length) {
                    $(".toShoppingCart").removeClass("d-none");
                }  else {
                    $(".toShoppingCart").addClass("d-none");
                } 
                //Todo: Flash message: data.message
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
                // Todo: error flash message
            }
        });
    });

    // Direkt über Attribt:

    // function toggleTaxNumberField() {
    //     var isEu = $('#address_country option:selected').attr('attr-iseu') === '1';
    //     if (isEu) {
    //         $('#address_taxNumber').closest('div').show();
    //     } else {
    //         $('#address_taxNumber').closest('div').hide();
    //     }
    // }
    // $('#address_country').on('change', toggleTaxNumberField);
    // toggleTaxNumberField();

}); 
