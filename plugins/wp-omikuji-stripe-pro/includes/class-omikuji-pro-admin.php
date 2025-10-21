<?php
namespace OmikujiStripePro;
if (!defined('ABSPATH')) exit;

class Admin {
  private static $instance=null;
  public static function instance(){ if(self::$instance===null) self::$instance=new self(); return self::$instance; }

  private function __construct(){
    add_action('admin_menu', [$this,'menu']);
    add_action('admin_init', [$this,'settings']);
  }

  public function menu(){
    add_menu_page('Omikuji Pro','Omikuji Pro','manage_options','omikuji-pro',[$this,'render'],'dashicons-database',60);
  }

  public function settings(){
    register_setting('omikuji_pro_group','omikuji_pro_pub_key');
    register_setting('omikuji_pro_group','omikuji_pro_sec_key');
    register_setting('omikuji_pro_group','omikuji_pro_price_amount'); // in JPY
    register_setting('omikuji_pro_group','omikuji_pro_currency');
    register_setting('omikuji_pro_group','omikuji_pro_success_page_id');
    register_setting('omikuji_pro_group','omikuji_pro_drawing_image_url');
    register_setting('omikuji_pro_group','omikuji_pro_drawing_video_url');
  }

  public function render(){
    ?>
    <div class="wrap">
      <h1>Omikuji Stripe Pro 設定</h1>
      <form method="post" action="options.php">
        <?php settings_fields('omikuji_pro_group'); do_settings_sections('omikuji_pro_group'); ?>
        <table class="form-table">
          <tr><th>Publishable Key</th>
              <td><input type="text" name="omikuji_pro_pub_key" value="<?php echo esc_attr(get_option('omikuji_pro_pub_key','')); ?>" class="regular-text"></td></tr>
          <tr><th>Secret Key</th>
              <td><input type="password" name="omikuji_pro_sec_key" value="<?php echo esc_attr(get_option('omikuji_pro_sec_key','')); ?>" class="regular-text"></td></tr>
          <tr><th>価格（税別・整数, JPY）</th>
              <td><input type="number" name="omikuji_pro_price_amount" value="<?php echo esc_attr(get_option('omikuji_pro_price_amount','200')); ?>" class="small-text"> 円</td></tr>
          <tr><th>通貨</th>
              <td><input type="text" name="omikuji_pro_currency" value="<?php echo esc_attr(get_option('omikuji_pro_currency','jpy')); ?>" class="small-text"></td></tr>
          <tr><th>成功ページ</th>
              <td><?php wp_dropdown_pages(['name'=>'omikuji_pro_success_page_id','selected'=>(int)get_option('omikuji_pro_success_page_id'),'show_option_none'=>'—選択—']); ?></td></tr>
          <tr><th>引いている途中の画像URL</th>
              <td><input type="text" name="omikuji_pro_drawing_image_url" value="<?php echo esc_attr(get_option('omikuji_pro_drawing_image_url','')); ?>" class="regular-text"></td></tr>
          <tr><th>引いている途中の動画URL（mp4）</th>
              <td><input type="text" name="omikuji_pro_drawing_video_url" value="<?php echo esc_attr(get_option('omikuji_pro_drawing_video_url','')); ?>" class="regular-text"></td></tr>
        </table>
        <?php submit_button(); ?>
      </form>
    </div>
    <?php
  }
}
