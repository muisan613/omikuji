<?php
namespace OmikujiStripe;
if ( ! defined( 'ABSPATH' ) ) exit;
class Rest {
  private static $instance=null;
  public static function instance(){ if(null===self::$instance) self::$instance=new self(); return self::$instance; }
  private function __construct(){ add_action('rest_api_init',[ $this,'routes' ]); }
  public function routes(){
    register_rest_route('omikuji/v1','/create-session',[
      'methods'=>'POST',
      'callback'=>[ $this,'create_session' ],
      'permission_callback'=>'__return_true'
    ]);
  }
  public function create_session($request){
    $secret = get_option('omikuji_stripe_secret_key','');
    $amount = (int)get_option('omikuji_stripe_price_amount',200);
    $currency = get_option('omikuji_stripe_currency','jpy');
    $success_page = get_permalink( (int)get_option('omikuji_stripe_success_page_id') );
    if(empty($secret)) return new \WP_Error('omikuji_config_error','Stripe Secret Keyが未設定です。',[ 'status'=>400 ]);
    if(empty($success_page)) return new \WP_Error('omikuji_config_error','成功ページが未設定です。',[ 'status'=>400 ]);

    $res = wp_remote_post('https://api.stripe.com/v1/checkout/sessions',[
      'headers'=>[ 'Authorization'=>'Bearer '.$secret, 'Content-Type'=>'application/x-www-form-urlencoded' ],
      'body'=>[
        'mode'=>'payment',
        'success_url'=> add_query_arg('session_id','{CHECKOUT_SESSION_ID}', $success_page ),
        'cancel_url'=> home_url('/'),
        'line_items[0][price_data][currency]' => $currency,
        'line_items[0][price_data][product_data][name]' => '電子おみくじ',
        'line_items[0][price_data][unit_amount]' => $amount,
        'line_items[0][quantity]' => 1
      ],
      'method'=>'POST','timeout'=>20
    ]);
    if(is_wp_error($res)) return new \WP_Error('stripe_http_error',$res->get_error_message(),[ 'status'=>500 ]);
    $code = wp_remote_retrieve_response_code($res);
    $body = wp_remote_retrieve_body($res);
    $data = json_decode($body,true);
    if($code>=400) return new \WP_Error('stripe_api_error','Stripe APIエラー: HTTP '.$code,[ 'status'=>500, 'body'=>$body ]);
    if(empty($data['id'])) return new \WP_Error('stripe_api_error','セッション作成に失敗しました。',[ 'status'=>500, 'body'=>$body ]);
    return [ 'id'=>$data['id'] ];
  }
}
