<?php
namespace Catali;
require_once "../.appinit.php";
use TymFrontiers\Data,
  TymFrontiers\Generic,
  TymFrontiers\HTTP,
  TymFrontiers\InstanceError,
  TymFrontiers\MultiForm;
##
$gen = new Generic;
$params = $gen->requestParam([
  "rdt" =>["rdt","url"]
], $_GET, []);
if (!$params) HTTP\Header::badRequest(true);
$rdt = empty($params['rdt']) ? WHOST : $params['rdt'];
if ($session->isLoggedIn()) {
  $session->logout();
  HTTP\Header::redirect($rdt);
} else {
  HTTP\Header::redirect($rdt);
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Logging out</title>
    <?php  include PRJ_ROOT . "/src/inc-iconset.php"; ?>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
    <meta name="description" content="User/Customer login">
    <meta name="keywords" content="website, user, login">
    <meta name="robots" content='index'>
  </head>
  <body> </body>
</html>