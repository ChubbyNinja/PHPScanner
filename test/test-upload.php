<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<?php
if (isset($_FILES)) {
    ?>
    <pre>
        <?php var_dump($_FILES);
    ?>
        <?php var_dump($_POST);
    ?>
    </pre>
<?php

}
?>
<hr>

<form action="test-upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file[]" multiple>
    <input type="text" name="test" value="tester">
    <input type="submit">
</form>

</body>
</html>