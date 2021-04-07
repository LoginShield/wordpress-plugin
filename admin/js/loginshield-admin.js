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
  	isActiveCheckbox: '#loginshield_active',
    btnActivateLoginShield: '#ActivateLoginShield',
    btnResetLoginShield: '#ResetLoginShield',
  };

  function AdminForm() {
  	if (!$(selectors.form).length)
  		return;

  	this.$form = $(selectors.form);
  	this.$registerForm = this.$form.find(selectors.registerForm);
  	this.$activateForm = this.$form.find(selectors.activateForm);
  	this.$iframe = this.$form.find(selectors.iframe);
  	this.$isActiveCheckbox = this.$form.find(selectors.isActiveCheckbox);
    this.$btnActivateLoginShield = this.$form.find(selectors.btnActivateLoginShield);
    this.$btnResetLoginShield = this.$form.find(selectors.btnResetLoginShield);

    this.$isActiveCheckbox.on('change', this.handleSecurityChange.bind(this));
    this.$btnActivateLoginShield.on('click', this.handleActivateLoginShield.bind(this));
    this.$btnResetLoginShield.on('click', this.handleResetLoginShield.bind(this));

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
      const isActive = e.target.checked;

      const response = await this.updateSecurity({ action, isActive });

      if (!response || response.error) {
        this.showMessage(response.message, 'error');
        return;
      }

      if (isActive) {
        this.showMessage('Your account is now protected by LoginShield.');
      } else {
        this.showMessage('LoginShield protection is now deactivated for your account.', 'warning');
      }
    },

    updateSecurity: function(payload) {
      return new Promise((resolve, reject) => {
        const { action, isActive } = payload;
        const url = loginshieldSettingAjax.api_base + "loginshield/account/edit";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            action        :  action,
            isActive      :  isActive,
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
      try {
        const self = this;

        this.$btnActivateLoginShield.addClass('loading');

        const response = await this.registerLoginShieldUser({ action: 'register-loginshield-user' });
        if (!response || response.error) {
          this.$btnActivateLoginShield.removeClass('loading');
          return;
        }

        const { forward } = await this.loginWithLoginShield({ mode: 'activate-loginshield' });

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
      } catch (e) {
        this.showMessage('Service is unavailable', 'error');
        this.$btnActivateLoginShield.removeClass('loading');
        console.error(e);
      }
    },

    registerLoginShieldUser: function (accountInfo) {
      return new Promise((resolve, reject) => {
        const { action } = accountInfo;
        const url = loginshieldSettingAjax.api_base + "loginshield/account/edit";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            action          :  action,
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

    resetLoginShieldUser: function (user_id) {
      return new Promise((resolve, reject) => {
        const url = loginshieldSettingAjax.api_base + "loginshield/account/reset";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            user_id          :  user_id,
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
        this.showMessage('LoginShield setup complete', 'success');
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
          this.showMessage(`Login failed: ${result.error}`, 'error');
          this.resetLoginForm();
          break;
        case 'cancel':
          this.showMessage('Login cancelled', 'warning');
          this.resetLoginForm();
          break;
        case 'timeout':
          this.showMessage('Login request expired', 'warning');
          this.resetLoginForm();
          break;
        default:
          console.error(`onResult: unknown status ${result.status}`);
          this.showMessage(`Login failed: ${result.status}`, 'error');
          break;
      }
    },

    enableActivateForm: function(isConfirmed = false) {
      if (isConfirmed) {
        this.$isActiveCheckbox.attr('checked', true);
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

    handleResetLoginShield: async function(e) {
      try {
        const self = this;

        this.$btnResetLoginShield.addClass('loading');

        const response = await this.resetLoginShieldUser(this.$btnResetLoginShield.attr('data-user-id'));
        if (!response || response.error) {
          return;
        }
        window.location.reload(true); // refresh the current page to show changes
      } catch (e) {
        this.showMessage('Service is unavailable', 'error');
        console.error(e);
      } finally {
        this.$btnResetLoginShield.removeClass('loading');
      }
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
    actionForm: '#ActionForm',
    btnAccessRequest: '#btnAccessRequest',
  };

  function SettingsForm() {
    if (!$(selectors.form).length)
      return;

    this.$form = $(selectors.form);
    this.$actionForm = $(selectors.actionForm);
    this.$btnAccessRequest = $(selectors.btnAccessRequest);

    this.clientId = this.$form.data('client-id');
    this.clientState = this.$form.data('client-state');
    this.grantToken = this.$form.data('grant-token');

    this.$btnAccessRequest.on('click', this.requestToken.bind(this));

    this.init();
  }

  SettingsForm.prototype = $.extend({}, SettingsForm.prototype, {
    init: async function() {
       if (this.onTokenExchange()) {
         this.exchangeToken();
       } else {
         this.verifyToken();
       }
    },
    
    onTokenExchange: function() {
      if (typeof this.clientId !== 'string' || !this.clientId )
        return false;

      if (typeof this.clientState !== 'string' || !this.clientState )
        return false;

      if (typeof this.grantToken !== 'string' || !this.grantToken )
        return false;

      return true;
    },

    exchangeToken: async function() {
      try {
        const response = await this.handleExchangeToken();
        if (!response) {
          this.showAccessRequestForm();
          this.showMessage('No response from server', 'error');
        }

        if (response.error) {
          this.showAccessRequestForm();
          this.showMessage(response.message, 'error');
          return;
        }

        if (response.access_token) {
          this.showNormalForm();
          this.showMessage('You have activated your account successfully.');
          // replace the location to remove the query parameters and load the realm id now that it's available
          const searchParams = new URLSearchParams(window.location.search);
          searchParams.delete("client_id");
          searchParams.delete("client_state");
          searchParams.delete("grant_token");
          const newURL = new URL(window.location.protocol + "//" + window.location.host + window.location.pathname + "?" + searchParams.toString() );
          window.location.replace(newURL.toString());
        }
      } catch (e) {
        this.showAccessRequestForm();
        console.error(e);
      }
    },

    requestToken: async function() {
      this.$btnAccessRequest.addClass('loading');

      const response = await this.handleRequestToken();
      const { payload, error, message } = response;

      if (payload && payload.redirect) {
        window.location = payload.redirect;
      }

      if (error) {
        this.showMessage(message, 'error');
      }

      this.$btnAccessRequest.removeClass('loading');
    },

    verifyToken: async function() {
      const response = await this.handleVerifyToken();

      const { status, error, message } = response;

      if (status === 'success') {
        this.showNormalForm();
        console.info(message);
        return;
      }

      if (error) {
        this.showAccessRequestForm();
        console.info(message);
      }
    },

    handleExchangeToken: function() {
      return new Promise((resolve, reject) => {
        const url = loginshieldSettingAjax.api_base + "loginshield/token/exchange";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            client_id       :  this.clientId,
            client_state    :  this.clientState,
            grant_token     :  this.grantToken,
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

    handleRequestToken: function() {
      return new Promise((resolve, reject) => {
        const url = loginshieldSettingAjax.api_base + "loginshield/token/request";
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

    handleVerifyToken: function() {
      return new Promise((resolve, reject) => {
        const url = loginshieldSettingAjax.api_base + "loginshield/token/verify";
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

    showAccessRequestForm: function() {
      this.$actionForm.removeClass('loading');
      this.$actionForm.addClass('action-required');
    },

    showNormalForm: function() {
      this.$actionForm.removeClass('loading');
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

