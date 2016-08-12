var Login = {
  srpClient: null,
  password: null,

  options: {
    emailId: '#email-login',
    formId: '#login-form',
    registerBtnId: '#loginBtn',
    passwordId: '#password-login',
    passwordSaltId: '#password-login-salt',
    passwordVerifierId: '#password-login-verifier',
    loginOutput: '#login-output'
  },

  defaults: {
    challengeResponse: {},
    verifyResponse: {}
  },

  initialize: function (options) {
    var me = this;
    var $output = $(Login.options.loginOutput);

    if (options) {
      me.options = $.extend({}, Login.options, options);
    }

    $(me.options.formId).on('submit', function (e) {
      e.preventDefault();

      var data = {
        email: me.getEmail(),
        challenge: me.getClient().startExchange()
      };

      $output.prepend('<b>-> Client, A</b><br/>' + data.challenge.A + '<br/>');

      $.post(me.options.url, data, function (response) {
        if (response.error) {
          $output.prepend('<b><- Server</b><br/>' + response.error + '<br/>');
        } else {
          me.onChallengeResponse(response);
        }
      }, 'json');

      return false;
    });
  },

  onChallengeResponse: function (response) {
    var me = this;
    var $output = $(Login.options.loginOutput);

    var data = {
      email: me.getEmail(),
      respondToChallenge: me.getClient().respondToChallenge(response.challengeResponse)
    };

    $output.prepend('<b><- Server, Salt</b><br/>' + response.challengeResponse.salt + '<br/>');
    $output.prepend('<b><- Server, B</b><br/>' + response.challengeResponse.B + '<br/>');

    $output.prepend('<b>-> Client, M</b><br/>' + data.respondToChallenge.M + '<br/>');

    $.post(me.options.url, data, function () {
      me.onRespondResponse.apply(me, arguments);
    }, 'json');
  },

  onRespondResponse: function (response) {
    var me = this;
    var $output = $(Login.options.loginOutput);

    if (response.error) {
      $output.prepend('<b><- Server</b><br/>' + response.error + '<br/>');
    } else {
      if (me.getClient().verifyConfirmation(response.verifyResponse)) {
        $(document).trigger('success');
        $output.prepend('<b><- Server</b><br/>Successfully Authenticated! Shared Strong Session Key: <br/>' + me.getClient().sessionKey() + '<br/>');
      }
    }

    $output.prepend('<hr/>');
  },

  getEmail: function () {
    return $(this.options.emailId).attr('value');
  },

  getPassword: function () {
    return $(this.options.passwordId).attr('value');
  },

  getClient: function () {
    if (this.srpClient === null || this.getPassword() !== this.password) {
      this.password = this.getPassword();
      this.srpClient = new _srp.Client(this.password);
    }

    return this.srpClient;
  }
};