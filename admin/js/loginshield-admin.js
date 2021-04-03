window.loginShield = window.loginShield || {};

loginShield.AdminForm = (function($) {
  let status = {
    loading: false,
    completed: false,
    products: []
  };

  let selectors = {
  	form: '#LoginShieldForm',
  	registerForm: '#RegisterForm',
  	activateForm: '#ActivateForm',
  	iframe: '#loginshield-content',
  	security: '#security',
    btnActivateLoginShield: '#ActivateLoginShield',
  };

  function AdminForm() {
  	if (!$(selectors.form).length)
  		return;

  	this.$form = $(selectors.form);
  	this.$registerForm = this.$form.find(selectors.registerForm);
  	this.$activateForm = this.$form.find(selectors.activateForm);
  	this.$iframe = this.$form.find(selectors.iframe);
  	this.$security = this.$form.find(selectors.security);
    this.$btnActivateLoginShield = this.$form.find(selectors.btnActivateLoginShield);

    this.$security.on('change', this.handleSecurityChange.bind(this));
    this.$btnActivateLoginShield.on('click', this.handleActivateLoginShield.bind(this));

    this.init();
  }

  AdminForm.prototype = $.extend({}, AdminForm.prototype, {
    init: function() {
      const mode = this.$form.data('mode');
      const loginshield = this.$form.data('loginshield');

      if (mode === 'resume-loginshield' && loginshield) {
        this.resumeLoginShield({ forward: loginshield })
      }
    },

    handleSecurityChange: async function(e) {
      const action = 'update-security';
      const isSecured = e.target.checked;

      const response = await this.updateSecurity({ action, isSecured });

      if (!response || response.error) {
        this.showMessage(response.message, 'error');
        return;
      }

      if (isSecured) {
        this.showMessage('Your account is protected by LoginShield.');
      } else {
        this.showMessage('Your account is not protected by LoginShield.');
      }
    },

    updateSecurity: function(payload) {
      return new Promise((resolve, reject) => {
        const { action, isSecured } = payload;
        const url = loginshieldSettingAjax.api_base + "loginshield/account/edit";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            action        :  action,
            isSecured     :  isSecured,
          }),
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', loginshieldSettingAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          console.error(error);
          reject(error);
        });
      });
    },

    handleActivateLoginShield: async function(e) {
      const self = this;
      const action = 'register-loginshield-user';
      const mode = 'activate-loginshield';

      this.$btnActivateLoginShield.addClass('loading');

      const response = await this.registerLoginShieldUser({ action });
      if (!response || response.error) {
        this.$btnActivateLoginShield.removeClass('loading');
        return;
      }

      const { forward } = await this.loginWithLoginShield({ mode });

      if (!forward)
        return;

      this.$btnActivateLoginShield.hide();

      loginshieldInit({
        elementId: 'loginshield-content',
        backgroundColor: '#f1f1f1',
        width: 500,
        height: 460,
        action: 'start',
        mode: 'link-device',
        forward: forward,
        rememberMe: true,
        onResult: function (result) {
          if (!result)
            return;

          self.onResult(result);
        },
      });
    },

    registerLoginShieldUser: function (accountInfo) {
      return new Promise((resolve, reject) => {
        const { action, loginshield } = accountInfo;
        const url = loginshieldSettingAjax.api_base + "loginshield/account/edit";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            action          :  action,
            loginshield     :  loginshield,
          }),
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', loginshieldSettingAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          console.error(error);
          reject(error);
        });
      });
    },

    loginWithLoginShield: async function (request) {
      return new Promise((resolve, reject) => {
        const { login, mode, verifyToken } = request;
        let url = loginshieldSettingAjax.api_base + "loginshield/session/login/loginshield";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            login       :  login,
            mode        :  mode,
            verifyToken :  verifyToken,
          }),
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', loginshieldSettingAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          console.error(error);
          reject(error);
        });
      });
    },

    resumeLoginShield: async function({ forward }) {
      const self = this;

      this.$btnActivateLoginShield.hide();

      loginshieldInit({
        elementId: 'loginshield-content',
        backgroundColor: '#f1f1f1',
        width: 500,
        height: 460,
        action: 'resume',
        forward: forward,
        rememberMe: true,
        onResult: function (result) {
          if (!result)
            return;

          self.onResult(result);
        },
      });
    },

    finishLoginShield: async function({ verifyToken }) {
      console.log(`finishLoginShield: verifying login with token: ${verifyToken}`);
      const { isAuthenticated, error, isConfirmed } = await this.loginWithLoginShield({ verifyToken });
      if (isAuthenticated) {
        this.enableActivateForm(isConfirmed);
        this.showMessage('LoginShield account registration is succeed.', 'success');
      } else if (error) {
        this.resetLoginForm();
        this.showMessage(`finishLoginShield error: ${error}`, 'error');
        console.error(`finishLoginShield error: ${error}`);
      }
    },

    onResult: function(result) {
      console.log('onResult : %o', result);

      if (!result)
        return;

      switch (result.status) {
        case 'verify':
          this.finishLoginShield({ verifyToken: result.verifyToken });
          break;
        case 'error':
          this.showMessage(`onResult: ${result.error}`, 'error');
          this.resetLoginForm();
          break;
        case 'cancel':
          this.showMessage(`onResult: ${result.status}`);
          this.resetLoginForm();
          break;
        default:
          this.showMessage(`onResult: unknown status ${result.status}`, 'error');
          console.error(`onResult: unknown status ${result.status}`);
          break;
      }
    },

    enableActivateForm: function(isConfirmed = false) {
      if (isConfirmed) {
        this.$security.attr('checked', true);
      }

      this.$iframe.html('');
      this.$registerForm.hide();
      this.$activateForm.show();
    },

    resetLoginForm: function() {
      this.$btnActivateLoginShield.show();
      this.$btnActivateLoginShield.removeClass('loading');
      this.$iframe.html('');
    },

    showMessage: function(text, status = 'normal') {
      if (!text || text === '')
        return;

      const normal = {
        textColor: '#FFFFFF',
        backgroundColor: '#2196F3',
        actionTextColor: '#FFFFFF'
      };

      const success = {
        textColor: '#FFFFFF',
        backgroundColor: '#4CAF50',
        actionTextColor: '#FFFFFF'
      };

      const warning = {
        textColor: '#1d1f21',
        backgroundColor: '#F9EE98',
        actionTextColor: '#1d1f21'
      };

      const error = {
        textColor: '#FFFFFF',
        backgroundColor: '#F66496',
        actionTextColor: '#FFFFFF'
      };

      let theme = '';
      switch (status) {
        case 'normal':
          theme = normal;
          break;
        case 'success':
          theme = success;
          break;
        case 'warning':
          theme = warning;
          break;
        case 'error':
          theme = error;
          break;
        default:
          theme = normal;
          break;
      }

      Snackbar.show({
        pos: 'bottom-center',
        text: text,
        textColor: theme.textColor,
        backgroundColor: theme.backgroundColor,
        actionTextColor: theme.actionTextColor,
      });
    },

    loading: function(loading) {
      if (loading) {
        status.loading = true;
        this.$btnSubmitForm.addClass('btn--loading');
      } else {
        status.loading = false;
        this.$btnSubmitForm.removeClass('btn--loading');
      }
    }
  });

  return AdminForm;
})(jQuery);

