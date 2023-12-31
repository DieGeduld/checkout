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
const bootstrap = require('bootstrap');

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

    $('input.ajax').on('keypress blur', function(event) {
        if (event.which === 13) {
            event.preventDefault();
            $(this).trigger('change');
        }
    });

    $(document).on('submit', '[action^="/shop/update"]', function(event) {
        event.preventDefault();
        return false;
    });

    $(document).on("click change", ".ajax", (e) => {
        e.preventDefault();
        $this = $(e.currentTarget);
        $replace = $(".editableShoppingCartWrapper");

        if ($this.attr("disabled")) {
            return;
        }

        let url = "";
        if ($this.is("a") || $this.is("button")) {
            url = $this.attr('href');
        } else {
            if (e.type === "click") {
                return;
            }
            url = $this.closest("form").attr('action');
            url += "?" + $this.closest("form").serialize();
        }

        $this.attr("disabled", true);
    
        $.ajax({
            url: url,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (data) {
                $replace.replaceWith(data.editableShoppingCartHtmlWrapper);
                $('.app_shop_shoppingcart').text(function() {
                    return $(this).text().replace(/\(\d+\)/, '(' +  data.products.length + ')');
                });

                if (data.products.length) {
                    $(".toShoppingCart").removeClass("d-none");
                }  else {
                    $(".toShoppingCart").addClass("d-none");
                } 
                createToast(data.message);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
                // Todo: error flash message
            },
            complete: function () {
                $this.attr("disabled", false);
            }
        });
    });

    function createToast(message) {
        var toastContainer = document.getElementById('toastContainer');
      
        // Erstellen des Toast-Elements
        var toast = document.createElement('div');
        toast.className = 'toast';
        toast.role = 'alert';
        toast['aria-live'] = 'assertive';
        toast['aria-atomic'] = 'true';
        toast.innerHTML = `
          <div class="toast-header">
            <strong class="me-auto">Toast Nachricht</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">${message}</div>
        `;
      
        // Toast zum Container hinzufügen und anzeigen
        toastContainer.appendChild(toast);
        var toastElement = new bootstrap.Toast(toast, { delay: 1300 });
        toastElement.show();
      }
      



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
