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
                    var title = widget.siblings('.widget-title');
                    title.find('.chances').hide();
                    title.find('.completed').show();
                }
            });
        }
    }

    SpreadPoint.Front.Facebook = {
        load: function() {
            this.initPageLike();
        },
        initPageLike: function() {
            FB.Event.subscribe('edge.create', function(url, element) {
                var widget = $(element).parent();
                SpreadPoint.Front.complete(widget);
            });
        }
    };
    
})(jQuery);