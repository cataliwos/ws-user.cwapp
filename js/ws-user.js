const usrLogin = (rsp = {}) => {
  if (rsp && rsp.status == "0.0" && "rdt" in rsp) {
    if ("eos" in param && parseBool(param.eos)) {
      console.log("ready");
      
      window.close();
    } else {
      setTimeout(() => {
        redirectTo(rsp.rdt);
      },1000 * 3);
    }
  }
}
const chkRegister = (frm) => {
  let $frm = $(frm);
  if ($frm.find("input#register-otp").val().length) {
    cwos.form.submit(frm, usrRegister);
  } else {
    // get otp
    cwos.faderBox.url('/app/ws-helper/popup/otp-email', {
      email : $frm.find("input[type=email]").eq(0).val(),
      name : $frm.find("input[name=name]").val(),
      surname : $frm.find("input[name=surname]").val(),
      cb : "RegWithOtp",
      MUST_NOT_EXIST : true,
      code_variant : "numbers",
      code_length : 7,
      theme: "theme" in param ? param.theme : ""
    }, { method : "GET", exitBtn : false });
    // console.error("OTP required");
  }
}
function RegWithOtp (code) {
  $("form#usr-register-form input[name=otp]").val(code.replace(/\s/g,'').toUpperCase());
  cwos.faderBox.close();
  setTimeout(() => {
    $("form#usr-register-form").submit();
  }, 500);
}
const usrRegister = (resp) => {
  if( resp && ( resp.errors.length <= 0 || resp.status == "0.0") ){
    // $('#register-form').reset();
    if ("eos" in param && parseBool(param.eos)) {
      window.close();
    } else {
      if ( resp.rdt.length > 0 ) {
        setTimeout(function(){ window.location = resp.rdt; },3200);
      } else {
        setTimeout(function(){ removeAlert(); },3200);
      }
    }
  }
}