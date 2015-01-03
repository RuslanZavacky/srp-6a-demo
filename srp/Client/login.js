var Login = {
  srpClient: null,
  password: null,

  options: {
    emailId: '#email-login',
    formId: '#login-form',
    registerBtnId: '#loginBtn',
    passwordId: '#password-login',
    passwordSaltId: '#password-login-salt',
    passwordVerifierId: '#password-login-verifier'
  },

  defaults: {
    challengeResponse: {},
    verifyResponse: {}
  },

  initialize: function (options) {
    var me = this;

    if (options) {
      me.options = options;
    }

    $(me.options.formId).on('submit', function (e) {
      e.preventDefault();

      var data = {
        email: me.getEmail(),
        challenge: me.getClient().startExchange()
      };

      $('#login-output').append('<b>-> Client, A</b><br/>' + data.challenge.A + '<br/>');

      $.post(me.options.url, data, function () {
        me.onChallengeResponse.apply(me, arguments);
      }, 'json');

      return false;
    });
  },

  onChallengeResponse: function (response) {
    var me = this;

    var data = {
      email: me.getEmail(),
      respondToChallenge: me.getClient().respondToChallenge(response.challengeResponse)
    };

    $('#login-output').append('<b><- Server, Salt</b><br/>' + response.challengeResponse.salt + '<br/>');
    $('#login-output').append('<b><- Server, B</b><br/>' + response.challengeResponse.B + '<br/>');

    $('#login-output').append('<b>-> Client, M</b><br/>' + data.respondToChallenge.M + '<br/>');

    $.post(me.options.url, data, function () {
      me.onRespondResponse.apply(me, arguments);
    }, 'json');
  },

  onRespondResponse: function (response) {
    var me = this;

    if (response.error) {
      $('#login-output').append('<b><- Server</b><br/>' + response.error + '<br/>');
    } else {
      if (me.getClient().verifyConfirmation(response.verifyResponse)) {
        $(document).trigger('success');
        $('#login-output').append('<b><- Session Key</b><br/>'+me.getClient().sessionKey()+'<br/>');
        $('#login-output').append('<b><- Server</b><br/>success!<br/>');
      }
    }

    $('#login-output').append('<hr/>');
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
}