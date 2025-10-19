<?php
namespace OmikujiStripePro; if ( ! defined( 'ABSPATH' ) ) exit;
class Webhook {
  private static $instance=null; public static function instance(){ if(null===self::$instance) self::$instance=new self(); return self::$instance; }
  private function __construct(){ add_action('rest_api_init',[ $this,'routes' ]); }
  public function routes(){ register_rest_route('omikuji-pro/v1','/webhook',[ 'methods'=>'POST','callback'=>[ $this,'handle' ],'permission_callback'=>'__return_true' ]); }
  public function handle($request){ $payload=$request->get_body(); return ['received'=>true]; }
}
