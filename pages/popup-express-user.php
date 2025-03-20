<?php
namespace Catali;
require_once "../.appinit.php";
use TymFrontiers\Data,
  TymFrontiers\Generic,
  TymFrontiers\HTTP,
  TymFrontiers\InstanceError,
  TymFrontiers\MultiForm;
use TymFrontiers\Location;

$gen = new Generic;
$params = $gen->requestParam([
  "payload" =>["payload","text",1,0],
  "theme" =>["theme", "username", 2, 28, [], "LOWER", ["-"]],
  "rdt" =>["rdt","url"]
], $_GET, ["rdt"]);
if (!$params) HTTP\Header::badRequest(false);

$theme_color = $params['theme'] = (empty($params['theme']) ? "catali-blue" : $params['theme']);
$location = new Location();
$db_name = get_database("data");
?>
<div id="fader-flow">
  <div class="view-space vw-medi">
    <div class="paddn -pall -p20">&nbsp;</div>
    <div class="grid-10-tablet grid-8-desktop center-tablet">
      <div class="sec-div theme-color <?php echo $theme_color; ?> bg-white drop-shadow">
        <header class="paddn -pall -p30 color-bg">
          <h2> <i class="fas fa-info-circle"></i> One-Time Checkout</h2>
        </header>
        <form
          id="user-email"
          class="block-ui paddn -pall -p20"
          method="post"
          action="/app/user/post/set-email"
          data-validate="false"
        onsubmit="doContinue(this); return false" >
          <input type="hidden" name="payload" value="<?php echo @ $params['payload']; ?>">
          <input type="hidden" name="rdt" value="<?php echo $params['rdt']; ?>">
          <input type="hidden" name="country" value="<?php echo empty($location->country) ? "" : $location->country; ?>">
          <input type="hidden" name="state" value="<?php echo empty($location->state) ? "" : $location->state; ?>">
          <input type="hidden" name="city" value="<?php echo empty($location->city) ? "" : $location->city; ?>">
          <input type="hidden" name="lga" value="">

          <div class="grid-12-tablet"><h3>Personal Info</h3></div>
          <div class="grid-6-tablet">
            <label for="xp-name">Name</label>
            <input type="text" name="name" required id="xp-name" placeholder="Name">
          </div>
          <div class="grid-6-tablet">
            <label for="xp-surname">Surname</label>
            <input type="text" name="surname" required id="xp-surname" placeholder="Surname">
          </div>
          <div class="grid-7-tablet">
            <label for="xp-email">Email</label>
            <input type="email" name="email" required id="xp-email" placeholder="email-address@domain.ext">
          </div>
          <div class="grid-5-tablet">
            <label for="xp-phone">Phone number</label>
            <input type="tel" name="phone" required id="xp-phone" placeholder="+234 801 234 5678">
          </div>
          <div class="grid-12-tablet">
            <h3>Mailing Address</h3>
            <p>Please provide the complete address, street address, city, state/province, postal code, and country, where invoice/shipping should be sent.</p>
          </div>
          <div class="grid-7-tablet">
            <label for="xp-country">Country/Region</label>
            <select name="country_code" id="xp-country" required>
              <option value="">* Choose a country</option>
              <optgroup label="Countries">
                <?php if ($countries = (new MultiForm($db_name, "countries", "code", $database))->findAll()) {
                  foreach ($countries as $country) {
                    echo "<option value=\"{$country->code}\"";
                      echo $location->country_code == $country->code ? " selected" : "";
                    echo ">{$country->name}</option>";
                  }
                } ?>
              </optgroup>
            </select>
          </div>
          <div class="grid-5-tablet">
            <label for="xp-state">State/Province</label>
            <select name="state_code" id="xp-state" required>
              <option value="">* Choose a state</option>
              <optgroup label="States">
                <?php if (!empty($location->country_code) && $states = (new MultiForm($db_name, "states", "code", $database))
                  ->findBySql("SELECT `code`, `name` FROM :db:.:tbl: WHERE country_code = '{$database->escapeValue($location->country_code)}'")
                ) {
                  foreach ($states as $state) {
                    echo "<option value=\"{$state->code}\"";
                      echo $location->state_code == $state->code ? " selected" : "";
                    echo ">{$state->name}</option>";
                  }
                } ?>
              </optgroup>
            </select>
          </div>
          <div class="grid-6-tablet">
            <label for="xp-city">City</label>
            <select name="city_code" id="xp-city" required>
              <option value="">* Choose a city</option>
              <optgroup label="Cities">
                <?php if (!empty($location->state_code) && $cities = (new MultiForm($db_name, "cities", "code", $database))
                  ->findBySql("SELECT `code`, `name` FROM :db:.:tbl: WHERE state_code = '{$database->escapeValue($location->state_code)}'")
                ) {
                  foreach ($cities as $city) {
                    echo "<option value=\"{$city->code}\"";
                      echo $location->city_code == $city->code ? " selected" : "";
                    echo ">{$city->name}</option>";
                  }
                } ?>
              </optgroup>
            </select>
          </div>
          <div class="grid-6-tablet">
            <label for="xp-lga">LGA (optional)</label>
            <select name="lga_code" id="xp-lga">
              <option value="">* Choose a LGA</option>
              <optgroup label="LGAs">
                <?php if (!empty($location->state_code) && $lgas = (new MultiForm($db_name, "lgas", "code", $database))
                  ->findBySql("SELECT `code`, `name` FROM :db:.:tbl: WHERE state_code = '{$database->escapeValue($location->state_code)}'")
                ) {
                  foreach ($lgas as $lga) {
                    echo "<option value=\"{$lga->code}\"";
                    echo ">{$lga->name}</option>";
                  }
                } ?>
              </optgroup>
            </select>
          </div>
          <div class="grid-7-tablet">
            <label for="xp-street">Street name</label>
            <input type="text" name="street" placeholder="Street Name" required autocomplete="street-address" id="xp-street">
          </div>
          <div class="grid-5-tablet">
            <label for="xp-apartment">House/Apartment No.</label>
            <input type="text" name="apartment" placeholder="House 25B" required autocomplete="address-line2" id="xp-apartment">
          </div>
          <div class="grid-6-tablet">
            <label for="xp-landmark">Nearest Landmark</label>
            <input type="text" name="landmark" placeholder="Oval Estate" required autocomplete="address-line3" id="xp-landmark">
          </div>
          <div class="grid-4-tablet">
            <label for="xp-zip-code">Zip Code (optional)</label>
            <input type="text" name="zip_code" placeholder="000000" id="xp-zip-code">
          </div>

          <div class="grid-5-tablet">
            <button id="submit-form" type="submit" class="theme-button <?php echo $theme_color; ?> no-shadow">Continue <i class="fas fa-arrow-right"></i> </button>
          </div>

          <br class="c-f">
        </form>

      </div>
    </div>
    <br class="c-f">
  </div>
