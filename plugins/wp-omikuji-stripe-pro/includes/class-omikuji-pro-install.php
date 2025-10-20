<?php
namespace OmikujiStripePro;
if ( ! defined( 'ABSPATH' ) ) exit;

class Install {
  const SCHEMA_VERSION = '120';

  public static function activate(){
    self::create_tables();
    self::seed_results_if_empty();
    update_option('omikuji_pro_schema', self::SCHEMA_VERSION);
  }

  public static function maybe_upgrade(){
    $cur = get_option('omikuji_pro_schema');
    if ($cur !== self::SCHEMA_VERSION){
      self::create_tables();
      self::seed_results_if_empty();
      update_option('omikuji_pro_schema', self::SCHEMA_VERSION);
    }
  }

  private static function create_tables(){
    global $wpdb;
    $draws = $wpdb->prefix . 'omikuji_draws';
    $results = $wpdb->prefix . 'omikuji_results';
    $charset = $wpdb->get_charset_collate();

    $sql1 = "CREATE TABLE $draws (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      session_id VARCHAR(255) NOT NULL,
      payment_status VARCHAR(50) DEFAULT '',
      amount INT DEFAULT 0,
      currency VARCHAR(10) DEFAULT 'jpy',
      result_key VARCHAR(50) DEFAULT '',
      result_text TEXT,
      weight_used INT DEFAULT 0,
      user_id BIGINT UNSIGNED DEFAULT 0,
      ip VARCHAR(100) DEFAULT '',
      ua TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY  (id),
      UNIQUE KEY session_unique (session_id)
    ) $charset;";

    $sql2 = "CREATE TABLE $results (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      rkey VARCHAR(64) NOT NULL,
      label VARCHAR(191) NOT NULL,
      text TEXT NOT NULL,
      weight INT NOT NULL DEFAULT 0,
      media_type VARCHAR(20) DEFAULT '',
      media_url TEXT,
      sort INT NOT NULL DEFAULT 0,
      active TINYINT(1) NOT NULL DEFAULT 1,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY rkey_unique (rkey)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql1);
    dbDelta($sql2);
  }

  public static function seed_results_if_empty(){
    global $wpdb; $table = $wpdb->prefix . 'omikuji_results';
    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
    if ($count > 0) return;

    $defaults = [
      ['rkey'=>'daikichi','label'=>'大吉','text'=>'大吉 🎉 最高の運気！大事な一歩を踏み出そう','weight'=>10,'sort'=>10],
      ['rkey'=>'chukichi','label'=>'中吉','text'=>'中吉 😊 いい流れ。小さな準備が実る日','weight'=>20,'sort'=>20],
      ['rkey'=>'shokichi','label'=>'小吉','text'=>'小吉 🍀 穏やかな運気。感謝が鍵','weight'=>25,'sort'=>30],
      ['rkey'=>'kichi','label'=>'吉','text'=>'吉 ✨ 焦らず、整えれば道は開く','weight'=>25,'sort'=>40],
      ['rkey'=>'suekichi','label'=>'末吉','text'=>'末吉 🌱 学びの時。種まきで未来が変わる','weight'=>15,'sort'=>50],
      ['rkey'=>'kyo','label'=>'凶','text'=>'凶 🌧 無理せず休息を。体を労って吉','weight'=>5,'sort'=>60],
    ];
    foreach ($defaults as $r){
      $wpdb->insert($table, [
        'rkey' => $r['rkey'],
        'label'=> $r['label'],
        'text' => $r['text'],
        'weight'=> (int)$r['weight'],
        'sort' => (int)$r['sort'],
        'active'=> 1,
      ], [ '%s','%s','%s','%d','%d','%d' ]);
    }
  }
}