loginShield.SettingsForm = (function($) {
  let status = {
    loading: false,
    completed: false,
    products: []
  };

  let selectors = {
    form: '#LoginShieldSettingsForm',
  };

  function SettingsForm() {
    if (!$(selectors.form).length)
      return;

    this.$form = $(selectors.form);
    // this.$registerForm = this.$form.find(selectors.registerForm);
    // this.$activateForm = this.$form.find(selectors.activateForm);
    // this.$iframe = this.$form.find(selectors.iframe);
    // this.$security = this.$form.find(selectors.security);
    // this.$btnActivateLoginShield = this.$form.find(selectors.btnActivateLoginShield);

    // this.$security.on('change', this.handleSecurityChange.bind(this));
    // this.$btnActivateLoginShield.on('click', this.handleActivateLoginShield.bind(this));

    this.init();
  }

  SettingsForm.prototype = $.extend({}, SettingsForm.prototype, {
    init: async function() {

      console.log('test...');

      const response = await this.verifyRealmInfo();

      console.log('response : ', response);

      // const mode = this.$form.data('mode');
      // const loginshield = this.$form.data('loginshield');
      //
      // if (mode === 'resume-loginshield' && loginshield) {
      //   this.resumeLoginShield({ forward: loginshield })
      // }
    },

    verifyRealmInfo: function() {
      return new Promise((resolve, reject) => {
        const url = loginshieldSettingAjax.api_base + "loginshield/verifyRealmInfo";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', loginshieldSettingAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          console.error(error);
          reject(error);
        });
      });
    },

    handleSecurityChange: async function(e) {
      const action = 'update-security';
      const isSecured = e.target.checked;

      const response = await this.updateSecurity({ action, isSecured });

      if (!response || response.error) {
        this.showMessage(response.message, 'error');
        return;
      }

      if (isSecured) {
        this.showMessage('Your account is protected by LoginShield.');
      } else {
        this.showMessage('Your account is not protected by LoginShield.');
      }
    },

    updateSecurity: function(payload) {
      return new Promise((resolve, reject) => {
        const { action, isSecured } = payload;
        const url = loginshieldSettingAjax.api_base + "loginshield/account/edit";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            action        :  action,
            isSecured     :  isSecured,
          }),
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', loginshieldSettingAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          console.error(error);
          reject(error);
        });
      });
    },

    handleActivateLoginShield: async function(e) {
      const self = this;
      const action = 'register-loginshield-user';
      const mode = 'activate-loginshield';

      this.$btnActivateLoginShield.addClass('loading');

      const response = await this.registerLoginShieldUser({ action });
      if (!response || response.error) {
        this.$btnActivateLoginShield.removeClass('loading');
        return;
      }

      const { forward } = await this.loginWithLoginShield({ mode });

      if (!forward)
        return;

      this.$btnActivateLoginShield.hide();

      loginshieldInit({
        elementId: 'loginshield-content',
        backgroundColor: '#f1f1f1',
        width: 500,
        height: 460,
        action: 'start',
        mode: 'link-device',
        forward: forward,
        rememberMe: true,
        onResult: function (result) {
          if (!result)
            return;

          self.onResult(result);
        },
      });
    },

    registerLoginShieldUser: function (accountInfo) {
      return new Promise((resolve, reject) => {
        const { action, loginshield } = accountInfo;
        const url = loginshieldSettingAjax.api_base + "loginshield/account/edit";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            action          :  action,
            loginshield     :  loginshield,
          }),
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', loginshieldSettingAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          console.error(error);
          reject(error);
        });
      });
    },

    loginWithLoginShield: async function (request) {
      return new Promise((resolve, reject) => {
        const { login, mode, verifyToken } = request;
        let url = loginshieldSettingAjax.api_base + "loginshield/session/login/loginshield";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            login       :  login,
            mode        :  mode,
            verifyToken :  verifyToken,
          }),
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', loginshieldSettingAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          console.error(error);
          reject(error);
        });
      });
    },

    resumeLoginShield: async function({ forward }) {
      const self = this;

      this.$btnActivateLoginShield.hide();

      loginshieldInit({
        elementId: 'loginshield-content',
        backgroundColor: '#f1f1f1',
        width: 500,
        height: 460,
        action: 'resume',
        forward: forward,
        rememberMe: true,
        onResult: function (result) {
          if (!result)
            return;

          self.onResult(result);
        },
      });
    },

    finishLoginShield: async function({ verifyToken }) {
      console.log(`finishLoginShield: verifying login with token: ${verifyToken}`);
      const { isAuthenticated, error, isConfirmed } = await this.loginWithLoginShield({ verifyToken });
      if (isAuthenticated) {
        this.enableActivateForm(isConfirmed);
        this.showMessage('LoginShield account registration is succeed.', 'success');
      } else if (error) {
        this.resetLoginForm();
        this.showMessage(`finishLoginShield error: ${error}`, 'error');
        console.error(`finishLoginShield error: ${error}`);
      }
    },

    onResult: function(result) {
      console.log('onResult : %o', result);

      if (!result)
        return;

      switch (result.status) {
        case 'verify':
          this.finishLoginShield({ verifyToken: result.verifyToken });
          break;
        case 'error':
          this.showMessage(`onResult: ${result.error}`, 'error');
          this.resetLoginForm();
          break;
        case 'cancel':
          this.showMessage(`onResult: ${result.status}`);
          this.resetLoginForm();
          break;
        default:
          this.showMessage(`onResult: unknown status ${result.status}`, 'error');
          console.error(`onResult: unknown status ${result.status}`);
          break;
      }
    },

    enableActivateForm: function(isConfirmed = false) {
      if (isConfirmed) {
        this.$security.attr('checked', true);
      }

      this.$iframe.html('');
      this.$registerForm.hide();
      this.$activateForm.show();
    },

    resetLoginForm: function() {
      this.$btnActivateLoginShield.show();
      this.$btnActivateLoginShield.removeClass('loading');
      this.$iframe.html('');
    },

    showMessage: function(text, status = 'normal') {
      if (!text || text === '')
        return;

      const normal = {
        textColor: '#FFFFFF',
        backgroundColor: '#2196F3',
        actionTextColor: '#FFFFFF'
      };

      const success = {
        textColor: '#FFFFFF',
        backgroundColor: '#4CAF50',
        actionTextColor: '#FFFFFF'
      };

      const warning = {
        textColor: '#1d1f21',
        backgroundColor: '#F9EE98',
        actionTextColor: '#1d1f21'
      };

      const error = {
        textColor: '#FFFFFF',
        backgroundColor: '#F66496',
        actionTextColor: '#FFFFFF'
      };

      let theme = '';
      switch (status) {
        case 'normal':
          theme = normal;
          break;
        case 'success':
          theme = success;
          break;
        case 'warning':
          theme = warning;
          break;
        case 'error':
          theme = error;
          break;
        default:
          theme = normal;
          break;
      }

      Snackbar.show({
        pos: 'bottom-center',
        text: text,
        textColor: theme.textColor,
        backgroundColor: theme.backgroundColor,
        actionTextColor: theme.actionTextColor,
      });
    },

    loading: function(loading) {
      if (loading) {
        status.loading = true;
        this.$btnSubmitForm.addClass('btn--loading');
      } else {
        status.loading = false;
        this.$btnSubmitForm.removeClass('btn--loading');
      }
    }
  });

  return SettingsForm;
})(jQuery);

