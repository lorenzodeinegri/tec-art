<?php

require_once ('Controller/LoginController.php');
require_once ('Controller/UsersController.php');
require_once ('Utilities/InputCheckUtilities.php');

session_start();

if (LoginController::isAuthenticatedUser()) {
    header('Location: Errore.php');
}

$message = '';

if (isset($_POST['submit']) && $_POST['submit'] === 'Registrati') {
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
    } else {
        $name = '';
    }
    if (isset($_POST['surname'])) {
        $surname = $_POST['surname'];
    } else {
        $surname = '';
    }
    if (isset($_POST['sex'])) {
        $sex = $_POST['sex'];
    } else {
        $sex = '';
    }
    if (isset($_POST['date'])) {
        $date = $_POST['date'];
    } else {
        $date = '';
    }
    if (isset($_POST['mail'])) {
        $mail = $_POST['mail'];
    } else {
        $mail = '';
    }
    if (isset($_POST['username'])) {
        $username = $_POST['username'];
    } else {
        $username = '';
    }
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    } else {
        $password = '';
    }
    if (isset($_POST['repetePassword'])) {
        $repeated_password = $_POST['repetePassword'];
    } else {
        $repeated_password = '';
    }

    $users_controller = new UsersController();
    $message = $users_controller->addUser($name, $surname, $sex, $date, $mail, $username, $password, $repeated_password);

    unset($users_controller);

    if ($message === '') {
        $_SESSION['username'] = InputCheckUtilities::prepareStringForChecks($_POST['username']);
        $_SESSION['just_registered'] = true;

        header('Location: AreaPersonale.php');
    }
}

if ($message === '') {
    $name = '';
    $surname = '';
    $sex = 'A';
    $date = '';
    $mail = '';
    $username = '';
}

$male = ' ';
$female = ' ';
$other = ' ';

if ($sex === 'A') {
    $other = ' checked="checked" ';
} else if ($sex === 'M') {
    $male = ' checked="checked" ';
} else if ($sex === 'F') {
    $female = ' checked="checked" ';
}

$document = file_get_contents('../HTML/Registrazione.html');
$login = LoginController::getAuthenticationMenu();

$document = str_replace("<span id='loginMenuPlaceholder'/>", $login, $document);
$document = str_replace("<span id='outputMessagePlaceholder'/>", $message, $document);
$document = str_replace("<span id='nameValuePlaceholder'/>", $name, $document);
$document = str_replace("<span id='surnameValuePlaceholder'/>", $surname, $document);
$document = str_replace("<span id='maleCheckedPlaceholder'/>", $male, $document);
$document = str_replace("<span id='femaleCheckedPlaceholder'/>", $female, $document);
$document = str_replace("<span id='otherCheckedPlaceholder'/>", $other, $document);
$document = str_replace("<span id='dateValuePlaceholder'/>", $date, $document);
$document = str_replace("<span id='mailValuePlaceholder'/>", $mail, $document);
$document = str_replace("<span id='usernameValuePlaceholder'/>", $username, $document);

echo $document;

?>
