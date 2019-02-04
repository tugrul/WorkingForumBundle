
(function($){

    $('.file-collection-container').each(function(){

        var container = $(this);

        var fileList = container.find('.file-collection-list > div');


        var counter = fileList.find('> .form-group').length;

        container.find('.btn-add-file').click(function(){

            var formField = $(fileList.data('prototype').replace(/__name__/g, counter++));

            fileList.append(formField);

            var row = formField.find('.custom-file')
                .wrap('<div class="form-row" />')
                .wrap('<div class="col" />').closest('.form-row');

            $('<button type="button" class="btn btn-danger btn-sm"><span class="fas fa-times"></span></button>')
                .appendTo(row).wrap('<div class="col-auto pt-1" />').click(function(){

                    $(this).closest('.form-group').remove();

            });



        });


    });

})(jQuery);

