(function () {
  'use strict';

  // Send request to How's My SSL web service. This returns info about browser's available cipher suites which we
  // can use to determine compliance with site's encryption policy.
  var oReq = new XMLHttpRequest();
  oReq.addEventListener("load", requestListener);
  oReq.open("GET", "https://www.howsmyssl.com/a/check");
  oReq.send();

  // Process response from How's My SSL (this). Check available cipher sutes against site's encryption policy. Then
  // display results to end user.
  function requestListener () {
      var response = '';
      var html = '';
      var el = {};
      this.resultCipherSuites = [];

      response = JSON.parse(this.responseText);
      console.log("This website is testing your browser's encryption capabilities here: https://www.howsmyssl.com/a/check. This is the response received:");
      console.log(response);

      response.given_cipher_suites.forEach(processResponseCipherSuites, this);
      console.log('this.resultCipherSuites:');
      console.log(this.resultCipherSuites);

      el = document.getElementById("encryption-policy-browser-test");
      if (el != null) {
          html = Drupal.theme.encryptionPolicyCheck(this.resultCipherSuites);
          el.innerHTML = html;
      }
  }


  Drupal.theme.encryptionPolicyCheck = function(result) {
      var html = '';
      html += '<table>';
      html += '<caption>' + Drupal.t('Test cipher suites available in browser against encryption policy') +'</caption>';
      html += '<thead>';
      html +=   '<tr>';
      html +=     '<th>' + Drupal.t('Cipher Suite') + '</th>';
      html +=     '<th>' + Drupal.t('Available on server') + '</th>';
      html +=     '<th>' + Drupal.t('Blacklist') + '</th>';
      html +=     '<th>' + Drupal.t('Whitelist') + '</th>';
      html +=   '</tr>';
      html += '</thead>';
      html += '<tbody>';

      for (var i = 0; i < result.length; i++ ) {
          html += '<tr>';
          html +=   '<th>' + result[i].name + '</th><td>' + result[i].available + '</td><td>' + result[i].blacklist + '</td><td>' + result[i].whitelist + '</td>';
          html += '</tr>';
      }

      html += '</tbody>';
      html += '</table>';

      return html;
  }

  function processResponseCipherSuites(cipher_suite_name, index, array) {
      var row = {};

      row.name = cipher_suite_name;
      row.available = drupalSettings.encryption_policy.cipher_suites_available.includes(cipher_suite_name);
      row.blacklist = drupalSettings.encryption_policy.cipher_suites_blacklist.includes(cipher_suite_name);
      row.whitelist = drupalSettings.encryption_policy.cipher_suites_whitelist.includes(cipher_suite_name);

      this.resultCipherSuites.push(row);
  }

})();