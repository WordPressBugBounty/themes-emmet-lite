<?php
/**
 * Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their store.
 *
 * @package WordPress
 * @subpackage Emmet
 * @since Emmet 1.1.0
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * MP_Emmet_Admin_Setup_Wizard class
 */
class MP_Emmet_Admin_Setup_Wizard {

    /** @var string Currenct Step */
    private $step = '';

    /** @var array Steps for the setup wizard */
    private $steps = array();

    /**
     * Hook in tabs.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'mp_emmet_admin_menus'));
    }

    /**
     * Add admin menus/screens.
     */
    public function mp_emmet_admin_menus() {
        add_theme_page(__('Theme Wizard', 'emmet-lite'), __('Theme Wizard', 'emmet-lite'), 'edit_theme_options', 'theme-setup', array($this, 'mp_emmet_setup_wizard'));
    }

    /**
     * Show the setup wizard
     */
    public function mp_emmet_setup_wizard() {
        $this->steps = array(
            'introduction' => array(
                'name' => __('Start', 'emmet-lite'),
                'view' => array($this, 'mp_emmet_setup_introduction'),
                'handler' => ''
            ),
            'section' => array(
                'name' => __('Front Page Setup', 'emmet-lite'),
                'view' => array($this, 'mp_emmet_setup_section'),
                'handler' => ''
            ),
            'customizer' => array(
                'name' => __('Customizer', 'emmet-lite'),
                'view' => array($this, 'mp_emmet_setup_customizer'),
                'handler' => ''
            ),
            'plugins' => array(
                'name' => __('Plugins', 'emmet-lite'),
                'view' => array($this, 'mp_emmet_setup_plugins'),
                'handler' => ''
            ),
            'install_plugins' => array(
                'name' => __('Install Plugins', 'emmet-lite'),
                'view' => array($this, 'mp_emmet_setup_install_plugins'),
                'handler' => ''
            ),
            'ready' => array(
                'name' => __('Ready', 'emmet-lite'),
                'view' => array($this, 'mp_emmet_setup_ready'),
                'handler' => ''
            )
        );
        $this->step = isset($_GET['step']) ? sanitize_key($_GET['step']) : current(array_keys($this->steps));

        // first step must be available without nonce check
        // other steps should check nonce because of plugins install and dismiss notice actions
        if ( $this->step !== current( array_keys( $this->steps ) ) ) {
            if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'theme_wizard_nonce' ) ) {
                echo esc_html__( 'Forbidden', 'emmet-lite' );
                return;
            }
        }

        $this->mp_emmet_setup_wizard_header();
        $this->mp_emmet_setup_wizard_steps();
        ?>
        <div class="wizard-panel">
            <?php
            $this->mp_emmet_setup_wizard_content();
            $this->mp_emmet_setup_wizard_footer();
            ?>
        </div>
        <?php
    }

    public function mp_emmet_get_next_step_link( $step_delta = 1 ) {
        $keys = array_keys($this->steps);
        return add_query_arg(
            array(
                'step' => $keys[array_search($this->step, array_keys($this->steps)) + $step_delta],
                '_wpnonce' => wp_create_nonce( 'theme_wizard_nonce' )
            ),
            remove_query_arg('translation_updated')
        );
    }

    public function mp_emmet_get_exit_link() {
        return add_query_arg(
            array(
                'mp-emmet-wizard-dismiss' => 'dismiss_admin_notices',
                '_wpnonce' => wp_create_nonce( 'theme_wizard_nonce')
            ),
            admin_url('themes.php')
        );
    }

    /*
     * Dismiss Theme Wizard admin notice
     */

    private function mp_emmet_wizard_dismiss() {
        update_user_meta(get_current_user_id(), 'mp_emmet_wizard_dismiss', 1);
    }

    /**
     * Setup Wizard Header
     */
    public function mp_emmet_setup_wizard_header() {
        if ( $this->step == array_keys( $this->steps )[ count( $this->steps ) - 1 ] ) {
            $this->mp_emmet_wizard_dismiss();
        }
        ?>
        <div class="wrap">
            <h2 class="text-center" style="margin-top: 0px;"><?php esc_html_e('Theme Quick Guided Tour', 'emmet-lite'); ?></h2>
        <?php
    }

        /**
         * Setup Wizard Footer
         */
    public function mp_emmet_setup_wizard_footer() {
        ?>
        </div>
        <?php
    }

    /**
     * Output the steps
     */
    public function mp_emmet_setup_wizard_steps() {
        $ouput_steps = $this->steps;
        $i = 0;
        ?>
        <ol class="theme-setup-steps subsubsub">
                <?php
                foreach ($ouput_steps as $step_key => $step) :
                    $i++;
                    ?>
                <li style="color:inherit;" >
                    <?php
                    $text = esc_html($step['name']);
                    if (array_search($this->step, array_keys($this->steps)) >= array_search($step_key, array_keys($this->steps)) || $this->step === $step_key) {
                        $text = '<strong>' . esc_html($step['name']) . '</strong>';
                    }
                    echo wp_kses_data($text);
                    if ($i < sizeof($ouput_steps)): echo " > ";
                    endif;
                    ?>
                </li>
            <?php endforeach; ?>
        </ol>
        <hr style="clear: both; margin: 60px 0 1em;"/>
        <?php
    }

    /**
     * Output the content for the current step
     */
    public function mp_emmet_setup_wizard_content() {
        echo '<div class="theme-setup-content">';
        call_user_func($this->steps[$this->step]['view']);
        echo '</div>';
    }

    /**
     * Introduction step
     */
    public function mp_emmet_setup_introduction() {
        ?>
        <h2><?php esc_html_e('Welcome to the Emmet Theme!', 'emmet-lite'); ?></h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <td>
                        <p><?php esc_html_e('Thank you for choosing Emmet theme. This Quick Guided Tour will show you how to:', 'emmet-lite'); ?></p>
                        <ul>
                            <li>- <?php esc_html_e('Configure your Front Page', 'emmet-lite'); ?></li>
                            <li>- <?php esc_html_e('Customize this theme in a few steps', 'emmet-lite'); ?></li>
                            <li>- <?php esc_html_e('Change colors, texts and images', 'emmet-lite'); ?></li>
                            <li>- <?php esc_html_e('Start e-commerce website and build pages visually', 'emmet-lite'); ?></li>
                        </ul>
                        <p><i><?php esc_html_e('If you don&rsquo;t want to go through the wizard, you can', 'emmet-lite'); ?>
                                <a href="<?php echo esc_url(admin_url('themes.php')); ?>" class="" ><?php esc_html_e('skip and return to the WordPress dashboard.', 'emmet-lite'); ?></a>
                                <?php esc_html_e('This Wizard is always available under Appearance > Theme Wizard menu.', 'emmet-lite'); ?></i></p>

                    </td>
                </tr>
            </tbody>
        </table>
        <hr>
        <p class="theme_setup-actions step text-center">
            <a href="<?php echo esc_url($this->mp_emmet_get_next_step_link()); ?>" class="button button-primary button-large"><?php esc_html_e('Let\'s Go!', 'emmet-lite'); ?></a>
        </p>
        <?php
    }

    /**
     * Customizer setup
     */
    public function mp_emmet_setup_customizer() {
        ?>
        <p class="theme_setup-actions step " style="float:right; margin: -0.3em 0 0;">
            <?php
            if ($this->checkPlugins()) :
                ?>
                <a href="<?php echo esc_url($this->mp_emmet_get_next_step_link(3)); ?>" class="button button-primary button-large"><?php esc_html_e('Continue', 'emmet-lite'); ?></a>
            <?php else: ?>
                <a href="<?php echo esc_url($this->mp_emmet_get_next_step_link()); ?>" class="button button-primary button-large"><?php esc_html_e('Continue', 'emmet-lite'); ?></a>
            <?php endif; ?>
        </p>
        <h2><?php esc_html_e('Customizer', 'emmet-lite'); ?></h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <td colspan="2">
                        <p><?php
                            echo wp_kses_post(sprintf(
								_x('One of the main tools of this theme is WordPress Customizer. Navigate to %1$s to change logo, website title, contact information, colors, background image, menus and so on. Once you are happy with your changes you can click save and your changes will be reflected on your live site.', '%1$s - Customizer path in theme wizard step','emmet-lite'),
                                '<strong>' . _x('Appearance > Customize', 'theme wizard step Customizer path','emmet-lite') . '</strong>'
                            )); ?></p>
                    </td>
                </tr>
                <tr colspan="2">
                    <td>
                        <h3><?php esc_html_e('1. Site Identity', 'emmet-lite'); ?></h3>
                    </td>
                </tr>
                <tr >
                    <td>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/admin-customizer.png'); ?>">
                    </td>
                    <td>
                        <p><?php esc_html_e('The Site Identity section in customizer allows you to change the site title, description, and control whether or not you want to display them in the header.', 'emmet-lite'); ?></p>
                    </td>
                </tr>
                <tr colspan="2">
                    <td>
                        <h3><?php esc_html_e('2. Theme Colors', 'emmet-lite'); ?></h3>
                    </td>
                </tr>
                <tr >
                    <td>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/admin-customizer-colors.png'); ?>">
                    </td>
                    <td>
                        <p><?php esc_html_e('The Colors section in customizer allows you to choose between 4 predefined colors or select your own colors.', 'emmet-lite'); ?></p>
                    </td>
                </tr>
                <tr colspan="2">
                    <td>
                        <h3><?php esc_html_e('3. Background Image', 'emmet-lite'); ?></h3>
                    </td>
                </tr>
                <tr >
                    <td>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/admin-customizer-background.png'); ?>">
                    </td>
                    <td>
                        <p><?php esc_html_e('This section in customizer allows you to chage main image of your website and configure it for your needs.', 'emmet-lite'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <hr/>
        <p class="theme_setup-actions step text-right" style="float:right; margin-top: 0.5em;">
            <?php
            if ($this->checkPlugins()) :
                ?>
                <a href="<?php echo esc_url($this->mp_emmet_get_next_step_link(3)); ?>" class="button button-primary button-large"><?php esc_html_e('Continue', 'emmet-lite'); ?></a>
            <?php else: ?>
                <a href="<?php echo esc_url($this->mp_emmet_get_next_step_link()); ?>" class="button button-primary button-large"><?php esc_html_e('Continue', 'emmet-lite'); ?></a>
            <?php endif; ?>
        </p>
        <p class="theme_setup-actions step text-right">
            <a href="<?php echo esc_url($this->mp_emmet_get_exit_link()); ?>" class="button button-large" ><?php esc_html_e('Exit', 'emmet-lite'); ?></a>
        </p>
        <?php
    }

    /**
     * Locale settings
     */
    public function mp_emmet_setup_section() {
        ?>
        <p class="theme_setup-actions step " style="float:right; margin: -0.3em 0 0;">
            <a href="<?php echo esc_url($this->mp_emmet_get_next_step_link()); ?>" class="button button-primary button-large"><?php esc_html_e('Continue', 'emmet-lite'); ?></a>
        </p>
        <h2><?php esc_html_e('Front Page Setup', 'emmet-lite'); ?></h2>


        <table class="form-table">
            <tbody>
                <tr>
                    <td colspan="2">
                        <p><?php esc_html_e('A huge feature of this theme is completely customizable Front Page. Front Page is the main page of your website that includes all content blocks you may need to build website. Features, Call to action, Portfolio, Contact form, Google Maps and many other blocks are available for you from the box. How to setup your Front Page:', 'emmet-lite'); ?></p>
                    </td>
                </tr>
                <tr colspan="2">
                    <td>
                        <h3><?php esc_html_e('1. Create Front Page', 'emmet-lite'); ?></h3>
                    </td>
                </tr>
                <tr >
                    <td>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/admin-customizer-frontpage.png'); ?>">
                    </td>
                    <td>
                        <ul>
                            <li><?php esc_html_e('1. Follow this link to ', 'emmet-lite'); ?> <a href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>" target="_blank"><?php esc_html_e('Create New Page', 'emmet-lite'); ?></a></li>
                            <li><?php esc_html_e('2. Name it "Home" or "Front Page"', 'emmet-lite'); ?></li>
                            <li><strong><?php esc_html_e('3. Choose "Customizable Front Page" template', 'emmet-lite'); ?></strong></li>
                            <li><?php esc_html_e('4. Press Publish button', 'emmet-lite'); ?></li>
                        </ul>
                        <p class="description"><?php esc_html_e('For more information, please view', 'emmet-lite'); ?> <a href="https://codex.wordpress.org/Pages#Creating_Pages" target="_blank"><?php esc_html_e('documentation', 'emmet-lite'); ?></a></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h3><?php esc_html_e('2. Setup Front Page', 'emmet-lite'); ?></h3>
                    </td>
                </tr>
                <tr >
                    <td>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/admin-customizer-frontpage-1.png'); ?>">
                    </td>

                    <td>
                        <ul>
                            <li><?php esc_html_e('1. Navigate to ', 'emmet-lite'); ?> <a href="<?php echo esc_url(admin_url('options-reading.php')); ?>" target="_blank"><?php esc_html_e('Settings -> Reading', 'emmet-lite'); ?></a></li>
                            <li><?php esc_html_e('2. In Settings -> Reading, set "Front page displays" to "A static page"', 'emmet-lite'); ?></li>
                            <li><?php esc_html_e('2. In Settings -> Reading, set "Front page" to "Home" or "Front Page" you\'ve created in first step', 'emmet-lite'); ?></li>
                            <li><?php esc_html_e('3. Scroll down and Save changes', 'emmet-lite'); ?></li>
                        </ul>
                        <p class="description"><?php esc_html_e('For more information, please view', 'emmet-lite'); ?> <a href="https://codex.wordpress.org/Creating_a_Static_Front_Page" target="_blank"><?php esc_html_e('documentation', 'emmet-lite'); ?></a></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h3><?php esc_html_e('3. Setup Posts Page (Blog)', 'emmet-lite'); ?></h3>
                    </td>
                </tr>
                <tr >
                    <td>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/admin-customizer-frontpage-2.png'); ?>">
                    </td>
                    <td>
                        <ul>
                            <li><?php esc_html_e('1. Create new page and name it "Blog"', 'emmet-lite'); ?></li>
                            <li><?php esc_html_e('2. In Settings -> Reading, set "Posts page" to "Blog"', 'emmet-lite'); ?></li>
                            <li><?php esc_html_e('3. Scroll down and Save changes', 'emmet-lite'); ?></li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>

        <hr/>
        <p class="theme_setup-actions step text-right" style="float:right; margin-top: 0.5em;">
            <a href="<?php echo esc_url($this->mp_emmet_get_next_step_link()); ?>" class="button button-primary button-large"><?php esc_html_e('Continue', 'emmet-lite'); ?></a>
        </p>
        <p class="theme_setup-actions step text-right">
            <a href="<?php echo esc_url($this->mp_emmet_get_exit_link()); ?>" class="button" ><?php esc_html_e('Exit', 'emmet-lite'); ?></a>
        </p>


        <?php
    }

    /**
     * Ready
     */
    public function mp_emmet_setup_ready() {
        ?>
        <h2 class="text-center"><?php esc_html_e('You are Ready!', 'emmet-lite'); ?></h2>
        <p><?php esc_html_e('Thank you for choosing Emmet theme.', 'emmet-lite'); ?></p>
        <hr/>
        <p class="theme_setup-actions step">
            <a href="<?php echo esc_url(admin_url('customize.php')); ?>" class="button button-primary button-large"><?php esc_html_e('Customize this theme', 'emmet-lite'); ?></a>
            <a href="<?php echo esc_url(admin_url('themes.php')); ?>" class="button button-large" ><?php esc_html_e('Return to the WordPress Dashboard', 'emmet-lite'); ?></a>
        </p>
        <?php
    }

    public function checkPlugins() {
        if ((is_plugin_active('mp-emmet/emmet.php') && is_plugin_active('another-mailchimp-widget/another-mailchimp-widget.php') && is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('regenerate-thumbnails/regenerate-thumbnails.php') && is_plugin_active('motopress-content-editor-lite/motopress-content-editor.php'))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Setup plugin
     */
    public function mp_emmet_setup_plugins() {

        $installed_plugins = get_plugins();
        ?>  <form  method="post" action="<?php echo esc_url($this->mp_emmet_get_next_step_link()); ?>">
            <p class="theme_setup-actions step" style="float:right; margin: -0.3em 0 0;">
                <a href="<?php echo esc_url($this->mp_emmet_get_next_step_link(2)); ?>" class="button"><?php esc_html_e('Skip', 'emmet-lite'); ?></a>
                <input class="button button-primary" type="submit" value="Install Plugins">
            </p>
            <h2><?php esc_html_e('This theme recommends the following free plugins:', 'emmet-lite'); ?></h2>
            <br>

            <?php
            if (!isset($installed_plugins['mp-emmet/emmet.php'])) :
                ?>
                <div class="checkbox" style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" value="1" checked="checked" name="plugins[mp_emmet_install]">
                        <?php esc_html_e('Emmet Theme Engine', 'emmet-lite') ?>
                        <p class="description"><?php esc_html_e('Adds Portfolio and Front Page Sections to this theme.', 'emmet-lite') ?></p>

                    </label>
                </div>
            <?php elseif (is_plugin_inactive('mp-emmet/emmet.php')) : ?>
                <div class="checkbox" style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" value="1" checked="checked" name="plugins[mp_emmet_activate]">
                        <?php esc_html_e('Emmet Theme Engine', 'emmet-lite') ?>
                        <p class="description"><?php esc_html_e('Activate this plugin.', 'emmet-lite'); ?></p>
                    </label>
                </div>
            <?php endif; ?>
            <?php
            if (!isset($installed_plugins['another-mailchimp-widget/another-mailchimp-widget.php'])) :
                ?>
                <div class="checkbox" style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" value="1" checked="checked" name="plugins[another_mailchimp_install]">

                        <?php esc_html_e('Newsletter widget', 'emmet-lite') ?>
                        <p class="description"><?php esc_html_e('Adds newsletter widget to this theme.', 'emmet-lite') ?></p>


                    </label>
                </div>
            <?php elseif (is_plugin_inactive('another-mailchimp-widget/another-mailchimp-widget.php')) : ?>
                <div class="checkbox" style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" value="1" checked="checked" name="plugins[another_mailchimp_activate]">
                        <?php esc_html_e('Newsletter widget', 'emmet-lite') ?>
                        <p class="description"><?php esc_html_e('Activate this plugin.', 'emmet-lite'); ?></p>

                    </label>
                </div>
            <?php endif; ?>
            <?php
            if (!isset($installed_plugins['motopress-content-editor-lite/motopress-content-editor.php'])) :
              ?>
              <div class="checkbox" style="margin-bottom: 15px;">
              <label>
              <input type="checkbox" value="1" checked="checked" name="plugins[motopress_lite_install]">
              <?php esc_html_e('MotoPress Content Editor Lite', 'emmet-lite') ?>
              <p class="description"><?php esc_html_e('Enhances the standard WordPress editor and enables to build websites visually. It\'s complete solution for building responsive pages without coding and simply by dragging and dropping content elements.',  'emmet-lite') ?></p>
              </label>
              </div>
              <?php elseif (is_plugin_inactive('motopress-content-editor-lite/motopress-content-editor.php')) : ?>
              <div class="checkbox" style="margin-bottom: 15px;">
              <label>
              <input type="checkbox" value="1" checked="checked" name="plugins[motopress_lite_activate]">
              <?php esc_html_e('MotoPress Content Editor Lite', 'emmet-lite') ?>
              <p class="description"><?php esc_html_e('Activate this plugin.', 'emmet-lite'); ?></p>
              </label>
              </div>
              <?php endif;
            ?>
            <?php
            if (!isset($installed_plugins['woocommerce/woocommerce.php'])) :
                ?>
                <div class="checkbox" style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" value="0" name="plugins[woocommerce_install]">

                        <?php esc_html_e('WooCommerce for eCommerce',  'emmet-lite') ?>
                        <p class="description"><?php esc_html_e('The world\'s favorite eCommerce solution that gives you complete control to sell anything. Install this plugin if you are going to sell from this website.', 'emmet-lite') ?></p>
                    </label>
                </div>
            <?php elseif (is_plugin_inactive('woocommerce/woocommerce.php')) : ?>
                <div class="checkbox" style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" value="0" name="plugins[woocommerce_activate]">

                        <?php esc_html_e('WooCommerce for eCommerce', 'emmet-lite') ?>
                        <p class="description"><?php esc_html_e('Activate this plugin.',  'emmet-lite'); ?></p>

                    </label>
                </div>
            <?php endif; ?>
            <?php
            if (!isset($installed_plugins['regenerate-thumbnails/regenerate-thumbnails.php'])) :
                ?>
                <div class="checkbox" style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" value="0" name="plugins[regenerate_thumbnails_install]">

                        <?php esc_html_e('Regenerate Thumbnails', 'emmet-lite') ?>


                    </label>
                    <p class="description"><?php esc_html_e('Allows you to regenerate the thumbnails for your images if you\'ve changed a theme. Available under Dashboard - Tools - Regenerate Thumbnails menu.', 'emmet-lite') ?></p>
                </div>
            <?php elseif (is_plugin_inactive('regenerate-thumbnails/regenerate-thumbnails.php')) : ?>
                <div class="checkbox" style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" value="0" name="plugins[regenerate_thumbnails_activate]" >
                        <?php esc_html_e('Regenerate Thumbnails', 'emmet-lite') ?>
                        <p class="description"><?php esc_html_e('Activate this plugin.', 'emmet-lite'); ?></p>

                    </label>
                </div>
            <?php endif; ?>

            <hr/>
            <p class="theme_setup-actions step text-right" style="float:right; margin-top: 0.5em;">
                <a href="<?php echo esc_url($this->mp_emmet_get_next_step_link(2)); ?>" class="button button-large"><?php esc_html_e('Skip', 'emmet-lite'); ?></a>

                <?php wp_nonce_field( 'theme_wizard_nonce' ); ?>
                <input class="button button-primary" type="submit" value="Install Plugins"> </p>
            <p class="theme_setup-actions step text-right">
                <a href="<?php echo esc_url($this->mp_emmet_get_exit_link()); ?>" class="button" ><?php esc_html_e('Exit', 'emmet-lite'); ?></a>
            </p>


        </form>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                var check = false;
                jQuery(".wizard-panel input[type='checkbox']").each(function () {
                    if (jQuery(this).is(":checked")) {
                        console.log('ddd');
                        check = true;
                    }
                });
                if (!check) {
                    jQuery('.wizard-panel input[type="submit"]').attr("disabled", "disabled");
                }
                jQuery('.wizard-panel input[type="checkbox"]').click(function () {
                    if (jQuery(this).is(":checked")) {
                        jQuery(this).parent().find('input[type="checkbox"]').attr('value', '1');
                    } else {
                        jQuery(this).parent().find('input[type="checkbox"]').attr('value', '0');
                    }
                    jQuery('.wizard-panel input[type="submit"]').attr("disabled", "disabled");
                    jQuery('.wizard-panel input[type="checkbox"]').each(function () {
                        if (jQuery(this).is(":checked")) {
                            jQuery('.wizard-panel input[type="submit"]').removeAttr("disabled");
                        }
                    });

                });
            });
        </script>

        <?php
    }

    public function mp_emmet_setup_install_plugins() {
        ?>
        <style type="text/css">
            .wrap .wrap{
                margin: 0;
            }
            .wrap .wrap p:nth-child(7),
            .wrap  .wrap h1{
                display:none;
            }
        </style>

        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery("html, body").animate({scrollTop: jQuery(document).height()}, 1000);
            });
        </script>

        <p class="theme_setup-actions step " style="float:right; margin: -0.3em 0 0;">
            <a href="<?php echo esc_url($this->mp_emmet_get_next_step_link()); ?>" class="button button-primary button-large"><?php esc_html_e('Continue', 'emmet-lite'); ?></a>
        </p>
        <h2><?php esc_html_e('Installing the plugins (this may take awhile)', 'emmet-lite'); ?></h2>
        <?php
        if (isset($_POST["plugins"])) {
            $array = $_POST["plugins"];
            if (!is_null($array)) {
                require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
                if (array_key_exists("mp_emmet_install", $array) || array_key_exists("mp_emmet_activate", $array)) {
	                echo  '<h4>' . esc_html__('Emmet Theme Engine', 'emmet-lite') . '</h4>';
                }
                if (array_key_exists("mp_emmet_install", $array)) {
                    $plugin = 'mp-emmet';
                    $url = 'https://static.getmotopress.com/plugins/mp-emmet.zip';
                    mp_emmet_install_plugin($plugin, $url);
                    $plugin_path = 'mp-emmet/emmet.php';
                    $plugin_name = __('Emmet Theme Engine', 'emmet-lite');
	                /* translators: %s - plugin name */
                    echo '<p>' . sprintf(__('Activating %s plugin...', 'emmet-lite'), esc_html($plugin_name)) . '</p>';
                    mp_emmet_activate_plugin($plugin_name, $plugin_path);
                }
                if (array_key_exists("mp_emmet_activate", $array)) {
                    $plugin_path = 'mp-emmet/emmet.php';
                    $plugin_name = __('Emmet Theme Engine', 'emmet-lite');
	                /* translators: %s - plugin name */
                    echo '<p>' . sprintf(__('Activating %s plugin...', 'emmet-lite'), esc_html($plugin_name)) . '</p>';
                    mp_emmet_activate_plugin($plugin_name, $plugin_path);
                }
                if (array_key_exists("another_mailchimp_install", $array) || array_key_exists("another_mailchimp_activate", $array)) {
                    echo '<h4>'.esc_html__('Newsletter widget', 'emmet-lite').'</h4>';
                }
                if (array_key_exists("another_mailchimp_install", $array)) {
                    $plugin = 'another-mailchimp-widget';
                    $url = 'https://downloads.wordpress.org/plugin/another-mailchimp-widget.zip';
                    mp_emmet_install_plugin($plugin, $url);
                    $plugin_path = 'another-mailchimp-widget/another-mailchimp-widget.php';
                    $plugin_name = __('Newsletter widget', 'emmet-lite');
	                /* translators: %s - plugin name */
                    echo '<p>' . sprintf(__('Activating %s plugin...', 'emmet-lite'), esc_html($plugin_name)) . '</p>';
                    mp_emmet_activate_plugin($plugin_name, $plugin_path);
                }
                if (array_key_exists("another_mailchimp_activate", $array)) {
                    $plugin_path = 'another-mailchimp-widget/another-mailchimp-widget.php';
                    $plugin_name = __('Newsletter widget', 'emmet-lite');
	                /* translators: %s - plugin name */
                    echo '<p>' . sprintf(__('Activating %s plugin...', 'emmet-lite'), esc_html($plugin_name)) . '</p>';
                    mp_emmet_activate_plugin($plugin_name, $plugin_path);
                }
                if (array_key_exists("motopress_lite_install", $array) || array_key_exists("motopress_lite_activate", $array)) {
                  _e('<h4>MotoPress Content Editor Lite</h4>', 'emmet-lite');
                  }
                  if (array_key_exists("motopress_lite_install", $array)) {
                  $plugin = 'motopress-content-editor-lite';
                  $url = 'https://static.getmotopress.com/plugins/motopress-content-editor-lite.zip';
                  mp_emmet_install_plugin($plugin, $url);
                  $plugin_path = 'motopress-content-editor-lite/motopress-content-editor.php';
                  $plugin_name = __('MotoPress Content Editor Lite', 'emmet-lite');
                  echo '<p>' . sprintf( __('Activating %s plugin...', 'emmet-lite' ), $plugin_name) . '</p>';
                  mp_emmet_activate_plugin($plugin_name, $plugin_path);
                  }
                  if (array_key_exists("motopress_lite_activate", $array)) {
                  $plugin_path = 'motopress-content-editor-lite/motopress-content-editor.php';
                  $plugin_name = __('MotoPress Content Editor Lite',  'emmet-lite' );
                  echo '<p>' . sprintf( __('Activating %s plugin...', 'emmet-lite' ), $plugin_name). '</p>';
                  mp_emmet_activate_plugin($plugin_name, $plugin_path);
                  }
                if (array_key_exists("woocommerce_install", $array) || array_key_exists("woocommerce_activate", $array)) {
                    echo '<h4>'.esc_html__('WooCommerce', 'emmet-lite').'</h4>';
                }
                if (array_key_exists("woocommerce_install", $array)) {
                    $plugin = 'woocommerce';
                    $url = 'https://downloads.wordpress.org/plugin/woocommerce.3.0.6.zip';
                    mp_emmet_install_plugin($plugin, $url);
                    $plugin_path = 'woocommerce/woocommerce.php';
                    $plugin_name = __('WooCommerce', 'emmet-lite');
	                /* translators: %s - plugin name */
                    echo '<p>' . sprintf(__('Activating %s plugin...', 'emmet-lite'), esc_html($plugin_name)) . '</p>';
                    mp_emmet_activate_plugin($plugin_name, $plugin_path);
                }
                if (array_key_exists("woocommerce_activate", $array)) {
                    $plugin_path = 'woocommerce/woocommerce.php';
                    $plugin_name = __('WooCommerce', 'emmet-lite');
	                /* translators: %s - plugin name */
                    echo '<p>' . sprintf(__('Activating %s plugin...', 'emmet-lite'), esc_html($plugin_name)) . '</p>';
                    mp_emmet_activate_plugin($plugin_name, $plugin_path);
                }
                if (array_key_exists("regenerate_thumbnails_install", $array) || array_key_exists("regenerate_thumbnails_activate", $array)) {
                    echo '<h4>'.esc_html__('Regenerate Thumbnails', 'emmet-lite').'</h4>';
                }
                if (array_key_exists("regenerate_thumbnails_install", $array)) {
                    $plugin = 'regenerate-thumbnails';
                    $url = 'https://downloads.wordpress.org/plugin/regenerate-thumbnails.zip';
                    mp_emmet_install_plugin($plugin, $url);
                    $plugin_path = 'regenerate-thumbnails/regenerate-thumbnails.php';
                    $plugin_name = __('Regenerate Thumbnails', 'emmet-lite');
	                /* translators: %s - plugin name */
                    echo '<p>' . sprintf(__('Activating %s plugin...', 'emmet-lite'), esc_html($plugin_name)) . '</p>';
                    mp_emmet_activate_plugin($plugin_name, $plugin_path);
                }
                if (array_key_exists("regenerate_thumbnails_activate", $array)) {
                    $plugin_path = 'regenerate-thumbnails/regenerate-thumbnails.php';
                    $plugin_name = __('Regenerate Thumbnails', 'emmet-lite');
	                /* translators: %s - plugin name */
                    echo '<p>' . sprintf(__('Activating %s plugin...', 'emmet-lite'), esc_html($plugin_name)) . '</p>';
                    mp_emmet_activate_plugin($plugin_name, $plugin_path);
                }
            }
        }
        ?>
        <hr/>
        <p class="theme_setup-actions step text-right" style="float:right; margin-top: 0.5em;">
            <a href="<?php echo esc_url($this->mp_emmet_get_next_step_link()); ?>" class="button button-primary button-large"><?php esc_html_e('Continue', 'emmet-lite'); ?></a>
        </p>
        <p class="theme_setup-actions step text-right">
            <a href="<?php echo esc_url($this->mp_emmet_get_exit_link()); ?>" class="button button-large" ><?php esc_html_e('Exit', 'emmet-lite'); ?></a>
        </p>
        <?php
    }

}

function mp_emmet_install_plugin($plugin, $url) {
    $title = '';
    $upgrader = new Plugin_Upgrader(
            $skin = new Plugin_Upgrader_Skin(
            compact('url', 'plugin', 'title')
            )
    );
    // Perform plugin insatallation from source url
    $upgrader->install($url);
    //Flush plugins cache so we can make sure that the installed plugins list is always up to date
    wp_cache_flush();
}

function mp_emmet_activate_plugin($plugin_name, $plugin_path) {
    $result = activate_plugin($plugin_path);

    if (is_wp_error($result)) {
        echo '<p>' . esc_html($plugin_name) . ' ' . esc_html__('plugin is not activated', 'emmet-lite') . '</p>';
    } else {
        echo '<p>' . esc_html($plugin_name) . ' ' . esc_html__('plugin is activated', 'emmet-lite') . '</p>';
    }
}

new MP_Emmet_Admin_Setup_Wizard();



