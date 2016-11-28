(function ($) {
    "use strict";

    var socialLogin = {};
    if ( typeof edgtf !== 'undefined' ) {
        edgtf.modules.socialLogin = socialLogin;
    }

    socialLogin.edgtfUserLogin = edgtfUserLogin;
    socialLogin.edgtfUserRegister = edgtfUserRegister;
    socialLogin.edgtfUserLostPassword = edgtfUserLostPassword;
    socialLogin.edgtfInitLoginWidgetModal = edgtfInitLoginWidgetModal;
    socialLogin.edgtfUpdateUserProfile = edgtfUpdateUserProfile;

    $(document).ready(edgtfOnDocumentReady);
    $(window).load(edgtfOnWindowLoad);
    $(window).resize(edgtfOnWindowResize);
    $(window).scroll(edgtfOnWindowScroll);

    /**
     * All functions to be called on $(document).ready() should be in this function
     */
    function edgtfOnDocumentReady() {
        edgtfInitLoginWidgetModal();
        edgtfUserLogin();
        edgtfUserRegister();
        edgtfUserLostPassword();
        edgtfUpdateUserProfile();
    }

    /**
     * All functions to be called on $(window).load() should be in this function
     */
    function edgtfOnWindowLoad() {
    }

    /**
     * All functions to be called on $(window).resize() should be in this function
     */
    function edgtfOnWindowResize() {
    }

    /**
     * All functions to be called on $(window).scroll() should be in this function
     */
    function edgtfOnWindowScroll() {
    }

    /**
     * Initialize login widget modal
     */
    function edgtfInitLoginWidgetModal() {

        var modalOpener = $('.edgtf-login-opener'),
            modalHolder = $('.edgtf-login-register-holder');

        if (modalOpener) {
            var tabsHolder = $('.edgtf-login-register-content');

            //Init opening login modal
            modalOpener.click(function (e) {
                e.preventDefault();
                modalHolder.fadeIn(300);
                modalHolder.addClass('opened');
            });

            //Init closing login modal
            modalHolder.click(function (e) {
                if (modalHolder.hasClass('opened')) {
                    modalHolder.fadeOut(300);
                    modalHolder.removeClass('opened');
                }
            });
            $('.edgtf-login-register-content').click(function (e) {
                e.stopPropagation();
            });
            // on esc too
            $(window).on('keyup', function (e) {
                if (modalHolder.hasClass('opened') && e.keyCode == 27) {
                    modalHolder.fadeOut(300);
                    modalHolder.removeClass('opened');
                }
            });

            //Init tabs
            tabsHolder.tabs();
        }
    }

    /**
     * Login user via Ajax
     */
    function edgtfUserLogin() {
        $('.edgtf-login-form').on('submit', function (e) {
            e.preventDefault();
            var ajaxData = {
                action: 'edgtf_membership_login_user',
                security: $(this).find('#edgtf-login-security').val(),
                login_data: $(this).serialize()
            };
            $.ajax({
                type: 'POST',
                data: ajaxData,
                url: EdgefAjaxUrl,
                success: function (data) {
                    var response;
                    response = JSON.parse(data);

                    edgtfRenderAjaxResponseMessage(response);
                    if (response.status == 'success') {
                        window.location = response.redirect;
                    }
                }

            });
            return false;
        });
    }

    /**
     * Register New User via Ajax
     */
    function edgtfUserRegister() {

        $('.edgtf-register-form').on('submit', function (e) {

            e.preventDefault();
            var ajaxData = {
                action: 'edgtf_membership_register_user',
                security: $(this).find('#edgtf-register-security').val(),
                register_data: $(this).serialize()
            };

            $.ajax({
                type: 'POST',
                data: ajaxData,
                url: EdgefAjaxUrl,
                success: function (data) {
                    var response;
                    response = JSON.parse(data);

                    edgtfRenderAjaxResponseMessage(response);
                    if (response.status == 'success') {
                        window.location = response.redirect;
                    }
                }
            });

            return false;
        });
    }

    /**
     * Reset user password
     */
    function edgtfUserLostPassword() {

        var lostPassForm = $('.edgtf-reset-pass-form');
        lostPassForm.submit(function (e) {
            e.preventDefault();
            var data = {
                action: 'edgtf_membership_user_lost_password',
                user_login: lostPassForm.find('#user_reset_password_login').val()
            };
            $.ajax({
                type: 'POST',
                data: data,
                url: EdgefAjaxUrl,
                success: function (data) {
                    var response = JSON.parse(data);
                    edgtfRenderAjaxResponseMessage(response);
                    if (response.status == 'success') {
                        window.location = response.redirect;
                    }
                }
            });
        });
    }

    /**
     * Response notice for users
     * @param response
     */
    function edgtfRenderAjaxResponseMessage(response) {

        var responseHolder = $('.edgtf-membership-response-holder'), //response holder div
            responseTemplate = _.template($('.edgtf-membership-response-template').html()); //Locate template for info window and insert data from marker options (via underscore)

        var messageClass;
        if (response.status === 'success') {
            messageClass = 'edgtf-membership-message-succes';
        } else {
            messageClass = 'edgtf-membership-message-error';
        }

        var templateData = {
            messageClass: messageClass,
            message: response.message
        };

        var template = responseTemplate(templateData);
        responseHolder.html(template);
    }

    /**
     * Update User Profile
     */
    function edgtfUpdateUserProfile() {
        var updateForm = $('#edgtf-membership-update-profile-form');
        if ( updateForm.length ) {
            var btnText = updateForm.find('button'),
                updatingBtnText = btnText.data('updating-text'),
                updatedBtnText = btnText.data('updated-text');

            updateForm.on('submit', function (e) {
                e.preventDefault();
                var prevBtnText = btnText.html();
                btnText.html(updatingBtnText);

                var ajaxData = {
                    action: 'edgtf_membership_update_user_profile',
                    data: $(this).serialize()
                };

                $.ajax({
                    type: 'POST',
                    data: ajaxData,
                    url: EdgefAjaxUrl,
                    success: function (data) {
                        var response;
                        response = JSON.parse(data);

                        // append ajax response html
                        edgtfRenderAjaxResponseMessage(response);
                        if (response.status == 'success') {
                            btnText.html(updatedBtnText);
                            window.location = response.redirect;
                        } else {
                            btnText.html(prevBtnText);
                        }
                    }
                });
                return false;
            });
        }
    }

})(jQuery);