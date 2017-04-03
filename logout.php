<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 14/01/17
 * Time: 16:07
 */
header('Location: ./login.php?logout=success');
session_start();
session_unset();
exit();