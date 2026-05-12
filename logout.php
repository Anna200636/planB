<?php
require_once __DIR__ . '/db.php';
session_unset();
session_destroy();
session_start();
setFlash('info', 'Вы вышли из аккаунта.');
redirect('index.php');
?>