</div>

<script type="text/javascript">
  function doContinue (fm) {
    fm = $(fm);
    let usrInfo = ["name", "surname", "email", "phone"];
    let addressInfo = [
      "country", "state", "city", "lga", "country_code",
      "state_code", "city_code", "lga_code", "zip_code",
      "street", "apartment", "landmark"
    ];
    let required = {
      name: "Enter name",
      surname: "Enter surname",
      email: "Enter valid email address",
      phone: "Enter phone number",
      country_code: "Choose your country/region",
      state_code: "Choose your state/province",
      city_code: "Choose your city",
      street: "Enter street information",
      apartment: "Provide house/apartment info",
      landmark: "Enter nearest landmark to your address"
    };
    let user = {};
    let address = {};
    $.each(usrInfo, function (_i, name) {
      let = inp = fm.find(`[name=${name}]`);
      if (inp.length) {
        if (name in required && inp.val().length <= 0) {
          alert(required[name], {type: 'warning'});
        }
        user[name] = inp.val();
      }
    });
    $.each(addressInfo, function (_i, name) {
      let = inp = fm.find(`[name=${name}]`);
      if (inp.length) {
        if (name in required && inp.val().length <= 0) {
          alert(required[name], {type: 'warning'});
        }
        address[name] = inp.val();
      }
    });
    let rdt = fm.find("input[name=rdt]").val();
    if (rdt.length && (new URL(rdt)).hostname == location.hostname) {
      // proceed
      redirectTo(setGet(rdt, {
        payload: fm.find("input[name=payload]").val(),
        express_user: JSON.stringify({
          user: user,
          address: address
        })
      }), false);
    } else {
      // console.error("Cannot redirect");
    }

  }
  (function(){
    $(document).on("change", "#xp-country", function (){
      if( $(this).val().length > 0) {
        $("select#xp-state, #xp-city, #xp-lga").val("");
        helpr_rsc(`/app/ws-helper/get/state`, function(results) {
          update_region ("select#xp-state optgroup", results);
        }, {country_code:$(this).val()}, {processData: true});
      } else {
        $("select#xp-state, #xp-city, #xp-lga").val("");
      }
    });
    $(document).on("change", "#xp-state", function (){
      if( $(this).val().length > 0) {
        $("#xp-city, #xp-lga").val("");
        helpr_rsc(`/app/helper/get/city`, function(results) {
          update_region ("select#xp-city optgroup", results);
        }, {state_code:$(this).val()}, {processData: true});
        helpr_rsc(`/app/helper/get/lga`, function(results) {
          update_region ("select#xp-lga optgroup", results);
        }, {state_code:$(this).val()}, {processData: true});
      } else {
        $("#xp-city, #xp-lga").val("");
      }
    });
    $(document).on("change", "#xp-country, #xp-state, #xp-city, #xp-lga", function() {
      let inp = $(`input[name=${$(this).attr('name').replace('_code', '')}]`);
      if (inp.length) {
        inp.val($(this).find("option:selected").text());
      }
    });
  })();
</script>