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
        <div class="loading">
            <span class="alert alert-primary d-block text-center">Verifying link for OTP Code . . .</span>
            <img src="./dist/img/loading.gif" alt="">
        </div>

        <div class="otp-form d-none">
            <div class="card" style="width: 500px;">
                <div class="card-header text-center">
                    <img src="./dist/img/logo-big.png" style="width: 100px;" alt="Logo" class="mb-2 pt-3">
                    <h1 class="text-center">OTP Generator and Authenticator</h1>
                </div>
                <div class="card-body">
                    <p class="text-center mb-3" style="font-size: 18px;">Enter your new password</p>

                    <form action="javascript:void(0)" id="reset_password_form">
                        <div class="form-group mb-3">
                            <label for="reset_password_password">Password</label>
                            <input type="password" class="form-control" id="reset_password_password" required>
                            <small class="text-danger d-none" id="error_reset_password_password">Passwords do not match.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="reset_password_confirm_password">Confirm Password</label>
                            <input type="password" class="form-control" id="reset_password_confirm_password" required>
                        </div>

                        <input type="hidden" id="reset_password_otp">

                        <button type="submit" class="btn btn-primary w-100" id="forgot_password_submit">Submit</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="error-form d-none">
            <span class="alert alert-danger d-block text-center d-none" id="alert_error_message"></span>

            <div class="card" style="width: 500px;">
                <div class="card-header text-center">
                    <img src="./dist/img/logo-big.png" style="width: 100px;" alt="Logo" class="mb-2 pt-3">
                    <h1 class="text-center">OTP Generator and Authenticator</h1>
                </div>
                <div class="card-body">
                    <div class="py-3">
                        <a href="index.php" class="btn btn-primary w-100">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Modal -->
    <div class="modal fade" id="otp_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">OTP Verification</h1>
                </div>
                <form action="javascript:void(0)" id="otp_form">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="otp_code">Enter 6-Digit OTP Code</label>
                            <input type="number" id="otp_code" class="form-control" required>
                            <small class="text-danger d-none" id="error_otp_code">Invalid OTP Code</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="otp_submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="dist/js/bootstrap.js"></script>
    <script src="dist/js/bootstrap.bundle.js"></script>
    <script src="dist/js/jquery.js"></script>

    <script>
        jQuery(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const hashed_otp = urlParams.has('otp') ? urlParams.get('otp') : null;

            $("#alert_error_message").removeClass("alert-danger");
            $("#alert_error_message").removeClass("alert-succcess");

            setTimeout(function() {
                if (hashed_otp) {
                    check_hashed_otp(hashed_otp);
                } else {
                    $(".loading").addClass("d-none");
                    $(".error-form").removeClass("d-none");

                    $("#alert_error_message").addClass("alert-danger");
                    $("#alert_error_message").removeClass("d-none");
                    $("#alert_error_message").text("Invalid or expired link!");
                }
            }, 1500);

            $("#reset_password_form").submit(function() {
                const otp = $("#reset_password_otp").val();
                const password = $("#reset_password_password").val();
                const confirm_password = $("#reset_password_confirm_password").val();

                if (password != confirm_password) {
                    $("#error_reset_password_password").removeClass("d-none");

                    $("#reset_password_password").addClass("is-invalid");
                    $("#reset_password_confirm_password").addClass("is-invalid");
                } else {
                    $("#forgot_password_submit").text("Please Wait...");
                    $("#forgot_password_submit").attr("disabled", true);

                    setTimeout(function() {
                        var formData = new FormData();

                        formData.append('password', password);
                        formData.append('otp', otp);
                        formData.append('reset_password', true);

                        $.ajax({
                            url: 'server.php',
                            data: formData,
                            type: 'POST',
                            dataType: 'JSON',
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response) {
                                    $(".error-form").removeClass("d-none");
                                    $(".otp-form").addClass("d-none");

                                    $("#alert_error_message").addClass("alert-success");
                                    $("#alert_error_message").removeClass("d-none");
                                    $("#alert_error_message").text("Reset password successful.");
                                }
                            },
                            error: function(_, _, error) {
                                console.error(error);
                            }
                        });
                    }, 1500);
                }
            })

            $("#reset_password_password").keydown(function() {
                $("#error_reset_password_password").addClass("d-none");

                $("#reset_password_password").removeClass("is-invalid");
                $("#reset_password_confirm_password").removeClass("is-invalid");
            })

            $("#reset_password_confirm_password").keydown(function() {
                $("#error_reset_password_password").addClass("d-none");

                $("#reset_password_password").removeClass("is-invalid");
                $("#reset_password_confirm_password").removeClass("is-invalid");
            })

            $("#otp_form").submit(function() {
                const otp_code = $("#otp_code").val();

                if (otp_code.length == 6) {
                    $("#otp_submit").text("Please Wait...");
                    $("#otp_submit").attr("disabled", true);

                    setTimeout(function() {
                        var formData = new FormData();

                        formData.append('otp_code', otp_code);
                        formData.append('otp_check_otp', true);

                        $.ajax({
                            url: 'server.php',
                            data: formData,
                            type: 'POST',
                            dataType: 'JSON',
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response) {
                                    $("#reset_password_otp").val(otp_code);
                                    $("#otp_modal").modal("hide");
                                } else {
                                    $("#otp_code").addClass("is-invalid");
                                    $("#error_otp_code").removeClass("d-none");

                                    $("#otp_submit").text("Submit");
                                    $("#otp_submit").removeAttr("disabled");
                                }
                            },
                            error: function(_, _, error) {
                                console.error(error);
                            }
                        });
                    }, 1500);
                } else {
                    $("#otp_code").addClass("is-invalid");
                    $("#error_otp_code").removeClass("d-none");
                }
            })

            $("#otp_code").keydown(function() {
                $("#otp_code").removeClass("is-invalid");
                $("#error_otp_code").addClass("d-none");
            })

            function check_hashed_otp(hashed_otp) {
                var formData = new FormData();

                formData.append('hashed_otp', hashed_otp);
                formData.append('check_hashed_otp', true);

                $.ajax({
                    url: 'server.php',
                    data: formData,
                    type: 'POST',
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response) {
                            $("#otp_modal").modal("show");

                            $(".loading").addClass("d-none");
                            $(".otp-form").removeClass("d-none");
                        } else {
                            $(".loading").addClass("d-none");
                            $(".error-form").removeClass("d-none");

                            $("#alert_error_message").addClass("alert-danger");
                            $("#alert_error_message").removeClass("d-none");
                            $("#alert_error_message").text("Invalid or expired link!");
                        }
                    },
                    error: function(_, _, error) {
                        console.error(error);
                    }
                });
            }
        })
    </script>
</body>

</html>