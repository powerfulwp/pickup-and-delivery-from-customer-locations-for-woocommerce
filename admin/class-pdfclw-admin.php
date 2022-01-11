<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    Pdfclw
 * @subpackage Pdfclw/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pdfclw
 * @subpackage Pdfclw/admin
 * @author     powerfulwp <apowerfulwp@gmail.com>
 */
class Pdfclw_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private  $plugin_name ;
    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private  $version ;
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name       The name of this plugin.
     * @param      string $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Pdfclw_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Pdfclw_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/pdfclw-admin.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Pdfclw_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Pdfclw_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/pdfclw-admin.js',
            array( 'jquery' ),
            $this->version,
            false
        );
    }
    
    /**
     * Plugin menu.
     *
     * @since 1.0.0
     * @return void
     */
    public function pdfclw_admin_menu()
    {
        $menu_slug = 'pdfclw-settings';
        // Add menu to main menu.
        add_menu_page(
            esc_html( __( 'Pickup from Customers', 'pdfclw' ) ),
            esc_html( __( 'Pickup from Customers', 'pdfclw' ) ),
            'read',
            $menu_slug,
            array( &$this, 'pdfclw_settings' ),
            'dashicons-location',
            56
        );
        add_submenu_page(
            $menu_slug,
            esc_html( __( 'Settings', 'pdfclw' ) ),
            esc_html( __( 'Settings', 'pdfclw' ) ),
            'edit_pages',
            'pdfclw-settings',
            array( &$this, 'pdfclw_settings' )
        );
    }
    
    /**
     * Pickup metaboxes.
     *
     * @return void
     */
    public function pdfclw_add_pickup_metaboxes()
    {
        add_meta_box(
            'pdfclw_pickup_address',
            'Pickup Location',
            array( &$this, 'pdfclw_pickup_address' ),
            'pdfclw_pickup',
            'normal',
            'default'
        );
    }
    
    /**
     * Pickup address.
     *
     * @return void
     */
    public function pdfclw_pickup_address()
    {
        global  $post ;
        $default_location = wc_get_customer_default_location();
        wp_enqueue_style(
            'woocommerce_admin_styles',
            WC()->plugin_url() . '/assets/css/admin.css',
            array(),
            WC_VERSION,
            'all'
        );
        wp_enqueue_script(
            'wc-admin-order-meta-boxes',
            WC()->plugin_url() . '/assets/js/admin/meta-boxes-order.min.js',
            array(
            'wc-admin-meta-boxes',
            'wc-backbone-modal',
            'selectWoo',
            'wc-clipboard'
        ),
            WC_VERSION
        );
        wp_localize_script( 'wc-admin-order-meta-boxes', 'woocommerce_admin_meta_boxes_order', array(
            'countries'              => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
            'i18n_select_state_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' ),
            'default_country'        => ( isset( $default_location['country'] ) ? $default_location['country'] : '' ),
            'default_state'          => ( isset( $default_location['state'] ) ? $default_location['state'] : '' ),
            'placeholder_name'       => esc_attr__( 'Name (required)', 'woocommerce' ),
            'placeholder_value'      => esc_attr__( 'Value (required)', 'woocommerce' ),
        ) );
        echo  '<div id="order_data" class="panel woocommerce-order-data">' ;
        $this->admin_order_pickup_location( $post );
        echo  '</div>' ;
    }
    
    /**
     * Admin order pickup form.
     *
     * @param object $order order object.
     * @return void
     */
    public function admin_order_pickup_location( $order )
    {
        wp_nonce_field( basename( __FILE__ ), 'pdfclw_nonce' );
        $address = '';
        $order_id = $order->get_id();
        // Get the location data if it's already been entered.
        $first_name = get_post_meta( $order_id, '_pdfclw_pickup_first_name', true );
        $last_name = get_post_meta( $order_id, '_pdfclw_pickup_last_name', true );
        $company = get_post_meta( $order_id, '_pdfclw_pickup_company', true );
        $address_1 = get_post_meta( $order_id, '_pdfclw_pickup_address_1', true );
        $address_2 = get_post_meta( $order_id, '_pdfclw_pickup_address_2', true );
        $city = get_post_meta( $order_id, '_pdfclw_pickup_city', true );
        $postcode = get_post_meta( $order_id, '_pdfclw_pickup_postcode', true );
        $country = get_post_meta( $order_id, '_pdfclw_pickup_country', true );
        $state = get_post_meta( $order_id, '_pdfclw_pickup_state', true );
        $array = array(
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'company'    => $company,
            'street_1'   => $address_1,
            'street_2'   => $address_2,
            'city'       => $city,
            'zip'        => $postcode,
            'country'    => ( '' !== $country ? WC()->countries->countries[$country] : '' ),
            'state'      => $state,
        );
        if ( '' !== $address_1 && '' !== $city ) {
            $address = pdfclw_format_address( 'address', $array );
        }
        if ( '' === $address ) {
            $address = __( 'No pickup address set.', 'pdfclw' );
        }
        $pickup_fields = array(
            'first_name' => array(
            'label' => __( 'First name', 'woocommerce' ),
            'show'  => false,
        ),
            'last_name'  => array(
            'label' => __( 'Last name', 'woocommerce' ),
            'show'  => false,
        ),
            'company'    => array(
            'label' => __( 'Company', 'woocommerce' ),
            'show'  => false,
        ),
            'address_1'  => array(
            'label' => __( 'Address line 1', 'woocommerce' ),
            'show'  => false,
        ),
            'address_2'  => array(
            'label' => __( 'Address line 2', 'woocommerce' ),
            'show'  => false,
        ),
            'city'       => array(
            'label' => __( 'City', 'woocommerce' ),
            'show'  => false,
        ),
            'postcode'   => array(
            'label' => __( 'Postcode / ZIP', 'woocommerce' ),
            'show'  => false,
        ),
            'country'    => array(
            'label'   => __( 'Country / Region', 'woocommerce' ),
            'show'    => false,
            'type'    => 'select',
            'class'   => 'js_field-country select short',
            'options' => array(
            '' => __( 'Select a country / region&hellip;', 'woocommerce' ),
        ) + WC()->countries->get_shipping_countries(),
        ),
            'state'      => array(
            'label' => __( 'State / County', 'woocommerce' ),
            'class' => 'js_field-state select short',
            'show'  => false,
        ),
        );
        echo  '<div class="order_data_column pdfclw_pickup" style="width:100%">' ;
        if ( get_post_type( $order_id ) === 'shop_order' ) {
            echo  '<h3>' . esc_html( __( 'Pickup from Customer', 'pdfclw' ) ) . '
					<a href="#" class="edit_address">' . esc_html( __( 'Edit', 'woocommerce' ) ) . '</a>
				</h3>
				<div class="address">' . wp_kses_post( $address ) . '</div>' ;
        }
        echo  '<div class="edit_address">' ;
        // Display form.
        if ( !empty($pickup_fields) ) {
            foreach ( $pickup_fields as $key => $field ) {
                if ( !isset( $field['type'] ) ) {
                    $field['type'] = 'text';
                }
                if ( !isset( $field['id'] ) ) {
                    $field['id'] = '_pdfclw_pickup_' . $key;
                }
                $field_name = '_pdfclw_pickup_' . $key;
                $field['value'] = get_post_meta( $order_id, $field_name, true );
                switch ( $field['type'] ) {
                    case 'select':
                        woocommerce_wp_select( $field );
                        break;
                    default:
                        woocommerce_wp_text_input( $field );
                        break;
                }
            }
        }
        echo  '</div>
					</div>' ;
    }
    
    /**
     * Admin plugin bar.
     *
     * @since 1.1.0
     * @return statement
     */
    public function pdfclw_admin_plugin_bar()
    {
        return '<div class="pdfclw_admin_bar">' . esc_html( __( 'Developed by', 'pdfclw' ) ) . ' <a href="https://powerfulwp.com/" target="_blank">PowerfulWP</a> | <a href="https://powerfulwp.com/pickup-and-delivery-from-customer-locations-for-woocommerce/" target="_blank" >' . esc_html( __( 'Premium', 'pdfclw' ) ) . '</a> | <a href="https://powerfulwp.com/docs/pickup-and-delivery-from-customer-locations-for-woocommerce/" target="_blank" >' . esc_html( __( 'Documents', 'pdfclw' ) ) . '</a></div>';
    }
    
    /**
     * Plugin register settings.
     *
     * @since 1.0.0
     * @return void
     */
    public function settings_init()
    {
        register_setting( 'pdfclw', 'pdfclw_pickup_enable' );
        register_setting( 'pdfclw', 'pdfclw_pickup_mandatory' );
        register_setting( 'pdfclw', 'pdfclw_pickup_permission' );
        add_settings_section(
            'pdfclw_setting_section',
            '',
            '',
            'pdfclw'
        );
        add_settings_field(
            'pdfclw_pickup_enable',
            __( 'Pickup from customers', 'pdfclw' ),
            array( $this, 'pdfclw_pickup_enable' ),
            'pdfclw',
            'pdfclw_setting_section'
        );
        add_settings_field(
            'pdfclw_pickup_mandatory',
            __( 'Pickup address is mandatory', 'pdfclw' ),
            array( $this, 'pdfclw_pickup_mandatory' ),
            'pdfclw',
            'pdfclw_setting_section'
        );
        add_settings_field(
            'pdfclw_pickup_permission',
            __( 'Pickup permissions', 'pdfclw' ),
            array( $this, 'pdfclw_pickup_permission' ),
            'pdfclw',
            'pdfclw_setting_section'
        );
        if ( pdfclw_is_free() ) {
            add_settings_field(
                'pdfclw_third_party_plugins',
                __( 'Third-party plugins support', 'pdfclw' ),
                array( $this, 'pdfclw_third_party_plugins' ),
                'pdfclw',
                'pdfclw_setting_section'
            );
        }
    }
    
    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function pdfclw_third_party_plugins()
    {
        ?>
		<p>
			<?php 
        echo  wp_kses_post( pdfclw_admin_premium_feature( '' ) . sprintf( __( 'Show the order pickup locations on %s plugin.', 'pdfclw' ), sprintf( __( '<a href="%s" target="_blank" >Local delivery drivers for woocommerce</a>', 'pdfclw' ), 'https://powerfulwp.com/local-delivery-drivers-for-woocommerce-premium/' ) ) ) ;
        ?>
		</p>
		<?php 
    }
    
    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function pdfclw_pickup_permission()
    {
        $pdfclw_pickup_permission = get_option( 'pdfclw_pickup_permission', '' );
        ?>
		<p>
			<?php 
        echo  wp_kses_post( pdfclw_admin_premium_feature( '' ) ) . esc_html( __( 'Enable pickup from customers for all products or for specific products.', 'pdfclw' ) ) ;
        ?>
		</p>
		<?php 
    }
    
    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function pdfclw_pickup_mandatory()
    {
        $pdfclw_pickup_mandatory = get_option( 'pdfclw_pickup_mandatory', '' );
        $checked = ( '1' === $pdfclw_pickup_mandatory ? 'checked' : '' );
        ?>
		<label for="pdfclw_pickup_mandatory" class='checkbox_toggle'>
			<input <?php 
        echo  esc_attr( $checked ) ;
        ?> type='checkbox' class='regular-text' name='pdfclw_pickup_mandatory' id='pdfclw_pickup_enable' value='1'>
			<?php 
        echo  esc_html( __( 'Set pickup from customers as mandatory on checkout.', 'pdfclw' ) ) ;
        ?>
		</label>
		<?php 
    }
    
    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function pdfclw_pickup_enable()
    {
        $pdfclw_pickup_enable = get_option( 'pdfclw_pickup_enable', '' );
        $checked = ( '1' === $pdfclw_pickup_enable ? 'checked' : '' );
        ?>
		<label for="pdfclw_pickup_enable" class='checkbox_toggle'>
			<input <?php 
        echo  esc_attr( $checked ) ;
        ?> type='checkbox' class='regular-text' name='pdfclw_pickup_enable' id='pdfclw_pickup_enable' value='1'>
			<?php 
        echo  esc_html( __( 'Enable pickup from customers on checkout.', 'pdfclw' ) ) ;
        ?>
		</label>
		<?php 
    }
    
    /**
     * Product cat add form fields.
     *
     * @return void
     */
    public function product_cat_add_form_fields()
    {
        ?>
		<div class="form-field">
			<input type="checkbox" name="pdfclw_pickup_permission" id="pdfclw_pickup_permission" value="1">
			<?php 
        echo  esc_html( __( 'Enable pickup from customer locations for all category products.', 'pdfclw' ) ) ;
        ?>
		</div>
		<?php 
    }
    
    /**
     * Add custom shipping option to products.
     *
     * @return void
     */
    public function add_custom_shipping_option_to_products()
    {
        global  $post, $product ;
        $pdfclw_pickup_permission = esc_attr( get_post_meta( $post->ID, 'pdfclw_pickup_permission', true ) );
        echo  '</div><div class="options_group">' ;
        woocommerce_wp_checkbox( array(
            'id'          => 'pdfclw_pickup_permission',
            'value'       => ( '1' === $pdfclw_pickup_permission ? '1' : '0' ),
            'label'       => esc_html( __( 'Pickup from customers', 'pdfclw' ) ),
            'description' => esc_html( __( 'Enable pickup from customer locations for this product.', 'pdfclw' ) ),
            'cbvalue'     => '1',
        ) );
    }
    
    /**
     * Save custom shipping option to products.
     *
     * @param int $post_id post id.
     * @return void
     */
    public function save_custom_shipping_option_to_products( $post_id )
    {
        $pdfclw_pickup_permission = ( isset( $_POST['pdfclw_pickup_permission'] ) ? sanitize_text_field( wp_unslash( $_POST['pdfclw_pickup_permission'] ) ) : '0' );
        update_post_meta( $post_id, 'pdfclw_pickup_permission', esc_attr( $pdfclw_pickup_permission ) );
    }
    
    /**
     * Product cat edit form fields
     *
     * @param object $term $array.
     * @return void
     */
    public function product_cat_edit_form_fields( $term )
    {
        // Getting term ID.
        $term_id = $term->term_id;
        // retrieve the existing value(s) for this meta field.
        $pdfclw_pickup_permission = get_term_meta( $term_id, 'pdfclw_pickup_permission', true );
        ?>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="meta_title">
						<?php 
        echo  esc_html( __( 'Pickup from customers', 'pdfclw' ) ) ;
        ?></label>
				</th>
				<td>
					<input type="checkbox" <?php 
        checked( '1', esc_attr( $pdfclw_pickup_permission ), true );
        ?> name="pdfclw_pickup_permission" id="pdfclw_pickup_permission" value="1">
					<?php 
        echo  esc_html( __( 'Enable pickup from customer locations for all category products.', 'pdfclw' ) ) ;
        ?>
				</td>
			</tr>
		<?php 
    }
    
    /**
     * Save product category.
     *
     * @param int $term_id termid.
     * @return void
     */
    public function save_product_category( $term_id )
    {
        $pdfclw_pickup_permission = filter_input( INPUT_POST, 'pdfclw_pickup_permission' );
        update_term_meta( $term_id, 'pdfclw_pickup_permission', $pdfclw_pickup_permission );
    }
    
    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function pdfclw_settings()
    {
        ?>
		<div class="wrap">
			<form action='options.php' method='post'>
				<h1 class="wp-heading-inline"><?php 
        echo  esc_html( __( 'General Settings', 'pdfclw' ) ) ;
        ?></h1>
				<?php 
        echo  wp_kses_post( self::pdfclw_admin_plugin_bar() ) ;
        echo  '<hr class="wp-header-end">' ;
        settings_fields( 'pdfclw' );
        do_settings_sections( 'pdfclw' );
        submit_button();
        ?>
			</form>
		</div>
		<?php 
    }
    
    /**
     * Save pickup meta.
     *
     * @param int    $post_id post id.
     * @param object $post post object.
     * @return string
     */
    public function pdfclw_save_pickup_meta( $post_id, $post )
    {
        // Return if the user doesn't have edit permissions.
        if ( !current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
        // Verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times.
        if ( !isset( $_POST['_pdfclw_pickup_address_1'] ) || !isset( $_POST['pdfclw_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pdfclw_nonce'] ) ), basename( __FILE__ ) ) ) {
            return $post_id;
        }
        // Now that we're authenticated, time to save the data.
        // This sanitizes the data from the field and saves it into an array $events_meta.
        $events_meta['_pdfclw_pickup_first_name'] = ( isset( $_POST['_pdfclw_pickup_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_first_name'] ) ) : '' );
        $events_meta['_pdfclw_pickup_last_name'] = ( isset( $_POST['_pdfclw_pickup_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_last_name'] ) ) : '' );
        $events_meta['_pdfclw_pickup_company'] = ( isset( $_POST['_pdfclw_pickup_company'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_company'] ) ) : '' );
        $events_meta['_pdfclw_pickup_address_1'] = ( isset( $_POST['_pdfclw_pickup_address_1'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_address_1'] ) ) : '' );
        $events_meta['_pdfclw_pickup_address_2'] = ( isset( $_POST['_pdfclw_pickup_address_2'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_address_2'] ) ) : '' );
        $events_meta['_pdfclw_pickup_city'] = ( isset( $_POST['_pdfclw_pickup_city'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_city'] ) ) : '' );
        $events_meta['_pdfclw_pickup_postcode'] = ( isset( $_POST['_pdfclw_pickup_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_postcode'] ) ) : '' );
        $events_meta['_pdfclw_pickup_country'] = ( isset( $_POST['_pdfclw_pickup_country'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_country'] ) ) : '' );
        $events_meta['_pdfclw_pickup_state'] = ( isset( $_POST['_pdfclw_pickup_state'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_state'] ) ) : '' );
        $events_meta['_pdfclw_pickup_phone'] = ( isset( $_POST['_pdfclw_pickup_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_phone'] ) ) : '' );
        // Cycle through the $events_meta array.
        // Note, in this example we just have one item, but this is helpful if you have multiple.
        foreach ( $events_meta as $key => $value ) {
            // Don't store custom data twice.
            if ( 'revision' === $post->post_type ) {
                return;
            }
            
            if ( get_post_meta( $post_id, $key, false ) ) {
                // If the custom field already has a value, update it.
                update_post_meta( $post_id, $key, $value );
            } else {
                // If the custom field doesn't have a value, add it.
                add_post_meta( $post_id, $key, $value );
            }
            
            if ( !$value ) {
                // Delete the meta key if there's no value.
                delete_post_meta( $post_id, $key );
            }
        }
    }
    
    /**
     * Save order meta.
     *
     * @param int $order_id order number.
     * @return statement
     */
    public function process_shop_order_meta( $order_id )
    {
        // Verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times.
        if ( !isset( $_POST['_pdfclw_pickup_address_1'] ) || !isset( $_POST['pdfclw_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pdfclw_nonce'] ) ), basename( __FILE__ ) ) ) {
            return $order_id;
        }
        $first_name = ( isset( $_POST['_pdfclw_pickup_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_first_name'] ) ) : '' );
        $last_name = ( isset( $_POST['_pdfclw_pickup_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_last_name'] ) ) : '' );
        $company = ( isset( $_POST['_pdfclw_pickup_company'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_company'] ) ) : '' );
        $address_1 = ( isset( $_POST['_pdfclw_pickup_address_1'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_address_1'] ) ) : '' );
        $address_2 = ( isset( $_POST['_pdfclw_pickup_address_2'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_address_2'] ) ) : '' );
        $city = ( isset( $_POST['_pdfclw_pickup_city'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_city'] ) ) : '' );
        $postcode = ( isset( $_POST['_pdfclw_pickup_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_postcode'] ) ) : '' );
        $country = ( isset( $_POST['_pdfclw_pickup_country'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_country'] ) ) : '' );
        $state = ( isset( $_POST['_pdfclw_pickup_state'] ) ? sanitize_text_field( wp_unslash( $_POST['_pdfclw_pickup_state'] ) ) : '' );
        $array = array(
            '_pdfclw_pickup_first_name' => $first_name,
            '_pdfclw_pickup_last_name'  => $last_name,
            '_pdfclw_pickup_company'    => $company,
            '_pdfclw_pickup_address_1'  => $address_1,
            '_pdfclw_pickup_address_2'  => $address_2,
            '_pdfclw_pickup_city'       => $city,
            '_pdfclw_pickup_postcode'   => $postcode,
            '_pdfclw_pickup_country'    => $country,
            '_pdfclw_pickup_state'      => $state,
        );
        
        if ( '' !== $address_1 && '' !== $city ) {
            // Update pickup location.
            foreach ( $array as $key => $value ) {
                update_post_meta( $order_id, $key, $value );
            }
        } else {
            // Delete pickup location.
            foreach ( $array as $key => $value ) {
                delete_post_meta( $order_id, $key );
            }
        }
    
    }
    
    /**
     * Print driver name in column
     *
     * @param string $column column name.
     * @param int    $post_id post number.
     * @since 1.0.0
     */
    public function orders_list_columns( $column, $post_id )
    {
        switch ( $column ) {
            case 'pdfclw_order_pickup':
                $address = '–';
                // Get the location data if it's already been entered.
                $first_name = get_post_meta( $post_id, '_pdfclw_pickup_first_name', true );
                $last_name = get_post_meta( $post_id, '_pdfclw_pickup_last_name', true );
                $company = get_post_meta( $post_id, '_pdfclw_pickup_company', true );
                $address_1 = get_post_meta( $post_id, '_pdfclw_pickup_address_1', true );
                $address_2 = get_post_meta( $post_id, '_pdfclw_pickup_address_2', true );
                $city = get_post_meta( $post_id, '_pdfclw_pickup_city', true );
                $postcode = get_post_meta( $post_id, '_pdfclw_pickup_postcode', true );
                $country = get_post_meta( $post_id, '_pdfclw_pickup_country', true );
                $state = get_post_meta( $post_id, '_pdfclw_pickup_state', true );
                $array = array(
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'company'    => $company,
                    'street_1'   => $address_1,
                    'street_2'   => $address_2,
                    'city'       => $city,
                    'zip'        => $postcode,
                    'country'    => ( '' !== $country ? WC()->countries->countries[$country] : '' ),
                    'state'      => $state,
                );
                
                if ( '' !== $address_1 && '' !== $city ) {
                    $pickup_from = '';
                    if ( '' !== $first_name ) {
                        $pickup_from .= $first_name . ' ' . $last_name . ', ';
                    }
                    if ( '' !== $company ) {
                        $pickup_from .= $company . ', ';
                    }
                    $address = '<a href="https://www.google.com/maps/place/' . pdfclw_format_address( 'map_address', $array ) . '" target="_blank">' . $pickup_from . pdfclw_format_address( 'address_line', $array ) . '</a>';
                }
                
                echo  wp_kses_post( $address ) ;
                break;
        }
    }
    
    /**
     * Columns order
     *
     * @param array $columns columns array.
     * @since 1.0.0
     * @return array
     */
    public function orders_list_columns_order( $columns )
    {
        $reordered_columns = array();
        // Inserting columns to a specific location.
        foreach ( $columns as $key => $column ) {
            $reordered_columns[$key] = $column;
            if ( 'billing_address' === $key ) {
                // Inserting after "Status" column.
                $reordered_columns['pdfclw_order_pickup'] = __( 'Pickup', 'pdfclw' );
            }
        }
        return $reordered_columns;
    }

}