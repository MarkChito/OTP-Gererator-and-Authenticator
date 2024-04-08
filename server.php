<?php
session_start();

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$servername = "localhost";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password);

$databaseName = "otp_generator_and_authenticator";

function send_email($recepient_name, $recepient_email, $subject, $message, $sender_name, $sender_username, $sender_password)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $sender_username;
        $mail->Password   = $sender_password;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->isHTML(true);
        $mail->setFrom($sender_username, $sender_name);
        $mail->addAddress($recepient_email, $recepient_name);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();

        return true;
    } catch (Exception $e) {
        return false;
    }
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST["check_database"])) {
    $sql = "SHOW DATABASES LIKE '" . $databaseName . "'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo json_encode(true);
    } else {
        echo json_encode(false);
    }
}

if (isset($_POST["initialize_database"])) {
    $sql = "CREATE DATABASE IF NOT EXISTS $databaseName";
    $conn->query($sql);

    $conn->select_db($databaseName);

    $sql = "CREATE TABLE IF NOT EXISTS `users` (`id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(30) NOT NULL, `email` VARCHAR(50) NOT NULL, `password` VARCHAR(255) NOT NULL, `otp` VARCHAR(6) NOT NULL)";
    $conn->query($sql);

    $data = array(
        "Administrator's Data" => array(
            "name" => 'Administrator',
            "email" => 'admin@gmail.com',
            "password" => '$2y$10$BmRa8k98SRp2IVQ5CEmlsu6aqpB/q.elkY6vX58OAg1HlK3CGlkh.',
            "otp" => "_null",
        ),
        "Juan's Data" => array(
            "name" => 'Juan Dela Cruz',
            "email" => 'juan@gmail.com',
            "password" => '$2y$10$CTZtEo2pK8Hw/ZqdyfJfSOG825fEzzhrisTUW8zr1gS0ri924OpYW',
            "otp" => "_null"
        ),
        "Pedro's Data" => array(
            "name" => 'Pedro Penduko',
            "email" => 'pedro@gmail.com',
            "password" => '$2y$10$Vkg14Wiu3C0BDn4nT3Okdu1fwCZso6tMJ/2Q2QL7yksv53YHTHxwK',
            "otp" => "_null"
        ),
    );

    foreach ($data as $userData) {
        $name = $userData['name'];
        $email = $userData['email'];
        $password_hash = $userData['password'];
        $otp = $userData['otp'];

        $sql = "INSERT INTO users (`name`, `email`, `password`, `otp`) VALUES ('$name', '$email', '$password_hash', '$otp')";
        $conn->query($sql);
    }

    $conn->close();

    echo json_encode(true);
}

if (isset($_POST["login"])) {
    $response = null;

    $conn->select_db($databaseName);

    $email = $_POST["email"];
    $password = $_POST["password"];

    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);

    $sql = "SELECT * FROM `users` WHERE email='" . $email . "'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $response = array(
                "status" => 200,
                "message" => "You fetched " . $row['name'] . "'s data.",
                "alert" => "success"
            );
        } else {
            $response = array(
                "status" => 404,
                "message" => "Email or Password is incorrect!",
                "alert" => "danger"
            );
        }
    } else {
        $response = array(
            "status" => 404,
            "message" => "Email or Password is incorrect!",
            "alert" => "danger"
        );
    }

    $conn->close();

    echo json_encode($response);
}

if (isset($_POST["forgot_password"])) {
    $_SESSION["forgot_password"] = true;

    echo json_encode(true);
}

if (isset($_POST["check_email"])) {
    $response = false;

    $conn->select_db($databaseName);

    $email = $_POST["email"];

    $email = mysqli_real_escape_string($conn, $email);

    $sql = "SELECT * FROM `users` WHERE email='" . $email . "'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $response = true;
    }

    echo json_encode($response);
}

if (isset($_POST["send_otp"])) {
    $response = false;

    $conn->select_db($databaseName);

    $email = $_POST["email"];

    $otp = mt_rand(100000, 999999);
    $hashed_otp = md5($otp);

    $sql = "UPDATE `users` SET `otp` = '" . $otp . "' WHERE `email` = '" . $email . "'";
    $conn->query($sql);

    $link = "http://localhost/OTP-Gererator-and-Authenticator/reset_password.php?otp=" . $hashed_otp;

    $sql_2 = "SELECT * FROM `users` WHERE email='" . $email . "'";
    $result_2 = $conn->query($sql_2);

    $row = $result_2->fetch_assoc();

    $recepient_name = $row["name"];
    $recepient_email = $row["email"];
    $subject = "Reset Password";
    $message = "
    <h1>Reset Password</h1>
    <br>
    <p>To reset your password, please click the link below:</p>
    <p>
        <a href='" . $link . "'>" . $link . "</a>
    </p>
    <br>
    <p>Here is your 6-digit OTP code: <b>" . $otp . "</b></p>
    <br>
    <br>
    <p>-- PHP Mailer Team --</p>
    ";

    $sender_name = "PHP Mailer Team";
    $sender_username = "phpmailer.00001@gmail.com";
    $sender_password = "vhmfoycjdqbnqeqq";

    if (send_email($recepient_name, $recepient_email, $subject, $message, $sender_name, $sender_username, $sender_password)) {
        $response = true;
    }

    echo json_encode($response);
}

if (isset($_POST["check_hashed_otp"])) {
    $response = false;

    $hashed_otp = $_POST["hashed_otp"];

    $conn->select_db($databaseName);

    $sql = "SELECT * FROM `users`";
    $result = $conn->query($sql);

    $otps = array();

    while ($row = $result->fetch_assoc()) {
        array_push($otps, $row["otp"]);
    }

    foreach ($otps as $otp) {
        if ($hashed_otp == md5($otp)) {
            $response = true;
        }
    }

    echo json_encode($response);
}

if (isset($_POST["otp_check_otp"])) {
    $response = false;

    $conn->select_db($databaseName);

    $otp_code = $_POST["otp_code"];

    $sql = "SELECT * FROM `users` WHERE `otp` = '" . $otp_code . "'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $response = true;
    }

    echo json_encode($response);
}

if (isset($_POST["reset_password"])) {
    $response = true;

    $conn->select_db($databaseName);

    $otp = $_POST["otp"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    $sql = "UPDATE `users` SET `password` = '" . $password . "', `otp` = '_null' WHERE `otp` = '" . $otp . "'";
    $result = $conn->query($sql);

    echo json_encode($response);
}
