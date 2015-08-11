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
            $('.jqui-tabs').tabs().show();
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
                $this.siblings('.switch-button-background').attr('title', title).addClass('click-note')
                    .find('.switch-button-button').attr('title', title).addClass('click-note');
            });
        },
        initializeListingCampaigns: function() {
            $('.campaign .title').hide();
            $('.campaign').on('mouseenter', function(){
                $(this).find('.title').slideToggle();
            }).on('mouseleave', function(){
                $(this).find('.title').slideToggle();
            });
        },
        initializeAccordions: function() {
            $('.jqui-accordion').accordion();
        },
        initializeDateTimePickers: function() {
            $('.jq-datetimepicker').datetimepicker();
        },
        initializeHints: function() {
            $('.focus-note').qtip({
                show: 'focus',
                hide: 'blur',
                position: {
                    my: 'center left',
                    at: 'center right',
                },
                style: { classes: 'qtip-blue qtip-rounded qtip-shadow' }
            });
            
            $('.click-note').qtip({
                show: 'click',
                hide: { 
                    distance: 50
                },
                position: {
                    my: 'top center',
                    at: 'bottom center',
                },
                style: { classes: 'qtip-blue qtip-rounded qtip-shadow' }
            });
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
            
            this.container = $(container).addClass('popup-container');
            this.action = $(action);
            this.close = $(close);
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
        },
    };
    
    SpreadPoint.Checkout = {
        paypalAction: false,
        plan: null,
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

                var fomatedCardNumber = field.val().split(" ").join("");
                if (fomatedCardNumber.length > 0) {
                  fomatedCardNumber = fomatedCardNumber.match(new RegExp('.{1,4}', 'g')).join(" ");
                }
                
                field.val(fomatedCardNumber);
            });
            
            $('.pay-pal-action').on('click', function() {
                $this.paypalAction = true;
                $('.checkout-submit').trigger('click');
            });
            
            $('#Checkout').submit(function(e) {
                e.preventDefault();
                
                var payPalForm = $(this);
                var action = payPalForm.attr('action');
                
                if ($this.paypalAction) {
                    payPalForm.attr('action',action.replace('submit','paypalStart'));
                } else {
                    payPalForm.attr('action',action.replace('paypalStart','submit'));
                }
            
                if ($this.valid()) {
                    this.submit();
                }

                $this.paypalAction = false;
            });
        },
        initUpgradeSection: function() {
            SpreadPoint.PopUp.create('.upgrade-payment-form', '.upgrade-plan-action');
        },
        validatePlan: function() {
            var plan = $('[name="plan"]:checked').val();
            if (!plan) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please select a package</li>');
                return false;
            } else if (this.plan !== null && plan == this.plan) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>You are already subscribed to this plan.</li>');
                return false;
            }
            
            return true;
        },
        valid: function() {

            if (!this.validatePlan()) {
                return false;
            }
            
            if (this.paypalAction) {
                return true;
            }
            
            if (!$('.custom-row-fullname input').val()) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please enter your name as it apears on your credit card</li>');
                return false;
            }
            
            if (!$('.custom-row-card_number input').val()) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please enter your credit card number</li>');
                return false;
            }
            
            if (!$('.custom-row-expiry_date input').val()) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Please enter your card\'s expiration date</li>');
                return false;
            }
            
            if (!$('.custom-row-cvc input').val()) {
                $('.global-messages').html('<li class="error"><i class="fa fa-times-circle"></i>Invalid CVC number</li>');
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
        SpreadPoint.Templates.Widgets.Type.FacebookLike,
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
                            SpreadPoint.Campaign.Controller.logError('Your facebook widget must have a <strong>Page Link</strong>', tab, identifier, true);
                            allValid = false;
                        } else if (!widget.page.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/)) {
                            SpreadPoint.Campaign.Controller.logError('Your facebook widget has an invalid <strong>Page Link</strong>', tab, identifier, true);
                            allValid = false;
                        } else if (widget.page.indexOf("facebook.com") === -1) {
                            SpreadPoint.Campaign.Controller.logError('Invalid <strong>facebook</strong> link', tab, identifier, true);
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
                
                if ($this.isFormValid() && SpreadPoint.Widgets.Controller.isValid() && SpreadPoint.Prize.Controller.isValid()) {

                    this.submit();
                }
            });
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
            if (!winnerEmail){
                this.logError("The <strong>Winner's Email</strong> is a required field.",".advanced-tab","winnerEmail");
                return false;
            } else if (winnerEmail.length > 20000) {
                this.logError("There is a limit of <strong>20000</strong> characters to the <strong>Winner's Email</strong> field",".advanced-tab","welcomeEmail");
                return false;
            }
            
            var sendWelcomeEmail = $('.custom-row-sendWelcomeEmail').find('.switch-button-background.checked').length;
            if (sendWelcomeEmail && !welcomeEmail) {
                this.logError("Please provide a <strong>Welcome Email</strong> or uncheck <strong>'Send Welcome Email'</strong>",".advanced-tab","welcomeEmail");
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
            
            this.loadEvents();
            this.loadActions();
        },
        loadEvents: function() {
            var self = this;
            
            $('.winner-prize').on('click', function() {
                var element = $(this);
                self.prize = element.data('prize');
                self.count = element.data('count');
                
                element.addClass('selected')
                    .siblings().removeClass('selected');
            });
            
            $('.winner-entrant').on('click', function(){
                var element = $(this);
                if (!self.prize) {
                    SpreadPoint.Campaign.Controller.logError('Please select a prize first','.winners', null, null);
                    return false;
                }
                
                var winners = self.selection[self.prize];
                if (!winners) {
                    winners = [];
                }
                
                var entrant = element.data('entrant');
                if (winners.indexOf(entrant) === -1 && winners.length < self.count) {
                    winners.push(entrant);
                    
                    var left = self.count - winners.length;
                    var prizeElement = $('.prizes-list').find('[data-prize="' + self.prize +'"]');
                    prizeElement.find('.count').html(left);
                    
                    var imageHtml = prizeElement.find('img').clone();
                    imageHtml.attr('width', 25).off('click');
                    element.find('.prizes-won').append(imageHtml);
                    
                    imageHtml.on('click', function() {
                        var $this = $(this);
                        
                        var key = $this.data('prize');
                        var value = $this.closest('.winner-entrant').data('entrant');
                        $this.remove();
                        
                        var selection = self.selection[key];
                        var index = selection.indexOf(value);
                        if (index > -1) {
                            selection.splice(index, 1);
                        }
                        
                        self.selection[key] = selection;
                        
                        var prizeElement = $('.prizes-list').find('[data-prize="' + key +'"] .count');
                        var count = prizeElement.html();
                        prizeElement.html(++count);
                        
                        return false;
                    });
                }
                
                self.selection[self.prize] = winners;
            });
        },
        loadActions: function() {
            var self = this;
            $('.action-reset').on('click', function() {
                self.selection = [];
                
                $('.prizes-won img').remove();
                
                $('.winner-prize .count').each(function() {
                    var $this = $(this);
                    var originalCount = $this.closest('.winner-prize').data('count');
                    $this.html(originalCount);
                });
            });
            
            $('.action-random').on('click', function() {
                if (!self.prize) {
                    SpreadPoint.Campaign.Controller.logError('Please select a prize first','.winners', null, null);
                    return false;
                }
                
                self.randomize();
            });
            
            $('.action-random-all').on('click', function() {
                $('.winner-prize').each(function() {
                    $(this).trigger('click');
                    self.randomize() ;
                });
            });
            
            $('.action-confirm').on('click', function(){
                if (self.validateSelection() && confirm("Are you sure? Winners cannot be changed.")) {
                    var data = JSON.stringify($.extend({}, self.selection));
                    $('.campaign-section-winners').prepend('<input type="hidden" name="winners-serialized" value=\'' + data + '\'/>');
                    $('#campaign').attr('action','/campaign/saveWinners/').submit();
                }
            });
        },
        randomize: function() {
            var self = this;
            var prize = this.prize;
            
            if (this.possibilities.length === 0) {
                $('.winner-entrant').each(function() {
                    var $this = $(this);
                    var entrant = $this.data('entrant');
                    var chances = $this.data('chance');
                    
                    for (var i = 0; i < chances; i++) {
                        self.possibilities.push(entrant);
                    }
                });
            }
            
            var winners = [];
            var count = $('.prizes-list').find('[data-prize="' + prize +'"]').data('count');
            var possibilities = this.possibilities.slice();
                
            while (winners.length < count && possibilities.length > 0) {
                var winner = this.possibilities[Math.floor(Math.random() * this.possibilities.length)];
                if (winners.indexOf(winner) === -1) {
                    winners.push(winner);
                    possibilities = possibilities.filter(function(element) {
                        return element !== winner;
                    });
                }
            }
            
            winners.forEach(function(element) {
                $('.entrants-list').find('[data-entrant="' + element +'"]').trigger('click');
            });
        },
        validateSelection: function() {
            var self = this;
            var entrantsCount = $('.winner-entrant').length;
            
            if (this.selection.length === 0) {
                SpreadPoint.Campaign.Controller.logError('Please select your winners.','.winners', null, null);
                return false;
            }
            
            var success  = true;
            $(".winner-prize").each(function(){
                var element = $(this);
                var prize = element.data('prize');
                var count = element.data('count');
                
                if ( !(prize in self.selection)
                        || (count !== self.selection[prize].length 
                        && entrantsCount !== self.selection[prize].length)
                ) {
                    var message = 'You didn\'t give out all your prizes for <strong>'
                        + element.data('name')
                        + '</strong>';
                    SpreadPoint.Campaign.Controller.logError(message,'.winners', null, null);
                    
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