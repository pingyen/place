<?php
    session_start();

    $_SESSION['key'] = uniqid();

    header('Location: map');
?>
