
(function($){

    $('.table-list-form').each(function(){

        var table = $(this);
        var tbody = table.find('tbody');
        var counter = tbody.find('tr').length;

        table.find('.btn-add-item').click(function(){
            tbody.append(table.data('prototype').replace(/__name__/g, counter++));
        });

        tbody.on('click', '.btn-remove-item', function() {

            var t = $(this);

            if (!t.data('confirm-delete') || confirm('admin.delete_subforum')) {
                t.closest('tr').remove();
            }

        });

    }); // .table-list-form

    $('.wf-admin-moderate-btn').click(function(event){

        event.preventDefault();

        var t = $(this);
        var data = t.data();

        if (data.reviewType === 2) {
            data.reason = prompt('admin.report.why');
        }

        $.post(this.href, data, function(result){

            console.log(result);

            t.closest('tr').remove();
        }, 'json');

    }); // .wf-admin-moderate-btn

})(jQuery);
