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
            "Like a page on facebook widget",
            "<div class='applied-widget-row'>",
                "<input type='text' class='applied-widget-element' data-key='page' />",
            "</div>",
            "<span class='close-widget'>X</span>",
        "</div>"
    ].join('\n');
    
    SpreadPoint.Templates.Prize = [
        "<div class='row-prize prize-{{referenceId}}' data-id='{{referenceId}}'>",
            "<div class='row-prize-element'>",
                "<label>Name</label>",
                "<input type='text' class='prize-element prize-element-input' data-key='name' value='{{name}}' />",
            "</div>",
            "<div class='row-prize-element'>",
                "<label>Description</label>",
                "<textarea class='prize-element prize-element-text' data-key='description'>{{description}}</textarea>",
            "</div>",
            "<div class='row-prize-element'>",
                "<label>Image</label>",
                "<input type='text' class='prize-element prize-element-input file-upload' data-key='image' value='{{image}}' />",
            "</div>",
            "<div class='row-prize-element'>",
                "<label>Count</label>",
                "<input type='text' class='prize-element prize-element-input' data-key='count' value='{{count}}' />",
            "</div>",
            "<span class='close-prize'>X</span>",
        "</div>"
    ].join('\n');

})(jQuery);