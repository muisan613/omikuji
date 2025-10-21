<?php
namespace OmikujiStripePro;
if (!defined('ABSPATH')) exit;

class Rest {
  private static $instance=null;
  public static function instance(){ if(self::$instance===null) self::$instance=new self(); return self::$instance; }
  private function __construct(){ add_action('rest_api_init', [$this,'routes']); }

  public function routes(){
    register_rest_route('omikuji-pro/v1','/create-session',[
      'methods'=>'POST',
      'callback'=>[$this,'create_session'],
      'permission_callback'=>'__return_true'
    ]);
    register_rest_route('omikuji-pro/v1','/finalize',[
      'methods'=>'POST',
      'callback'=>[$this,'finalize'],
      'permission_callback'=>'__return_true'
    ]);
  }

  public function create_session($req){
    $secret = get_option('omikuji_pro_sec_key','');
    $pub    = get_option('omikuji_pro_pub_key','');
    $amount = (int)get_option('omikuji_pro_price_amount', 200);
    $currency = get_option('omikuji_pro_currency','jpy');
    $success = get_permalink((int)get_option('omikuji_pro_success_page_id'));
    if (!$secret || !$pub || !$success) return new \WP_Error('config','キー/成功ページが未設定',[ 'status'=>400 ]);

    $headers = [
      'Authorization' => 'Bearer '.$secret,
      'Content-Type'  => 'application/x-www-form-urlencoded'
    ];
    $body = [
      'mode' => 'payment',
      'success_url' => add_query_arg('session_id','{CHECKOUT_SESSION_ID}', $success),
      'cancel_url'  => home_url('/'),
      'payment_method_types[0]' => 'card',
      'line_items[0][price_data][currency]' => $currency,
      'line_items[0][price_data][product_data][name]' => '電子おみくじ（Pro）',
      'line_items[0][price_data][unit_amount]' => $amount,
      'line_items[0][quantity]' => 1,
    ];
    $res = wp_remote_post('https://api.stripe.com/v1/checkout/sessions',[ 'headers'=>$headers, 'body'=>$body ]);
    if (is_wp_error($res)) return $res;
    $data = json_decode(wp_remote_retrieve_body($res), true);
    if (empty($data['id'])) return new \WP_Error('stripe_error','セッション作成に失敗',[ 'status'=>500, 'data'=>$data ]);
    return [ 'id'=>$data['id'] ];
  }

  public function finalize($req){
    $params = $req->get_json_params();
    $session_id = sanitize_text_field($params['session_id'] ?? '');
    if (!$session_id) return new \WP_Error('bad_request','session_id required',[ 'status'=>400 ]);

    $secret = get_option('omikuji_pro_sec_key','');
    $res = wp_remote_get('https://api.stripe.com/v1/checkout/sessions/'.rawurlencode($session_id), [
      'headers'=>['Authorization'=>'Bearer '.$secret]
    ]);
    if (is_wp_error($res)) return $res;
    $data = json_decode(wp_remote_retrieve_body($res), true);
    if (empty($data['payment_status']) || $data['payment_status'] !== 'paid') {
      return new \WP_Error('not_paid','支払い未完了',[ 'status'=>402, 'data'=>$data ]);
    }

    // ここで本来はDB抽選するが、まずは固定で検証可能に
    $results = ['大吉 🎉','中吉 😊','小吉 🍀','吉 🙂','末吉 😌'];
    $choice = $results[array_rand($results)];
    return ['status'=>'ok','result_text'=>$choice,'payment_status'=>$data['payment_status']];
  }
}
