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
    private $first;
    private $last;
    private $error;

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

    private function getSiteInfo( $info = 'name' ) {
      $infos = array(
        'name' => 'blogname',
        'url' => 'siteurl',
        'admin_email' => 'admin_email'
      );

      if(function_exists('get_bloginfo')) $out = get_bloginfo($info);
      else {
        global $wpdb;
        $oTable = $wpdb->prefix . 'options';

        $oSql = "SELECT wo.option_value FROM $oTable wo WHERE wo.option_name = %s  LIMIT 1;";
        $out = $wpdb->get_var($wpdb->prepare($oSql, $infos[$info]));
      }
      return $out;
    }

    private function parseText( $text, $advert ) {
      $out = str_replace('[name]', $advert, $text);
      $out = str_replace('[site]', self::getSiteInfo(), $out);
      $out = str_replace('Simple Ads Manager', "<a href='http://www.simplelib.com/?p=480' target='_blank'>Simple Ads Manager</a>", $out);
      $out = str_replace('[month]', $this->month, $out);
      $out = str_replace('[first]', $this->first, $out);
      $out = str_replace('[last]', $this->last, $out);

      return $out;
    }

    private function getMailStyle() {
      return "
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
    .td-num {
      text-align: right;
    }
    .mess {
      font-family: Arial, Helvetica, Tahoma, sans-serif;
      font-size: 11px;
    }
    .total {font-size: 13px}
  </style>
      ";
    }

    private function buildMessage( $user ) {
      global $wpdb;

      $options = $this->options;
      $aTable = $wpdb->prefix . 'sam_ads';
      $sTable = $wpdb->prefix . 'sam_stats';

      $columns = array(
        'mail_hits' => 'Hits',
        'mail_clicks' => 'Clicks',
        'mail_cpm' => 'CPM',
        'mail_cpc' => 'CPC',
        'mail_ctr' => 'CTR'
      );

      $date = new DateTime('now');
      if($this->options['mail_period'] === 'monthly') {
        $date->modify('-1 month');
        $first = $date->format('Y-m-01 00:00:00');
        $last = $date->format('Y-m-t 23:59:59');
        $this->first = $first;
        $this->last = $last;
      }
      else {
        $date->modify('-1 week');
        $dd = 7 - ((integer) $date->format('N'));
        if($dd > 0) $date->modify("+{$dd} day");
        $last = $date->format('Y-m-d 23:59:59');
        $date->modify('-6 day');
        $first = $date->format('Y-m-d 00:00:00');

        $this->first = $first;
        $this->last = $last;
      }

      $greeting = self::parseText($options['mail_greeting'], $user['adv_name']);
      $textBefore = self::parseText($options['mail_text_before'], $user['adv_name']);
      $textAfter = self::parseText($options['mail_text_after'], $user['adv_name']);
      $warning = self::parseText($options['mail_warning'], $user['adv_name']);
      $message = self::parseText($options['mail_message'], $user['adv_name']);

      $sql = "SELECT
                  sa.id,
                  sa.pid,
                  sa.name,
                  sa.description,
                  @ad_hits := (SELECT COUNT(*) FROM $sTable ss WHERE ss.event_time >= %s AND ss.event_time <= %s AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 0) AS ad_hits,
                  @ad_clicks := (SELECT COUNT(*) FROM $sTable ss WHERE ss.event_time >= %s AND ss.event_time <= %s AND ss.id = sa.id AND ss.pid = sa.pid AND ss.event_type = 1) AS ad_clicks,
                  (sa.cpm / @ad_hits * 1000) AS e_cpm,
                  sa.cpc AS e_cpc,
                  (@ad_clicks / @ad_hits * 100) AS e_ctr
                FROM $aTable sa
                WHERE sa.adv_mail = %s AND sa.trash = FALSE AND NOT (sa.ad_schedule AND sa.ad_end_date <= %s);";
      $ads = $wpdb->get_results($wpdb->prepare($sql, $first, $last, $first, $last, $user['adv_mail'], $last), ARRAY_A);

      $this->error = $wpdb->prepare($sql, $first, $last, $first, $last, $user['adv_mail'], $last); //$wpdb->last_error;

      $mess = '';

      if(!empty($ads) && is_array($ads)) {
        $sql = "SELECT COUNT(*)
                  FROM $sTable ss
                  INNER JOIN $aTable sa
                    ON ss.id = sa.id
                  WHERE sa.adv_mail = %s
                    AND sa.trash = FALSE
                    AND ss.event_time >= %s AND ss.event_time <= %s
                    AND ss.event_type = %d";

        $hits = $wpdb->get_var($wpdb->prepare($sql, $user['adv_mail'], $first, $last, 0));
        $clicks = $wpdb->get_var($wpdb->prepare($sql, $user['adv_mail'], $first, $last, 1));

        $style = self::getMailStyle();
        $ths = '';
        foreach($columns as $key => $column)
          $ths .= (($options[$key]) ? "<th class='w10'>{$column}</th>" : '');
        $mess .= "
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
<head>
  <title>Ad campaign report</title>
  {$style}
</head>
<body>
<p>{$greeting}</p>
<p>{$textBefore}</p>
<table class='sam-table'>
  <thead>
    <tr>
      <th class='w25'>Name</th>
      <th class='w25'>Description</th>
      {$ths}
    </tr>
  </thead>
  <tbody>";
        $k = 0;
        foreach($ads as $ad) {
          $cpm = number_format($ad['e_cpm'], 2);
          $cpc = number_format($ad['e_cpc'], 2);
          $ctr = number_format($ad['e_ctr'], 2) . '%';

          $class = ( ($k % 2) == 1 ) ? 'odd' : 'even';
          $mess .= "<tr class='{$class}'><td>{$ad['name']}</td><td>{$ad['description']}</td>";
          $mess .= (($options['mail_hits']) ? "<td class='td-num'>{$ad['ad_hits']}</td>" : '');
          $mess .= (($options['mail_clicks']) ? "<td class='td-num'>{$ad['ad_clicks']}</td>" : '');
          $mess .= (($options['mail_cpm']) ? "<td class='td-num'>{$cpm}</td>" : '');
          $mess .= (($options['mail_cpc']) ? "<td class='td-num'>{$cpc}</td>" : '');
          $mess .= (($options['mail_ctr']) ? "<td class='td-num'>{$ctr}</td>" : '');
          $mess .= "</tr>";
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
          $message = self::buildMessage($adv);
          $subject = self::parseText($this->options['mail_subject'], $adv['adv_name']);
          if(!empty($message)) {
            if(function_exists('wp_mail')) wp_mail($adv['adv_mail'], $subject, $message, $headers);
            else {
              $samAdminMail = self::getSiteInfo('admin_email');
              $headers .= "From: SAM Info <{$samAdminMail}>" . "\r\n";
              mail($adv['adv_mail'], $subject, $message, $headers);
            }
            $k++;
          }
        }
      }
      return ($k == 0) ? $this->error : $k;
    }

    public function buildPreview($user) {
      $date = new DateTime('now');
      if($this->options['mail_period'] === 'monthly') {
        $date->modify('-1 month');
        $first = $date->format('Y-m-01 00:00:00');
        $last = $date->format('Y-m-t 23:59:59');
        $this->first = $first;
        $this->last = $last;
      }
      else {
        $date->modify('-1 week');
        $dd = 7 - ((integer) $date->format('N'));
        if($dd > 0) $date->modify("+{$dd} day");
        $last = $date->format('Y-m-d 23:59:59');
        $date->modify('-6 day');
        $first = $date->format('Y-m-d 00:00:00');

        $this->first = $first;
        $this->last = $last;
      }

      $options = $this->options;
      $greeting = self::parseText($options['mail_greeting'], $user);
      $textBefore = self::parseText($options['mail_text_before'], $user);
      $textAfter = self::parseText($options['mail_text_after'], $user);
      $warning = self::parseText($options['mail_warning'], $user);
      $message = self::parseText($options['mail_message'], $user);

      $ads = array(
        array(
          'name' => 'Header Ad',
          'description' => 'Ad in the header of blog.',
          'ad_hits' => 10000,
          'ad_clicks' => 10,
          'e_cpm' => 95.36,
          'e_cpc' => 15.00,
          'e_ctr' => 0.1
        ),
        array(
          'name' => 'Sidebar Ad',
          'description' => 'Ad in the sidebar of blog.',
          'ad_hits' => 5000,
          'ad_clicks' => 1,
          'e_cpm' => 99.99,
          'e_cpc' => 10.00,
          'e_ctr' => 0.02
        ),
        array(
          'name' => 'Footer Ad',
          'description' => 'Ad in the footer of blog.',
          'ad_hits' => 8000,
          'ad_clicks' => 5,
          'e_cpm' => 9.9936,
          'e_cpc' => 5.00,
          'e_ctr' => 0.0625
        )
      );
      $hits = 23000;
      $clicks = 16;

      $columns = array(
        'mail_hits' => 'Hits',
        'mail_clicks' => 'Clicks',
        'mail_cpm' => 'CPM',
        'mail_cpc' => 'CPC',
        'mail_ctr' => 'CTR'
      );
      $ths = '';
      foreach($columns as $key => $column)
        $ths .= (($options[$key]) ? "<th class='w10'>{$column}</th>" : '');

      $mess = "<p>{$greeting}</p>
<p>{$textBefore}</p>
<table class='sam-table'>
  <thead>
    <tr>
      <th class='w25'>Name</th>
      <th class='w25'>Description</th>
      {$ths}
    </tr>
  </thead>
  <tbody>";
      $k = 0;
      foreach($ads as $ad) {
        $cpm = number_format($ad['e_cpm'], 2);
        $cpc = number_format($ad['e_cpc'], 2);
        $ctr = number_format($ad['e_ctr'], 2) . '%';

        $class = ( ($k % 2) == 1 ) ? 'odd' : 'even';
        $mess .= "<tr class='{$class}'><td>{$ad['name']}</td><td>{$ad['description']}</td>";
        $mess .= (($options['mail_hits']) ? "<td class='td-num'>{$ad['ad_hits']}</td>" : '');
        $mess .= (($options['mail_clicks']) ? "<td class='td-num'>{$ad['ad_clicks']}</td>" : '');
        $mess .= (($options['mail_cpm']) ? "<td class='td-num'>{$cpm}</td>" : '');
        $mess .= (($options['mail_cpc']) ? "<td class='td-num'>{$cpc}</td>" : '');
        $mess .= (($options['mail_ctr']) ? "<td class='td-num'>{$ctr}</td>" : '');
        $mess .= "</tr>";
        $k++;
      }
      $mess .= "</tbody></table>";
      $mess .= "
<p class='total'>Hits: {$hits}</p>
<p class='total'>Clicks: {$clicks}</p>
<p>{$textAfter}</p>
<p class='mess'>{$warning}</p>
<p class='mess'>{$message}</p>";

      return $mess;
    }
  }
}

