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
            this.initializeWikiHints();
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
            });
        },
        initializeWikiHints: function() {
            $('.custom-form-row i').on('click', function(){
                $(this).closest('.custom-form-row').find('.comment').toggle();
            });
        },
        initializeListingCampaigns: function() {
            $('.campaign .title').hide();
            $('.campaign').on('mouseenter', function(){
                $(this).find('.title').slideToggle();
            }).on('mouseleave', function(){
                $(this).find('.title').slideToggle();
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
        init: function (selector, name) {
            selector = selector || '.file-upload';
            $(selector).not('.file-upload-wrapper .file-upload').each(function(){
                var $this = $(this);
                $this.wrap("<div class='file-upload-wrapper'></div>")
                    .attr('readonly',true)
                    .after(
                        $('<input class="uploader" name="' + name + '"/>').attr('type', 'file'),
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
        init: function(prizes) {
            this.prizes = prizes;
            
            if (this.prizes.length === 0) {
                // Add initial prize
                this.add();
            } else {
                // Load existing prizes
                this.loadPrizes();
                this.reloadListeners();
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
            this.prizes.forEach(function(prize){
                if (prize) {
                    var source = SpreadPoint.Templates.Prize;
                    var template = Handlebars.compile(source);
                    var html = template(prize);

                    $('.prizes').append(html);
                    
                    var selectorId = prize.referenceId;
                    var imageFieldSelector = ".prize-" + selectorId + " .file-upload";
                    var imageFieldName = 'prize-' + selectorId;

                    SpreadPoint.Uploader.init(imageFieldSelector, imageFieldName);
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
        reloadListeners: function() {
            var $this = this;
            
            $('.close-prize').on('click', function(){
                var prizeId = $(this).closest('.row-prize').data('id');
                $this.remove(prizeId);
            });
            
            var selectorId = this.prizes.length;
            var imageFieldSelector = ".prize-" + selectorId + " .file-upload";
            var imageFieldName = 'prize-' + selectorId;
            
            SpreadPoint.Uploader.init(imageFieldSelector, imageFieldName);
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
    }
    
    SpreadPoint.Campaign = {}
    SpreadPoint.Campaign.Controller = {
        // Create widgets and prizes controller
        init: function(widgetTypes, appliedWidgets, prizes) {
            SpreadPoint.Widgets.Controller.init(widgetTypes, appliedWidgets);
            SpreadPoint.Prize.Controller.init(prizes);
            
            var $this = this;
            $('input[name="type"]').on('change', function(){
                $this.initializeSchedule();
            });
            
            this.initializeSchedule();
            this.attachSubmitEvent();
        },
        /**
         * Initialize campaign type switch
         */
        initializeSchedule: function() {
            var isRepeating = this.get('type', ':checked') == 2;
            $('.custom-row-endtime').toggle(!isRepeating);
            $('.custom-row-cycleDuration').toggle(isRepeating);
            $('.custom-row-cyclesCount').toggle(isRepeating);
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
            var valid = true;
            
            var title = this.get('title');
            if (!title) {
                this.logError("You didn't provide a <strong>'Title'</strong> for your campign",".general-tab","title");
                valid = false;
            } else if (title.length > 32) {
                this.logError("The maximum <strong>'Title'</strong> length is <strong>32</strong>",".general-tab","title");
                valid = false;
            }
            
            var title = this.get('description');
            if (!title) {
                this.logError("You didn't provide a <strong>'Description'</strong> for your campign",".general-tab","description");
                valid = false;
            } else if (title.length > 500) {
                this.logError("The maximum <strong>'Description'</strong> length is <strong>500</strong>",".general-tab","description");
                valid = false;
            }
            
            if (!this.get('banner')) {
                this.logError("You didn't provide a <strong>'Banner'</strong> for your campign",".general-tab","banner");
                valid = false;
            }
            
            if (this.get('titleCss').length > 255) {
                this.logError("The maximum allowed <strong>'Title Css'</strong> length is <strong>255</strong> characters",".general-tab","titleCss");
                valid = false;
            }
            
            if (this.get('descriptionCss').length > 255) {
                this.logError("The maximum allowed <strong>'Description Css'</strong> length is <strong>255</strong> characters",".general-tab","descriptionCss");
                valid = false;
            }
            
            if (this.get('termsAndConditions').length > 255) {
                this.logError("There is a limit of <strong>50000</strong> characters to the <strong>'Terms & Conditions'</strong> field",".advanced-tab","termsAndConditions");
                valid = false;
            }
            
            var welcomeEmail = this.get('welcomeEmail');
            if (welcomeEmail.length > 20000) {
                this.logError("There is a limit of <strong>20000</strong> characters to the <strong>'Welcome Email'</strong> field",".advanced-tab","welcomeEmail");
                valid = false;
            }
            
            var sendWelcomeEmail = $('.custom-row-sendWelcomeEmail').find('.switch-button-background.checked').length;
            if (sendWelcomeEmail && !welcomeEmail) {
                this.logError("Please provide a <strong>Welcome Email</strong> or uncheck <strong>'Send Welcome Email'</strong>",".advanced-tab","welcomeEmail");
                valid = false;
            }
            
            var startTime = this.get('startTime');
            if (!startTime) {
                this.logError("You didn't provide a valid <strong>'Start Time'</strong> for your campaign",".schedule-tab","startTime");
                valid = false;
            }
        
            var type = this.get('type', ':checked');
            if (type == 1) {
                var endTime = this.get('endTime');
                if (!endTime) {
                    this.logError("You didn't provide a valid <strong>'End Time'</strong> for your campaign",".schedule-tab","endTime");
                    valid = false;
                } else if (new Date(startTime) > new Date(endTime) ) {
                    this.logError("Your campaign can't end before it started. Please fix your <strong>'Start Time & End Time'</strong>",".schedule-tab","endTime");
                    valid = false;
                }
            } else if (type == 2) {
                var cyclesDuration = this.get('cycleDuration');
                if (!cyclesDuration) {
                    this.logError("You didn't provide a <strong>'Cycle Duration'</strong> for your campaign",".schedule-tab","cycleDuration");
                    valid = false;
                } else if (isNaN(parseFloat(cyclesDuration)) || !isFinite(cyclesDuration)) {
                    this.logError("The <strong>'Cycle Duration'</strong> must be a numeric value",".schedule-tab","cycleDuration");
                    valid = false;
                } else if (cyclesDuration <= 0) {
                    this.logError("The <strong>'Cycle Duration'</strong> must be greater than 0",".schedule-tab","cycleDuration");
                    valid = false;
                }
                
                var cyclesCount = this.get('cyclesCount');
                if (!cyclesCount) {
                    this.logError("You didn't provide a <strong>'Cycle Count'</strong> for your campaign",".schedule-tab","cyclesCount");
                    valid = false;
                } else if (isNaN(parseFloat(cyclesCount)) || !isFinite(cyclesCount)) {
                    this.logError("The <strong>'Cycle Count'</strong> must be a numeric value",".schedule-tab","cyclesCount");
                    valid = false;
                } else if (cyclesCount <= 0) {
                    this.logError("The <strong>'Cycle Count'</strong> must be greater than 0",".schedule-tab","cycleDuration");
                    valid = false;
                }
            } else {
                this.logError("Invalid <strong>Competition Type</strong>", ".schedule-tab", "custom-row-type", true);
                valid = false;
            }
            
            return valid;
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
        },
    }
})(jQuery);

//$.get("http://ipinfo.io", function(response) {
//    console.log(response);
//}, "jsonp");