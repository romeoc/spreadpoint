(function ($) {
    
    if (typeof SpreadPoint === 'undefined') {
        SpreadPoint = {};
    }

    // These are loaded on every page
    SpreadPoint.Defaults = function() {};
    SpreadPoint.Defaults.prototype = {
        callFunctions: function() { 
            this.initializeMobileMenu();
            this.initializeAccountDropdown();
        },
        initializeMobileMenu: function() {
            $('.toggle-menu').jPushMenu();
        },
        initializeAccountDropdown: function(){
            var accountLinks = $('.account-links');
            var timeout;
            
            var refferenceElement = $('.header-account-section');
            if (refferenceElement.length !== 0) {
                var offset = ($(window).width() - (refferenceElement.offset().left + refferenceElement.width())) - 50;
                accountLinks.css({right: offset});

                $('.header-account-section .account-username').on('mouseenter', function(){
                    accountLinks.show();
                }).on('mouseleave', function(){
                    timeout = setTimeout(function(){
                        accountLinks.hide();
                    }, 100);
                });

                accountLinks.on('mouseenter', function(){
                    clearTimeout(timeout);
                }).on('mouseleave', function(){
                    accountLinks.hide();
                });
            }
        }
    };
    
    // Login form display and AJAX Calls 
    SpreadPoint.LoginForm = function() {};
    SpreadPoint.LoginForm.prototype = {
        init: function(){
            this.body = $('body');
            this.loginOverlay = $('.login-overlay');
            this.loginFormContainer = $('.login-form-container');
            this.initializeLoginForm();
        },
        initializeLoginForm: function() {
            
            var $this = this;

            $('.login-action').on('click', function(){
                $this.loginFormContainer.slideDown(300).prependTo('body');
                $this.loginOverlay.show().on('click', function(){
                    $this.resetScroll($this);
                }).prependTo('body');
                $('.close-action').on('click', function(){
                    $this.resetScroll($this);
                });

                $this.body.addClass('no-scroll');
            });

            $('#Login').submit(function(e) {
                e.preventDefault();

                var thisForm = $(this);
                var postData = thisForm.serializeArray();
                var formURL = thisForm.attr("action");

                $.ajax({
                    url : formURL,
                    data : postData,
                    method: "POST"
                }).done(function(data){
                    if (data.status) {
                        location.reload();
                    } else {
                        $('.login-form .error').html(data.message);
                    }
                });
            });
        },
        resetScroll: function($this) {
            $this.body.removeClass('no-scroll');
            $this.loginFormContainer.slideUp(200);            
            $this.loginOverlay.hide();
        },
    };
    
    // Register Form AJAX Calls and visual adjustments
    SpreadPoint.RegisterForm = function() {};
    SpreadPoint.RegisterForm.prototype = { 
        init: function() {
            var $this = this;
            
            // Trigger click on checkbox when clicking on label
            $('.custom-row-terms-and-conditions label').on('click', function(){
                $(this).siblings('.terms-checkbox').trigger('click');
            });
           
            // Register Form AJAX
            $('#Register').submit(function(e) {
                e.preventDefault();
                
                if ($this.isFormValid()) {
                    var thisForm = $(this);
                    var postData = thisForm.serializeArray();
                    var formURL = thisForm.attr("action");

                    $.ajax({
                        url : formURL,
                        data : postData,
                        method: "POST"
                    }).done(function(data){
                        if (data.status) {
                            window.location = "/checkout";
                        } else {
                            $('.register-form .error').html(data.message);
                        }
                    });
                }
            });
        },
        isFormValid: function() {
            var email = $('.register-form .custom-row-email').find('input').val();
            var password = $('.register-form .custom-row-password').find('input').val();

            // Email Validation
            var emailRegex = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
            if (!emailRegex.test(email)) {
                $('.register-form .error').html('Please provide a valid email address');
                return false;
            }

            // Password Validation
            if (password.length < 6) {
                $('.register-form .error').html('Your password should contain at least 6 characters');
                return false;
            }

            var hasNumber = /[0-9]/;
            if(!hasNumber.test(password)) {
                $('.register-form .error').html('Your password must contain at least one number (0-9)');
                return false;
            }

            var hasLetter = /[a-zA-z]/;
            if(!hasLetter.test(password)) {
                $('.register-form .error').html('Your password must contain at least one letter (a-z,A-Z)');
                return false;
            }

            return true;
        }
    };
    
    SpreadPoint.Checkout = function() {};
    SpreadPoint.Checkout.prototype = { 
        init: function() {
            this.addFieldEvents();
        },
        addFieldEvents: function() {
            $('.checkout-plan').on('click', function(){
                $(this).addClass('selected')
                    .siblings().removeClass('selected');
            });
            
            $('.custom-row-card_number input').on('keyup', function(){
                var field = $(this);

                var fomatedCardNumber = field.val().split(" ").join("");
                if (fomatedCardNumber.length > 0) {
                  fomatedCardNumber = fomatedCardNumber.match(new RegExp('.{1,4}', 'g')).join(" ");
                }
                
                field.val(fomatedCardNumber);
            });
        }
    };
})(jQuery);