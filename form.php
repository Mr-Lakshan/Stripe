<?php
session_start();
$amount = isset($_SESSION['amount']) ? $_SESSION['amount'] : 0;
$item = isset($_SESSION['item']) ? $_SESSION['item'] : '';

if (isset($_SESSION['amount'])) {



?>

  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment Form - Black & Gold</title>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
      body {
        font-family: 'Arial', sans-serif;
        background-color: #1f1f1f;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        color: #fff;
      }

      h2 {
        color: #f5c300;
        /* text-align: center; */
        /* margin-bottom: 20px; */
      }

      #payment-form {
        background-color: #222;
        padding: 30px;
        border-radius: 8px;
        width: 100%;
        max-width: 450px;
        box-sizing: border-box;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
      }

      .step {
        display: none;
      }

      .step.active {
        display: block;
      }

      label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #f5c300;
      }

      input[type="text"],
      input[type="email"],
      input[type="number"],
      input[type="password"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #444;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 16px;
        background-color: #333;
        color: #fff;
      }

      input[type="text"]:focus,
      input[type="email"]:focus,
      input[type="number"]:focus,
      input[type="password"]:focus {
        border-color: #f5c300;
        outline: none;
      }

      #card-element {
        background-color: #333;
        padding: 10px;
        border: 1px solid #444;
        border-radius: 4px;
        font-size: 16px;
        margin-bottom: 20px;
      }

      button[type="button"],
      button[type="submit"] {
        padding: 15px;
        border: none;
        border-radius: 4px;
        width: 100%;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s ease;
      }

      /* Pay Button */
      button[type="submit"] {
        background-color: #f5c300;
        color: black;
        margin-top: 15px;
      }

      button[type="submit"]:hover {
        background-color: #d39b00;
      }

      /* Back Button */
      .back-button {
        background-color: #444;
        color: #fff;
        margin-top: 15px;
        width: auto;
        padding: 15px 25px;
        display: inline-block;
      }

      .back-button:hover {
        background-color: #666;
      }

      .step-footer {
        margin-top: 20px;
        text-align: center;
      }

      .success-message {
        color: #4CAF50;
        font-weight: bold;
        text-align: center;
        margin-top: 20px;
      }

      .error-message {
        color: #e74c3c;
        font-weight: bold;
        text-align: center;
        margin-top: 20px;
      }

      .error {
        color: red;
      }

      section {
        background-color: #4CAF50;
      }
    </style>
    <link rel="icon" href="./imagespexels-danny-meneses-340146-943096.jpg" type="image/x-icon">
  </head>

  <body>

    <h2>Payment Form</h2>

    <form id="payment-form">
      <!-- Step 1: Customer Details -->
      <div class="step active" id="step-1">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required>
        <span class="error" id="nameerr"></span>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
        <span class="error" id="emailerr"></span>

        <label for="address">Address</label>
        <input type="text" id="address" name="address" required>
        <span class="error" id="adderr"></span>

        <label for="city">City</label>
        <input type="text" id="city" name="city" required>
        <span class="error" id="cityerr"></span>

        <label for="state">State</label>
        <input type="text" id="state" name="state" required>
        <span class="error" id="stateerr"></span>

        <label for="zip">ZIP Code</label>
        <input type="text" id="zip" name="zip" required>
        <span class="error" id="ziperr"></span>

        <label for="country">Country</label>
        <input type="text" id="country" name="country" required>
        <span class="error" id="countryerr"></span>

        <button type="button" id="next-step-1">Next</button>
      </div>

      <!-- Step 2: Payment Information -->
      <div class="step" id="step-2">
        <label for="amount">Payble Amount</label>
        <input type="text" id="amount" name="amount" value="<?php echo htmlspecialchars($amount) ?>" required placeholder="Enter amount in USD" min="1" readonly>
        <input type="text" id="product" name="product" value="<?php echo htmlspecialchars($item) ?>" required placeholder="your product" min="1" readonly>

        <label for="card-element">Card Details</label>
        <div id="card-element"></div> <!-- Stripe Element for card details -->

        <button type="button" id="back-step-2" class="back-button">Back</button>
        <button type="submit" id="submit-payment">Pay</button>
      </div>


      <div class="step" id="step-3">
        <div class="success-message">
          Payment Successful! Thank you for your order.
        </div>
      </div>


    </form>
    <div class="success-message" id="payment-status">

    </div>
    <script>
      $(document).ready(function() {
        $('#name').on('input', checkname);
        $('#email').on('input', checkemail);
        $('#address').on('input', checkmailadd);
        $('#city').on('input', checkcity);
        $('#state').on('input', checkstate);
        $('#zip').on('input', checkzip);
        $('#country').on('input', checkcountry);



        $('#email').on('blur', function() {
          var email = $('#email').val();
          // If email is valid, call checkCustomer
          // if (checkemail()) {
          //   checkCustomer(email);
          // }
        });
        var item = "<?php echo htmlspecialchars($item) ?>";

        function decodeHtmlEntities(str) {
          var txt = document.createElement("textarea");
          txt.innerHTML = str;

          // Remove the extra double quotes at start and end
          let cleanStr = txt.value.replace(/^"/, '').replace(/"$/, '');

          const data = JSON.parse(cleanStr);
          return data.map(obj => obj.name);
        }

        try {

          let names = decodeHtmlEntities(item);
          console.log("Names:", names);
          $('#product').val(names);
        } catch (error) {
          console.error("Error processing data:", error);
        }

      })

      var stripe = Stripe('pk_test_51LSAwGSH0s7VQxZwacaFgnyuTHwXjwEnmKVCZEMALhLZRUvAgZKEEnAcAKLO2PiRFfGmqfXS12sBhVTWuXL5CAQr00Gaz5QtcF'); // Your Stripe publishable key
      var elements = stripe.elements();
      var card = elements.create('card');
      card.mount('#card-element');


      var currentStep = 1;
      var form = document.getElementById('payment-form');

      // Step Navigation Functions
      function showStep(step) {
        var allSteps = document.querySelectorAll('.step');
        allSteps.forEach(function(stepEl) {
          stepEl.classList.remove('active');
        });
        document.getElementById('step-' + step).classList.add('active');


      }

      // Handle Next Button (Step 1)
      document.getElementById('next-step-1').addEventListener('click', function() {
        // Validate Step 1 - Ensure that the fields are filled out
        var name = document.getElementById('name').value;
        var email = document.getElementById('email').value;
        var address = document.getElementById('address').value;
        var city = document.getElementById('city').value;
        var state = document.getElementById('state').value;
        var zip = document.getElementById('zip').value;
        var country = document.getElementById('country').value;

        if (!checkname() || !checkemail() || !checkcity() || !checkstate() || !checkzip() || !checkmailadd() || !checkcountry()) {
          if (!checkname() && !checkemail() && !checkcity() && !checkstate() && !checkzip() && !checkmailadd() && !checkcountry()) {


            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'Please complete all fields in Step 1.',
            });
          };
          return;
        }

        showStep(2); // Move to Step 2
      });

      // Handle Back Button (Step 2)
      document.getElementById('back-step-2').addEventListener('click', function() {
        showStep(1); // Go back to Step 1
      });


      form.addEventListener('submit', function(event) {
        event.preventDefault();

        var name = document.getElementById('name').value;
        var email = document.getElementById('email').value;
        var address = document.getElementById('address').value;
        var city = document.getElementById('city').value;
        var state = document.getElementById('state').value;
        var zip = document.getElementById('zip').value;
        var country = document.getElementById('country').value;
        var amount = document.getElementById('amount').value * 100; // Convert amount to cents
        var product = document.getElementById('product').value;
        // Create the Stripe token for card details
        stripe.createToken(card).then(function(result) {
          if (result.error) {
            console.error(result.error.message);
            Swal.fire({
              icon: 'error',
              title: 'Card Error',
              text: 'There was an error with your card information.',
            });
          } else {
            var token = result.token.id; // The token ID

            // Send the token, customer details, and amount to your backend using AJAX
            $.ajax({
              url: 'checkout.php',
              method: 'POST',
              dataType: 'json',
              data: JSON.stringify({
                token: token,
                name: name,
                email: email,
                address: address,
                city: city,
                state: state,
                zip: zip,
                country: country,
                amount: amount,
                product: product
              }),
              contentType: 'application/json',
              success: function(data) {
                if (data.success) {


                  stripe.confirmCardPayment(data.charge.client_secret, {
                    payment_method: {
                      card: card,
                      billing_details: {
                        name: name,
                        email: email,
                        address: address
                      }
                    }
                  }).then(function(result) {
                    if (result.error) {
                      // Show error to your customer
                      $("#payment-status").text(`Payment failed: ${result.error.message}`);
                      Swal.fire({
                        icon: 'error',
                        title: 'Payment Failed',
                        text: result.error.message,
                      });
                    } else {
                      if (result.paymentIntent.status === 'succeeded') {
                        Swal.fire({
                          icon: 'success',
                          title: 'Payment Successful!',
                          text: 'Thank you for your order.',
                        });
                       
                        window.location.href = 'products.html';
                      } else if (result.paymentIntent.status === 'requires_action') {
                        
                        stripe.handleCardAction(result.paymentIntent.client_secret);
                      }
                    }
                  });

                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Payment Failed',
                    text: 'There was an error processing your payment.',
                  });
                  $('#email').val('');
                }
              },


              error: function(xhr, status, error) {
                console.error('Error during payment: ' + error);
                Swal.fire({
                  icon: 'error',
                  title: 'Payment Error',
                  text: 'There was an error processing your payment. Please try again.',
                });
                $('#email').val('');
              }
            });
          }
        });
      });

      function PaymentAsExisting(customerId) {


        var amount = document.getElementById('amount').value * 100; // Convert amount to cents
        var product = document.getElementById('product').value;
        // Create the Stripe token for card details



        $.ajax({
          url: 'checkoutWexisting.php',
          method: 'POST',
          data: {
            customerId: customerId,
            amount: amount,
            product: product
          },
          success: function(data) {
            console.log(data);
            data = JSON.parse(data);
            if (data.success === true) {
              showStep(3);
              Swal.fire({
                icon: 'success',
                title: 'Payment Successful!',
                text: 'Thank you for your order.',
              });
              setTimeout(function() {
                window.location.href = 'products.html';
              }, 4000);
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Payment Failed',
                text: 'There was an error processing your payment.',
              });
              $('#email').val('');
            }
          },
          error: function(xhr, status, error) {
            console.error('Error during payment: ' + error);
            Swal.fire({
              icon: 'error',
              title: 'Payment Error',
              text: 'There was an error processing your payment. Please try again.',
            });
            $('#email').val('');
          }
        });
      }




      function confirmPaymentIntent(clientSecret) {
        $.ajax({
          url: "confirm-payment.php",
          method: "POST",
          contentType: "application/json",
          data: JSON.stringify({
            clientSecret: clientSecret
          }),
          success: function(result) {
            console.log(result);

            if (result.success) {
              $("#payment-status").text("Payment Successful!");
            } else {
              $("#payment-status").text(`Payment failed: ${result.error || "Unknown error"}`);
            }
          },
          error: function(xhr, status, error) {
            $("#payment-status").text(`Error: ${error}`);
          }
        });
      }

      // Validation functions
      function checkname() {
        let pattern = /^[a-zA-Z\s]+$/;
        let name = $('#name').val();
        let validname = pattern.test(name);

        if (name == '') {
          $("#nameerr").text("Please enter your name");
          return false;
        }
        if (!validname) {
          $("#nameerr").text("Please enter a valid name");
          return false;
        }
        $("#nameerr").text("");
        return true;
      }

      function checkemail() {
        let pattern = /^[\w.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        let email = $('#email').val();
        let validemail = pattern.test(email);

        if (email == '') {
          $("#emailerr").text("Please enter your email");
          return false;
        }
        if (!validemail) {
          $("#emailerr").text("Please enter a valid email address");
          return false;
        }
        $("#emailerr").text("");
        return true;
      }

      function checkmailadd() {
        let pattern = /^[a-zA-Z0-9\s,.\-\/#&()]+(\r?\n[a-zA-Z0-9\s,.\-\/#&()]+)*$/;
        let mail_add = $('#address').val();
        let validmail_add = pattern.test(mail_add);

        if (mail_add == "") {
          $("#adderr").text("Please enter an address");
          return false;
        }
        if (!validmail_add) {
          $("#adderr").text("Please enter a valid address");
          return false;
        }
        $("#adderr").text("");
        return true;
      }

      function checkcity() {
        let pattern = /^[a-zA-Z\s]+$/;
        let city = $('#city').val();
        let validcity = pattern.test(city);

        if (city == "") {
          $("#cityerr").text("Please enter a city");
          return false;
        }
        if (!validcity) {
          $("#cityerr").text("Please enter a valid city name");
          return false;
        }
        $("#cityerr").text("");
        return true;
      }

      function checkzip() {
        let pattern = /^[0-9]{6}$/;
        let zip = $('#zip').val();
        let validzip = pattern.test(zip);

        if (zip == "") {
          $("#ziperr").text("Please enter a ZIP code");
          return false;
        }
        if (!validzip) {
          $("#ziperr").text("Please enter a valid ZIP code");
          return false;
        }
        $("#ziperr").text("");
        return true;
      }

      function checkcountry() {
        let pattern = /^[a-zA-Z\s]+$/;
        let country = $('#country').val();
        let validcountry = pattern.test(country);

        if (country == "") {
          $("#countryerr").text("Please enter a country");
          return false;
        }
        if (!validcountry) {
          $("#countryerr").text("Please enter a valid country name");
          return false;
        }
        $("#countryerr").text("");
        return true;
      }

      function checkstate() {
        let pattern = /^[a-zA-Z\s]+$/;
        let state = $('#state').val();
        let validstate = pattern.test(state);

        if (state == "") {
          $("#stateerr").text("Please enter a state");
          return false;
        }
        if (!validstate) {
          $("#stateerr").text("Please enter a valid state name");
          return false;
        }
        $("#stateerr").text("");
        return true;
      }

      function checkCustomer(email) {
        $.ajax({
          url: 'checkCustomer.php',
          method: 'POST',
          data: {
            email: email
          },
          success: function(response) {
            console.log("Check response:", response); // Log the response for debugging

            // If the customer exists
            if (response === 'Customer ID matches') {
              Swal.fire({
                icon: 'warning',
                title: 'Customer Exists',
                text: 'This email is already registered. Would you like to verify your Customer ID or change your email?',
                showCancelButton: true,
                confirmButtonText: 'Verify Customer ID',
                cancelButtonText: 'Change Email',
              }).then((result) => {
                if (result.isConfirmed) {
                  // If user clicks "Verify Customer ID", proceed to customer ID verification
                  Swal.fire({
                    title: 'Enter your Customer ID',
                    input: 'text',
                    inputLabel: 'Customer ID',
                    inputPlaceholder: 'Enter your customer ID',
                    inputValidator: (value) => {
                      if (!value) {
                        return 'Customer ID cannot be empty!';
                      }
                    },
                    preConfirm: (customerId) => {
                      // Validate customer ID
                      return checkCustomerId(email, customerId);
                    }
                  });
                } else {
                  // If user clicks "Change Email", show the email input as a new email.
                  $('#email').val(''); // Clear the email field
                  $('#email').focus(); // Focus on the email field to let the user input a new email
                  Swal.fire({
                    icon: 'info',
                    title: 'Change Email',
                    text: 'Please enter a new email address.',
                  });
                }
              });
            } else {
              // If customer does not exist, proceed normally
              console.log('Customer does not exist');
            }
          },
          error: function(xhr, status, error) {
            console.error('Error while checking customer:', error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'There was an error while checking customer.',
            });
          }
        });
      }

      function checkCustomerId(email, customerId) {
        $.ajax({
          url: 'checkCustomerId.php', // Endpoint to validate customer ID
          method: 'POST',
          data: {
            email: email,
            customerId: customerId
          },
          success: function(response) {
            console.log(response);
            if (response === 'Customer ID matches') {
              // Customer ID verified successfully
              Swal.fire({
                icon: 'success',
                title: 'Customer Verified',
                text: 'Customer ID verified successfully. Proceeding to payment.',
              }).then(() => {
                // Proceed with payment or next steps
                proceedToPayment(customerId);
              });
            } else {
              // Customer ID mismatch
              Swal.fire({
                icon: 'error',
                title: 'Verification Failed',
                text: 'The Customer ID does not match the records.',
              });
            }
          },
          error: function(xhr, status, error) {
            console.error('Error while verifying customer ID:', error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'There was an error while verifying Customer ID.',
            });
          }
        });
      }

      // Proceed to the payment after customer is verified
      function proceedToPayment(customerId) {
        // Logic for proceeding with the payment (e.g., calling the existing payment function)
        PaymentAsExisting(customerId); // Make sure this is your intended function for processing the payment
        console.log('Proceeding to payment...');
      }
    </script>

  </body>

  </html>
<?php } else {
?>
  <section>
    <p>For Shopping visit then peoceed to <a href="products.html">our store</a>Pay</p>
  </section>
<?php
}

?>