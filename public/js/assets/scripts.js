(function ($) {
    
    if (typeof SpreadPoint === 'undefined') {
        SpreadPoint = {};
    }

    // These are loaded on every page
    SpreadPoint.Defaults = {
        callFunctions: function() { 
            this.initializeMobileMenu();
            this.initializeAccountDropdown();
            this.initializeTabs();
            this.initializeSwitches();
        },
        // Mobile right side menu
        initializeMobileMenu: function() {
            $('.toggle-menu').jPushMenu();
        },
        // Logged In User Dropdown
        initializeAccountDropdown: function(){
            var accountLinks = $('.account-links');
            var timeout;
            
            var refferenceElement = $('.header-account-section');
            if (refferenceElement.length !== 0) {
                // Position of account link
                var offset = ($(window).width() - (refferenceElement.offset().left + refferenceElement.width())) - 50;
                accountLinks.css({right: offset});

                // Show links when we hover on account links
                $('.header-account-section .account-username').on('mouseenter', function(){
                    accountLinks.show();
                }).on('mouseleave', function(){
                    timeout = setTimeout(function(){
                        accountLinks.hide();
                    }, 100);
                });

                // don't hide dropdown links if we over over the dropdown
                accountLinks.on('mouseenter', function(){
                    clearTimeout(timeout);
                }).on('mouseleave', function(){
                    accountLinks.hide();
                });
            }
        },
        // Initialize all jQuery UI tabs
        initializeTabs: function() {
            $('.jqui-tabs').tabs();
        },
        // Initialize all jQuery Switches
        initializeSwitches: function() {
            $(".jqui-switch").switchButton({
                on_label: 'Yes',
                off_label: 'No'
            });
        }
    };
    
    // Login form display and AJAX Calls 
    SpreadPoint.LoginForm = {
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
    SpreadPoint.RegisterForm = { 
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
    
    SpreadPoint.Checkout = { 
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
    
    /**
     * Create file upload element for inputs
     */
    SpreadPoint.Uploader = {
        init: function () {
            $('.file-upload').not('.file-upload-wrapper .file-upload').each(function(){
                var $this = $(this);
                $this.wrap("<div class='file-upload-wrapper'></div>")
                    .attr('readonly',true)
                    .after(
                        $('<input class="uploader" name="file[]"/>').attr('type', 'file'),
                        $('<button class="remove-file">Remove</button>').attr('type', 'button')
                    );
            });
            
            this.bindUploader();
            this.bindRemoveButton();
        },
        bindUploader: function() {
            $('.uploader').on('change',function(){
                var $this = $(this);
                $this.siblings('.file-upload').val(
                    $this.val().split('\\').pop()
                ).trigger('blur');
            });
        },
        bindRemoveButton: function() {
            $('.remove-file').on('click', function(){
                $(this).siblings('.file-upload').val('').trigger('blur');
            });
        }
    }
    
    SpreadPoint.Widgets = {};
    SpreadPoint.Widgets.Map = [
        SpreadPoint.Templates.Widgets.Type.EnterContest,
        SpreadPoint.Templates.Widgets.Type.FacebookPageLike,
    ];
    SpreadPoint.Widgets.Controller = {
        /**
         * Initialize Widget Section
         * 
         * @param JSON allWidgetTypes - all available widgets 
         * @param JSON appliedWidgets - the widgets already applied to this campaign
         * @returns {undefined}
         */
        init: function(allWidgetTypes, appliedWidgets) {
            this.allWidgetTypes = allWidgetTypes;
            this.appliedWidgets = appliedWidgets;
            this.defaultWidgetId = 1;
            
            if (this.appliedWidgets.length === 0) {
                // If we have no widgets added yet we add the default 
                // widget which is used to register to the competition
                this.add(this.defaultWidgetId);
            } else {
                // Load applied widgets so they can be modified if necessary
                this.loadAppliedWidgets();
                this.reloadListeners();
            }
            
            // We load the available widgets so users can add them
            this.loadWidgetTypes();
            // And add data processing events before the form is submited
            this.addSubmitEvents();
        },
        /**
         * Add a new widget
         * 
         * @param int widgetTypeId - this is included in 'allWidgetTypes'
         */
        add: function(widgetTypeId) {
            var newWidget = { widgetType: widgetTypeId, referenceId: this.appliedWidgets.length + 1};
            this.appliedWidgets.push(newWidget);
                
            var source   = SpreadPoint.Widgets.Map[widgetTypeId - 1];
            var template = Handlebars.compile(source);
            
            var html = template(newWidget);
            $('.applied-widgets').append(html);
            
            this.reloadListeners();
            this.updateInputField();
        },
        /**
         * Add a new widget
         * 
         * @param int widgetId - this is included in 'appliedWidgets'
         */
        remove: function(widgetId) {
            $('.applied-widget-' + widgetId).remove();
            
            delete this.appliedWidgets[widgetId - 1];
            this.updateInputField();
        },
        /**
         * Loads the applied widgets. These are the widgets that already are added to the campaign.
         */
        loadAppliedWidgets: function() {
            this.appliedWidgets.forEach(function(appliedWidget){
                if (appliedWidget) {
                    var source = SpreadPoint.Widgets.Map[appliedWidget.widgetType - 1];
                    var template = Handlebars.compile(source);
                    var html = template(appliedWidget);

                    $('.applied-widgets').append(html);
                }
            });
        },
        /**
         * Loads available widget types. These are the widgets that can be added to the campaign.
         */
        loadWidgetTypes: function() {
            var source   = SpreadPoint.Templates.Widgets.Available;
            var template = Handlebars.compile(source);
            var html = template(this.allWidgetTypes);
            $('.available-widgets').html(html);
            
            this.loadWidgetTypeEvents();
        },
        /**
         * Update the field that is sent when submiting the form
         */
        updateInputField: function() {
            var data = JSON.stringify(this.appliedWidgets);
            $('.widgets-serialized').val(data);
        },
        /**
         * Reloads the click events for removing/updating objects
         */
        reloadListeners: function() {
            var $this = this;
            
            $('.close-widget').on('click', function(){
                var widgetId = $(this).closest('.applied-widget').data('id');
                $this.remove(widgetId);
            });
        },
        /**
         * Attach the events that will add the widget types on click
         */
        loadWidgetTypeEvents: function() {
            var $this = this;
            
            $('.widget-type').on('click', function(){
                var widgetTypeId = $(this).data('type');
                $this.add(widgetTypeId);
            });
        },
        /**
         * Adds an event before the form submit that will update all widgets data
         */
        addSubmitEvents: function() {
            var $this = this;
            $('#campaign').submit(function(e) {
                e.preventDefault();
                
                $('.applied-widget-element').each(function(){
                    var element = $(this);
                    var elementKey = element.data('key');
                    var elementValue = element.val();
                    var widgetId = element.closest('.applied-widget').data('id');
                    
                    $this.appliedWidgets[widgetId - 1][elementKey] = elementValue;
                });
                
                $this.updateInputField();
                this.submit();
            });
        }
    }
})(jQuery);

//$.get("http://ipinfo.io", function(response) {
//    console.log(response);
//}, "jsonp");