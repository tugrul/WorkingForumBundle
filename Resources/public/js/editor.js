(function($){

    $('.markdown-editor-container').each(function(){

        var container = $(this);


        container.find('textarea').markdownEditor({
            preview: true,
            onPreview: function (content, callback) {
                $.post(Routing.generate('workingforum_post_preview'), {content: content}, function(result){
                    callback(result);
                }, 'html');
            }
        });

        var editor = ace.edit(container.find('.md-editor').get(0));

        container.find('.markdown-editor-emoji-list').insertAfter(container.find('.md-container .md-toolbar'))
            .show().find('img').click(function(){

            editor.insert($(this).data('key'));

        });

    });



})(jQuery);

