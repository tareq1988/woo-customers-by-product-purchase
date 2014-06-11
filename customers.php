<?php
/*
Plugin Name: WooCommerce Customers by Product Order
Plugin URI: http://tareq.wedevs.com/
Description: Description
Version: 0.1
Author: Tareq Hasan
Author URI: http://tareq.wedevs.com/
License: GPL2
*/

/**
 * Copyright (c) 2014 Tareq Hasan (email: tareq@wedevs.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WC_Customers_By_Product_Order class
 *
 * @class WC_Customers_By_Product_Order The class that holds the entire WC_Customers_By_Product_Order plugin
 */
class WC_Customers_By_Product_Order {

    /**
     * Constructor for the WC_Customers_By_Product_Order class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );

        add_filter( 'woocommerce_admin_reports', array( $this, 'woocommerce_admin_reports' ) );
    }

    /**
     * Initializes the WC_Customers_By_Product_Order() class
     *
     * Checks for an existing WC_Customers_By_Product_Order() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WC_Customers_By_Product_Order();
        }

        return $instance;
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'wc-customer-by-order', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    function woocommerce_admin_reports( $reports ) {
        $reports['customers']['reports']['customer-list-product'] = array(
            'title'       => __( 'Customer List by Product', 'wc-customer-by-order' ),
            'description' => '',
            'hide_title'  => true,
            'callback'    => array( $this, 'get_report' )
        );

        return $reports;
    }

    function get_report() {
        $products = $this->get_products();

        include dirname( __FILE__ ) . '/view.php';
    }

    public function get_products() {
        $the_query = new WP_Query( array(
            'post_type'      => 'product',
            'posts_per_page' => -1
        ) );

        $options = array( '-1' => __( '- select product -', 'wc-customer-by-order' ) );
        $products = $the_query->get_posts();

        foreach ($products as $product) {
            $options[$product->ID] = $product->post_title;
        }

        return $options;
    }

    function get_users( $product_id ) {
        global $wpdb;

        $sql = "SELECT o.ID as order_id, u.ID as user_id, u.user_email, terms.name as order_status, o.post_date
                FROM $wpdb->posts as o
                LEFT JOIN $wpdb->postmeta um ON o.ID = um.post_id
                LEFT JOIN $wpdb->users u ON um.meta_value = u.ID

                LEFT JOIN {$wpdb->prefix}woocommerce_order_items oi ON oi.order_id = o.ID
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id

                LEFT JOIN {$wpdb->term_relationships} rel ON oi.order_id = rel.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tax ON rel.term_taxonomy_id = tax.term_taxonomy_id
                LEFT JOIN {$wpdb->terms} terms ON tax.term_id = terms.term_id

                WHERE o.post_type = 'shop_order' AND

                    um.meta_key = '_customer_user' AND
                    oim.meta_key = '_product_id' AND
                    oim.meta_value = %d

                ORDER BY o.ID DESC";

        $results = $wpdb->get_results( $wpdb->prepare( $sql, $product_id ) );

        return $results;
    }

} // WC_Customers_By_Product_Order

$baseplugin = WC_Customers_By_Product_Order::init();