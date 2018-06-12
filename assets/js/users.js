



// Show delete post window
function viewRemove(post_id) {
    main_wrapper = $('.story_' + post_id);
    button_wrapper = main_wrapper.find('.remove-btn');
    SK_progressIconLoader(button_wrapper);
    
    $.get(
        '/request.php',

        {
            t: 'post',
            post_id: post_id,
            a: 'view_remove'
        },

        function(data) {
            if (data.status == 200) {
                $(document.body)
                    .append(data.html)
                    .css('overflow','hidden');

                $('.window-wrapper').css('margin-top', $(document).scrollTop() + ($('.window-background').height()/2-20) - $('.window-wrapper').height() + 'px');
            }
            
            SK_progressIconLoader(button_wrapper);
        }
    );
}

// Cancel remove
function cancelRemove(post_id) {
    // main_wrapper = $('.story_' + post_id);
    // SK_progressIconLoader(main_wrapper.find('.remove-btn'));
    SK_closeWindow();
}

// Delete post
function removePost(post_id) {
    SK_closeWindow();
    $.get('/request.php', {t: 'post', post_id: post_id, a: 'remove'}, function(data) {
        
        if (data.status == 200) {
            $('.story_' + post_id).slideUp(function(){
                $(this).remove();
            });
        }
    });
}

/* View comment remove */
function viewCommentRemove(comment_id) {
    main_wrapper = $('.comment_' + comment_id);
    button_wrapper = main_wrapper.find('.comment-remove-btn');
    
    SK_progressIconLoader(button_wrapper);
    
    $.get(
        '/request.php',

        {
            t: 'comment',
            comment_id: comment_id,
            a: 'view_remove'
        },

        function(data)
        {
            if (data.status == 200)
            {
                $(document.body)
                    .append(data.html)
                    .css('overflow','hidden');
                
                //if ($('#main').width() < 920)
                //{
                    $('.window-wrapper').css('margin-top', ($(document).scrollTop()+10)+'px');
                    $('.window-wrapper').css('margin-top', $(document).scrollTop() + ($('.window-background').height()/2-20) - $('.window-wrapper').height() + 'px');
                //}
            }
            
            SK_progressIconLoader(button_wrapper);
        }
    );
}

/* Cancel comment remove */
function cancelCommentRemove(comment_id) {
    // button = $('.comment_' + comment_id).find('.comment-remove-btn');
    // SK_progressIconLoader(button);
    SK_closeWindow();
}

/* Remove comment */
function removeComment(comment_id) {
    SK_closeWindow();

    $.get(
        '/request.php',

        {
            t: 'comment',
            comment_id: comment_id,
            a: 'remove'
        },

        function(data)
        {
            if (data.status == 200)
            {
                $('.comment_' + comment_id).slideUp(function()
                {
                    $(this).remove();
                });
            }
        }
    );
}



