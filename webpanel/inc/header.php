<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHPScanner web panel</title>

    <style>
        <?=file_get_contents(PHPSC_ROOT . '/webpanel/css/style.css')?>
    </style>


    <link href='https://fonts.googleapis.com/css?family=Raleway:400,700,500' rel='stylesheet' type='text/css'>
</head>
<body>

<div class="wrapper">

    <header>
        <div class="logo"><h1>PHPScanner <em>v<?=$this->get_phpsc_version()?></em></h1> <h2>Scanning your user uploads, so you don't have to worry!</h2></div>
    </header>

    <div class="content">