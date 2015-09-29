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
            this.initializeAccordions();
            this.initializeDateTimePickers();
            this.initializeHints();
            this.initializeKalypto();
            this.initializeCountDowns();
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
                accountLinks.css({right: Math.max(offset,5)});

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
            $('.jqui-tabs').tabs().removeClass('invisible');
            $('.jqui-tabs-vertical').tabs().removeClass('invisible')
                .addClass('ui-tabs-vertical ui-helper-clearfix');
            $('jqui-tabs-vertical li').removeClass('ui-corner-top')
                .addClass('ui-corner-left');
        },
        // Initialize all jQuery Switches
        initializeSwitches: function() {
            $(".jqui-switch").switchButton({
                on_label: 'Yes',
                off_label: 'No'
            }).each(function(){
                var $this = $(this);
                var title = $this.attr('title');
                $this.siblings('.switch-button-label').attr('title', title).addClass('click-note');
                $this.siblings('.switch-button-background').attr('title', title).addClass('click-note');
            });
        },
        initializeListingCampaigns: function() {
            $('.campaign .title').hide();
            $('.campaign').on('mouseenter', function(){
                $(this).find('.title').slideToggle();
            }).on('mouseleave', function(){
                $(this).find('.title').slideToggle();
            });
            
            // Disable edit on mobile by redirecting to campaign view
            if (screen.width <= 769) {
                $('.campaign a').each(function() {
                    var self = $(this);
                    var href = self.attr('href');
                    
                    href = href.replace('edit', 'view');
                    self.attr('href', href);
                });
            }
        },
        initializeAccordions: function() {
            $('.jqui-accordion').accordion({
                collapsible: true,
                heightStyle: "content"
            });
        },
        initializeDateTimePickers: function() {
            $('.jq-datetimepicker').datetimepicker();
        },
        initializeHints: function() {
            $('.focus-note').qtip({
                position: {
                    my: 'center left',
                    at: 'center right'
                },
                style: { classes: 'qtip-blue qtip-rounded qtip-shadow' }
            });
            
            $('.focus-downward-note').qtip({
                position: {
                    my: 'top left',
                    at: 'center right'
                },
                style: { classes: 'qtip-blue qtip-rounded qtip-shadow' }
            });
            
            $('.click-note').qtip({
                position: {
                    my: 'top center',
                    at: 'bottom center'
                },
                style: { classes: 'qtip-blue qtip-rounded qtip-shadow' }
            });
        },
        initializeKalypto: function() {
            $('input:checkbox.kalypsify').kalypto();
            $('input:radio.kalypsify').kalypto({toggleClass: "toggleR"});
        },
        initializeCountDowns: function() {
            $(".jq-countdown").each(function() {
                var self = $(this);
                var deadline = self.data('deadline');
                $(this).countdown(deadline, function(event) {
                    self.text(
                        event.strftime('%-D day%!D %H:%M:%S')
                    );
                });
            });
        }
    };
    
    SpreadPoint.Settings = {
        action: null,
        init: function() {
            var self = this;
            self.action = $('#settings').attr('action');
            
            $('.change-password-action').on('click', function() {
                $(this).siblings('.inputs').toggle();
            });
            
            $('.switch-action').on('click', function() {
                self.action = $(this).data('action');
            });
            
            $('#settings').submit(function(e) {
                e.preventDefault();
                $(this).attr('action', self.action);
                
                if (self.valid()) {
                    this.submit();
                }
            });
        },
        valid: function() {
            if ($('.change-password .inputs').is(':visible')) {
                var oldPassword = $('#settings [name="old-password"]').val();
                var password = $('#settings [name="new-password"]').val();
                var confirm = $('#settings [name="confirm-password"]').val();

                if (oldPassword.length === 0 || password.length === 0 || confirm.length === 0) {
                    SpreadPoint.Campaign.Controller.logError('All fields are mandatory.');
                    return false;
                } else if (password !== confirm) {
                    SpreadPoint.Campaign.Controller.logError('The passwords do not match');
                    return false;
                }

                // Password Validation
                if (password.length < 6) {
                    SpreadPoint.Campaign.Controller.logError('Your password should contain at least 6 characters');
                    return false;
                }

                var hasNumber = /[0-9]/;
                if(!hasNumber.test(password)) {
                    SpreadPoint.Campaign.Controller.logError('Your password must contain at least one number (0-9)');
                    return false;
                }

                var hasLetter = /[a-zA-z]/;
                if(!hasLetter.test(password)) {
                    SpreadPoint.Campaign.Controller.logError('Your password must contain at least one letter (a-z,A-Z)');
                    return false;
                }
            }
            return true;
        }
    };
    
    SpreadPoint.FollowTo = function (target, pos) {
        var $this = $(target),
            $window = $(window);

        $window.scroll(function(e) {
            $this.css({
                top: Math.max(0, pos - $window.scrollTop())
            });
        });
    };
    
    SpreadPoint.PopUp = {
        create: function(container, action, close, moveContainer){
            this.body = $('body');
            this.overlay = $('<div class="popup-overlay hide">').prependTo(this.body);
            
            this.container = (container instanceof $) ? container : $(container);
            this.action = (action instanceof $) ? action : $(action);
            this.close = (close instanceof $) ? close : $(close);
            
            this.container = this.container.addClass('popup-container');
            this.move = moveContainer;
            
            this.loadEvents();
        },
        loadEvents: function() {
            
            var $this = this;

            this.action.on('click', function() {
                $this.container.slideDown(300);
                if ($this.move) {
                    $this.container.prependTo($this.body);
                }
                $this.overlay.show().on('click', function(){
                    $this.resetScroll($this);
                });
                $this.close.on('click', function(){
                    $this.resetScroll($this);
                });

                $this.body.addClass('no-scroll');
            });
        },
        resetScroll: function($this) {
            $this.body.removeClass('no-scroll');
            $this.container.slideUp(200);            
            $this.overlay.hide();
        }
    };
    
    // Login form display and AJAX Calls 
    SpreadPoint.LoginForm = {
        init: function(){
            SpreadPoint.PopUp.create('.login-form-popup', '.login-action', '.close-action', true);
            this.initializeLoginForm();
        },
        initializeLoginForm: function() {
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
        }
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
    
    SpreadPoint.PasswordReset = {
        init: function() {
            var self = this;
            $('#change-password').submit(function(e) {
                e.preventDefault();
                
                if (self.valid()) {
                    this.submit();
                }                
            });
        },
        valid: function() {
            var password = $('#change-password [name="password"]').val();
            var confirm = $('#change-password [name="confirm"]').val();
            
            if (password.length === 0 || confirm.length === 0) {
                SpreadPoint.Campaign.Controller.logError('All fields are mandatory.');
                return false;
            } else if (password !== confirm) {
                SpreadPoint.Campaign.Controller.logError('The passwords do not match');
                return false;
            }
            
            // Password Validation
            if (password.length < 6) {
                SpreadPoint.Campaign.Controller.logError('Your password should contain at least 6 characters');
                return false;
            }

            var hasNumber = /[0-9]/;
            if(!hasNumber.test(password)) {
                SpreadPoint.Campaign.Controller.logError('Your password must contain at least one number (0-9)');
                return false;
            }

            var hasLetter = /[a-zA-z]/;
            if(!hasLetter.test(password)) {
                SpreadPoint.Campaign.Controller.logError('Your password must contain at least one letter (a-z,A-Z)');
                return false;
            }
            
            return true;
        }
    };
    
    SpreadPoint.ContactForm = {
        init: function() {
            var $this = this;
            
            $('#contact').submit(function(e) {
                e.preventDefault();
                
                if ($this.valid()) {
                    this.submit();
                }
            });
        },
        valid: function() {
            var fullname = $('.contact-form-fullname input').val();
            var email = $('.contact-form-email input').val();
            var message = $('.contact-form-message textarea').val();

            if (!fullname) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please enter your name</li>');
                return false;
            }
            
            var emailRegex = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
            if (!email) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please enter your email address</li>');
                return false;
            } else if (!emailRegex.test(email)) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please provide a valid email address</li>');
                return false;
            }
            
            if (!message) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please enter a message</li>');
                return false;
            } else if (message.length > 20000) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>The maximum message length is 20000 characters</li>');
                return false;
            }
            
            return true;
        }
    };
    
    SpreadPoint.SupportForm = {
        init: function() {
            var $this = this;
            
            $('#support').submit(function(e) {
                e.preventDefault();
                
                if ($this.valid()) {
                    this.submit();
                }
            });
        },
        valid: function() {
            var fullname = $('.support-form-fullname input').val();
            var email = $('.support-form-email input').val();
            var subject = $('.support-form-subject input').val();
            var message = $('.support-form-message textarea').val();

            if (!fullname) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please enter your name</li>');
                return false;
            }
            
            var emailRegex = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
            if (!email) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please enter your email address</li>');
                return false;
            } else if (!emailRegex.test(email)) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please provide a valid email address</li>');
                return false;
            }
            
            if (!subject) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please enter a subject</li>');
                return false;
            } else if (subject.length > 500) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>The maximum subject length is 500 characters</li>');
                return false;
            }
            
            if (!message) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please enter your message</li>');
                return false;
            } else if (message.length > 20000) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>The maximum message length is 20000 characters</li>');
                return false;
            }
            
            return true;
        }
    };
    
    SpreadPoint.Checkout = {
        paypalAction: false,
        plan: null,
        billingPeriod: null,
        init: function() {
            this.addFieldEvents();
            
            var selectedPlan = $('[name="plan"]:checked').val();
            $('.visible-plan .checkout-plan').filter(function() { 
                return $(this).data("value") == selectedPlan;
            }).addClass('selected');
            
            this.initUpgradeSection();
        },
        addCurrentPlanValidation: function(plan) {
            this.plan = plan;
        },
        addCurrentBillingPeriodValidation: function(billingPeriod) {
            this.billingPeriod = billingPeriod;
        },
        addFieldEvents: function() {
            var $this = this;
            
            $('.checkout-plan').on('click', function(){
                var $this = $(this);
                
                $this.addClass('selected')
                    .siblings().removeClass('selected');
            
                $('.hidden-plan-element').find('input:radio[name="plan"]')
                    .filter('[value=' + $this.data('value') + ']')
                    .prop('checked', true);
            });
            
            $('.custom-row-card_number input').on('keyup', function(){
                var field = $(this);

                var spacelessCard = field.val().split(" ").join("");
                var fomatedCardNumber = spacelessCard;
                if (fomatedCardNumber.length > 0) {
                  fomatedCardNumber = fomatedCardNumber.match(new RegExp('.{1,4}', 'g')).join(" ");
                }
                
                field.val(fomatedCardNumber);
                $('.real-card').val(spacelessCard);
            });
            
            $('.custom-row-expiry_date input').on('keyup', function(){
                var field = $(this);
                var expirationDate = field.val();
                var key = event.keyCode;
                
                // backspace || delete || arrow keys
                if (key === 8 || key === 46 || (key >36 && key < 41)) {
                    return false;
                }
                
                var input = expirationDate[expirationDate.length - 1];
                if ((isNaN(input) && input !== '/') || (input === '/' && expirationDate.length !== 3)) {
                    expirationDate = expirationDate.slice(0, -1);
                } else {
                    if (expirationDate.length === 1 && expirationDate > 1) {
                        expirationDate = '0' + expirationDate;
                    }
                    if (expirationDate.length === 2) {
                        expirationDate += '/';
                    }
                    if (expirationDate.length === 5 && expirationDate.slice(-2) != 20) {
                        expirationDate = expirationDate.slice(0, 3) + 20 + expirationDate.slice(3);
                    }
                }
                
                field.val(expirationDate);
                
                var values = expirationDate.split('/');
                $('.expiry-month').val(values[0]);
                $('.expiry-year').val(values[1]);
            });
            
            $('.pay-pal-action').on('click', function() {
                $this.paypalAction = true;
                $('.checkout-submit').trigger('click');
            }).on('mouseenter', function(){
                $(this).removeClass('greyscale');
            }).on('mouseleave', function(){
                $(this).addClass('greyscale');
            });
            
            $('#Checkout').submit(function(e) {
                e.preventDefault();

                var self = this;
                var form = $(this);
                var action = form.attr('action');
                var submitButton = form.find('.custom-row-submit-action input');
                var loader = $('.loader');
                
                submitButton.prop('disabled', true);
                submitButton.addClass('disabled');
                loader.show();
                
                if ($this.paypalAction) {
                    form.attr('action',action.replace('submit','paypalStart'));
                } else {
                    form.attr('action',action.replace('paypalStart','submit'));
                }
                
                if ($this.valid() && $this.checkEnterprisePlan()) {
                    if ($this.paypalAction) {
                        this.submit();
                    } else {
                        Stripe.card.createToken(form, function(status, response) {
                            if (response.error) {
                                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>' + response.error.message +'</li>');
                                submitButton.prop('disabled', false);
                                submitButton.removeClass('disabled');
                                loader.hide();
                            } else {
                                var token = response.id;
                                form.append($('<input type="hidden" name="stripeToken" />').val(token));
                                self.submit();
                            }
                        });
                    }
                } else {
                    submitButton.prop('disabled', false);
                    submitButton.removeClass('disabled');
                    loader.hide();
                }
                
                $this.paypalAction = false;
            });
        },
        checkEnterprisePlan: function() {
            var selectedPlan = $('[name="plan"]:checked').val();
            var enterprisePlan = 2;

            if (selectedPlan == enterprisePlan) {
                $('.upgrade-payment-form').hide();
                window.location.href = "/contact";
                
                return false;
            }
            
            return true;
        },
        initUpgradeSection: function() {
            SpreadPoint.PopUp.create('.upgrade-payment-form', '.upgrade-plan-action');
        },
        valid: function() {

            var plan = $('[name="plan"]:checked').val();
            
            if (plan == 2) {
                window.location = "/contact";
            }
            
            if (!plan) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please select a package</li>');
                return false;
            } 
                        
            var period = $('[name="period"]:checked').val();
            if (!period) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please select a billing period.</li>');
                return false;
            }
            
            if ((this.plan !== null && plan == this.plan) && (this.billingPeriod !== null && period == this.billingPeriod)) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>You are already subscribed to this plan.</li>');
                return false;
            }

            return true;
        }
    };
    
    /**
     * Create file upload element for inputs
     */
    SpreadPoint.Uploader = {
        init: function (selector, name) {
            selector = selector || '.file-upload';
            $(selector).not('.file-upload-wrapper .file-upload').each(function(){
                var $this = $(this);
                $this.attr('readonly',true);

                var data = {name: name};
                var imageUrl = $this.data('src');
                if (imageUrl) {
                    data['image'] = imageUrl;
                }
                
                var source   = SpreadPoint.Templates.Uploader;
                var template = Handlebars.compile(source);
                var html = template(data);
                
                $this.after(html);
            });
            
            this.bindUploader();
            this.bindActions();
        },
        bindUploader: function() {
            $('.uploader').on('change',function(){
                var $this = $(this);
                $this.parent().siblings('.file-upload').val(
                    $this.val().split('\\').pop()
                ).trigger('blur');
            });
        },
        bindActions: function() {
            $('.remove-file').on('click', function(){
                $(this).parent().siblings('.file-upload').val('').trigger('blur');
            });
            $('.select-file').off('click').on('click', function(){
                $(this).siblings('.uploader').trigger('click');
            });
            $('.enlarge-image').magnificPopup({ 
                type: 'image'
            });
        }
    }
    
    SpreadPoint.Widgets = {};
    SpreadPoint.Widgets.Map = [
        SpreadPoint.Templates.Widgets.Type.EnterContest,
        SpreadPoint.Templates.Widgets.Type.VisitPage,
        SpreadPoint.Templates.Widgets.Type.FacebookShare,
        SpreadPoint.Templates.Widgets.Type.TwitterTweet,
        SpreadPoint.Templates.Widgets.Type.TwitterFollow,
        SpreadPoint.Templates.Widgets.Type.Reference
    ];
    SpreadPoint.Widgets.Controller = {
        /**
         * Initialize Widget Section
         * 
         * @param JSON allWidgetTypes - all available widgets 
         * @param JSON appliedWidgets - the widgets already applied to this campaign
         * @returns {undefined}
         */
        init: function(allWidgetTypes, appliedWidgets, baseImagePath) {
            this.allWidgetTypes = allWidgetTypes;
            this.appliedWidgets = appliedWidgets;
            this.baseImagePath = baseImagePath;
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
        },
        /**
         * Add a new widget
         * 
         * @param int widgetTypeId - this is included in 'allWidgetTypes'
         */
        add: function(widgetTypeId) {
            if (this.defaultWidgetId === widgetTypeId && this.hasDefaultWidget()) {
                SpreadPoint.Campaign.Controller.logError('You can only have one of those widgets.', '.entries-tab', null, true);
                return false;
            }
            
            var newWidget = { widgetType: widgetTypeId, referenceId: this.appliedWidgets.length + 1};
            this.appliedWidgets.push(newWidget);
                
            var source   = SpreadPoint.Widgets.Map[widgetTypeId - 1];
            var template = Handlebars.compile(source);
            
            var html = template(newWidget);
            $('.applied-widgets').append(html);
            
            this.reloadListeners();
            this.updateInputField();
            
            window.scrollTo(0, $(document).height());
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
        hasDefaultWidget: function() {
            var defaultWidgetType = this.defaultWidgetId;
            var result = false;
            
            this.appliedWidgets.forEach(function(appliedWidget) {
                if (appliedWidget.widgetType === defaultWidgetType) {
                    result = true;
                }
            });
            
            return result;
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
            
            var selector = '.applied-widgets textarea';
            SpreadPoint.Campaign.Controller.adjustTextareas(selector);
            SpreadPoint.Defaults.initializeHints();
            
            $('.widget-header').off('click').on('click', function(){
                $(this).siblings('.applied-widget-row').toggle();
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
         * Process all data
         */
        finalize: function() {
            var $this = this;
                
            $('.applied-widget-element').each(function(){
                var element = $(this);
                var elementKey = element.data('key');
                var elementValue = element.val();
                var widgetId = element.closest('.applied-widget').data('id');

                $this.appliedWidgets[widgetId - 1][elementKey] = elementValue;
            });

            $this.updateInputField();
        },
        /**
         * Check if all widgets data is valid
         * @return bool
         */ 
        isValid: function() {
            var tab = '.entries-tab';
            var allValid = true;
            
            if (this.appliedWidgets.filter(String).length === 0) {
                this.add(this.defaultWidgetId);
                var identifier = '.applied-widget-' + this.appliedWidgets.length;
                SpreadPoint.Campaign.Controller.logError('You must have at least one <strong>Enter Contest</strong> Widget', tab, identifier, true);
                
                return false;
            }
            
            var hasDefaultWidget = false;
            this.appliedWidgets.forEach(function(widget){
                var identifier = '.applied-widget-' + widget.referenceId;
                
                if (!widget.earningValue) {
                    SpreadPoint.Campaign.Controller.logError('Every widget must have an <strong>Earning Value</strong>', tab, identifier, true);
                    allValid = false;
                } else if (isNaN(parseFloat(widget.earningValue)) || !isFinite(widget.earningValue)) {
                    SpreadPoint.Campaign.Controller.logError('The widget <strong>Earning Value</strong> must be a numeric value', tab, identifier, true);
                    allValid = false;
                } else if (widget.earningValue <= 0) {
                    SpreadPoint.Campaign.Controller.logError('The widget <strong>Earning Value</strong> must be greater than 0', tab, identifier, true);
                    allValid = false;
                }
                
                if (!widget.title) {
                    SpreadPoint.Campaign.Controller.logError('Every widget must have a <strong>Title</strong>', tab, identifier, true);
                    allValid = false;
                } else if (widget.title.length > 32) {
                    SpreadPoint.Campaign.Controller.logError('The maximum <strong>Title</strong> length is 140 characters', tab, identifier, true);
                    allValid = false;
                }
                
                switch (widget.widgetType) {
                    case 1: 
                        hasDefaultWidget = true;
                        break;
                    case 2:
                        if (!widget.page) {
                            SpreadPoint.Campaign.Controller.logError('Your page visit widget must have a <strong>Page Link</strong>', tab, identifier, true);
                            allValid = false;
                        } else if (!widget.page.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/)) {
                            SpreadPoint.Campaign.Controller.logError('Your page visit widget has an invalid <strong>Page Link</strong>', tab, identifier, true);
                            allValid = false;
                        }
                        break;
                    case 3:
                        if (!widget.link) {
                            SpreadPoint.Campaign.Controller.logError('Your facebook widget must have a <strong>Link</strong>', tab, identifier, true);
                            allValid = false;
                        } else if (!widget.link.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/)) {
                            SpreadPoint.Campaign.Controller.logError('Your facebook widget has an invalid <strong>Page Link</strong>', tab, identifier, true);
                            allValid = false;
                        }
                        
                        if (!widget.title) {
                            SpreadPoint.Campaign.Controller.logError('Your facebook widget must have a <strong>Title</strong>', tab, identifier, true);
                            allValid = false;
                        }
                        break;
                    case 4:
                        if (!widget.message) {
                            SpreadPoint.Campaign.Controller.logError('Your twitter widget must have a <strong>Default Message</strong>', tab, identifier, true);
                            allValid = false;
                        } else if (widget.message.width > 140) {
                            SpreadPoint.Campaign.Controller.logError('The maximum <strong>Message</strong> length is 140 characters', tab, identifier, true);
                            allValid = false;
                        }
                        break;
                    case 5:
                        if (!widget.user) {
                            SpreadPoint.Campaign.Controller.logError('Your twitter widget must have a <strong>User</strong>', tab, identifier, true);
                            allValid = false;
                        }
                        break;
                }
            });
            
            if (!hasDefaultWidget) {
                this.add(this.defaultWidgetId);
                var identifier = '.applied-widget-' + this.appliedWidgets.length;
                SpreadPoint.Campaign.Controller.logError('You must have at least one <strong>Enter Contest</strong> Widget', tab, identifier, true);
                
                return false;
            }
            
            return allValid;
        }
    }
    
    SpreadPoint.Prize = {};
    SpreadPoint.Prize.Controller = {
        /**
         * Initialize prize section
         * 
         * @param JSON array prizes
         */
        init: function(prizes, baseImagePath) {
            this.prizes = prizes;
            this.baseImagePath = baseImagePath;
            
            if (this.prizes.length === 0) {
                // Add initial prize
                this.add();
            } else {
                // Load existing prizes
                this.loadPrizes();
            }
            
            // Initialize events
            this.addCreationEvents();
        },
        /**
         * Add a new prize
         */
        add: function() {
            var newPrize = { referenceId: this.prizes.length + 1};
            this.prizes.push(newPrize);
                
            var source   = SpreadPoint.Templates.Prize;
            var template = Handlebars.compile(source);
            
            var html = template(newPrize);
            $('.prizes').append(html);
            
            this.reloadListeners();
            this.updateInputField();
            
            window.scrollTo(0, $(document).height());
        },
        /**
         * Remove a prize by it's reference id
         * 
         * @param int prizeId / referenceId
         */
        remove: function(prizeId) {
            $('.prize-' + prizeId).remove();
            
            delete this.prizes[prizeId - 1];
            this.updateInputField();
        },
        /**
         * Load existing prizes received as a parameter
         */
        loadPrizes: function() {
            var $this = this;
            var count = 1;
            this.prizes.forEach(function(prize){
                if (prize) {
                    if (prize['image']) {
                        prize['src'] = $this.baseImagePath + prize.image;
                    }
                    
                    var source = SpreadPoint.Templates.Prize;
                    var template = Handlebars.compile(source);
                    var html = template(prize);

                    $('.prizes').append(html);
                    $this.reloadListeners(count);
                    count++;
                }
            });
        },
        /**
         * Update yje input field that is sent when the form is submited
         */
        updateInputField: function() {
            var data = JSON.stringify(this.prizes);
            $('.prizes-serialized').val(data);
        },
        /**
         * Adds image uploader and prize removal events
         */
        reloadListeners: function(count) {
            var $this = this;
            
            $('.close-prize').on('click', function(){
                var prizeId = $(this).closest('.row-prize').data('id');
                $this.remove(prizeId);
            });
            
            var selector = '.prizes textarea';
            SpreadPoint.Campaign.Controller.adjustTextareas(selector);
            
            var selectorId = count || this.prizes.length;
            var imageFieldSelector = ".prize-" + selectorId + " .file-upload";
            var imageFieldName = 'prize-' + selectorId;
            
            SpreadPoint.Uploader.init(imageFieldSelector, imageFieldName);
            SpreadPoint.Defaults.initializeHints();
            
            $('.prize-header').off('click').on('click', function(){
                $(this).siblings('.row-prize-element').toggle();
            });
        },
        /**
         * Adds prize addition event
         */
        addCreationEvents: function() {
            var $this = this;
            $('.add-prize-action').on('click', function(){
                $this.add();
            });
        },
        /**
         * Process all data
         */
        finalize: function() {
            var $this = this;
                
            $('.prize-element').each(function(){
                var element = $(this);
                var elementKey = element.data('key');
                var elementValue = element.val();
                var prizeId = element.closest('.row-prize').data('id');

                $this.prizes[prizeId - 1][elementKey] = elementValue;
            });

            $this.updateInputField();
        },
        /**
         * Check if all prize data is valid
         * @return bool
         */ 
        isValid: function() {
            var tab = '.prize-tab';
            var allValid = true;
            
            if (this.prizes.filter(String).length === 0) {
                SpreadPoint.Campaign.Controller.logError('You must have at least one prize', tab);
                this.add();
                
                return false;
            }
            
            this.prizes.forEach(function(prize){
                var identifier = '.prize-' + prize.referenceId;
                
                if (!prize.name) {
                    SpreadPoint.Campaign.Controller.logError('Your prize does not have a <strong>Title</strong>',tab, identifier, true);
                    allValid = false;
                } else if (prize.name.length > 32) {
                    SpreadPoint.Campaign.Controller.logError('The prize <strong>Title</strong> limit is 32', tab, identifier, true);
                    allValid = false;
                }
                
                if (!prize.description) {
                    SpreadPoint.Campaign.Controller.logError('Your prize does not have a <strong>Description</strong>', tab, identifier, true);
                    allValid = false;
                } else if (prize.description.length > 255) {
                    SpreadPoint.Campaign.Controller.logError('The prize <strong>Description</strong> limit is 255', tab, identifier, true);
                    allValid = false;
                }
                
                if (!prize.image) {
                    SpreadPoint.Campaign.Controller.logError('Your prize does not have an <strong>Image</strong>', tab, identifier, true);
                    allValid = false;
                }
                
                if (!prize.count) {
                    SpreadPoint.Campaign.Controller.logError('Please specify the prize <strong>Count</strong>', tab, identifier, true);
                    allValid = false;
                } else if (isNaN(parseFloat(prize.count)) || !isFinite(prize.count)) {
                    SpreadPoint.Campaign.Controller.logError('The prize <strong>Count</strong> must be a numeric value', tab, identifier, true);
                    allValid = false;
                } else if (prize.count <= 0) {
                    SpreadPoint.Campaign.Controller.logError('The prize <strong>Count</strong> must be greater than 0', tab, identifier, true);
                    allValid = false;
                }
            });
            
            return allValid;
        }
    };
    
    SpreadPoint.Campaign = {};
    SpreadPoint.Campaign.Controller = {
        // Create widgets and prizes controller
        init: function(widgetTypes, appliedWidgets, prizes, baseImagePath) {
            this.baseImagePath = baseImagePath;
            SpreadPoint.Widgets.Controller.init(widgetTypes, appliedWidgets, baseImagePath);
            SpreadPoint.Prize.Controller.init(prizes, baseImagePath);
            
            var $this = this;
            $('input[name="type"]').on('change', function(){
                $this.initializeSchedule();
            });
            
            this.adjustTextareas();
            this.initializeSchedule();
            this.attachSubmitEvent();
            this.loadStatusChangeActions();
            
            $('.row-prize-element').hide();
            $('.applied-widget-row').hide();
        },
        loadStatusChangeActions: function() {
            var self = this;
            $('.action-unpause').on('click', function(e) {
                if (!self.isEverythingValid()) {
                    return false;
                }
            });
        },
        /**
         * Adjust textarea lines to it's content
         */
        adjustTextareas: function(selector) {
            selector = selector || '.campaign-form-row textarea';
            
            $(selector).each(function(){
                var $this = $(this);
                if ($this.val().length > 0) {
                    $(this).height(this.scrollHeight);
                }
            });

            $(selector).on('keyup', function(){
                var $this = $(this);
                $this.height(0);
                $this.height(this.scrollHeight);
            });
        },
        /**
         * Initialize campaign type switch
         */
        initializeSchedule: function() {
            var isRepeating = this.get('type', ':checked') == 2;
            $('.custom-row-endtime').toggle(!isRepeating);
            $('.custom-row-cycleDuration').toggle(isRepeating);
            $('.custom-row-cyclesCount').toggle(isRepeating);
            $('.custom-row-retainPreviousEntrants').toggle(isRepeating);
        },
        // This is triggered before the form is submited
        attachSubmitEvent: function() {
            var $this = this;
            $('#campaign').submit(function(e) {
                e.preventDefault();
                
                // Update all widgets and prizes data
                SpreadPoint.Widgets.Controller.finalize();
                SpreadPoint.Prize.Controller.finalize();
                
                if ($this.isEverythingValid()) {
                    this.submit();
                }
            });
        },
        isEverythingValid: function() {
            return (this.isFormValid() && SpreadPoint.Widgets.Controller.isValid() && SpreadPoint.Prize.Controller.isValid());
        },
        // Check if all form data is valid before submit
        isFormValid: function() {
            var title = this.get('title');
            if (!title) {
                this.logError("You didn't provide a <strong>'Title'</strong> for your campign",".general-tab","title");
                return false;
            } else if (title.length > 32) {
                this.logError("The maximum <strong>'Title'</strong> length is <strong>32</strong>",".general-tab","title");
                return false;
            }
            
            var title = this.get('description');
            if (!title) {
                this.logError("You didn't provide a <strong>'Description'</strong> for your campign",".general-tab","description");
                return false;
            } else if (title.length > 500) {
                this.logError("The maximum <strong>'Description'</strong> length is <strong>500</strong>",".general-tab","description");
                return false;
            }
            
            if (!this.get('banner')) {
                this.logError("You didn't provide a <strong>'Banner'</strong> for your campign",".general-tab","banner");
                return false;
            }
            
            if (this.get('titleCss').length > 255) {
                this.logError("The maximum allowed <strong>'Title Css'</strong> length is <strong>255</strong> characters",".general-tab","titleCss");
                return false;
            }
            
            if (this.get('descriptionCss').length > 255) {
                this.logError("The maximum allowed <strong>'Description Css'</strong> length is <strong>255</strong> characters",".general-tab","descriptionCss");
                return false;
            }
            
            if (this.get('termsAndConditions').length > 255) {
                this.logError("There is a limit of <strong>50000</strong> characters to the <strong>'Terms & Conditions'</strong> field",".advanced-tab","termsAndConditions");
                return false;
            }
            
            var welcomeEmail = this.get('welcomeEmail');
            if (welcomeEmail.length > 20000) {
                this.logError("There is a limit of <strong>20000</strong> characters to the <strong>'Welcome Email'</strong> field",".advanced-tab","welcomeEmail");
                return false;
            }
            
            var winnerEmail = this.get('winnerEmail');
            if (winnerEmail && winnerEmail.length > 20000) {
                this.logError("There is a limit of <strong>20000</strong> characters to the <strong>Winner's Email</strong> field",".advanced-tab","welcomeEmail");
                return false;
            }
            
            var sendWelcomeEmail = $('.custom-row-sendWelcomeEmail').find('.switch-button-background.checked').length;
            if (sendWelcomeEmail && !welcomeEmail) {
                this.logError("Please provide a <strong>Welcome Email</strong> or uncheck <strong>'Send Welcome Email'</strong>",".advanced-tab","welcomeEmail");
                return false;
            }
            
            var notifyWinners = $('.custom-row-notifyWinners').find('.switch-button-background.checked').length;
            if (notifyWinners && !winnerEmail) {
                this.logError("Please provide a <strong>Winner's Email</strong> or uncheck the <strong>'Notify Winners'</strong> field",".advanced-tab","welcomeEmail");
                return false;
            }
            
            var startTime = this.get('startTime');
            if (!startTime) {
                this.logError("You didn't provide a valid <strong>'Start Time'</strong> for your campaign",".schedule-tab","startTime");
                return false;
            }
        
            var type = this.get('type', ':checked');
            if (type == 1) {
                var endTime = this.get('endTime');
                if (!endTime) {
                    this.logError("You didn't provide a valid <strong>'End Time'</strong> for your campaign",".schedule-tab","endTime");
                    return false;
                } else if (new Date(startTime) > new Date(endTime) ) {
                    this.logError("Your campaign can't end before it started. Please fix your <strong>'Start Time & End Time'</strong>",".schedule-tab","endTime");
                    return false;
                }
            } else if (type == 2) {
                var cyclesDuration = this.get('cycleDuration');
                if (!cyclesDuration) {
                    this.logError("You didn't provide a <strong>'Cycle Duration'</strong> for your campaign",".schedule-tab","cycleDuration");
                    return false;
                } else if (isNaN(parseFloat(cyclesDuration)) || !isFinite(cyclesDuration)) {
                    this.logError("The <strong>'Cycle Duration'</strong> must be a numeric value",".schedule-tab","cycleDuration");
                    return false;
                } else if (cyclesDuration <= 0) {
                    this.logError("The <strong>'Cycle Duration'</strong> must be greater than 0",".schedule-tab","cycleDuration");
                    return false;
                }
                
                var cyclesCount = this.get('cyclesCount');
                if (!cyclesCount) {
                    this.logError("You didn't provide a <strong>'Cycle Count'</strong> for your campaign",".schedule-tab","cyclesCount");
                    return false;
                } else if (isNaN(parseFloat(cyclesCount)) || !isFinite(cyclesCount)) {
                    this.logError("The <strong>'Cycle Count'</strong> must be a numeric value",".schedule-tab","cyclesCount");
                    return false;
                } else if (cyclesCount <= 0) {
                    this.logError("The <strong>'Cycle Count'</strong> must be greater than 0",".schedule-tab","cycleDuration");
                    return false;
                }
            } else {
                this.logError("Invalid <strong>Competition Type</strong>", ".schedule-tab", "custom-row-type", true);
                return false;
            }
            
            return true;
        },
        // get an element by name
        get: function(name, additionalSelectors) {
            additionalSelectors = additionalSelectors || '';
            return $('[name="'+name+'"]' + additionalSelectors).val();
        },
        // Log an error message
        logError: function(message, tab, element, skipNameTag) {
            message = '<li class="error"><i class="fa fa-times-circle"></i>' + message + '</li>';
            $('.global-messages').html(message);
            $(tab).trigger('click');
            
            if (element) {
                if (!skipNameTag) {
                    element = '[name="' + element + '"]';
                }

                $(element).addClass('has-errors');
            }
            
            window.scrollTo(0,0);
        }
    };
    
    SpreadPoint.Campaign.Winner = {
        init: function() {
            this.selection = [];
            this.possibilities = [];
            this.prize = null;
            this.count = 0;
            this.cycle = 0;
            
            this.loadEvents();
            this.loadActions();
        },
        loadEvents: function() {
            var self = this;
            
            $('.cycle-title').on('click', function() {
                $(this).siblings('.winners-content').slideToggle();
            });
            
            $('.winners-content').last().show()
                .siblings('.cycle-title').find('i').toggleClass('fa-angle-double-up fa-angle-double-down');
            
            $('.campaign-cycle').each(function() {
                var cycle = $(this);
                var cycleId = cycle.data('cycle');
                
                cycle.find('.winner-prize').on('click', function() {
                    var element = $(this);
                    self.cycle = cycleId;
                    self.prize = element.data('prize');
                    self.count = element.data('count');

                    $('.winner-prize').removeClass('selected');
                    element.addClass('selected');
                });

                cycle.find('.winner-entrant').on('click', function(){
                    var element = $(this);
                    if (!self.prize || cycleId != self.cycle) {
                        SpreadPoint.Campaign.Controller.logError('Please select a prize first','.winners', null, null);
                        return false;
                    }

                    if (!self.selection[cycleId]) {
                        self.selection[cycleId] = [];
                    }
                    
                    var winners = self.selection[cycleId][self.prize];
                    if (!winners) {
                        winners = [];
                    }

                    var entrant = element.data('entrant');
                    if (winners.indexOf(entrant) === -1 && winners.length < self.count) {
                        winners.push(entrant);

                        var left = self.count - winners.length;
                        var prizeElement = cycle.find('.prizes-list').find('[data-prize="' + self.prize +'"]');
                        prizeElement.find('.count').html(left);

                        var imageHtml = prizeElement.find('img').clone();
                        imageHtml.attr('width', 25).off('click');
                        element.find('.prizes-won').append(imageHtml);

                        imageHtml.on('click', function() {
                            var $this = $(this);

                            var key = $this.data('prize');
                            var value = $this.closest('.winner-entrant').data('entrant');
                            $this.remove();

                            var selection = self.selection[cycleId][key];
                            var index = selection.indexOf(value);
                            if (index > -1) {
                                selection.splice(index, 1);
                            }

                            self.selection[cycleId][key] = selection;

                            var prizeElement = cycle.find('.prizes-list').find('[data-prize="' + key +'"] .count');
                            var count = prizeElement.html();
                            prizeElement.html(++count);

                            return false;
                        });
                    }

                    self.selection[cycleId][self.prize] = winners;
                });
            });
        },
        loadActions: function() {
            var self = this;
            
            $('.campaign-cycle').each(function() {
                var cycle = $(this);
                var cycleId = cycle.data('cycle');
                
                cycle.find('.action-reset').on('click', function() {
                    self.selection[cycleId] = [];

                    cycle.find('.prizes-won img').remove();

                    cycle.find('.winner-prize .count').each(function() {
                        var $this = $(this);
                        var originalCount = $this.closest('.winner-prize').data('count');
                        $this.html(originalCount);
                    });
                });

                cycle.find('.action-random').on('click', function() {
                    if (!self.prize || cycleId != self.cycle) {
                        SpreadPoint.Campaign.Controller.logError('Please select a prize first','.winners', null, null);
                        return false;
                    }

                    self.randomize(cycle);
                });

                cycle.find('.action-random-all').on('click', function() {
                    cycle.find('.winner-prize').each(function() {
                        $(this).trigger('click');
                        self.randomize(cycle) ;
                    });
                });

                cycle.find('.action-confirm').on('click', function(){
                    if (self.validateSelection(cycle) && confirm("Are you sure? Winners cannot be changed.")) {
                        var data = JSON.stringify($.extend({}, self.selection[cycleId]));
                        $('.campaign-section-winners').prepend('<input type="hidden" name="winners-serialized" value=\'' + data + '\'/>');
                        $('.campaign-section-winners').prepend('<input type="hidden" name="cycle" value=\'' + cycleId + '\'/>');
                        $('#campaign').attr('action','/campaign/saveWinners/').submit();
                    }
                });
            });
        },
        randomize: function(cycle) {
            var self = this;
            var prize = this.prize;
            
            var cycleId = cycle.data('cycle');
            
            if (!this.possibilities[cycleId]) {
                this.possibilities[cycleId] = [];
            }
            
            if (this.possibilities[cycleId].length === 0) {
                cycle.find('.winner-entrant').each(function() {
                    var $this = $(this);
                    var entrant = $this.data('entrant');
                    var chances = $this.data('chance');
                    
                    for (var i = 0; i < chances; i++) {
                        self.possibilities[cycleId].push(entrant);
                    }
                });
            }
            
            var winners = [];            
            var count = cycle.find('.prizes-list').find('[data-prize="' + prize +'"]').data('count');
            var possibilities = this.possibilities[cycleId].slice();
            
            var allCycleWinners = []; 
            if (this.selection[cycleId]) {
                this.selection[cycleId].forEach(function(element) {
                    allCycleWinners = allCycleWinners.concat(element);
                });
            }
            
            possibilities = possibilities.filter(function(element) {
                return allCycleWinners.indexOf(element) === -1;
            });
            
            while (winners.length < count && possibilities.length > 0) {
                var winner = possibilities[Math.floor(Math.random() * possibilities.length)];
                if (winners.indexOf(winner) === -1) {
                    winners.push(winner);
                    possibilities = possibilities.filter(function(element) {
                        return element !== winner;
                    });
                }
            }
            
            winners.forEach(function(element) {
                cycle.find('.entrants-list').find('[data-entrant="' + element +'"]').trigger('click');
            });
        },
        validateSelection: function(cycle) {
            var self = this;
            var entrantsCount = cycle.find('.winner-entrant').length;
            var cycleId = cycle.data('cycle');
            
            if (!this.selection[cycleId] || this.selection[cycleId].length === 0) {
                SpreadPoint.Campaign.Controller.logError('Please select your winners.','.winners', null, null);
                return false;
            }
            
            var selectedEntrants = cycle.find('.prizes-won img').length;
            if (entrantsCount === selectedEntrants) {
                return true;
            }
            
            var success  = true;
            cycle.find(".winner-prize").each(function(){
                var element = $(this);
                var prize = element.data('prize');
                var count = element.data('count');
                
                if ( !(prize in self.selection[cycleId])
                        || (count !== self.selection[cycleId][prize].length 
                        && entrantsCount !== self.selection[cycleId][prize].length)
                ) {
                    var message = 'You didn\'t give out all your prizes for <strong>'
                        + element.data('name')
                        + '</strong>';
                    SpreadPoint.Campaign.Controller.logError(message, '.winners', null, null);
                    
                    element.trigger('click');
                    success = false;
                }

                return success;
            });
            
            return success;
        }
    };
    
})(jQuery);

//$.get("http://ipinfo.io", function(response) {
//    console.log(response);
//}, "jsonp");