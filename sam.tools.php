<?php
/**
 * Author: minimus
 * Date: 23.12.13
 * Time: 18:25
 */
if(!class_exists('SamMailer')) {
  class SamMailer {
    private $options;
    private $advertisersList;
    private $month;

    public function __construct( $settings ) {
      $this->options = $settings;
      $this->advertisersList = self::getAdvertisersList();
    }

    private function getAdvertisersList() {
      global $wpdb;

      $aTable = $wpdb->prefix . 'sam_ads';

      $sql = "SELECT sa.adv_nick, sa.adv_name, sa.adv_mail FROM $aTable sa WHERE sa.adv_mail > '' GROUP BY sa.adv_mail;";
      $list = $wpdb->get_results($sql, ARRAY_A);

      return $list;
    }

    private function parseText( $text, $advert ) {
      $out = str_replace('[name]', $advert['adv_name'], $text);
      $out = str_replace('[site]', get_bloginfo('name'), $out);
      $out = str_replace('Simple Ads Manager', "<a href='http://www.simplelib.com/?p=480' target='_blank'>Simple Ads Manager</a>", $out);
      $out = str_replace('[month', $this->month, $out);

      return $out;
    }

    private function buildMessage( $user ) {
      global $wpdb;

      $options = $this->options;
      $aTable = $wpdb->prefix . 'sam_ads';
      $sTable = $wpdb->prefix . 'sam_stats';
      $greeting = self::parseText($options['mail_greeting'], $user);
      $textBefore = self::parseText($options['mail_text_before'], $user);
      $textAfter = self::parseText($options['mail_text_after'], $user);
      $warning = self::parseText($options['mail_warning'], $user);
      $message = self::parseText($options['mail_message'], $user);

      $date = new DateTime('now');
      $date->modify('-1 month');
      $month = $date->format('Y-m-d');
      $monthL = $date->format('Y-m-t');
      $this->month = $date->format('F');

      $sql = "SELECT
                sa.id,
                sa.pid,
                sa.name,
                sa.description,
                @ad_hits := (SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 0) AS ad_hits,
                @ad_clicks := (SELECT COUNT(*) FROM $sTable ss WHERE (EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM ss.event_time)) AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 1) AS ad_clicks,
                (sa.cpm / @ad_hits * 1000) AS e_cpm,
                sa.cpc AS e_cpc,
                (@ad_clicks / @ad_hits * 100) AS e_ctr
              FROM $aTable sa
              WHERE sa.adv_mail = %s AND sa.trash = FALSE AND NOT (sa.ad_schedule AND sa.ad_end_date <= %s);";
      $ads = $wpdb->get_results($wpdb->prepare($sql, $month, $month, $user['adv_mail'], $monthL), ARRAY_A);

      $mess = '';

      if(!empty($ads) && is_array($ads)) {
        $sql = "SELECT COUNT(*)
                  FROM $sTable ss
                  INNER JOIN $aTable sa
                    ON ss.id = sa.id
                  WHERE sa.adv_mail = %s
                    AND sa.trash = FALSE
                    AND (EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM ss.event_time))
                    AND ss.event_type = %d";
        $hits = $wpdb->get_var($wpdb->prepare($sql, $user['adv_mail'], $month, 0));
        $clicks = $wpdb->get_var($wpdb->prepare($sql, $user['adv_mail'], $month, 1));

        $mess .= "
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
<head>
  <title>Ad campaign report</title>
  <style type='text/css'>
    .sam-table {
          border-collapse: separate;
      border-spacing: 1px;
      background-color: #CDCDCD;
      margin: 10px 0 15px 0;
      font-size: 9pt;
      font-family: Arial,sans-serif;
      width: 100%;
      text-align: left;
      line-height: 20px;
    }
    .sam-table th {
      background-color: #E6EEEE;
      border: 1px solid #FFFFFF;
      padding: 4px;
      color: #3D3D3D!important;
    }
    .sam-table td {
      color: #3D3D3D;
      padding: 4px;
      background-color: #FFFFFF;
      vertical-align: top;
    }
    .even {border: 1px solid #ddd;}
    .even td {background-color: #FFFFFF;}
    .odd td {background-color: #FFFFE8;}
    .w25 {
      width: 25%;
    }
    .w10 {
      width: 10%;
    }
    .mess {
      font-family: Arial, Helvetica, Tahoma, sans-serif;
      font-size: 11px;
    }
    .total {font-size: 13px}
  </style>
</head>
<body>
<p>{$greeting}</p>
<p>{$textBefore}</p>
<table class='sam-table'>
  <thead>
    <tr>
      <th class='w25'>Name</th>
      <th class='w25'>Description</th>
      <th class='w10'>Hits</th>
      <th class='w10'>Clicks</th>
      <th class='w10'>CPM</th>
      <th class='w10'>CPC</th>
      <th class='w10'>CTR</th>
    </tr>
  </thead>
  <tbody>";
        $k = 0;
        foreach($ads as $ad) {
          $class = ( ($k % 2) == 1 ) ? 'odd' : 'even';
          $mess .= "<tr class='{$class}'><td>{$ad['name']}</td><td>{$ad['description']}</td><td>{$ad['ad_hits']}</td><td>{$ad['ad_clicks']}</td><td>{$ad['e_cpm']}</td><td>{$ad['e_cpc']}</td><td>{$ad['e_ctr']}</td></tr>";
          $k++;
        }
        $mess .= "</tbody></table>";
        $mess .= "
<p class='total'>Hits: {$hits}</p>
<p class='total'>Clicks: {$clicks}</p>
<p>{$textAfter}</p>
<p class='mess'>{$warning}</p>
<p class='mess'>{$message}</p>
</body>
</html>";
      }

      return $mess;
    }

    public function setContentType() {
      return 'text/html';
    }

    public function sendMails() {
      $k = 0;
      $advertisers = $this->advertisersList;
      if(!empty($advertisers) && is_array($advertisers)) {
        $headers = 'Content-type: text/html; charset=UTF-8' . "\r\n";
        //$headers .= 'From: Tests <wordpress@simplelib.com>' . "\r\n";
        foreach($advertisers as $adv) {
          $subject = self::parseText($this->options['mail_subject'], $adv);
          $message = self::buildMessage($adv);
          wp_mail($adv['adv_mail'], $subject, $message, $headers);
          $k++;
        }
      }
      return $k;
    }
  }
}