(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

  $(document).ready(function() {

    new loginShield.AdminForm();
    new loginShield.SettingsForm();

  });

})( jQuery );

function saveLoginShieldSetting(e) {

	jQuery(e).addClass('disabled_btn');
	jQuery('.Loderimg').show();
	var error = 0;
	jQuery("#LoginShieldSettingsForm .input_fields").each(function () {
		if (jQuery(this).val() == '') {
			jQuery(this).addClass('empty_fields');
			error++;
		}
	});
	if (error > 0) {
		jQuery(e).removeClass('disabled_btn');
		jQuery('.Loderimg').hide();
		jQuery('.response_msg').html('*There is some error. Please check all the fields and try again.');
		jQuery('.response_msg').addClass('error_msg');
		jQuery('.response_msg').removeClass('success_msg');
		setTimeout(function () {
			jQuery('.response_msg').html('Response Message');
			jQuery('.response_msg').removeClass('error_msg success_msg');
		}, 2000);
		return false;
	}
	console.log('ajax-start');
	jQuery.ajax({
		url: loginshieldSettingAjax.ajax_url,
		type: 'post',
		data: {
			action 	: 'loginshield_enterprise_settings',
			formdata: jQuery("#LoginShieldSettingsForm").serialize()
		},
		success: function(res) {
			jQuery(e).removeClass('disabled_btn');
			jQuery('.Loderimg').hide();
			var obj = JSON.parse(res);
			if(obj.status==0){
				jQuery('.response_msg').html('*There some error. Please check all the fields and try again.');
				jQuery('.response_msg').addClass('error_msg');
				jQuery('.response_msg').removeClass('success_msg');
				setTimeout(function() {
					jQuery('.response_msg').html('Response Message');
					jQuery('.response_msg').removeClass('error_msg success_msg');
				}, 1500);

			}else{
				jQuery('.response_msg').html('*Setting saved successfully.');
				jQuery('.response_msg').addClass('success_msg');
				jQuery('.response_msg').removeClass('error_msg');
				setTimeout(function() {
					jQuery('.response_msg').html('Response Message');
					jQuery('.response_msg').removeClass('error_msg success_msg');
				}, 1500);
			}
		},
		error: function (err) {
			console.log('err : ', err);
		}
	});
}
