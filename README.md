# PHPScanner
PHPScanner is a small and simple script which scans uploaded content for known scripts like c99 before your PHP script handles the request

# Usage
This script is intended to be included at your php.ini level, example:
```
auto_prepend_file =/etc/php5/extensions/PHPScanner/scanner.php
```