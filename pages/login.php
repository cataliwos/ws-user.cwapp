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
  "payload" =>["payload","script",1,0],
  "eos" =>["eos","boolean"],
  "express" =>["express","boolean"],
  "rdt" =>["rdt","url"]
], $_GET, []);
if (!$params) HTTP\Header::badRequest(true);
if ($session->isLoggedIn()) {
  $rdt = empty($params['rdt']) ? WHOST : $params['rdt'];
  HTTP\Header::redirect(Generic::setGet($rdt, [
    "payload" => $params['payload']
  ]));
}
$ws = ws_info();
$reg_link = Generic::setGet(WHOST . "/ws-user/register", [
  "payload" => $params['payload'],
  "rdt" => $params['rdt'],
  "express" => $params['express']
]);
$params["wscode"] = $ws->wscode;
$params["wsdomain"] = $ws->domain;

if (!$ws->published) HTTP\Header::redirect("/status");
$page_name = "ws-login"; $page_group = "ws-user";

$theme_color = $params['theme'] = $ws->brand_color->name;
$conn = \query_conn();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>User Login | <?php echo $ws->name; ?></title>
    <?php  include PRJ_ROOT . "/src/inc-iconset.php"; ?>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
    <meta name="description" content="User/Customer login">
    <meta name="keywords" content="website, user, login">
    <meta name="author" content="<?php echo get_constant("PRJ_AUTHOR"); ?>">
    <meta name="creator" content="<?php echo get_constant("PRJ_CREATOR"); ?>">
    <meta name="publisher" content="<?php echo get_constant("PRJ_PUBLISHER"); ?>">
    <meta name="robots" content='index'>
    <link rel="stylesheet" href="/app/cataliwos/plugin.cwapp/css/font-awesome.min.css">
    <link rel="stylesheet" href="/app/cataliwos/plugin.cwapp/css/theme.min.css">
    <!-- Project styling -->
    <link rel="stylesheet" href="/app/cataliwos/ws-helper.cwapp/css/helper.min.css">
    <link rel="stylesheet" href="/assets/css/base.min.css">
    <link rel="stylesheet" href="/app/cataliwos/ws-user.cwapp/css/ws-user.min.css">

    <script type="text/javascript">
      if (typeof window["param"] == undefined || !window["param"]) window["param"] = {};
      <?php  foreach ($params as $k=>$val) { echo "param['{$k}'] = '{$val}';"; } ?>
    </script>
  </head>
  <body class="theme-<?php echo $theme_color; ?>">
    <h1 class="hidden">User Login</h1>

    <?php include get_constant("PRJ_INC_HEADER"); ?>
    <section id="main-content">
      <div class="view-space vw-medi">
        <div class="grid-8-tablet grid-7-laptop grid-6-desktop center-tablet">
          <form 
            class="block-ui theme-color <?php echo $theme_color; ?> bg-white drop-shadow paddn -pall -p30 margn -mtop -mbottom -m30"
            action="/ws-user/post/login" 
            autocomplete="off" 
            method="post"
            data-validate="false"
            onsubmit="cwos.form.submit(this, usrLogin); return false;"
          id="usr-login-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken('usr-login-form', \strtotime("+5 Hours")); ?>">
            <input type="hidden" name="form" value="usr-login-form">
            <input type="hidden" name="rdt" value="<?php echo @ $params['rdt']; ?>">
            <input type="hidden" name="payload" value="<?php echo @ $params['payload']; ?>">

            <div class="grid-12-tablet">
              <h2>Please login to continue</h2>
              <p class="font-size-09">Logging in allows you to continue from where you left off and access your orders and requests on our website. You can log in using your <a href="https://www.cataliws.com/index/about-us#catali-profile" target="_blank" class="bold">Catali Profile</a> or create a new one if you don't already have an account. Your information is securely stored and managed in accordance with <a href="https://www.cataliws.com/index/terms-and-conditions" class="bold" target="_blank">Catali's Terms</a> and <a href="https://www.cataliws.com/index/privacy-policy" class="bold" target="_blank">Privacy Policy</a>.</p>
            </div>
            <div class="grid-12-tablet">
              <label for="usr-email">ID: (Email | Profile Code | Phone)</label>
              <input type="text" name="userid" required placeholder="your-email@domain.ext" autocomplete="email" id="usr-email">
            </div>
            <div class="grid-8-tablet">
              <label for="usr-password">Password</label>
              <input type="password" name="password" required placeholder="enter-password" autocomplete="off" id="usr-password">
            </div>
            <div class="grid-6-phone grid-4-tablet push-right"> <br>
              <button type="submit" class="cwos-btn no-shadow <?php echo $ws->brand_color->name; ?>"> Login <i class="fas fa-sign-in-alt"></i></button>
            </div>
            <div class="grid-12-tablet">
              <input type="checkbox" name="remember" id="usr-remember" value="1">
              <label for="usr-remember">Remember me</label>
            </div>
            <br class="c-f">
            <p class="align-center paddn -ptop -p30 margn -mtop -m20 bordr -btop -bsolid -bthin">
              Don't have an account? <br> <a href="<?php echo $reg_link; ?>" class="bold"> <i class="fas fa-plus"></i> Register</a>
              | <a href="https://app.cataliws.com/user/password-reset" target="_blank" class="bold">Forgot password</a>
            </p>
          </form>
          <p class="align-center paddn -p30">
            <?php if (!empty($params['rdt']) && (bool)$params['express']) {
              echo "<a href=\"#\" class=\"bold\" onclick=\"cwos.faderBox.url('/ws-user/popup/express-user', {rdt: '{$params['rdt']}', payload: '{$params['payload']}', theme:'{$theme_color}'}, {exitBtn: true});\">Use One-Time checkout</a>";
            } ?>
          </p>
        </div>
      </div>

    <br class="c-f">      
    </section>
    <?php include get_constant("PRJ_INC_FOOTER"); ?>
    <!-- Required scripts -->
    <script src="/app/cataliwos/plugin.cwapp/js/jquery.min.js"></script>
    <script src="/app/cataliwos/plugin.cwapp/js/functions.min.js"></script>
    <script src="/app/cataliwos/plugin.cwapp/js/constants.min.js"></script>
    <script src="/app/cataliwos/plugin.cwapp/js/class-object.min.js"></script>
    <script src="/app/cataliwos/plugin.cwapp/js/theme.min.js"></script>
    <script src="/app/cataliwos/ws-helper.cwapp/js/ws-helper.min.js"></script>
    <script src="/assets/js/base.min.js"></script>
    <script src="/app/cataliwos/ws-user.cwapp/js/ws-user.min.js"></script>
    <!-- project scripts -->
    <script type="text/javascript">
    </script>
  </body>
</html>