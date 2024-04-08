<?php
session_start();

unset($_SESSION["forgot_password"]);
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
        <div class="loading d-none">
            <span class="alert alert-success d-block text-center">Please wait a moment . . .</span>
            <img src="./dist/img/loading.gif" alt="">
        </div>

        <div class="login-form d-none">
            <span class="alert d-block text-center d-none" id="alert_message"></span>

            <div class="card" style="width: 500px;">
                <div class="card-header text-center">
                    <img src="./dist/img/logo-big.png" style="width: 100px;" alt="Logo" class="mb-2 pt-3">
                    <h1 class="text-center">OTP Generator and Authenticator</h1>
                </div>
                <div class="card-body">
                    <p class="text-center mb-3" style="font-size: 18px;">Test your Email and Password</p>

                    <form action="javascript:void(0)" id="login_form">
                        <div class="form-group mb-3">
                            <label for="login_email">Email</label>
                            <input type="email" class="form-control" id="login_email" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="login_password">Password</label>
                            <input type="password" class="form-control" id="login_password" required>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="login_show_password">
                            <label class="form-check-label" for="login_show_password">Show Password</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="login_submit">Submit</button>

                        <div class="mt-3">
                            <span>Forgot your password?</span>
                            <a href="javascript:void(0)" id="forgot_password_link">Click here</a>
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
            check_database();

            $("#login_show_password").change(function() {
                var passwordField = $("#login_password");
                var passwordFieldType = passwordField.attr("type");

                if ($(this).is(":checked")) {
                    passwordField.attr("type", "text");
                } else {
                    passwordField.attr("type", "password");
                }
            })

            $("#login_form").submit(function() {
                const email = $("#login_email").val();
                const password = $("#login_password").val();

                $("#login_submit").text("Please Wait...");
                $("#login_submit").attr("disabled", true);

                $("#alert_message").addClass("d-none");
                $("#alert_message").removeClass("alert-success");
                $("#alert_message").removeClass("alert-danger");

                var formData = new FormData();

                formData.append('email', email);
                formData.append('password', password);
                formData.append('login', true);

                $.ajax({
                    url: 'server.php',
                    data: formData,
                    type: 'POST',
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        setTimeout(function() {
                            $("#alert_message").text(response.message)
                            $("#alert_message").addClass("alert-" + response.alert);
                            $("#alert_message").removeClass("d-none");

                            $("#login_submit").text("Submit");
                            $("#login_submit").removeAttr("disabled");
                        }, 1500);
                    },
                    error: function(_, _, error) {
                        console.error(error);
                    }
                });
            })

            $("#forgot_password_link").click(function() {
                var formData = new FormData();

                formData.append('forgot_password', true);

                $.ajax({
                    url: 'server.php',
                    data: formData,
                    type: 'POST',
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response) {
                            location.href = "forgot_password.php";
                        }
                    },
                    error: function(_, _, error) {
                        console.error(error);
                    }
                });
            })

            function check_database() {
                var formData = new FormData();

                formData.append('check_database', true);

                $.ajax({
                    url: 'server.php',
                    data: formData,
                    type: 'POST',
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response) {
                            $(".loading").addClass("d-none");
                            $(".login-form").removeClass("d-none");
                        } else {
                            $(".loading").removeClass("d-none");
                            $(".login-form").addClass("d-none");

                            initialize_database();
                        }
                    },
                    error: function(_, _, error) {
                        console.error(error);
                    }
                });
            }

            function initialize_database() {
                setTimeout(function() {
                    var formData = new FormData();

                    formData.append('initialize_database', true);

                    $.ajax({
                        url: 'server.php',
                        data: formData,
                        type: 'POST',
                        dataType: 'JSON',
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response) {
                                $(".loading").addClass("d-none");
                                $(".login-form").removeClass("d-none");
                            }
                        },
                        error: function(_, _, error) {
                            console.error(error);
                        }
                    });
                }, 1500);
            }
        })
    </script>
</body>

</html>