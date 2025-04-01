<!DOCTYPE html>
<html dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>Nice admin Template - The Ultimate Multipurpose admin template</title>
    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
    <style>
        #error-message {
            color: #d9534f; /* Bootstrap's danger color */
            background-color: #f2dede; /* Light red background */
            border: 1px solid #ebccd1; /* Border color */
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
            font-weight: bold;
            display: none; /* Initially hidden */
        }
        /* Remove arrows from number input */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }

        input[type=number] {
            -moz-appearance: textfield; /* Firefox */
        }
        /* Adjust logo size */
        .logo img {
            width: 100px; /* Adjust width as needed */
            height: auto; /* Maintain aspect ratio */
        }

        /* Adjust font size for Sign Up text */
        .logo h5 {
            font-size: 20px; /* Adjust font size as needed */
        }
    </style>
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>
    <div class="main-wrapper">
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <div class="preloader">
            <div class="lds-ripple">
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center" style="background:url(img/background.jpeg) no-repeat center center; background-size: cover;">
            <div class="auth-box">
                <div>
                    <div class="logo">
                        <span class="db"><img src="img/logo.png" alt="logo" /></span>
                        <h5 class="font-medium m-b-20">Sign Up</h5>
                    </div>
                    <div id="error-message" style="color: red; margin-top: 10px;"></div> <!-- Error message container -->
                    <form class="form-horizontal m-t-20" action="functions/save_signup.php" method="POST">
                        <div class="form-group row">
                            <div class="col-12">
                                <input class="form-control form-control-lg" type="text" name="name" required placeholder="Full Name">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-12">
                                <input class="form-control form-control-lg" type="text" name="email" required placeholder="Email">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-12">
                                <input class="form-control form-control-lg" type="number" name="phone" required placeholder="Phone Number" maxlength="11" value="09">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-12">
                                <input class="form-control form-control-lg" type="text" name="address" required placeholder="Address">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-12">
                                <input class="form-control form-control-lg" type="password" name="password" required placeholder="Password">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-12">
                                <input class="form-control form-control-lg" type="password" name="confirm_password" required placeholder="Confirm Password">
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <div class="col-xs-12 p-b-20">
                                <button class="btn btn-block btn-lg btn-info" type="submit">SIGN UP</button>
                            </div>
                        </div>
                        <div class="form-group m-b-0 m-t-10 ">
                            <div class="col-sm-12 text-center ">
                                Already have an account? <a href="login.php" class="text-info m-l-5 "><b>Sign In</b></a>
                            </div>
                        </div>
                    </form>



                </div>
            </div>
        </div>
    </div>
    </div>
    <!-- ============================================================== -->
    <!-- Login box.scss -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Page wrapper scss in scafholding.scss -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Page wrapper scss in scafholding.scss -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Right Sidebar -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Right Sidebar -->
    <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- All Required js -->
    <!-- ============================================================== -->
    <script src="assets/libs/jquery/dist/jquery.min.js "></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="assets/libs/popper.js/dist/umd/popper.min.js "></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js "></script>
    <!-- ============================================================== -->
    <!-- This page plugin js -->
    <!-- ============================================================== -->
    <script>
        $('[data-toggle="tooltip "]').tooltip();
        $(".preloader ").fadeOut();

        document.querySelector('form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            const formData = new FormData(this);

            fetch('functions/save_signup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const errorMessageDiv = document.getElementById('error-message');
                if (data.error) {
                    errorMessageDiv.textContent = data.error;
                    errorMessageDiv.style.display = 'block'; // Show the error message
                } else {
                    errorMessageDiv.textContent = ''; // Clear any previous error
                    errorMessageDiv.style.display = 'none'; // Hide the error message
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful',
                        text: data.success,
                        timer: 3000, // Close after 3 seconds
                        timerProgressBar: true // Show a progress bar
                    }).then(() => {
                        // Optionally redirect or reset the form
                        // window.location.href = 'login.php'; // Example redirect
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        document.querySelector('input[type="number"]').addEventListener('keydown', function(event) {
            // Allow: backspace, delete, tab, escape, enter, and .
            if ([46, 8, 9, 27, 13, 110, 190].indexOf(event.keyCode) !== -1 ||
                // Allow: Ctrl+A, Command+A
                (event.keyCode === 65 && (event.ctrlKey === true || event.metaKey === true)) ||
                // Allow: home, end, left, right, down, up
                (event.keyCode >= 35 && event.keyCode <= 40)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
                event.preventDefault();
            }
        });

        document.querySelector('input[name="phone"]').addEventListener('input', function(event) {
            let value = event.target.value;

            // Ensure the phone number starts with "09"
            if (!value.startsWith("09")) {
                value = "09" + value.replace(/^0+/, ''); // Remove leading zeros and prepend "09"
            }

            // Limit to 11 digits
            if (value.length > 11) {
                value = value.slice(0, 11);
            }

            event.target.value = value;
        });
    </script>
    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>