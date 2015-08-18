(function ($) {
    if (typeof SpreadPoint === 'undefined') {
        SpreadPoint = {};
    }


    SpreadPoint.Front = {
        complete: function(widget) {
            
            var widgetId = widget.data('id');
            var url = '/campaign/complete/' + widgetId;
            
            $.ajax({
                url: url
            }).done(function(data) {
                if (data.status == true) {
                    var title = widget.prev('h3');
                    title.find('.chances').hide();
                    title.find('.completed').show();
                }
            });
        },
        error: function(message) {
            message = '<li class="error"><i class="fa fa-times-circle"></i>' + message + '</li>';
            $('.global-messages').html(message);
        }
    }

    SpreadPoint.Front.Facebook = {
        load: function() {
            this.initPageLike();
            this.initShare();
        },
        initPageLike: function() {
            $('.facebook-visit').on('click', function(){
                var widget = $(this).parent();
                SpreadPoint.Front.complete(widget);
            });
        },
        initShare: function() {
            $('.facebook-share-widget').on('click', function(e){
                e.preventDefault();
                var widget = $(this).parent();
                
                FB.ui({
                    method: 'share',
                    href: $(this).attr('href')
                }, function(response) {
                    if (response && !response.error_code) {
                        SpreadPoint.Front.complete(widget);
                    }
                });
            });
        }
    };
    
    SpreadPoint.Front.Twitter = {
        lastClicked: false,
        load: function() {
            this.initTweet();
            this.initFollow();
        },
        initTweet: function() {
            twttr.events.bind('tweet', function (event) {
                var widget = $(event.target).parent();
                SpreadPoint.Front.complete(widget);
            });
        },
        initFollow: function() {
            twttr.events.bind('follow', function (event) {
                var widget = $(event.target).parent();
                SpreadPoint.Front.complete(widget);
            });
        }
    };
    
    SpreadPoint.Front.EnterContest = {
        init: function() {
            var self = this;
            $('#enter-contest').submit(function(e){
                e.preventDefault();
                
                if (self.valid()) {
                    this.submit();
                }
            });
        },
        valid: function() {
            var ageRequirement  = $('#ageRequirement');
            if (ageRequirement.length !== 0 && !ageRequirement.is(':checked')) {
                SpreadPoint.Front.error('You don\'t meet the age requirement for this contest.');
                return false;
            }
            
            var terms  = $('#terms');
            if (terms.length !== 0 && !terms.is(':checked')) {
                SpreadPoint.Front.error('You must agree to the terms & conditions.');
                return false;
            }
            
            return true;
        }
    };
})(jQuery);