if(!class_exists('SamStatsCleaner')) {
  class SamStatsCleaner {
    private $options;

    public function __construct($settings) {
      $this->options = $settings;
    }

    private function errorWrite($eTable, $rTable, $eSql = null, $eResult = null, $lastError = null, $date = null) {
      global $wpdb;

      if(!is_null($eResult)) {
        if($eResult === false) {
          $wpdb->insert(
            $eTable,
            array(
              'error_date' => current_time('mysql'),
              'table_name' => $rTable,
              'error_type' => 1,
              'error_msg' => (empty($lastError)) ? __('An error occurred during updating process...', SAM_DOMAIN) : $lastError,
              'error_sql' => $eSql,
              'resolved' => 0
            ),
            array('%s', '%s', '%d', '%s', '%s', '%d')
          );
        }
        else {
          $wpdb->insert(
            $eTable,
            array(
              'error_date' => current_time('mysql'),
              'table_name' => $rTable,
              'error_type' => 0,
              'error_msg' => (empty($lastError)) ? sprintf( __('All statistical data before %s is cleared...', SAM_DOMAIN), $date ) : $lastError,
              'error_sql' => $eSql,
              'resolved' => 1
            ),
            array('%s', '%s', '%d', '%s', '%s', '%d')
          );
        }
      }
    }

    public function clear($date = null) {
      if($this->options['keepStats'] == 0) return;

      if($date == null) {
        $nowDate = new DateTime('now');
        $modify = ($this->options['keepStats'] < 12) ? '-' . $this->options['keepStats'] . ' month' : '-1 year';
        $nowDate->modify($modify);
        $date = $nowDate->format('Y-m-01 00:00');
        $sDate = $nowDate->format(str_replace(array('d', 'j'), array('01', '1'), get_option('date_format')));
      }
      else $sDate = $date;

      global $wpdb;
      $dbResult = null;
      $el = (integer)$this->options['errorlog'];

      $sTable = $wpdb->prefix . 'sam_stats';
      $eTable = $wpdb->prefix . "sam_errors";

      $sql = "DELETE FROM $sTable WHERE event_time < %s;";
      $dbResult = $wpdb->query($wpdb->prepare($sql, $date));
      if($el) {
        self::errorWrite($eTable, $sTable, $wpdb->prepare($sql, $date), $dbResult, $wpdb->last_error, $sDate);
        $dbResult = null;
      }
    }
  }
}