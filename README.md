# PHPScanner
PHPScanner is a small and simple script which scans uploaded content for known scripts like c99 before your PHP script handles the request

## Usage
This script is intended to be included at your php.ini level, example:
```
auto_prepend_file =/etc/php5/extensions/PHPScanner/scanner.php
```

The benefit of this, is you do not need to remember to include it for each project, as long as that project is running on the same web server this script will be included.

You can alternatively include this manually in your PHP script
```
<?php
require( 'path/to/PHPScanner/scanner.php' );
```

## Example Output
There is no need to change how your uploads are handled (not even for WordPress, Joomla or other Content Management Systems).

Here's an example output for `var_dump( $_FILES );`

```
array(1) {
  ["my_file"]=>
  array(7) {
    ["name"]=>
    string(11) "Capture.JPG"
    ["type"]=>
    string(10) "image/jpeg"
    ["tmp_name"]=>
    string(14) "/tmp/phpJ1kFGr"
    ["error"]=>
    int(0)
    ["size"]=>
    int(127218)
    ["scan_results"]=>
    string(2) "OK"
    ["scan_details"]=>
    array(0) {
    }
  }
}
```

As you can see, there are two additional keys `scan_results` and `scan_details`.

1. `scan_results` is a string, with two values available

   **OK** - Scan completed and the file was considered clean
   
   **PUP** - Scan completed and the file was considered a Potentially Unwanted Program