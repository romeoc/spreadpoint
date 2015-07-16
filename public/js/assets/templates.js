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
            "<div class='widget-header'>",
                "<span class='prize-header-name'>Enter Contest</span>",
                "<button type='button' class='close-widget'>Remove Widget</button>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Earning Value</label><input type='text' class='applied-widget-element' data-key='earningValue' value='{{earningValue}}' />",
                "</div>",
                "<div class='note'>",
                    "<i class='fa fa-question-circle'></i>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');
    SpreadPoint.Templates.Widgets.Type.FacebookPageLike = [
        "<div class='applied-widget applied-widget-{{referenceId}} widget-type-facebooklikepage' data-id='{{referenceId}}'>",
            "<div class='widget-header'>",
                "<span class='prize-header-name'>Like a page on facebook widget</span>",
                "<button type='button' class='close-widget'>Remove Widget</button>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Page Link</label><input type='text' class='applied-widget-element' data-key='page' value='{{page}}' />",
                "</div>",
                "<div class='note'>",
                    "<i class='fa fa-question-circle'></i>",
                    "<p class='comment'></p>",
                "</div>",   
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Earning Value</label><input type='text' class='applied-widget-element' data-key='earningValue' value='{{earningValue}}' />",
                "</div>",
                "<div class='note'>",
                    "<i class='fa fa-question-circle'></i>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');
    
    SpreadPoint.Templates.Prize = [
        "<div class='row-prize prize-{{referenceId}}' data-id='{{referenceId}}'>",
            "<div class='row-prize-element campaign-form-row prize-header'>",
                "<span class='prize-header-name'>",
                    "{{#if name}}",
                        "{{name}}",
                    "{{else}}",
                        "New Prize",
                    "{{/if}}",
                "</span>",
                "<button type='button' class='close-prize'>Remove Prize</button>",
            "</div>",
            "<div class='row-prize-element campaign-form-row'>",
                "<div class='field'>",
                    "<label>Name</label><input type='text' class='prize-element prize-element-input' data-key='name' value='{{name}}' />",
                "</div>",
                "<div class='note'>",
                    "<i class='fa fa-question-circle'></i>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
            "<div class='row-prize-element campaign-form-row'>",
                "<div class='field'>",
                    "<label>Description</label>",
                    "<i class='fa fa-question-circle'></i>",
                    "<textarea class='prize-element prize-element-text' data-key='description'>{{description}}</textarea>",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
            "<div class='row-prize-element campaign-form-row'>",
                "<div class='field'>",
                    "<label>Image</label><input type='text' class='prize-element prize-element-input file-upload' data-key='image' value='{{image}}' />",
                "</div>",
                "<div class='note'>",
                    "<i class='fa fa-question-circle'></i>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
            "<div class='row-prize-element campaign-form-row'>",
                "<div class='field'>",
                    "<label>Count</label><input type='text' class='prize-element prize-element-input' data-key='count' value='{{count}}' />",
                "</div>",
                "<div class='note'>",
                    "<i class='fa fa-question-circle'></i>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');

})(jQuery);