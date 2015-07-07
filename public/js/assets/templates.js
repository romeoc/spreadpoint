(function ($) {
    
    if (typeof SpreadPoint === 'undefined') {
        SpreadPoint = {};
    }
    
    SpreadPoint.Templates = {};
    SpreadPoint.Templates.Widgets = {};
    SpreadPoint.Templates.Widgets.Available = [
        "<ul class='available-widgets'>",
            "{{#each this}}",
                "<li class='widget-type widget-type-{{@key}}' data-type='{{@key}}'>",
                    "<i class='icon-type-{{@key}}'></i>{{this}}",
                "</li>",
            "{{/each}}",
        "</ul>"
    ].join('\n');
    
    SpreadPoint.Templates.Widgets.Type = {}
    SpreadPoint.Templates.Widgets.Type.EnterContest = [
        "<div class='applied-widget applied-widget-{{referenceId}} widget-type-entercontest' data-id='{{referenceId}}'>",
            "Widget Type Enter Contest",
            "<span class='close-widget'>X</span>",
        "</div>"
    ].join('\n');
    SpreadPoint.Templates.Widgets.Type.FacebookPageLike = [
        "<div class='applied-widget applied-widget-{{referenceId}} widget-type-facebooklikepage' data-id='{{referenceId}}'>",
            "Like a page on facebook widget {{hehe}}",
            "<div class='applied-widget-row'>",
                "<input type='text' class='applied-widget-element' data-key='page' />",
            "</div>",
            "<span class='close-widget'>X</span>",
        "</div>"
    ].join('\n');

})(jQuery);