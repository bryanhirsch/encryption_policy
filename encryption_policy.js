(function () {
  'use strict';
  // Custom javascript
  function requestListener () {
    var response = JSON.parse(this.responseText);
    console.log("This website is testing your browser's encryption capabilities here: https://www.howsmyssl.com/a/check. This is the response received:");
    console.log(this.responseText);
    console.log(response);

    Drupal.theme('');
  }

  var oReq = new XMLHttpRequest();
  oReq.addEventListener("load", requestListener);
  oReq.open("GET", "https://www.howsmyssl.com/a/check");
  oReq.send();

  Drupal.theme.encryptionPolicyCheck = function(result) {
    var output = '<h1>hello world</h1>';
    console.log('HELLO WORLD');
    console.log(result);
    return output;
  }
})();