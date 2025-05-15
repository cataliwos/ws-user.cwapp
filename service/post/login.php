<?php
namespace Catali;
use TymFrontiers\HTTP\Header,
    TymFrontiers\Generic,
    TymFrontiers\InstanceError,
    TymFrontiers\API\Authentication,
    TymFrontiers\Data;
use TymFrontiers\Location;
use TymFrontiers\MultiForm;
use TymFrontiers\Validator;

require_once "../../.appinit.php";
\header("Content-Type: application/json");

$post = $_POST;
$data_obj = new Data;
$gen = new Generic;
$params = $gen->requestParam([
  "userid" => ["userid", "pattern", "/^((252([\d\-\s]{8,15}))|(([a-z\d\-\_]+)\@([a-z\d\-\.]+)\.([a-z]{2,}))|(\+?([\d\s\-]{10,15})))$/"],
  "password" =>["password","text",6,32],
  "remember" => ["remember", "boolean"],
  "country_code" => ["country_code", "username", 2, 2],
  "rdt" => ["rdt", "url"],
  "payload" =>["payload","script",1,0],
  "form" => ["form","text",2,55],
  "CSRF_token" => ["CSRF_token","text",5,500]
], $post, ["userid", "password", "CSRF_token", "form"]);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen, false))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted"
  ]);
  exit;
}
if ( !$gen->checkCSRF($params["form"],$params["CSRF_token"]) ){
  $errors = (new InstanceError ($gen, false))->get("checkCSRF",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "otp_req" => false,
    "email" => "",
    "message" => "Request failed.",
    "rdt" => ""
  ]);
  exit;
} if (empty($params['country_code'])) {
  $params['country_code'] = @ (new Location())->country_code;
}
$rdt = !empty($params['rdt']) ? $params['rdt'] : WHOST;
$remember = $params['remember'];
unset($params["form"]);
unset($params["CSRF_token"]);
unset($params["remember"]);

$req_error = [];
$params['userid'] = $data_obj->encodeEncrypt($params['userid']);
$params['password'] = $data_obj->encodeEncrypt($params['password']);
$params['ws'] = get_constant("PRJ_WSCODE");
$token = api_token(get_constant("API_APP_NAME"), $params);
$params["token"] = $token;
// send request
$rest = client_query("https://ws." . get_constant("PRJ_BASE_DOMAIN") ."/ws-service/post/user-login", $params, "POST");
if ($rest && \gettype($rest) == "object") {
  if (!\property_exists($rest, "user") || $rest->status !== "0.0") {
    echo \json_encode($rest);
    exit;
  }
} else {
  echo \json_encode([
    "status" => "5.1",
    "errors" => ["Unable to complete user authentication request, try again later."],
    "message" => "Request failed"
  ]);
  exit;
}
$user = $rest->user;
if (!$remember) {
  $remember = \strtotime("+5 Hours");
} else {
  $remember = \strtotime("+1 Week");
}
$session->login($user, $remember);
$cart_user = !empty($_COOKIE["_wscartusr"]) ? $data_obj->decodeDecrypt($_COOKIE["_wscartusr"]) : null;
$conn = query_conn();
if ($cart_user) {
  $db_name = \get_database("base");
  $conn->query("UPDATE `{$db_name}`.`shopping_cart` SET `user` = '{$conn->escapeValue($session->name)}' WHERE `user` = '{$conn->escapeValue($cart_user)}'");
}
// clear cart
// echo "<tt><pre>";
// print_r($user);
// echo "</pre></tt>"; exit;

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Logged in successfully.",
  "rdt" => Generic::setGet($rdt, [
    "payload" => $params['payload']
  ])
]);
exit;