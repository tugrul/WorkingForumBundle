
;(function($){

    $(function () {

        $('.btn-move-thread').popover({
            container: 'body',
            html: true
        });


        $('.btn-quote-post').click(function() {
            var editor = ace.edit($('#wf-post-content').find('.md-editor').get(0));

            editor.insert('[quote=' + $(this).data('post-id') + ']');
        });

    });

})(jQuery);





