<?php

include __DIR__ . '/../Eszkozok/GlobalSettings.php';

echo password_hash($_REQUEST['pw'], PASSWORD_BCRYPT);