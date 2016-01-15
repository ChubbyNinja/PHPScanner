<?php
/**
 * Created for PHPScanner
 * User: Danny Hearnah
 * Author: Skyblue Creations Ltd.
 *
 * Date: 1/15/2016
 * Time: 2:52 PM
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<?php
if( isset($_FILES) ) {
?>
    <pre>
        <?php var_dump( $_FILES ); ?>
    </pre>
<?php
}
?>
<hr>

<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="file[]" multiple>
    <input type="submit">
</form>

</body>
</html>