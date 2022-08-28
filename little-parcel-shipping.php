<?php
/**
 * Plugin Name: Little Parcel Shipping
 * Description: Little Parcel Shipping
 * Version: 1.0
 * Author: George Githae
 * 
 */
/*
Little Parcel Shipping is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Little Parcel Shipping is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Little Parcel Shipping. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

///variable containing endpoints to be
  //0. get address keyed in by seller - assumption I can get data from other plugin
  //1. get data from google. (storing to db or not?)
  //2. get coordinate for pickup(get the closest one? amd one with the most) could be multiple
  //3. get current items in cart or just get the total mmhhhh
  //4. Send location data to little estimator.
  //5. Sum with the total cut the show client.
  //6. The confirm and make payment.
  
  //two if
  //7. complete order(assumption that notification is sent to warehouse for prep)
//send request to little requesting pickup.
//share pickup details with the ware

if ( ! defined( 'WPINC' ) ) {
 
  die;

}

/*
* Check if WooCommerce is active
*/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

  function little_parcel_shipping_method() {
      if ( ! class_exists( 'Little_Parcel_Shipping_Method' ) ) {
          class Little_Parcel_Shipping_Method extends WC_Shipping_Method {
              /**
               * Constructor for your shipping class
               *
               * @access public
               * @return void
               */
              public function __construct() {
                  $this->id                 = 'little_parcel'; 
                  $this->method_title       = __( 'Little Parcel Shipping', 'little_parcel' );  
                  $this->method_description = __( 'Custom Shipping Method for Little', 'little_parcel' ); 

                  //Availabity & countries
                  $this->availability = 'including';
                  //To add more countries add to the list
                  $this->countries = array(
                      'KE', // Kenya
                      );
                  
                  $this->init();

                  $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                  $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Little Parcel Shipping', 'little_parcel' );
              }

              /**
               * Init your settings
               *
               * @access public
               * @return void
               */
              function init() {
                  // Load the settings API
                  $this->init_form_fields(); 
                  $this->init_settings(); 

                  // Save settings in admin if you have any defined
                  add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
              }

              /**
               * Define settings field for this shipping
               * @return void 
               */
              function init_form_fields() { 
 
                $this->form_fields = array(
         
                 'enabled' => array(
                      'title' => __( 'Enable', 'little_parcel' ),
                      'type' => 'checkbox',
                      'description' => __( 'Enable this shipping.', 'little_parcel' ),
                      'default' => 'yes'
                      ),
         
                 'title' => array(
                    'title' => __( 'Title', 'little_parcel' ),
                      'type' => 'text',
                      'description' => __( 'Title to be display on site', 'little_parcel' ),
                      'default' => __( 'Little Parcel Shipping', 'little_parcel' )
                      ),
                  'url' => array(
                    'title' => __( 'URL', 'little_parcel' ),
                      'type' => 'url',
                      'description' => __( 'Little API URL', 'little_parcel' ),
                      'default' => 'https://api.little.bz'
                      ),
                  'estimate_endpoint' => array(
                    'title' => __( 'Estimate endpoint', 'little_parcel' ),
                      'type' => 'uri',
                      'description' => __( 'Endpoing for Get estimate', 'little_parcel' ),
                      'default' => '/service/ride/estimate'
                      ),
                  'token_endpoint' => array(
                    'title' => __( 'Token endpoint', 'little_parcel' ),
                      'type' => 'uri',
                      'description' => __( 'Endpoing for Get token', 'little_parcel' ),
                      'default' => '/app/token'
                      ),
                  'username' => array(
                    'title' => __( 'Username', 'little_parcel' ),
                      'type' => 'text',
                      'description' => __( 'API username', 'little_parcel' ),
                      'default' => __('username', 'little_parcel')
                      ),
                  'password' => array(
                    'title' => __( 'Password', 'little_parcel' ),
                      'type' => 'password',
                      'description' => __( 'API password', 'little_parcel' ),
                      'default' => __('password', 'little_parcel')
                      ),
                     );
         
            }
          
              /**
               * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
               *
               * @access public
               * @param mixed $package
               * @return void
               */
              public function calculate_shipping( $package = [] ) {
                $cost = 20;
                $logger = wc_get_logger();
                $logger->error('-------------Log this item---------------');
                $estimated_cost = 0;
                $destinations_lat ='-1.2145631084373765';
                $destinations_lng ='36.88803169652222';
                $pickup_lat = '';
                $pickup_lng = '';
                $est_url = 'https://api.little.bz/service/ride/estimate?';
                $auth_url = 'https://api.little.bz/app/token';
                $auth_username = 'eff1c7a019b8e95b';
                $auth_pwd = 'yLWXcsbyhjMgLtVPCROMfw==';
                $auth_arg = [
                  'headers' => [
                    'Authorization' => 'Basic '  .base64_encode($auth_username.':'.$auth_pwd),
                    'accept' => 'application/json',
                    'content-type' => 'application/json'            
 
                  ]
                ];


              

                $pickup_locations = [
                  'Nairobi' => [
                    'lat' => '-1.297784833885268',
                    'lng' => '36.83662881186512'
                  ]
                  ,'Kisumu' => [
                    'lat' => '-0.09240995145906879',
                    'lng' => '34.75578484822132'
                  ]
                  ,'Mombasa' => [
                    'lat' => '-4.062755750219816',
                    'lng' => '39.667529510022995'
                  ]
                  ,'Eldoret' => [
                    'lat' => '',
                    'lng' => ''
                  ]
                ];

                $pickup_county = $package['destination']['city'];

                foreach($pickup_locations as $key => $pickup)
                {
                   if($key == $pickup_county){
                      $pickup_lat = $pickup['lat'];
                      $pickup_lng = $pickup['lng'];
                    }
                }

                /// generate little token
                $response = wp_remote_get($auth_url, $auth_arg);
                $response_body = wp_remote_retrieve_body($response);
                $result = json_decode($response_body);
                $token = $result->token;
              

                // get estimate
                $est_arg = [
                  'headers' => [
                    'Authorization' => 'Bearer '  .$token             
 
                  ]
                ];
                $est_data = [
                  'from_latlng' => $pickup_lat.','. $pickup_lng,
                  'to_latlng' => $destinations_lat.','. $destinations_lng,
                ];
                $est_query_url = $est_url . http_build_query($est_data);
                $est_response = wp_remote_get($est_query_url, $est_arg);
                $est_response_body = wp_remote_retrieve_body($est_response);
                $est_result = json_decode($est_response_body);

                error_log('-------------EST response-------');
                error_log(var_export($est_result,true));

                foreach($est_result->estimates as $estimate){
                  if($estimate->vehicle ==  'Parcels'){
                    $estimated_cost = $estimate->meta->min;
                  }
                }

                  
 
                $rate = array(
                  'id' => $this->id,
                  'label' => $this->title,
                  'cost' => $estimated_cost
              );
           
              $this->add_rate( $rate );
                          
              }
            }

        }

  }

  add_action( 'woocommerce_shipping_init', 'little_parcel_shipping_method' );

  function add_little_parcel_shipping_method( $methods ) {
      $methods[] = 'Little_Parcel_Shipping_Method';
      return $methods;
  }

  add_filter( 'woocommerce_shipping_methods', 'add_little_parcel_shipping_method' );

  add_action( 'init', 'register_my_new_order_statuses' );
