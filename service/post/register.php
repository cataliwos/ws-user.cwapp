<?php
namespace Catali;
use TymFrontiers\HTTP\Header,
  TymFrontiers\Generic,
  TymFrontiers\InstanceError,
  TymFrontiers\API\Authentication,
  TymFrontiers\Data,
  TymFrontiers\Location,
  TymFrontiers\MultiForm,
TymFrontiers\Validator;

require_once "../../.appinit.php";
\header("Content-Type: application/json");

$post = $_POST;
$data_obj = new Data;
if (!empty($post['country_code']) && !empty($post['phone'])) {
  $post['phone'] = $data_obj->phoneToIntl($post['phone'], $post['country_code']);
}
$gen = new Generic;
$params = $gen->requestParam([
  "country_code" => ["country_code", "username", 2, 2],
  "name" => ["name", "name"],
  "surname" => ["surname", "name"],
  "email" => ["email", "email"],
  "phone" => ["phone", "tel"],
  "password" =>["password","text",6,32],
  "password_repeat" =>["password_repeat","text",6,32],
  "accepted_terms" => ["accepted_terms", "boolean"],
  "rdt" => ["rdt", "url"],
  "payload" =>["payload","script",1,0],
  "otp" =>["otp","username", 3, 28, [], "mixed", [" ", "-", "_", "."]],
  "form" => ["form","text",2,55],
  "CSRF_token" => ["CSRF_token","text",5,500]
], $post, ["country_code", "name", "surname", "email", "phone", "password", "CSRF_token", "form"]);
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
} if ($params['password'] !== $params['password_repeat']) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Password mismatch"],
    "message" => "Request halted"
  ]);
  exit;
} if (!(bool)$params['accepted_terms']) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["You must accept the terms and conditions to proceed."],
    "message" => "Request halted"
  ]);
  exit;
}
$rdt = Generic::setGet((!empty($params['rdt']) ? $params['rdt'] : WHOST), [
  "payload" => $params['payload']
]);

unset($params["form"]);
unset($params["CSRF_token"]);
unset($params["password_repeat"]);
$params['ws'] = $params['referrer'] = get_constant("PRJ_WSCODE");

$req_error = [];
$params['password'] = $data_obj->encodeEncrypt($params['password']);

$token = api_token(get_constant("API_APP_NAME"), $params);
$params["token"] = $token;
// send request
$rest = client_query("https://ws." . get_constant("PRJ_BASE_DOMAIN") ."/ws-service/post/user-register", $params, "POST");
if ($rest && \gettype($rest) == "object") {
  if (empty($rest->user) || $rest->status !== "0.0") {
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
$remember = \strtotime("+5 Hours");

$session->login($user, $remember);
$cart_user = !empty($_COOKIE["_wscartusr"]) ? $data->decodeDecrypt($_COOKIE["_wscartusr"]) : null;
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
  "message" => "Your profile was created and you have been logged in successfully.",
  "rdt" => $rdt
]);
exit;