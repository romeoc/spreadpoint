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
                    "<label>Widget Title *</label><input type='text' class='focus-note applied-widget-element' data-key='title' value='{{#if title}}{{title}}{{else}}Enter Our Contest{{/if}}' title='Name the action you want the users to take. This will be visible on the campaign page as well.' />",
                "</div>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Earning Value *</label><input type='text' class='focus-note applied-widget-element' data-key='earningValue' value='{{earningValue}}' title='The number of chances participants obtain once they complete the action.' />",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');
    SpreadPoint.Templates.Widgets.Type.VisitPage = [
        "<div class='applied-widget applied-widget-{{referenceId}} widget-type-visitpage' data-id='{{referenceId}}'>",
            "<div class='widget-header'>",
                "<span class='prize-header-name'>Visit a Web Page</span>",
                "<button type='button' class='close-widget'>Remove Widget</button>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Page Link *</label><input type='text' class='focus-note applied-widget-element' data-key='page' value='{{page}}' title='Introduce the link you want participants to access.' />",
                "</div>",   
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Widget Title *</label><input type='text' class='focus-note applied-widget-element' data-key='title' value='{{#if title}}{{title}}{{else}}Visit our Website{{/if}}' title='Name the action you want the users to take. This will be visible on the campaign page as well.' />",
                "</div>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Earning Value *</label><input type='text' class='focus-note applied-widget-element' data-key='earningValue' value='{{earningValue}}' title='The number of chances participants obtain once they complete the action.' />",
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
                    "<label>Link *</label><input type='text' class='focus-note applied-widget-element' data-key='link' value='{{link}}' title='Introduce the link you want participants to share.' />",
                "</div>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Widget Title *</label><input type='text' class='focus-note applied-widget-element' data-key='title' value='{{#if title}}{{title}}{{else}}Share a Link on Facebook{{/if}}' title='Name the action you want the users to take. This will be visible on the campaign page as well.' />",
                "</div>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Earning Value *</label><input type='text' class='focus-note applied-widget-element' data-key='earningValue' value='{{earningValue}}' title='The number of chances participants obtain once they complete the action.' />",
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
                    "<label>Message *</label><input type='text' class='focus-note applied-widget-element' data-key='message' value='{{message}}' title='What would you want the tweet to sound like? Introduce your default tweet here.' />",
                "</div>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Widget Title *</label><input type='text' class='focus-note applied-widget-element' data-key='title' value='{{#if title}}{{title}}{{else}}Tweet About Us{{/if}}' title='Name the action you want the users to take. This will be visible on the campaign page as well.' />",
                "</div>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Earning Value *</label><input type='text' class='focus-note applied-widget-element' data-key='earningValue' value='{{earningValue}}' title='The number of chances participants obtain once they complete the action.' />",
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
                    "<label>User *</label><input type='text' class='focus-note applied-widget-element' data-key='user' value='{{user}}' title='Your twitter username.' />",
                "</div>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Widget Title *</label><input type='text' class='focus-note applied-widget-element' data-key='title' value='{{#if title}}{{title}}{{else}}Follow Us on Twitter{{/if}}' title='Name the action you want the users to take. This will be visible on the campaign page as well.' />",
                "</div>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Earning Value *</label><input type='text' class='focus-note applied-widget-element' data-key='earningValue' value='{{earningValue}}' title='The number of chances participants obtain once they complete the action.' />",
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
                    "<label>Widget Title *</label><input type='text' class='focus-note applied-widget-element' data-key='title' value='{{#if title}}{{title}}{{else}}Tell Your Friends{{/if}}' title='Name the action you want the users to take. This will be visible on the campaign page as well.' />",
                "</div>",
            "</div>",
            "<div class='applied-widget-row campaign-form-row'>",
                "<div class='field'>",
                    "<label>Earning Value *</label><input type='text' class='focus-note applied-widget-element' data-key='earningValue' value='{{earningValue}}' title='The number of chances participants obtain once they complete the action.' />",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');
    
    SpreadPoint.Templates.Prize = [
        "<div class='row-prize prize-{{referenceId}}' data-id='{{referenceId}}'>",
            "<div class='prize-header'>",
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
                    "<label>Name *</label><input type='text' class='focus-note prize-element prize-element-input' data-key='name' value='{{name}}' title='Introduce the name of the prize you wish to award. We advise starting with the most valuable prize first.' />",
                "</div>",
            "</div>",
            "<div class='row-prize-element campaign-form-row'>",
                "<div class='field'>",
                    "<label>Description *</label>",
                    "<textarea class='focus-note prize-element prize-element-text' data-key='description' title='A description of your prize in 2-3 sentences.'>{{description}}</textarea>",
                "</div>",
            "</div>",
            "<div class='row-prize-element campaign-form-row'>",
                "<div class='field'>",
                    "<label>Image *</label><input type='text' class='focus-note prize-element prize-element-input file-upload' data-key='image' value='{{image}}' data-src='{{src}}' title='Choose a representative picture of your prize.' />",
                "</div>",
            "</div>",
            "<div class='row-prize-element campaign-form-row'>",
                "<div class='field'>",
                    "<label>Count *</label><input type='text' class='focus-note prize-element prize-element-input' data-key='count' value='{{count}}' title='Introduce the number of prizes you would like to award. If your campaign is repeating and has multiple cycles, the amount placed here relates to the amount of prizes for every cycle or period.' />",
                "</div>",
            "</div>",
        "</div>"
    ].join('\n');

})(jQuery);