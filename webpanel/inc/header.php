<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHPScanner web panel</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/foundation/6.1.1/foundation.min.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway:400,700,500' rel='stylesheet' type='text/css'>

    <style>
        <?=file_get_contents(PHPSC_ROOT.'/webpanel/css/style.css')?>
    </style>

</head>
<body>

<div class="wrapper ">

    <div class="row">
        <header class="large-12 column">
            <div class="row">
                <div class="small-12 large-6 columns">
                    <div class="logo"><h1>PHPScanner <em>v<?=$this->get_phpsc_version()?></em></h1> <h5>Scanning your user uploads, so you don't have to worry!</h5></div>
                </div>
                <div class="small-12 large-6 columns">

                    <?php
                    if ($Webpanel->is_authenticated()) {
                        ?>
                        <ul class="menu">
                            <li><a href="?phpsc&phpsc_action=dashboard">Dashboard</a></li>
                            <li><a href="?phpsc&phpsc_action=logout">Logout</a></li>
                        </ul>
                        <?php

                    }
                    ?>
                </div>
            </div>

        </header>
    </div>

    <div class="content row">