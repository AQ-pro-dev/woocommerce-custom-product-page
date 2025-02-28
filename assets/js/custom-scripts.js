// Initialize Swiper sliders
const productThumbSlider = new Swiper(".product-thumb-slider", {
    loop: true,
    spaceBetween: 44,
    slidesPerView: 4,
    watchSlidesProgress: true,
  });

  const productImageSlider = new Swiper(".product-image-slider", {
    loop: true,
    navigation: {
        nextEl: ".swiper-button-next.pi-nav-btn",
        prevEl: ".swiper-button-prev.pi-nav-btn",
    },
    scrollbar: {
        el: '.swiper-scrollbar',
        hide: false,
        grabCursor: true,
    },
    thumbs: {
        swiper: productThumbSlider,
    },
  });

  // Quantity button event listeners
  const buttonPlus = document.querySelector(".qty-btn-plus");
  const buttonMinus = document.querySelector(".qty-btn-minus");

  const updateQuantity = (button, increment) => {
    button.addEventListener("click", () => {
        const qtyInput = button.parentNode.querySelector(".input-qty");
        let qtyValue = Number(qtyInput.value);
        qtyValue = increment ? Math.min(qtyValue + 1, 1) : Math.max(qtyValue - 1, 0);
        qtyInput.value = qtyValue;
    });
  };

  updateQuantity(buttonPlus, true);
  updateQuantity(buttonMinus, false);


  $(document).ready(function() {

    ///////////////////// Start Product Variation /////////////////////////////

    function updateVariationId() {
      var product_id   = $('#product_id').val();
      var length       = $('#product_length').val();
      var color        = $('input[name="attribute_color"]:checked').val();
      var qrCodeOption = $('input[name="attribute_qr-code-preferences"]:checked').val();
      var engraving    = $('#is_engraving').length ? ($('#is_engraving').is(':checked') ? 'yes' : 'no') : null;
      var medical_alert_device  = $('#att_medical_alert_devices').val();
      var medical_identifications  = $('#att_medical_identifications').val();
      var metal_type  = $('#att_metal_type').val();


      // Show or hide engraving element
      if ($('#is_engraving').length) {
          $('#show-engraving').toggle(engraving === 'yes');
          $('#engravingTxt').text(`Engraving (${qrCodeOption === 'back-side' ? 'front' : 'back'})`);
      }

      var data = {
          action: 'get_variation_id',
          product_id: product_id,
          attributes: {
              'attribute_pa_length': length,
              'attribute_pa_color': color,
              'attribute_pa_qr-code-preferences': qrCodeOption
          }
      };

      // Add attribute_engrave only if the checkbox exists
      if ($('#is_engraving').length) {
          data.attributes['attribute_pa_engrave'] = engraving;
      }

      if (medical_alert_device) {
          data.attributes['attribute_pa_medical-alert-devices'] = medical_alert_device;
      }

      if (medical_identifications) {
          data.attributes['attribute_pa_medical-identifications'] = medical_identifications;
      }

      if (metal_type) {
          data.attributes['attribute_pa_metal-type'] = metal_type;
      }

      $.post(ajax_object_for_listing.ajax_for_listing, data, function(response) {
          if (response.success) {
              $('#variation_id').val(response.data.variation_id);
              updateVariationGallery(response.data.variation_id);
          } else {
              console.log('Error fetching variation ID:', response);
          }
      });
  }
  function updateVariationGallery(variation_id) {
    $.ajax({
        url: ajax_object_for_listing.ajax_for_listing,
        type: 'POST',
        data: {
            action: 'get_variation_images',
            variation_id: variation_id
        },
        success: function(response) {
            if (response.success) {
                // Replace only the main image
                if (response.data.main_image) {
                    var mainImageHtml = `
                    <div class="swiper-slide">
                        <div class="image">
                            <img src="${response.data.main_image}" alt="Variation Image" />
                        </div>
                    </div>`;

                    // Replace the first slide with the new main image
                    $('.product-image-slider .swiper-wrapper .swiper-slide:first-child').html(mainImageHtml);
                }

                // No need to touch the gallery thumbnails
            } else {
                console.log('Error fetching variation images:', response);
            }
        }
    });
}
  ////////////////
  function swapEngravingFields() {
      var product_id   = $('#product_id').val();
      var qrCodeOption = $('input[name="attribute_qr-code-preferences"]:checked').val();
      var engraving    = $('#is_engraving').length ? ($('#is_engraving').is(':checked') ? 'yes' : 'no') : null;

      var data = {
          action: 'swap_engraving_fields',
          product_id: product_id,
          qrCodeOption: qrCodeOption,
          engraving: engraving
      };

      $.post(ajax_object_for_listing.ajax_for_listing, data, function(response) {
          if (response.success) {
              // alert('sdsd');
              // console.log(response.data.fields_content);
              $('#engravingFields').html(response.data.fields_content);
          } else {
              console.log('Error fetching engraving Fields', response);
          }
      });
  }
  
  $('input[name="attribute_color"]').change(updateVariationId);
  $('input[name="attribute_qr-code-preferences"]').change(updateVariationId);
  $('#is_engraving').change(updateVariationId);
  $('#second_step_next').click(swapEngravingFields);


  function checkoutfunction() {
    $('#step-loader').addClass('active');

    $.ajax({
        url: ajax_object_for_Qrcode.ajax_url,
        type: 'POST',
        data: {
            action: 'check_login_status'
        },
        success: function(response){
            if(response.data.logged_in){
                
                var form = $("#cart");
                var fieldValues = [];
                var engraving = $('#is_engraving').length ? ($('#is_engraving').is(':checked') ? 'yes' : 'no') : null;
            
                // Check if the checkbox exists and is checked
                if ($('#is_engraving').length > 0 && $('#is_engraving').is(':checked')) {
                    $('input[id^="engrave"]').each(function() {
                        var value = $(this).val();
                        fieldValues.push(value);
                    });
                }
            
                var data = {
                    action: 'process_engrave_fields',
                    'attribute_engrave': engraving,
                    formdata: form.serialize(),
                };
            
                // Add fieldValues only if it's not empty
                if (fieldValues.length > 0) {
                    data.fieldValues = fieldValues;
                }
            
                $.post(ajax_object_for_listing.ajax_for_listing, data, function(response) {
                    if (response.success) {
                    //alert(response.data.redirecturl);
                        window.location.href = response.data.redirecturl;
                    } else {
                        console.error('AJAX error:', response.error);
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX request failed:', textStatus, errorThrown);
                });

            } 
            else{
                loadVideoScript('CUaRAfrgB6c2');
                showStep(2);
                return; // Stop here and wait for login/signup
            } 
        }
    });

    
  }

  $('#checkout').click(checkoutfunction);

 ///////////////////// Start Custom function to remove https prefix  ////////////////////////// 
//remove http prefix 
function removeHttpPrefix(url) {
    return url.replace(/^https?:\/\//, ''); // Removes https:// or https://
}
 ///////////////////// End Custom function to remove https prefix //////////////////////////

    ///////////////////// End Product Variation code //////////////////////////

      // Initialize FancyBox on the main slides
      $('.product-image-slider .image').each(function () {
          $(this).find('img').wrap('<a data-fancybox="single-product-gallery" href="' + $(this).find('img').attr('src') + '"></a>');
      });
      function generateQrcode(paz_ue, userID) {
        if (!$('#qrcode').length > 0) {
            return false;
        }
        $('#qrcode').empty();
    
        var userEmail = (paz_ue ? atob(paz_ue) : user_data.email);
        var userId = (userID ?? user_data.ID);
        var productID = $('#product_id').val();
        let originalUrl = $('#qr_code_scanning_link').val();
        let modifiedUrl = originalUrl.replace("patient-details", "pd");
        var qrCodeData = modifiedUrl + btoa(userId + "," + productID);
        var withoutHttpPrefix = removeHttpPrefix(qrCodeData);
        console.log(withoutHttpPrefix);
    
        var qrcodeContainer = document.getElementById("qrcode");
        if (!qrcodeContainer) {
            console.error('Target element for QR code not found');
            return;
        }
        const qrSize = 40; // Size of the QR code

        // QR code configuration
        const qrContent = withoutHttpPrefix; // Data for the QR code
        const typeNumber = 2; // QR code version
        const errorCorrectionLevel = 'L'; // Error correction level

        // Create QR Code instance
        const qr = qrcode(typeNumber, errorCorrectionLevel);
        qr.addData(qrContent);
        qr.make();

        // Get the number of cells (modules) in the QR code
        const numCells = qr.getModuleCount();
        const cellSize = Math.floor(qrSize / numCells); // Calculate cell size

        // Generate SVG without margin or background
        let svg = qr.createSvgTag({
            scalable: true,
            foreground: '#373435',  // This color might not work as expected directly, so we will manually edit the path
            background: null,
            cellSize: cellSize,
            margin: 0
        });

        // Modify the SVG string to remove rect elements (background) and change path color
        svg = svg.replace(/<rect[^>]*fill="[^"]*"/g, ''); // Remove background fill if any
        svg = svg.replace(/<path/g, `<path fill="#373435"`); // Manually set path fill color to #373435

        // Insert the modified SVG into the DOM

        //const qrcodeDiv = document.getElementById('qrcode');
        qrcodeContainer.innerHTML = svg;
        
        // Use a setTimeout to wait for the QR code to be generated
        setTimeout(function() {
            var serializer = new XMLSerializer();
            var svgString = serializer.serializeToString(qrcodeContainer); 
            // Send the SVG data to the server via AJAX
            $.ajax({
                url: ajax_object_for_Qrcode.ajax_url,
                type: 'POST',
                data: {
                    action: 'save_qr_code',
                    product_id: productID,
                    qr_code_image: svgString,  // Pass SVG data URL to the server
                    qr_code_data: withoutHttpPrefix
                },
                success: function(response) {
                    console.log('QR code saved successfully:', response);
                    $('#qr_code_url').val(response.data.url); // Store the saved QR code URL
                    $('#qrcode-main').html("<img src='"+$('#dummy_qr_code').val()+"' style='width: 225px; height:267;'>");
                },
                error: function(response) {
                    console.log('Error saving QR code:', response);
                }
            }); 
        }, 500); // Adjust the timeout as needed
    }
    
      // Create an observer instance to detect changes in the qrcodeContainer

      // Ensure this generateQrcode function is defined globally
        window.generateQrcode = generateQrcode;

      function showStep(step) {
        //   var isLoggedIn = ajax_login_signup_data.is_logged_in === '1';
        //   alert(isLoggedIn);
            if (step === 2) {

                $.ajax({
                    url: ajax_object_for_Qrcode.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'check_login_status'
                    },
                    success: function(response){
                        if(response.data.logged_in){
                            loadVideoScript('CUaRAfrgB6c2');
                            generateQrcode(response.data.paz_ue, response.data.userID );
                            // Update step visibility
                            $('.step').removeClass('active');
                            $('.step[data-step="' + step + '"]').addClass('active');

                        } else {
                            loadVideoScript('XYaeq7FrTJYS');
                            $('#loginSignupModal').show();
                            showStep(1);
                            return; // Stop here and wait for login/signup
                        }
                    }
                });
            } else {
                // Update step visibility
                $('.step').removeClass('active');
                $('.step[data-step="' + step + '"]').addClass('active');
                $('html, body').animate({
                    scrollTop: $('.product-detail-wrapper').offset().top
                }, 1000); // Duration of the scroll in milliseconds
            }

            if (step === 3) {
                loadVideoScript('FMK66SXE8nKQ');
            }
        //   if (step == 2 && isLoggedIn) {
        //       generateQrcode();
        //   }

          // // Update step visibility
          // $('.step').removeClass('active');
          // $('.step[data-step="' + step + '"]').addClass('active');

          // Update step indicator
          $('.steps-label-wrapper li').each(function() {
              var liStep = $(this).data('step');
              if (liStep < step) {
                  $(this).addClass('completed').removeClass('current');
              } else if (liStep === step) {
                  $(this).removeClass('completed').addClass('current');
              } else {
                  $(this).removeClass('completed current');
              }
          });
      }

      // Ensure this showStep function is defined globally
      window.showStep = showStep;

      function validateStep(step) {
        var isValid = true;

        // Clear existing error messages
        $('.error-message').remove();

        if (step === 1) {
            var colorChecked = $('input[name="attribute_color"]:checked').length;
            var quantity = $('input[name="quantity"]').val();
            if (!colorChecked && $('input[name="attribute_color"]').length) {
                isValid = false;
                $('input[name="attribute_color"]').first().closest('div.field-wrapped').after('<span class="error-message" style="color:red;display: block;padding-bottom: 25px;">Seleccione un color de atributo.</span>');
                $('input[name="attribute_color"]').first().focus();
            }
            if (quantity  != 1) {
                isValid = false;
                $('input[name="quantity"]').closest('div.field-wrapped').after('<span class="error-message" style="color:red;display: block;padding-bottom: 25px;">Por favor, ingrese un valor de 1.</span>');
                $('input[name="quantity"]').focus();
            }
        } else if (step === 2) {
            var qrCodePreferenceChecked = $('input[name="attribute_qr-code-preferences"]:checked').length;

            if (!qrCodePreferenceChecked) {
                isValid = false;
                $('input[name="attribute_qr-code-preferences"]').first().closest('div.qr-code-preference').after('<span class="error-message" style="color:red;display: block;padding-bottom: 25px;">Seleccione una preferencia de código QR.</span>');
                $('input[name="attribute_qr-code-preferences"]').first().focus();
            }
        }

        return isValid;
      }



      $('.cta-next').on('click', function() {

          var currentStep = $('.step.active').data('step');
          var nextStep = currentStep + 1;
      // alert(validateStep(currentStep));
          if (validateStep(currentStep)) {
              showStep(nextStep);
          }
      });

      $('.cta-back').on('click', function() {
          var currentStep = $('.step.active').data('step');
          var prevStep = currentStep - 1;
          showStep(prevStep);
      });
      loadVideoScript('XYaeq7FrTJYS');
      // Initialize the form to show the first step
      showStep(1);

  });

  if ($('#is_engraving').length > 0){
  const canvas_engrave = document.getElementById('engravedCanvas');
  const ctx = canvas_engrave.getContext('2d');
  const image = new Image();

  function loadImageAndUpdateCanvas() {
      image.src = document.getElementById('engraved_img_src').value;
      image.onload = updateCanvas;
  }

  function updateCanvas() {
      ctx.clearRect(0, 0, canvas_engrave.width, canvas_engrave.height);
      ctx.drawImage(image, 0, 0, canvas_engrave.width, canvas_engrave.height);

      const paddingTop = parseInt(document.getElementById('padding-top').value);
      const paddingBottom = parseInt(document.getElementById('padding-bottom').value);
      const paddingLeft = parseInt(document.getElementById('padding-left').value);
      const paddingRight = parseInt(document.getElementById('padding-right').value);
      const fontSize = parseInt(document.getElementById('font-size').value);
      const fontStyle = document.getElementById('font-style').value;
      const textcolor = document.getElementById('text-color').value;
      const lineSpacing = parseInt(document.getElementById('line-spacing').value);
      const orientation = document.querySelector('input[name="orientation"]').value;

      const contentWidth = canvas_engrave.width - paddingLeft - paddingRight;
      const contentHeight = canvas_engrave.height - paddingTop - paddingBottom;

      const inputFields = document.querySelectorAll('#engravingFields input');
      ctx.font = `${fontSize}px ${fontStyle}`;
      ctx.fillStyle = textcolor;

      ctx.save();
      ctx.translate(paddingLeft + contentWidth / 2, paddingTop + contentHeight / 2); // Move to center of content area
      if (orientation === 'vertical') {
          ctx.rotate(Math.PI / 2); // Rotate 90 degrees for vertical
          ctx.translate(-contentHeight / 2, -contentWidth / 2); // Adjust translation after rotation
      } else {
          ctx.translate(-contentWidth / 2, -contentHeight / 2); // Move back to top-left of content area
      }

      inputFields.forEach((input, index) => {
          const text = input.value;
          ctx.fillText(text, 0, lineSpacing + index * lineSpacing); // Draw text at new origin with line spacing
      });

      ctx.restore();
  }

  // Call it once to initialize
  loadImageAndUpdateCanvas();
  }

  jQuery(document).ready(function ($) {
    // Check login status
    // var isLoggedIn = ajax_login_signup_data.is_logged_in === '1';


    $('.close').on('click', function () {
        $('#loginSignupModal').hide();
    });

    // Tab switching
    $('#loginTab').on('click', function () {
        $(this).addClass('active');
        $('#signupTab').removeClass('active');
        $('#loginForm').show();
        $('#signupForm').hide();
        $('#password-reset-form').hide();
        $('#messageSec').hide();
    });

    $('#signupTab').on('click', function () {
        $(this).addClass('active');
        $('#loginTab').removeClass('active');
        $('#signupForm').show();
        $('#loginForm').hide();
        $('#password-reset-form').hide();
        $('#messageSec').hide();
    });

    $('#lost-pass-link').on('click', function (){
        $('#loginTab').removeClass('active');
        $('#loginForm').hide();
        $('#password-reset-form').show();
        $('#messageSec').hide();
    });

   // Login form submission
    $('#login-form').on('submit', function (e) {
        e.preventDefault();

        var password = $('input[name="password"]', this).val();

        // Base64 encode the password
        var encodedPassword = btoa(password);
        var data = {
            action: 'ajax_login',
            email: $('input[name="email"]', this).val(),
            password: encodedPassword,
        };

        $.post(ajax_login_signup_data.ajax_sign_inup_url, data, function (response) {
            // alert(response.success);
            if (response.success) {
                // alert('success');
                isLoggedIn = true;

                $('#modalTabs').hide();
                $('#signupForm').hide();
                $('#loginForm').hide();
                $('#messageSec').show();
                $('#messageText').html('<span style="color:green;">You are successfully logged in now.</span>');
                setTimeout(function(){
                    $('#loginSignupModal').hide();
                }, 2000);
                loadVideoScript('CUaRAfrgB6c2');
                showStep(2);
                // alert('123');
            } else {
                $('#signupForm').hide();
                $('#loginForm').show();
                $('#messageSec').show();
                $('#messageText').html('<span style="color:red;">Please try again!<br> try correct username and password.<span>');
            }
        });
    });
    //password reset form submission
    $('#password-reset').on('submit', function (e){
        e.preventDefault();

        var email = $('input[name="user_login"]', this).val();

        var data = {
            action: 'ajax_password_reset',
            email: email
        };


        $.post(ajax_login_signup_data.ajax_sign_inup_url, data, function(response){
            if(response.success){
                $('#loginTab').addClass('active');
                $('#signupTab').removeClass('active');
                $('#loginForm').show();
                $('#signupForm').hide();
                $('#password-reset-form').hide();
                $("#messageSec").show();
                $("#messageText").html('<span style="color:green;">Se ha enviado un enlace para restablecer la contraseña a tu correo electrónico. </span>');
            } else{
                $('#messageSec').show();
                if(response.data.message){
                    $('#messageText').html('<span style="color:red;">El restablecimiento de la contraseña falló.<br>' + response.data.message + '</span>');
                }
            }
        })
    })
    // Signup form submission
    $('#signup-form').on('submit', function (e) {
        e.preventDefault();

        var password = $('input[name="password"]', this).val();

        // Base64 encode the password
        var encodedPassword = btoa(password);

        var data = {
            action: 'ajax_signup',
            name: $('input[name="name"]', this).val(),
            email: $('input[name="email"]', this).val(),
            password: encodedPassword,
        };

        $.post(ajax_login_signup_data.ajax_sign_inup_url, data, function (response) {
            if (response.success) {
                isLoggedIn = true;
                $('#loginSignupModal').hide();
                loadVideoScript('CUaRAfrgB6c2');
                showStep(2);
                
            } else {
                // alert('Signup failed: ' + response.data.message);
                // $('#signupTab').removeClass('active');
                // $('#loginTab').addClass('active');
                // $('#loginForm').hide();
                // $('#signupForm').show();
                $('#messageSec').show();
                // console.log(response.data_msg.message);
                // alert(response.data_msg.message);
                if(response.data_msg.message){
                    $('#messageText').html('<span style="color:red;">Signup failed.<br>'+response.data_msg.message);
                }

            }
        });
    });

        // Function to check if the emails match
        function checkEmailMatch() {
            const email = $('#email').val();
            const confirmEmail = $('#confirm-email').val();
            const emailMatchMessage = $('#email-match-message');

            if (email && confirmEmail && email === confirmEmail) {
                emailMatchMessage.css('display', 'block');
                emailMatchMessage.css('color', 'green');
                emailMatchMessage.text('Email matched.');
            } else if(email && confirmEmail) {
                emailMatchMessage.css('display', 'block');
                emailMatchMessage.css('color', 'red');
                emailMatchMessage.text('Email not matching.');
            } else {
                emailMatchMessage.css('display', 'none');
            }
        }

        // Function to check if the passwords match
        function checkPasswordMatch() {
            const password = $('#password').val();
            const confirmPassword = $('#confirm-password').val();
            const passwordMatchMessage = $('#password-match-message');

            if (password && confirmPassword && password === confirmPassword) {
                passwordMatchMessage.css('display', 'block');
                passwordMatchMessage.css('color', 'green');
                passwordMatchMessage.text('Password matched.');
            } else if(password && confirmPassword) {
                passwordMatchMessage.css('display', 'block');
                passwordMatchMessage.css('color', 'red');
                passwordMatchMessage.text('Password not matching.');
            } else {
                passwordMatchMessage.css('display', 'none');
            }
        }

        // Add event listeners to the input fields
        $('#email, #confirm-email').on('input', checkEmailMatch);
        $('#password, #confirm-password').on('input', checkPasswordMatch);


        ////engraving attr hidden values
        $('#dp-eng-css-fields').on('click', function () {
            toggleInputType('#padding-top', 'hidden', 'number');
            toggleInputType('#padding-bottom', 'hidden', 'number');
            toggleInputType('#padding-left', 'hidden', 'number');
            toggleInputType('#padding-right', 'hidden', 'number');
            toggleInputType('#font-size', 'hidden', 'number');
            toggleInputType('#font-style', 'hidden', 'text'); // This will remain the same since both types are 'text'
            toggleInputType('#line-spacing', 'hidden', 'number');
            toggleInputType('#text-color', 'hidden', 'color');
        });

        function toggleInputType(selector, type1, type2) {
            var input = $(selector);
            input.attr('type', input.attr('type') === type1 ? type2 : type1);
            jQuery('.hdn-inp-lbl').css('display', input.attr('type') === type1 ? 'none' : 'block');
        }

        if ($('.acf-input').length > 0){
         $('.acf-input').on('change', function() {
            var newValue = $(this).val();
            var fieldName = $(this).data('field');
            var productId = $(this).data('pid');
    
            $.ajax({
                url: ajax_object_for_Qrcode.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_acf_field',
                    product_id: productId,
                    field_name: fieldName,
                    new_value: newValue
                },
                success: function(response) {
                    if(response.success) {
                        console.log('ACF field updated successfully!');
                    } else {
                        console.error('Failed to update ACF field.');
                    }
                },
                error: function() {
                    console.error('An error occurred while updating the field.');
                }
            });
          });
        }
});