function register_my_new_order_statuses() {
    register_post_status( 'wc-order-shipped', array(
        'label'                     => _x( 'Initiate pickup', 'Order status', 'woocommerce' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Initiate pickup <span class="count">(%s)</span>', 'Initiate pickup<span class="count">(%s)</span>', 'woocommerce' )
    ) );
}

add_filter( 'wc_order_statuses', 'my_new_wc_order_statuses' );
// Register in wc_order_statuses.
function my_new_wc_order_statuses( $order_statuses ) {
    $order_statuses['wc-order-shipped'] = _x( 'Initiate pickup', 'Order status', 'woocommerce' );
    return $order_statuses;
}

include_once WP_PLUGIN_DIR .'/woocommerce/woocommerce.php';
// register a custom post status 'awaiting-delivery' for Orders
add_action( 'init', 'register_custom_post_status', 20 );
function register_custom_post_status() {
    register_post_status( 'wc-awaiting-delivery', array(
        'label'                     => _x( 'Kargoya Verildi', 'Order status', 'woocommerce' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Kargoya Verildi <span class="count">(%s)</span>', 'Kargoya Verildi <span class="count">(%s)</span>', 'woocommerce' )
    ) );
}

// Adding custom status 'awaiting-delivery' to order edit pages dropdown
add_filter( 'wc_order_statuses', 'custom_wc_order_statuses' );
function custom_wc_order_statuses( $order_statuses ) {
    $order_statuses['wc-awaiting-delivery'] = _x( 'Kargoya Verildi', 'Order status', 'woocommerce' );
    return $order_statuses;
}

// Adding custom status 'awaiting-delivery' to admin order list bulk dropdown
add_filter( 'bulk_actions-edit-shop_order', 'custom_dropdown_bulk_actions_shop_order', 20, 1 );
function custom_dropdown_bulk_actions_shop_order( $actions ) {
    $actions['mark_awaiting-delivery'] = __( 'Kargoya Verildi', 'woocommerce' );
    return $actions;
}

// Adding action for 'awaiting-delivery'
add_filter( 'woocommerce_email_actions', 'custom_email_actions', 20, 1 );
function custom_email_actions( $actions ) {
    $actions[] = 'woocommerce_order_status_wc-awaiting-delivery';
    return $actions;
}

add_action( 'woocommerce_order_status_wc-awaiting-delivery', array( WC(), 'send_transactional_email' ), 10, 1 );

// Sending an email notification when order get 'awaiting-delivery' status
add_action('woocommerce_order_status_awaiting-delivery', 'awaiting_delivery_order_status_email_notification', 20, 2);
function awaiting_delivery_order_status_email_notification( $order_id, $order ) {
    // HERE below your settings
    $heading   = __('Kargoya Verildi','woocommerce');
    $subject   = '[{site_title}] Siparişiniz Kargoya Verildi ({order_number}) - {order_date}';

        // The email notification type
        $email_key   = 'WC_Email_Customer_Processing_Order';

        // Get specific WC_emails object
        $email_obj = WC()->mailer()->get_emails()[$email_key];

        // Sending the customized email
        $email_obj->trigger( $order_id );
}

// Customize email heading for this custom status email notification
add_filter( 'woocommerce_email_heading_customer_processing_order', 'email_heading_customer_awaiting_delivery_order', 10, 2 );
function email_heading_customer_awaiting_delivery_order( $heading, $order ){
    if( $order->has_status( 'awaiting-delivery' ) ) {
      error_log('----------awaiting-delivery---------email_heading_customer_awaiting_delivery_order----');
        $email_key   = 'WC_Email_Customer_Processing_Order'; // The email notification type
        $email_obj   = WC()->mailer()->get_emails()[$email_key]; // Get specific WC_emails object
        $heading_txt = __('Kargoya Verildi','woocommerce'); // New heading text

        return $email_obj->format_string( $heading_txt );
    }
    return $heading;
}

// Customize email subject for this custom status email notification
add_filter( 'woocommerce_email_subject_customer_processing_order', 'email_subject_customer_awaiting_delivery_order', 10, 2 );
function email_subject_customer_awaiting_delivery_order( $subject, $order ){
    if( $order->has_status( 'awaiting-delivery' ) ) {
      error_log('----------awaiting-delivery---------email_subject_customer_awaiting_delivery_order----');
        $email_key   = 'WC_Email_Customer_Processing_Order'; // The email notification type
        $email_obj   = WC()->mailer()->get_emails()[$email_key]; // Get specific WC_emails object
        $subject_txt = sprintf( __('[%s] Siparişiniz Kargoya Verildi (%s) - %s', 'woocommerce'), '{site_title}', '{order_number}', '{order_date}' ); // New subject text

        return $email_obj->format_string( $subject_txt );
    }
    return $subject;
}
}

?>