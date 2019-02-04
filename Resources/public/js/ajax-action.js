
(function($){

    var hooks = {
        workingforum_moderate_post: {
            pre: function (body) {

                var reason = prompt(this.data('report-why'));

                if (reason === '' || reason.trim() === '') {
                    alert(this.data('invalid-reason'));
                    return false;
                }

                body.reason = reason;
            }
        },
        workingforum_vote_up: {
            post: function (resp) {

                if (!resp.success) {
                    return;
                }

                this.find('.fa-thumbs-up').removeClass('far').addClass('fas');

                this.closest('.btn-vote-container')
                    .find('.wf-post-vote-count').text(resp.voteCount);
            }
        },
        workingforum_vote_down: {
            post: function (resp) {

                if (!resp.success) {
                    return;
                }

                this.find('.fa-thumbs-up').removeClass('fas').addClass('far');


                this.closest('.btn-vote-container')
                    .find('.wf-post-vote-count').text(resp.voteCount);
            }
        }
    };

    $('.btn-ajax-action').each(function(){

        $(this).click(function(e){

            e.preventDefault();

            var t = $(this);
            var route = t.data('route');

            if (!route) {
                return;
            }

            if (t.is('button:disabled') || t.hasClass('disabled')) {
                return;
            }

            if (t.data('confirm') && !confirm(t.data('confirm'))) {
                return;
            }

            var body = t.data('body') || {};

            if (hooks[route] && hooks[route].pre) {
                if (hooks[route].pre.call(t, body) === false) {
                    return;
                }
            }

            if (t.is('button')) {
                t.prop('disabled', true);
            } else {
                t.addClass('disabled');
            }

            $.post(Routing.generate(route, t.data('route-params')), body, function(resp) {

                if (hooks[route] && hooks[route].post) {
                    if (hooks[route].post.call(t, resp) === false) {
                        return;
                    }
                }

                if (t.is('button')) {
                    t.prop('disabled', false);
                } else {
                    t.removeClass('disabled');
                }

                if (!resp.success) {
                    if (resp.message) {
                        alert(resp.message);
                    }
                    return;
                }

                if (resp.state) {

                    if (resp.state.location) {
                        window.location.href = resp.state.location;
                        return;
                    }

                    if (resp.state.reload) {
                        window.location.reload();
                        return;
                    }

                    if (resp.state.dispose) {
                        t.remove();
                        return;
                    }

                    if (resp.state.content) {
                        t.html(resp.state.content);
                    }

                    if (resp.state.data) {
                        t.data(resp.state.data);
                    }
                }

                if (resp.message) {
                    alert(resp.message);
                }


            }, 'json');

        });


    });

})(jQuery);

