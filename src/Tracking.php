<?php

/**
 * Salesfire Tracking
 *
 * @package     Salesfire_Tracking
 * @version     1.0.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Salesfire_Tracking
{
    private $script = '';

    private $events = [];

    private $data_layer_injected = false;

    public function init() {
        if (wp_doing_cron() || is_admin() || ! get_option('salesfire_tracking')) {
            return;
        }

        $this->base_tag();
        $this->add_salesfire_settings();

        if (class_exists( 'WooCommerce' )) {
            if ( ! wp_doing_ajax() ) {
                add_action( 'wp', array( $this, 'product_event' ) );
            }

            add_action( 'woocommerce_before_thankyou', array( $this, 'order_event' ), 10, 1 );
        }

        // Print to head.
        add_action( 'wp_head', array( $this, 'print_script' ) );
    }

    private function base_tag() {
        if ($uuid = get_option('salesfire_uuid')) {
            $this->script .= sprintf(
                '<script async src="https://cdn.salesfire.co.uk/code/%s.js"></script>',
                esc_attr( $uuid )
            );
        }
    }

    /**
     * Enqueues the product page event code for printing.
     *
     * @return void
     */
    public function product_event()
    {
        if ( is_product() ) {
            $product = wc_get_product();

            $data = (object)[
                "ecommerce" => (object)[
                    "view" => (object)[
                        "sku"           => $this->get_product_parent_id($product),
                        "name"          => $product->get_title(),
                        "variant"       => $product->get_description(),
                        "price"         => $product->get_price(),
                        "currency"      => "GBP",
                    ]
                ]
            ];

            $this->add_event( $data );
        }
    }

    /**
     * Enqueues the order event code for printing.
     *
     * @return void
     */
    public function order_event($order_id)
    {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        $data = (object)[
            "ecommerce" => (object)[
                "purchase" => (object)[
                    "id"        => $order_id,
                    "revenue"   => $order->get_subtotal() - $order->get_total_discount(),
                    "shipping"  => $order->get_shipping_total(),
                    "tax"       => $order->get_tax_totals(),
                    "currency"  => $order->get_currency(),
                    "products"  => [],
                ],
            ],
        ];

        foreach ( $order->get_items() as $order_item ) {
            if ( ! method_exists( $order_item, 'get_product' ) ) {
                continue;
            }

            $product = $order_item->get_product();

            $data->ecommerce->purchase->products[] = (object)[
                "sku"           => $this->get_product_id($product),
                "parent_sku"    => $this->get_product_parent_id($product),
                "name"          => $product->get_title(),
                "variant"       => $product->get_description(),
                "price"         => $product->get_price(),
                "currency"      => $order->get_currency(),
                "quantity"      => $order_item->get_quantity(),
            ];
        }

        $this->add_event( $data );
    }

    /**
     * Enqueues or prints the given event, depending on if
     * we have already run wp_head or not.
     *
     * @param string $event The event's type.
     * @param array  $data  The data to be passed to the JS function.
     *
     * @return void
     */
    private function add_event( $event )
    {
        if (did_action('wp_head')) {
            $this->inject_layer();
            echo '<script>window.sfDataLayer.push(' . json_encode($event) . ');</script>';
        } else {
            $this->events[] = $event;
        }
    }

    public function inject_layer()
    {
        if (! $this->data_layer_injected) {
            echo '<script>window.sfDataLayer = window.sfDataLayer || [];</script>';

            $this->data_layer_injected = true;
        }
    }

    public function print_script()
    {
        if (! empty($this->script)) {
            echo $this->script;

            if (! empty($this->events)) {
                $this->inject_layer();

                echo '<script>';

                foreach ($this->events as $event) {
                    echo 'window.sfDataLayer.push(' . json_encode($event) . ');';
                }

                echo '</script>';
            }
        }
    }

    /**
     * Create an event to be stored in our datalayer for additional information which can be accesed from the client script.
     */
    public function add_salesfire_settings()
    {
        $this->events[] = (object)[
            "salesfire" => (object) [
                "platform" => "woocommerce",
            ],
        ];
    }

    protected function get_product_parent_id($product)
    {
        return $product->is_type( 'variation' )
            ? $product->get_parent_id()
            : $product->get_id();
    }

    protected function get_product_id($product)
    {
        return $product->get_id();
    }
}
