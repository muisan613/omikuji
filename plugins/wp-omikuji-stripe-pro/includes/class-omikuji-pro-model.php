<?php
namespace OmikujiStripePro;
if ( ! defined( 'ABSPATH' ) ) exit;

class ResultRepository {
  public static function all_active(){
    global $wpdb; $table = $wpdb->prefix.'omikuji_results';
    $rows = $wpdb->get_results("SELECT rkey AS `key`, label, text, weight, media_type, media_url FROM $table WHERE active=1 ORDER BY sort ASC, id ASC", ARRAY_A);
    if (!is_array($rows)) $rows = [];
    foreach ($rows as &$r){
      $r['weight'] = isset($r['weight']) ? (int)$r['weight'] : 0;
      $r['key'] = isset($r['key']) ? (string)$r['key'] : '';
      $r['text'] = isset($r['text']) ? (string)$r['text'] : '';
    }
    return $rows;
  }
  public static function total_weight(){
    $sum = 0; foreach (self::all_active() as $r){ $w = (int)($r['weight'] ?? 0); if ($w>0) $sum += $w; }
    return $sum;
  }
}
