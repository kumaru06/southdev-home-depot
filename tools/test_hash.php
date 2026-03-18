<?php
$hash = '$2y$10$u5u9RGJq.87xvwl7vhzskeuV6vDhZhn1aLxsxbAQdy17I6zB.ut7W';
$pw = 'Demo@1234';
var_dump(password_verify($pw, $hash));