let vpPlayerInstance; // Track the current player instance

function loadVideoScript(embedId) {
    // Remove the existing iframe if it exists
    $('iframe.videopal-iframe').remove();

    // Destroy the existing player instance if it exists
    if (vpPlayerInstance && typeof vpPlayerInstance.destroy === "function") {
        vpPlayerInstance.destroy(); // Destroy the old instance
    }

    // Clear associated elements
    $('.videopal-container, .videopal-play-layer, .videopal-powered, .videopal-buttons, .videopal-form-container, .videopal-button').remove();

    // Generate the iframe URL
    const iframeUrl = `https://videopal.me/embed/${embedId}?referer=${encodeURIComponent(window.location.href)}`;

    // Dynamically create the iframe
    const iframeHtml = `
        <iframe 
            frameborder="0" 
            width="380" 
            height="300" 
            allow="autoplay" 
            src="${iframeUrl}" 
            id="videopal-iframe-${embedId}" 
            class="videopal-iframe" 
            name="videopal_iframe" 
            style="position: fixed; inset: auto auto 0px 0px; z-index: 99999; width: 380px; height: 300px;" 
            allowfullscreen>
        </iframe>
    `;

    // Append the iframe to the container
    $('#videopal-container').html(iframeHtml);

    // Reinitialize VpPlayer (if needed)
    reinitializeVideoPal(embedId);
}

function reinitializeVideoPal(embedId) {
    // Reload the VpPlayer script to ensure proper initialization
    const scriptUrl = `https://videopal.me/js/vp_player.min.js?v=1.1.29&cacheBust=${new Date().getTime()}`;

    $.getScript(scriptUrl)
        .done(function () {
            vpPlayerInstance = new VpPlayer({
                embedId: embedId
            });
            console.log('VpPlayer initialized for embedId:', embedId);
        })
        .fail(function () {
            console.error('Failed to load the VpPlayer script.');
        });
}