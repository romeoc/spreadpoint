(function ($) {
    
    if (typeof SpreadPoint === 'undefined') {
        SpreadPoint = {};
    }
    
    SpreadPoint.Templates = {};
    
    SpreadPoint.Templates.Uploader = [
        "<div class='file-upload-wrapper'>",
            "<input type='file' class='uploader hide' name='{{name}}' />",
            "<button type='button' class='remove-file file-button'>Remove</button>",
            "<button type='button' class='select-file file-button'>Select</button>",
            "{{#if image}}",
                "<a href='{{image}}' alt='Enlarge Image' class='enlarge-image'>",
                    "<img src='{{image}}' alt='Banner' height='39' />",
                "</a>",
            "{{/if}}",
        "</div>"
    ].join('\n')
    
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
    
    SpreadPoint.Templates.Widgets.Type = {};
    SpreadPoint.Templates.Widgets.Type.EnterContest = [
        "<div class='applied-widget applied-widget-{{referenceId}} widget-type-entercontest' data-id='{{referenceId}}'>",
            "<div class='widget-header'>",
                "<span class='prize-header-name'>Enter Contest</span>",
                "<button type='button' class='close-widget'>Remove Widget</button>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Earning Value<i class='fa fa-question-circle'></i></label><input type='text' class='applied-widget-element' data-key='earningValue' value='{{earningValue}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');
    SpreadPoint.Templates.Widgets.Type.FacebookLike = [
        "<div class='applied-widget applied-widget-{{referenceId}} widget-type-facebooklikepage' data-id='{{referenceId}}'>",
            "<div class='widget-header'>",
                "<span class='prize-header-name'>Like a page on facebook widget</span>",
                "<button type='button' class='close-widget'>Remove Widget</button>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Page Link<i class='fa fa-question-circle'></i></label><input type='text' class='applied-widget-element' data-key='page' value='{{page}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",   
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Earning Value<i class='fa fa-question-circle'></i></label><input type='text' class='applied-widget-element' data-key='earningValue' value='{{earningValue}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');
    SpreadPoint.Templates.Widgets.Type.FacebookShare = [
        "<div class='applied-widget applied-widget-{{referenceId}} widget-type-facebookshare' data-id='{{referenceId}}'>",
            "<div class='widget-header'>",
                "<span class='prize-header-name'>Share a Link on facebook widget</span>",
                "<button type='button' class='close-widget'>Remove Widget</button>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Link<i class='fa fa-question-circle'></i></label><input type='text' class='applied-widget-element' data-key='link' value='{{link}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",   
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Earning Value<i class='fa fa-question-circle'></i></label><input type='text' class='applied-widget-element' data-key='earningValue' value='{{earningValue}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');
    SpreadPoint.Templates.Widgets.Type.TwitterTweet = [
        "<div class='applied-widget applied-widget-{{referenceId}} widget-type-twittertweet' data-id='{{referenceId}}'>",
            "<div class='widget-header'>",
                "<span class='prize-header-name'>Twitter Tweet</span>",
                "<button type='button' class='close-widget'>Remove Widget</button>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Message<i class='fa fa-question-circle'></i></label><input type='text' class='applied-widget-element' data-key='message' value='{{message}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",   
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Earning Value<i class='fa fa-question-circle'></i></label><input type='text' class='applied-widget-element' data-key='earningValue' value='{{earningValue}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');
    SpreadPoint.Templates.Widgets.Type.TwitterFollow = [
        "<div class='applied-widget applied-widget-{{referenceId}} widget-type-twitterfollow' data-id='{{referenceId}}'>",
            "<div class='widget-header'>",
                "<span class='prize-header-name'>Twitter Follow</span>",
                "<button type='button' class='close-widget'>Remove Widget</button>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>* User<i class='fa fa-question-circle'></i></label><input type='text' class='applied-widget-element' data-key='user' value='{{user}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",   
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Earning Value<i class='fa fa-question-circle'></i></label><input type='text' class='applied-widget-element' data-key='earningValue' value='{{earningValue}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');
    SpreadPoint.Templates.Widgets.Type.Reference = [
        "<div class='applied-widget applied-widget-{{referenceId}} widget-type-reference' data-id='{{referenceId}}'>",
            "<div class='widget-header'>",
                "<span class='prize-header-name'>Reference</span>",
                "<button type='button' class='close-widget'>Remove Widget</button>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Earning Value<i class='fa fa-question-circle'></i></label><input type='text' class='applied-widget-element' data-key='earningValue' value='{{earningValue}}' />",
                "</div>",
                "<div class='note'>",
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
                    "<label>* Name<i class='fa fa-question-circle'></i></label><input type='text' class='prize-element prize-element-input' data-key='name' value='{{name}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
            "<div class='row-prize-element campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Description<i class='fa fa-question-circle'></i></label>",
                    "<textarea class='prize-element prize-element-text' data-key='description'>{{description}}</textarea>",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
            "<div class='row-prize-element campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Image<i class='fa fa-question-circle'></i></label><input type='text' class='prize-element prize-element-input file-upload' data-key='image' value='{{image}}' data-src='{{src}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
            "<div class='row-prize-element campaign-form-row'>",
                "<div class='field'>",
                    "<label>* Count<i class='fa fa-question-circle'></i></label><input type='text' class='prize-element prize-element-input' data-key='count' value='{{count}}' />",
                "</div>",
                "<div class='note'>",
                    "<p class='comment'></p>",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');

})(jQuery);