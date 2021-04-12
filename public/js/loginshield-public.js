window.publicloginshield = window.publicloginshield || {};

publicloginshield.LoginForm = (function($) {
	const selectors = {
		form: '#LoginShieldLoginForm',
		btnNext: '#btnNext',
		btnLogin: '#btnLogin',
		login: '#user_login',
		password: '#user_pass',
		remember: '#rememberme',
		formGroupLogin: '.form-group-login',
		formGroupPassword: '.form-group-password',
		formGroupLoginShield: '.form-group-loginshield',
		formGroupAction: '.form-group-action',
    iframe: '#loginshield-content',
		errorMsg: '.error-msg',
	};

	function LoginForm() {
		if (!$(selectors.form).length)
			return;

		this.rememberMe = false;
		this.isLoginSubmitted = false;
		this.isPasswordSubmitted = false;
		this.isLoading = false;

		this.$form = $(selectors.form);
		this.$login = this.$form.find(selectors.login);
		this.$password = this.$form.find(selectors.password);
		this.$rememberMe = this.$form.find(selectors.remember);
		this.$formGroupLogin = this.$form.find(selectors.formGroupLogin);
		this.$formGroupPassword = this.$form.find(selectors.formGroupPassword);
		this.$formGroupLoginShield = this.$form.find(selectors.formGroupLoginShield);
		this.$formGroupAction = this.$form.find(selectors.formGroupAction);
    this.$iframe = this.$form.find(selectors.iframe);
		this.$btnNext = this.$form.find(selectors.btnNext);
		this.$btnLogin = this.$form.find(selectors.btnLogin);

		this.$formGroupLogin.on('keyup', this.handleLoginChange.bind(this));
		this.$formGroupPassword.on('keyup', this.handlePasswordChange.bind(this));
    this.$rememberMe.on('change', this.handleRememberMeChange.bind(this));
		this.$btnNext.on('click', this.handleNext.bind(this));
		this.$btnLogin.on('click', this.handleLogin.bind(this));

		this.init();
	}

	LoginForm.prototype = $.extend({}, LoginForm.prototype, {
    init: function() {
      const mode = this.$form.data('mode');
      const loginshield = this.$form.data('loginshield');

      if (mode === 'resume-loginshield' && loginshield) {
        this.resumeLoginShield({ forward: loginshield })
      }
    },

		handleLoginChange: function(e) {
			const login = $(e.target).val();
			this.validateLogin(login);
		},

		handlePasswordChange: function(e) {
			const password = $(e.target).val();
			this.validatePassword(password);
		},

    handleRememberMeChange: function(e) {
      this.rememberMe = e.target.checked;
    },

    handleNext: async function(e) {
			if (this.isLoading)
				return;

      this.isLoginSubmitted = true;

			const login = this.$login.val();
			const isLoginValid = this.validateLogin(login);

			if (!isLoginValid)
				return;

			this.isLoading = true;
			this.$btnNext.addClass('loading');

			this.checkUserLoginshieldEnabled({ login })
				.then(response => {
					if (response && response.isActivated) {
            this.enableLoginShieldForm(login);
					} else {
            this.isLoading = false;
            this.$btnNext.removeClass('loading');
            this.enablePasswordForm();
					}
				})
				.catch(error => {
          this.isLoading = false;
          this.$btnNext.removeClass('loading');
					this.showMessage('Service is unavailable', 'error');
				});
		},

    handleLogin: async function(e) {
      if (this.isLoading)
        return;

      this.isPasswordSubmitted = true;

      const login = this.$login.val();
      const password = this.$password.val();
      const isPasswordValid = this.validatePassword(password);

      if (!isPasswordValid)
        return;

      this.isLoading = true;
      this.$btnLogin.addClass('loading');

      this.loginWithPassword({ login, password, remember: this.rememberMe })
				.then(response => {
					if (response.isLoggedIn) {
						this.redirect();
					} else {
						this.showMessage('Invalid username or password', 'error');
                        this.resetLoginForm();
					}
				})
				.catch(error => {
					this.showMessage('Service is unavailable');
				})
				.finally(() => {
					this.isLoading = false;
					this.$btnLogin.removeClass('loading');
				});
    },

		validateLogin: function(login) {
			const isEmpty = login === '';
      if (!isEmpty) {
        this.$formGroupLogin.removeClass('has-error');
      } else {
        this.$formGroupLogin.addClass('has-error');
      }

			if (isEmpty) {
				this.$formGroupLogin.find(selectors.errorMsg).html('Please enter username or email address.');
				this.$formGroupLogin.find(selectors.errorMsg).show();
			} else {
				this.$formGroupLogin.find(selectors.errorMsg).html('');
				this.$formGroupLogin.find(selectors.errorMsg).hide();
			}

      if (!this.isLoginSubmitted) {
        this.$formGroupLogin.removeClass('has-error');
        this.$formGroupLogin.find(selectors.errorMsg).html('');
        this.$formGroupLogin.find(selectors.errorMsg).hide();
      }

			return !isEmpty;
		},

		validatePassword: function(password) {
			const isEmpty = password === '';
			if (!isEmpty) {
				this.$formGroupPassword.removeClass('has-error');
			} else {
				this.$formGroupPassword.addClass('has-error');
			}

			if (isEmpty) {
				this.$formGroupPassword.find(selectors.errorMsg).html('Please enter a password.');
				this.$formGroupPassword.find(selectors.errorMsg).show();
			} else {
				this.$formGroupPassword.find(selectors.errorMsg).html('');
				this.$formGroupPassword.find(selectors.errorMsg).hide();
			}

			if (!this.isPasswordSubmitted) {
				this.$formGroupPassword.removeClass('has-error');
				this.$formGroupPassword.find(selectors.errorMsg).html('');
				this.$formGroupPassword.find(selectors.errorMsg).hide();
			}

			return !isEmpty;
		},

		enablePasswordForm: function() {
      this.$formGroupLogin.hide();
      this.$formGroupPassword.show();

      this.$btnNext.hide();
      this.$btnLogin.show();
		},

		enableLoginShieldForm: async function(login) {
      const self = this;
      const { forward } = await this.loginWithLoginShield({ login });

      if (!forward) {
        this.handleUnavailableService();
        return;
			}

      this.$formGroupLogin.hide();
      this.$formGroupAction.hide();
      this.$formGroupLoginShield.show();
      this.$form.addClass('onLoginShield');

      loginshieldInit({
        elementId: 'loginshield-content',
        backgroundColor: '#f1f1f1',
        width: 500,
        height: 460,
        action: 'start',
        mode: null,
        forward: forward,
        rememberMe: true, // this.rememberMe,
        onResult: function (result) {
          if (!result)
            return;

          self.onResult(result);
        },
      });
		},

    checkUserLoginshieldEnabled: function(payload) {
      const { login } = payload;
      return new Promise((resolve, reject) => {
        const url = loginShieldPublicAjax.api_base + "loginshield/checkUserWithLogin";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            login        :  login,
          }),
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', loginShieldPublicAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          reject(error);
        });
      });
    },

		loginWithPassword: function(payload) {
      const { login, password, remember } = payload;
      return new Promise((resolve, reject) => {
        const url = loginShieldPublicAjax.api_base + "loginshield/loginWithPassword";
        $.ajax({
          url        : url,
          method     : 'POST',
          dataType   : 'json',
          contentType: 'application/json',
          cache      : false,
          data       : JSON.stringify({
            login        :  login,
            password     :  password,
            remember     :  remember,
          }),
          beforeSend : function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', loginShieldPublicAjax.nonce);
          }
        }).done(function (data) {
          resolve(data);
        }).fail(function (error) {
          reject(error);
        });
      });
		},

		loginWithLoginShield: function(payload) {
      const { login, mode, verifyToken } = payload;
      const searchParams = new URLSearchParams(window.location.search);
      const redirectTo = searchParams.get('redirect_to'); // may be null
      
			return new Promise((resolve, reject) => {
				const url = loginShieldPublicAjax.api_base + "loginshield/session/login/loginshield";
				$.ajax({
					url        : url,
					method     : 'POST',
					dataType   : 'json',
					contentType: 'application/json',
					cache      : false,
					data       : JSON.stringify({
            login    		:  login,
            mode        :  mode,
            verifyToken :  verifyToken,
            redirectTo: redirectTo,
					}),
					beforeSend : function (xhr) {
						xhr.setRequestHeader('X-WP-Nonce', loginShieldPublicAjax.nonce);
					}
				}).done(function (data) {
					resolve(data);
				}).fail(function (error) {
					reject(error);
				});
			});
		},

    resumeLoginShield: async function({ forward }) {
      const self = this;

      loginshieldInit({
        elementId: 'loginshield-content',
        backgroundColor: '#f1f1f1',
        width: 500,
        height: 460,
        action: 'resume',
        forward: forward,
        rememberMe: true, // this.rememberMe,
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
      console.log(`finishLoginShield: isAuthenticated ${isAuthenticated}`);
      if (isAuthenticated) {
        // this.showMessage('LoginShield account registration is succeed.', 'success');
        this.redirect();
      } else if (error) {
        console.error(`finishLoginShield error: ${error}`);
        this.resetLoginForm();
        this.showMessage(`Login failed: ${error}`, 'error');
      } else {
        console.error('finishLoginShield: unknown error');
        this.resetLoginForm();
        this.showMessage('Login failed', 'error');
      }
    },

		redirect: function() {
			const redirectTo = this.$form.data('redirect-to');
			if (redirectTo) {
                window.location = redirectTo;
            } else {
				window.location = '/';
            }
		},

    resetLoginForm: function() {
      this.isLoading = false;
      this.$formGroupLogin.show();
      this.$btnNext.show();
      this.$btnNext.removeClass('loading');
      this.$formGroupAction.show();
      this.$formGroupPassword.hide();
      this.$password.val("");
      this.$btnLogin.hide();
      this.$btnLogin.removeClass('loading');
      this.$formGroupLoginShield.hide();
      this.$iframe.html('');
      this.$form.removeClass('onLoginShield');
		},

		handleUnavailableService() {
    	console.warn('LoginShield service is unavailable');
      this.resetLoginForm();
      this.enablePasswordForm();
		},

    onResult: function(result) {
      console.log('onResult : %o', result);

      if (!result) {
        this.handleUnavailableService();
        return;
			}

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
          this.handleUnavailableService();
          break;
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
	});

	return LoginForm;
})(jQuery);

(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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

		new publicloginshield.LoginForm();

	});

})( jQuery );
