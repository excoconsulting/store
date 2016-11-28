<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Facebook advertisement.
 * 
 * This advert will be shown when the user activates the
 * new plugin. It is a little popup with little text and
 * show the facebook like button that will redirect to
 * MatthiasWeb' Facebook site.
 */
remove_action('admin_footer', 'matthiasweb_advert');
add_action('admin_footer', 'matthiasweb_advert');
if (!function_exists('matthiasweb_advert')) {
    function matthiasweb_advert() {
        $screen = get_current_screen();
        if ($screen->id != "plugins") {
            return;
        }
        
        $option = get_option('matthiasweb_advert_hide', false);
        if ($option == true) {
            return;
        }
        
        if (isset($_GET["matthiasweb-advert-off"])) {
            update_option('matthiasweb_advert_hide', true);
            return;
        }
        
        ?>
<style>
    #matthiasweb-advert-overlay {
        position: fixed;
        top: 0px;
        left: 0px;
        right: 0px;
        bottom: 0px;
        background: rgba(0,0,0,0.5);
        z-index: 99999;
    }
    
    #matthiasweb-advert {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 500px;
        height: 300px;
        margin: -165px 0px 0px -265px;
        background: white;
        text-align: center;
        border-top: 10px solid #4C67A1;
        box-shadow: 0px 2px 1px 1px #B1B1B1, 0px 2px 5px 1px rgba(0, 0, 0, 0.42);
        border-radius: 0px 0px 5px 5px;
        color: #999;
        padding: 15px;
    }
    
    #matthiasweb-advert h1,
    #matthiasweb-advert h2 {
        color: #4C67A1;
    }
</style>
<div id="matthiasweb-advert-overlay">
    <div id="matthiasweb-advert">
        <h1><i class="fa fa-facebook-official"></i></h1>
        <h2>MatthiasWeb now on Facebook!</h2>
        <p>
            First, I should say a huge <strong>thank you</strong> for buying this
            plugin.
            You are on Facebook? Let me say it so: Plugins
            like this need a wide audience to become more fame.
        </p>
        <p>
            If you <i class="fa fa-heart"></i> the currently activated plugin... let this
            know your facebook friends.
        </p>
        <p>
            <a href="https://www.facebook.com/MatthiasWeb-165939027104880/" target="_blank" class="button button-primary">
                <i class="fa fa-facebook-official"></i> MatthiasWeb on Facebook
            </a><br /><br />
            <a href="?matthiasweb-advert-off" class="button">
                <i class="fa fa-times"></i> Dismiss this notice
            </a>
        </p>
    </div>
</div>
<?php
    }
}

if (!function_exists('matthiasweb_advert_activation')) {
    function matthiasweb_advert_activation() {
        delete_option('matthiasweb_advert_hide');
    }
}
?>