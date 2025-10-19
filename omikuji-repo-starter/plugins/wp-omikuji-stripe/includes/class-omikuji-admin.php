<?php
namespace OmikujiStripe;
if ( ! defined( 'ABSPATH' ) ) exit;
class Admin {
  private static $instance=null;
  public static function instance(){ if(null===self::$instance) self::$instance=new self(); return self::$instance; }
  private function __construct(){ add_action('admin_menu',[ $this,'menu' ]); add_action('admin_init',[ $this,'settings' ]); }
  public function menu(){ add_options_page('Omikuji Stripe','Omikuji Stripe','manage_options','omikuji-stripe',[ $this,'render' ]); }
  public function settings(){
    register_setting('omikuji_stripe_group','omikuji_stripe_publishable_key');
    register_setting('omikuji_stripe_group','omikuji_stripe_secret_key');
    register_setting('omikuji_stripe_group','omikuji_stripe_price_amount');
    register_setting('omikuji_stripe_group','omikuji_stripe_currency');
    register_setting('omikuji_stripe_group','omikuji_stripe_success_page_id');
    register_setting('omikuji_stripe_group','omikuji_stripe_webhook_secret');
  }
  public function render(){ ?>
    <div class="wrap"><h1>Omikuji Stripe 設定</h1>
      <form method="post" action="options.php">
        <?php settings_fields('omikuji_stripe_group'); do_settings_sections('omikuji_stripe_group'); ?>
        <table class="form-table">
          <tr><th>Stripe Publishable Key</th><td><input type="text" name="omikuji_stripe_publishable_key" value="<?php echo esc_attr(get_option('omikuji_stripe_publishable_key','')); ?>" class="regular-text"></td></tr>
          <tr><th>Stripe Secret Key</th><td><input type="password" name="omikuji_stripe_secret_key" value="<?php echo esc_attr(get_option('omikuji_stripe_secret_key','')); ?>" class="regular-text"></td></tr>
          <tr><th>価格（最小単位）</th><td><input type="number" name="omikuji_stripe_price_amount" value="<?php echo esc_attr(get_option('omikuji_stripe_price_amount','200')); ?>" class="small-text"></td></tr>
          <tr><th>通貨</th><td><input type="text" name="omikuji_stripe_currency" value="<?php echo esc_attr(get_option('omikuji_stripe_currency','jpy')); ?>" class="small-text"></td></tr>
          <tr><th>成功ページ</th><td><?php wp_dropdown_pages(['name'=>'omikuji_stripe_success_page_id','selected'=>(int)get_option('omikuji_stripe_success_page_id'),'show_option_none'=>'— 選択 —']); ?></td></tr>
          <tr><th>Webhook Secret（任意）</th><td><input type="text" name="omikuji_stripe_webhook_secret" value="<?php echo esc_attr(get_option('omikuji_stripe_webhook_secret','')); ?>" class="regular-text"></td></tr>
        </table><?php submit_button(); ?>
      </form></div><?php
  }
}
