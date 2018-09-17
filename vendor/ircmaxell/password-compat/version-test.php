<?php

require "lib/password.php";
echo "Test for functionality of compat library: " . (\MyParcelModule\PasswordCompat\binary\check() ? "Pass" : "Fail");
echo "\n";
