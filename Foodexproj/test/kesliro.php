<?php

sleep(6);

$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");

$txt = "John Doe\n";
fwrite($myfile, $txt);

fclose($myfile);