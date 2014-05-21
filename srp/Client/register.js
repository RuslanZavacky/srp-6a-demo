var Register = {
  verifier: null,
  password: null,

  options: {
    emailId: '#email-login',
    formId: '#register-form',
    registerBtnId: '#registerBtn',
    passwordId: '#password',
    passwordSaltId: '#password-salt',
    passwordVerifierId: '#password-verifier'
  },

  initialize: function (options) {
    var me = this;

    if (options) {
      me.options = options;
    }

    $(options.formId).on('submit', $.proxy(function () {
      me.onPasswordChange();
    }, me));

    $(options.passwordId).on('keyup', $.proxy(function (event) {
      $(event.currentTarget).val().length ? me.enableSubmitBtn() : me.disableSubmitBtn();
      random16byteHex.advance(Math.floor(event.keyCode/4));
      me.onPasswordChange();
    }, me));
  },

  disableSubmitBtn: function() {
    $(this.options.registerBtnId).attr('disabled', true);
  },

  enableSubmitBtn: function() {
    $(this.options.registerBtnId).removeAttr('disabled');
  },

  onPasswordChange: function () {
    var me = this;

    var verifier = this.generateVerifier();

    $(me.options.passwordSaltId).attr('value', verifier.salt);
    $(me.options.passwordVerifierId).attr('value', verifier.verifier);

    $('#password-salt-output').text(verifier.salt);
    $('#password-verifier-output').text(verifier.verifier);
  },

  getPassword: function () {
    return $(this.options.passwordId).attr('value');
  },

  generateVerifier: function () {
    if (this.verifier === null || this.getPassword() !== this.password) {
      this.password = this.getPassword();
      this.verifier = _srp.generateVerifier(this.password);
    }

    return this.verifier;
  }
}
