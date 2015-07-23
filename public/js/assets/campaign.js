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
        }
    }

    SpreadPoint.Front.Facebook = {
        load: function() {
            this.initPageLike();
            this.initShare();
        },
        initPageLike: function() {
            FB.Event.subscribe('edge.create', function(url, element) {
                var widget = $(element).parent();
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
    }
})(jQuery);