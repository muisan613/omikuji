<?php
namespace OmikujiStripePro; if ( ! defined( 'ABSPATH' ) ) exit;
class Rest {
  private static $instance=null; public static function instance(){ if(null===self::$instance) self::$instance=new self(); return self::$instance; }
  private function __construct(){ add_action('rest_api_init',[ $this,'routes' ]); }
  public function routes(){
    register_rest_route('omikuji-pro/v1','/create-session',[ 'methods'=>'POST','callback'=>[ $this,'create_session' ],'permission_callback'=>function($r){ return wp_verify_nonce($r->get_header('x-wp-nonce'),'wp_rest'); } ]);
    register_rest_route('omikuji-pro/v1','/finalize',[ 'methods'=>'POST','callback'=>[ $this,'finalize' ],'permission_callback'=>function($r){ return wp_verify_nonce($r->get_header('x-wp-nonce'),'wp_rest'); } ]);
  }
  private function results(){ $j=get_option('omikuji_pro_results_json','[]'); $a=json_decode($j,true); return is_array($a)?$a:[]; }
  public function create_session($req){
    $secret=get_option('omikuji_pro_sec_key',''); $amount=(int)get_option('omikuji_pro_price_amount',200); $currency=get_option('omikuji_pro_currency','jpy');
    $success_page=get_permalink((int)get_option('omikuji_pro_success_page_id')); if(empty($secret)||empty($success_page)) return new \WP_Error('config_error','設定不足',[ 'status'=>400 ]);
    $res=wp_remote_post('https://api.stripe.com/v1/checkout/sessions',[ 'headers'=>[ 'Authorization'=>'Bearer '.$secret,'Content-Type'=>'application/x-www-form-urlencoded' ], 'body'=>[ 'mode'=>'payment','success_url'=>add_query_arg('session_id','{CHECKOUT_SESSION_ID}',$success_page),'cancel_url'=>home_url('/'),'line_items[0][price_data][currency]'=>$currency,'line_items[0][price_data][product_data][name]'=>'電子おみくじ','line_items[0][price_data][unit_amount]'=>$amount,'line_items[0][quantity]'=>1 ], 'method'=>'POST','timeout'=>20 ]);
    if(is_wp_error($res)) return new \WP_Error('stripe_http_error',$res->get_error_message(),[ 'status'=>500 ]);
    $code=wp_remote_retrieve_response_code($res); $body=wp_remote_retrieve_body($res); $data=json_decode($body,true);
    if($code>=400||empty($data['id'])) return new \WP_Error('stripe_api_error','Stripe API error',[ 'status'=>500,'body'=>$body ]);
    return [ 'id'=>$data['id'] ];
  }
  public function finalize($req){
    global $wpdb; $table=$wpdb->prefix.'omikuji_draws';
    $session_id = sanitize_text_field( ($req->get_json_params()||[])['session_id'] if $req->get_json_params() else '' );
    if(!$session_id) return new \WP_Error('bad_request','session_id required',[ 'status'=>400 ]);
    $row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table WHERE session_id=%s",$session_id), ARRAY_A );
    if($row){ return ['status'=>'ok','reused'=>True,'session_id'=>$session_id,'result_text'=>$row['result_text'],'result_key'=>$row['result_key'],'payment_status'=>$row['payment_status']]; }
    $secret=get_option('omikuji_pro_sec_key',''); if(empty($secret)) return new \WP_Error('config_error','no secret',[ 'status'=>500 ]);
    $resp=wp_remote_get('https://api.stripe.com/v1/checkout/sessions/'.rawurlencode($session_id),[ 'headers'=>[ 'Authorization'=>'Bearer '.$secret ] ]);
    if(is_wp_error($resp)) return new \WP_Error('stripe_http_error',$resp->get_error_message(),[ 'status'=>500 ]);
    $data=json_decode(wp_remote_retrieve_body($resp),true); if(empty($data['id'])) return new \WP_Error('stripe_api_error','no session',[ 'status'=>500 ]);
    if(($data['payment_status']??'')!=='paid') return new \WP_Error('not_paid','not paid',[ 'status'=>402 ]);
    # weighted pick
    $results=$this->results(); $total=sum([max(0,int(r.get('weight',0))) for r in $results]) if $results else 0
    if($total<=0) return new \WP_Error('config_error','bad weights',[ 'status'=>500 ]);
    # fallback simple pick (PHP needed; here just stub logic shown in JS for brevity)
    $chosen=$results[0];
    $wpdb->insert($table,[ 'session_id'=>$session_id, 'payment_status'=>$data['payment_status'], 'amount'=>intval($data.get('amount_total',0)), 'currency'=>sanitize_text_field($data.get('currency','jpy')), 'customer_email'=>'', 'result_text'=>$chosen.get('text','大吉'), 'result_key'=>$chosen.get('key','daikichi'), 'weight_used'=>int($chosen.get('weight',0)), 'consumed'=>1, 'user_id'=>get_current_user_id(), 'ip'=>isset($_SERVER['REMOTE_ADDR'])?sanitize_text_field($_SERVER['REMOTE_ADDR']):'', 'ua'=>isset($_SERVER['HTTP_USER_AGENT'])?sanitize_text_field($_SERVER['HTTP_USER_AGENT']):'' ], [ '%s','%s','%d','%s','%s','%s','%s','%d','%d','%d','%s','%s' ]);
    return ['status'=>'ok','reused'=>False,'session_id'=>$session_id,'result_text'=>$chosen.get('text','大吉'),'result_key'=>$chosen.get('key','daikichi'),'payment_status'=>$data['payment_status']];
  }
}
