<?php
namespace OmikujiStripePro;
if ( ! defined( 'ABSPATH' ) ) exit;

class Rest {
  private static $instance = null;
  public static function instance(){ if (null===self::$instance) self::$instance = new self(); return self::$instance; }
  private function __construct(){ add_action('rest_api_init',[ $this,'routes' ]); }

  public function routes(){
    register_rest_route(
      'omikuji-pro/v1',
      '/create-session',
      [
        'methods'  => 'POST',
        'callback' => [ $this, 'create_session' ],
        'permission_callback' => function($req){ return wp_verify_nonce( $req->get_header('x-wp-nonce'), 'wp_rest'); }
      ]
    );
    register_rest_route(
      'omikuji-pro/v1',
      '/finalize',
      [
        'methods'  => 'POST',
        'callback' => [ $this, 'finalize' ],
        'permission_callback' => function($req){ return wp_verify_nonce( $req->get_header('x-wp-nonce'), 'wp_rest'); }
      ]
    );
  }

  private function results_conf(){
    $json = get_option('omikuji_pro_results_json','[]');
    $arr = json_decode($json,true);
    return is_array($arr) ? $arr : [];
  }

  public function create_session($req){
    $secret  = get_option('omikuji_pro_sec_key','');
    $amount  = (int) get_option('omikuji_pro_price_amount',200);
    $currency= get_option('omikuji_pro_currency','jpy');
    $success = get_permalink( (int) get_option('omikuji_pro_success_page_id') );
    if(!$secret || !$success) return new \WP_Error('config','設定不足',[ 'status'=>400 ]);

    if($currency === 'jpy' && $amount < 50){
      return new \WP_Error('amount_small','価格が小さすぎます（JPYは50以上）',[ 'status'=>400 ]);
    }

    $lock_key = 'omikuji_cs_lock_' . md5( (string)($_SERVER['REMOTE_ADDR']??'') . (string)($_SERVER['HTTP_USER_AGENT']??'') );
    if ( get_transient($lock_key) ) return new \WP_Error('rate_limit','少し待ってからお試しください',[ 'status'=>429 ]);
    set_transient($lock_key, 1, 10);

    $headers = [
      'Authorization' => 'Bearer '.$secret,
      'Content-Type'  => 'application/x-www-form-urlencoded',
      'Idempotency-Key' => wp_generate_uuid4(),
    ];
    $body = [
      'mode' => 'payment',
      'success_url' => add_query_arg('session_id','{CHECKOUT_SESSION_ID}', $success),
      'cancel_url'  => home_url('/'),
      'payment_method_types[0]' => 'card',
      'line_items[0][price_data][currency]' => $currency,
      'line_items[0][price_data][product_data][name]' => '電子おみくじ',
      'line_items[0][price_data][unit_amount]' => $amount,
      'line_items[0][quantity]' => 1,
    ];

    $res = wp_remote_post('https://api.stripe.com/v1/checkout/sessions', [
      'headers'=>$headers, 'body'=>$body, 'timeout'=>25, 'method'=>'POST'
    ]);
    if(is_wp_error($res)) return new \WP_Error('http',$res->get_error_message(),[ 'status'=>500 ]);
    $code = wp_remote_retrieve_response_code($res);
    $body = wp_remote_retrieve_body($res);
    $data = json_decode($body,true);
    if($code>=400 || empty($data['id'])){
      return new \WP_Error('stripe','Stripe API error', [ 'status'=>500, 'body'=>$body ]);
    }
    return [ 'id'=>$data['id'] ];
  }

  private function weighted_pick($results){
    $sum = 0;
    if (is_array($results)) {
      foreach ($results as $r) {
        $w = isset($r['weight']) ? (int)$r['weight'] : 0;
        if ($w > 0) { $sum += $w; }
      }
    }
    if ($sum <= 0) {
      return $results[0] ?? ['key'=>'daikichi','text'=>'大吉','weight'=>1];
    }
    try { $n = random_int(1, $sum); } catch (\Exception $e) { $n = mt_rand(1, $sum); }
    foreach ($results as $r) {
      $w = isset($r['weight']) ? (int)$r['weight'] : 0;
      if ($w <= 0) continue;
      $n -= $w;
      if ($n <= 0) return $r;
    }
    return end($results);
  }

  public function finalize($req){
    global $wpdb; $table = $wpdb->prefix.'omikuji_draws';

    $params = $req->get_json_params();
    $session_id = sanitize_text_field( $params['session_id'] ?? '' );
    if(!$session_id) return new \WP_Error('bad_request','session_id required',[ 'status'=>400 ]);

    $row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table WHERE session_id=%s", $session_id), ARRAY_A );
    if($row){
      return [ 'status'=>'ok','reused'=>true,'session_id'=>$session_id,'result_text'=>$row['result_text'],'result_key'=>$row['result_key'],'payment_status'=>$row['payment_status'] ];
    }

    $secret = get_option('omikuji_pro_sec_key','');
    if(!$secret) return new \WP_Error('config','Secret未設定',[ 'status'=>500 ]);

    $resp = wp_remote_get('https://api.stripe.com/v1/checkout/sessions/'.rawurlencode($session_id), [
      'headers'=>[ 'Authorization'=>'Bearer '.$secret ],
      'timeout'=>20
    ]);
    if(is_wp_error($resp)) return new \WP_Error('http',$resp->get_error_message(),[ 'status'=>500 ]);
    $data = json_decode( wp_remote_retrieve_body($resp), true );
    if( empty($data['id']) ) return new \WP_Error('stripe','Session not found',[ 'status'=>404 ]);
    if( ($data['payment_status'] ?? '') !== 'paid' ){
      return new \WP_Error('not_paid','支払いが完了していません',[ 'status'=>402 ]);
    }

    $results = $this->results_conf();
    if(!is_array($results) || !count($results)) $results = [['key'=>'daikichi','text'=>'大吉','weight'=>1]];
    $chosen = $this->weighted_pick($results);

    $wpdb->insert($table, [
      'session_id'     => $session_id,
      'payment_status' => sanitize_text_field($data['payment_status'] ?? ''),
      'amount'         => (int)($data['amount_total'] ?? 0),
      'currency'       => sanitize_text_field($data['currency'] ?? 'jpy'),
      'result_text'    => sanitize_text_field($chosen['text'] ?? '大吉'),
      'result_key'     => sanitize_text_field($chosen['key'] ?? 'daikichi'),
      'weight_used'    => (int)($chosen['weight'] ?? 0),
      'user_id'        => get_current_user_id(),
      'ip'             => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '',
      'ua'             => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
    ], [ '%s','%s','%d','%s','%s','%s','%d','%d','%s','%s' ] );

    return [ 'status'=>'ok','reused'=>false,'session_id'=>$session_id,'result_text'=>$chosen['text'],'result_key'=>$chosen['key'],'payment_status'=>$data['payment_status'] ];
  }
}
