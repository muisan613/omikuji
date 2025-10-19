<?php
namespace OmikujiStripePro; if ( ! defined( 'ABSPATH' ) ) exit;
class Install {
  public static function activate(){
    global $wpdb; $table = $wpdb->prefix.'omikuji_draws'; $charset=$wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      session_id VARCHAR(255) NOT NULL,
      payment_status VARCHAR(50) DEFAULT '',
      amount INT DEFAULT 0,
      currency VARCHAR(10) DEFAULT 'jpy',
      customer_email VARCHAR(190) DEFAULT '',
      result_text TEXT,
      result_key VARCHAR(50) DEFAULT '',
      weight_used INT DEFAULT 0,
      consumed TINYINT(1) DEFAULT 1,
      user_id BIGINT UNSIGNED DEFAULT 0,
      ip VARCHAR(100) DEFAULT '',
      ua TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY session_unique (session_id)
    ) $charset;";
    require_once ABSPATH.'wp-admin/includes/upgrade.php'; dbDelta($sql);
    add_option('omikuji_pro_results_json', json_encode([
      ["key"=>"daikichi","label"=>"大吉","weight"=>10,"text"=>"大吉 🎉 最高の運気！大事な一歩を踏み出そう"],
      ["key"=>"chukichi","label"=>"中吉","weight"=>20,"text"=>"中吉 😊 いい流れ。小さな準備が実る日"],
      ["key"=>"shokichi","label"=>"小吉","weight"=>25,"text"=>"小吉 🍀 穏やかな運気。感謝が鍵"],
      ["key"=>"kichi","label"=>"吉","weight"=>25,"text"=>"吉 ✨ 焦らず、整えれば道は開く"],
      ["key"=>"suekichi","label"=>"末吉","weight"=>15,"text"=>"末吉 🌱 学びの時。種まきで未来が変わる"],
      ["key"=>"kyo","label"=>"凶","weight"=>5,"text"=>"凶 🌧 無理せず休息を。体を労って吉"]
    ], JSON_UNESCAPED_UNICODE ));
  }
  public static function uninstall(){}
}
