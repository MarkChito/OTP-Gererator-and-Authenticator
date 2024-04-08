<?php
session_start();

if (!isset($_SESSION["forgot_password"])) {
    header("location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>OTP Gererator and Authenticator</title>

    <link rel="shortcut icon" href="dist/img/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="dist/css/bootstrap.css">
</head>

<body class="bg-dark">
    <div class="d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="d-block">
            <span class="alert alert-success d-block text-center d-none" id="alert_message">Please check your Email.</span>

            <div class="card" style="width: 500px;">
                <div class="card-header text-center">
                    <img src="./dist/img/logo-big.png" style="width: 100px;" alt="Logo" class="mb-2 pt-3">
                    <h1 class="text-center">OTP Generator and Authenticator</h1>
                </div>
                <div class="card-body">
                    <p class="text-center mb-3" style="font-size: 18px;">Enter your registered email address</p>

                    <form action="javascript:void(0)" id="forgot_password_form">
                        <div class="form-group mb-3">
                            <label for="forgot_password_email">Email</label>
                            <input type="email" class="form-control" id="forgot_password_email" required>
                            <small class="text-danger d-none" id="error_forgot_password_email">Email is not registered.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="forgot_password_submit">Submit</button>

                        <div class="mt-3">
                            <span>Remember your password?</span>
                            <a href="index.php">Click here</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="dist/js/bootstrap.js"></script>
    <script src="dist/js/bootstrap.bundle.js"></script>
    <script src="dist/js/jquery.js"></script>

    <script>
        jQuery(document).ready(function() {
            $("#forgot_password_form").submit(function() {
                const email = $("#forgot_password_email").val();

                $("#forgot_password_submit").text("Please Wait...");
                $("#forgot_password_submit").attr("disabled", true);

                $("#alert_message").addClass("d-none");

                var formData = new FormData();

                formData.append('email', email);
                formData.append('check_email', true);

                $.ajax({
                    url: 'server.php',
                    data: formData,
                    type: 'POST',
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response) {
                            var formData = new FormData();

                            formData.append('email', email);
                            formData.append('send_otp', true);

                            $.ajax({
                                url: 'server.php',
                                data: formData,
                                type: 'POST',
                                dataType: 'JSON',
                                processData: false,
                                contentType: false,
                                success: function(response) {
                                    if (response) {
                                        $("#alert_message").removeClass("d-none");

                                        $("#forgot_password_email").val("");

                                        $("#forgot_password_submit").removeAttr("disabled");
                                        $("#forgot_password_submit").text("Submit");
                                    }
                                },
                                error: function(_, _, error) {
                                    console.error(error);
                                }
                            });
                        } else {
                            $("#forgot_password_email").addClass("is-invalid");
                            $("#error_forgot_password_email").removeClass("d-none");

                            $("#forgot_password_submit").removeAttr("disabled");
                            $("#forgot_password_submit").text("Submit");
                        }
                    },
                    error: function(_, _, error) {
                        console.error(error);
                    }
                });
            })

            $("#forgot_password_email").keydown(function() {
                $("#forgot_password_email").removeClass("is-invalid");
                $("#error_forgot_password_email").addClass("d-none");
            })
        })
    </script>
</body>

</html>