<?php
namespace Catali;
require_once "../.appinit.php";
use TymFrontiers\Data,
  TymFrontiers\Generic,
  TymFrontiers\HTTP,
  TymFrontiers\Location,
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
$login_link = Generic::setGet(WHOST . "/ws-user/login", [
  "payload" => $params['payload'],
  "rdt" => $params['rdt'],
  "express" => $params['express']
]);
$ws = ws_info();

$params["wscode"] = $ws->wscode;
$params["wsdomain"] = $ws->domain;

if (!$ws->published) HTTP\Header::redirect("/status");
$page_name = "ws-register"; $page_group = "ws-user";

$theme_color = $params['theme'] = $ws->brand_color->name;
$conn = \query_conn();
$country_code = "";
try {
  $country_code = (new Location())->country_code;
} catch (\Throwable $th) {
}
$user_max_age = 105;
$user_min_age = 18;
$data_db = get_database("data");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>User Registration | <?php echo $ws->name; ?></title>
    <?php  include PRJ_ROOT . "/src/inc-iconset.php"; ?>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
    <meta name="description" content="User/Customer registration">
    <meta name="keywords" content="website, user, registration">
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
    <h1 class="hidden">User Register</h1>

    <?php include get_constant("PRJ_INC_HEADER"); ?>
    <section id="main-content">
      <div class="view-space vw-medi">
        <div class="grid-8-tablet grid-7-laptop grid-6-desktop center-tablet">
          <form 
            class="block-ui theme-color <?php echo $theme_color; ?> bg-white drop-shadow paddn -pall -p30 margn -mtop -mbottom -m30"
            action="/ws-user/post/register" 
            autocomplete="off" 
            method="post"
            data-validate="false"
            onsubmit="chkRegister(this); return false;"
          id="usr-register-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken('usr-register-form', \strtotime("+5 Hours")); ?>">
            <input type="hidden" name="form" value="usr-register-form">
            <input type="hidden" name="rdt" value="<?php echo @ $params['rdt']; ?>">
            <input type="hidden" name="payload" value="<?php echo @ $params['payload']; ?>">
            <input type="hidden" name="otp" id="register-otp" value="">

            <div class="grid-12-tablet">
              <h2>Register to continue</h2>
              <p class="font-size-09">Having an acount allows you to continue from where you left off and access your orders and requests on our website. Your information is securely stored and managed in accordance with <a href="https://www.cataliws.com/index/terms-and-conditions" class="bold" target="_blank">Catali's Terms</a> and <a href="https://www.cataliws.com/index/privacy-policy" class="bold" target="_blank">Privacy Policy</a>.</p>
            </div>
            <div class="grid-7-laptop">
              <label for="country_code">Country/region</label>
              <select name="country_code" id="country_code" required>
                <option value="">* Choose a country</option>
                <optgroup label="Countries">
                  <?php
                  if ($countries = (new MultiForm($data_db, "countries", "code", $database))->findAll()) {
                    foreach ($countries as $c) {
                      echo "<option value=\"{$c->code}\"";
                        echo $country_code == $c->code ? " selected" : "";
                      echo ">{$c->name}</option>";
                    }
                  }
                  ?>
                </optgroup>
              </select>
            </div>
            <div class="grid-6-tablet">
              <label for="usr-name">Name</label>
              <input type="text" name="name" autocomplete="name" id="usr-name" required placeholder="First name">
            </div>
            <div class="grid-6-tablet">
              <label for="usr-surname">Surame</label>
              <input type="text" name="surname" autocomplete="name" id="usr-surname" required placeholder="Surname">
            </div>
            <div class="grid-7-tablet">
              <label for="usr-email">Email</label>
              <input type="email" name="email" required placeholder="your-email@domain.ext" autocomplete="email" id="usr-email">
            </div>
            <div class="grid-5-tablet">
              <label for="usr-phone">Phone</label>
              <input type="tel" name="phone" required placeholder="0801 234 5678" autocomplete="tel-national" id="usr-phone">
            </div>
            <div class="grid-6-tablet">
              <label for="usr-password">New Password</label>
              <input type="password" name="password" required placeholder="enter-password" autocomplete="off" id="usr-password">
            </div>
            <div class="grid-6-tablet">
              <label for="usr-passwordrpt">Repeat Password</label>
              <input type="password" name="password_repeat" required placeholder="repeat-password" autocomplete="off" id="usr-passwordrpt">
            </div>
            <div class="grid-12-tablet align-center">
              <label>Kindly read and accept <a href="https://www.cataliws.com/index/terms-and-conditions" target="_blank"> <b><i class="fas fa-link"></i> Applicable Terms &amp; Conditions</b></a></label>
            </div>
            <div class="grid-7-tablet">
              <input type="checkbox" class="solid" name="accepted_terms" value="1" id="accept-terms">
              <label for="accept-terms" class="bold color-text">I have read and accepted terms.</label>
            </div>
            <div class="grid-10-phone grid-5-tablet push-right">
              <button type="submit" class="cwos-btn no-shadow <?php echo $ws->brand_color->name; ?>"> <i class="fas fa-plus"></i> Register Now</button>
            </div>

            <br class="c-f">
            <p class="align-center paddn -ptop -p30 margn -mtop -m20 bordr -btop -bsolid -bthin">
              Already have an account? <a href="<?php echo $login_link; ?>" class="bold"> <i class="fas fa-sign-in-alt"></i> Login</a>
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