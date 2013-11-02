<?php
if(!class_exists('SamAd')) {
  class SamAd {
    private $args = array();
    private $useCodes = false;
    private $crawler = false;
    public $ad = '';
    
    public function __construct($args = null, $useCodes = false, $crawler = false) {
      $this->args = $args;
      $this->useCodes = $useCodes;
      $this->crawler = $crawler;
      $this->ad = $this->buildAd($this->args, $this->useCodes);
    }

    private function getSettings() {
      $options = get_option(SAM_OPTIONS_NAME, '');
      return $options;
    }

    private function getSize($ss, $width, $height) {
      if($ss == 'custom') return array('width' => $width, 'height' => $height);
      else {
        $aSize = explode("x", $ss);
        return array('width' => $aSize[0], 'height' => $aSize[1]);
      }
    }
    
    private function buildAd( $args = null, $useCodes = false ) {
      if(is_null($args)) return '';
      if(empty($args['id']) && empty($args['name'])) return '';
      
      global $wpdb;          
      $pTable = $wpdb->prefix . "sam_places";
      $aTable = $wpdb->prefix . "sam_ads";
      
      $settings = $this->getSettings();
      if(!empty($args['id'])) $wid = "$aTable.id = {$args['id']}";
      else $wid = "$aTable.name = '{$args['name']}'";
      
      $output = '';
      
      $aSql = "SELECT
                  $aTable.id,
                  $aTable.pid,
                  $aTable.code_mode,
                  $aTable.ad_code,
                  $aTable.ad_img,
                  $aTable.ad_alt,
                  $aTable.ad_no,
                  $aTable.ad_target,
                  $aTable.ad_swf,
                  $aTable.ad_swf_flashvars,
                  $aTable.ad_swf_params,
                  $aTable.ad_swf_attributes,
                  $aTable.count_clicks,
                  $aTable.code_type,
                  $pTable.code_before,
                  $pTable.code_after,
                  $pTable.place_size,
                  $pTable.place_custom_width,
                  $pTable.place_custom_height
                FROM $aTable
                  INNER JOIN $pTable
                    ON $aTable.pid = $pTable.id
                WHERE $wid;";
      $ad = $wpdb->get_row($aSql, ARRAY_A);
      if($ad['code_mode'] == 0) {
        if((int)$ad['ad_swf']) {
          $id = "ad-".$ad['id'].'-'.rand(1111, 9999);
          $file = $ad['ad_img'];
          $sizes = self::getSize($ad['place_size'], $ad['place_custom_width'], $ad['place_custom_height']);
          $width = $sizes['width'];
          $height = $sizes['height'];
          $flashvars = (!empty($ad['ad_swf_flashvars'])) ? $ad['ad_swf_flashvars'] : '{}';
          $params = (!empty($ad['ad_swf_params'])) ? $ad['ad_swf_params'] : '{}';
          $attributes = (!empty($ad['ad_swf_attributes'])) ? $ad['ad_swf_attributes'] : '{}';
          $text = __('Flash ad').' ID:'.$ad['id'];
          $output = "
          <script type='text/javascript'>
          var
            flashvars = $flashvars,
            params = $params,
            attributes = $attributes;
          attributes.id = '$id';
          attributes.styleclass = 'sam_ad';
          swfobject.embedSWF('$file', '$id', '$width', '$height', '9.0.0', '', flashvars, params, attributes);
          </script>
          <div id='$id'>$text</div>
          ";
        }
        else {
          $outId = ((int) $ad['count_clicks'] == 1) ? " id='a".rand(10, 99)."_".$ad['id']."' class='sam_ad'" : '';
          $aStart ='';
          $aEnd ='';
          $iTag = '';
          if(!empty($settings['adDisplay'])) $target = '_'.$settings['adDisplay'];
          else $target = '_blank';
          if(!empty($ad['ad_target'])) {
            //$aStart = ((in_array((integer)$ad['ad_no'], array(2,3))) ? '<noindex>' : '')."<a href='{$ad['ad_target']}' target='_blank' ".((in_array((integer)$ad['ad_no'], array(1,3))) ? " rel='nofollow'" : '').">";
            //$aEnd = "</a>".(in_array((integer)$ad['ad_no'], array(2,3))) ? '</noindex>' : '';
            $aStart = "<a $outId href='{$ad['ad_target']}' target='$target' ".">";
            $aEnd = "</a>";
          }
          if(!empty($ad['ad_img'])) $iTag = "<img src='{$ad['ad_img']}' ".((!empty($ad['ad_alt'])) ? " alt='{$ad['ad_alt']}' " : '')." />";
          $output = $aStart.$iTag.$aEnd;
        }
      }
      else {
        if($ad['code_type'] == 1) {
          ob_start();
          eval('?>'.$ad['ad_code'].'<?');
          $output = ob_get_contents();
          ob_end_clean();
        }
        else $output = $ad['ad_code'];
      }
      if(!$this->crawler && !is_admin())
        $wpdb->query("UPDATE $aTable SET $aTable.ad_hits = $aTable.ad_hits+1 WHERE $aTable.id = {$ad['id']};");
      
      if(is_array($useCodes)) $output = $useCodes['before'].$output.$useCodes['after'];
      elseif($useCodes) $output = $ad['code_before'].$output.$ad['code_after'];
      return $output;
    }
  }
}

if(!class_exists('SamAdPlace')) {
  class SamAdPlace {
    private $args = array();
    private $useCodes = false;
    private $crawler = false;
    public $ad = '';
    private $clauses;
    
    public function __construct($args = null, $useCodes = false, $crawler = false, $clauses = null) {
      global $SAM_Query;

      $this->args = $args;
      $this->useCodes = $useCodes;
      $this->crawler = $crawler;
      if(is_null( $clauses )) $this->clauses = $SAM_Query['clauses'];
      else $this->clauses = $clauses;
      $this->ad = $this->buildAd($this->args, $this->useCodes);
    }
    
    private function getSettings() {
      $options = get_option(SAM_OPTIONS_NAME, '');      
      return $options;
    }

    private function getSize($ss, $width, $height) {
      if($ss == 'custom') return array('width' => $width, 'height' => $height);
      else {
        $aSize = explode("x", $ss);
        return array('width' => $aSize[0], 'height' => $aSize[1]);
      }
    }

    private function errorWrite($eTable, $rTable, $eSql = null, $eResult = null, $lastError = null) {
      global $wpdb;

      //if(!is_null($eResult)) {
        if(!$eResult) {
          $wpdb->insert(
            $eTable,
            array(
              'error_date' => current_time('mysql'),
              'table_name' => $rTable,
              'error_type' => 2,
              'error_msg' => $lastError,
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
              'error_msg' => __('Empty data...', SAM_DOMAIN),
              'error_sql' => $eSql,
              'resolved' => 1
            ),
            array('%s', '%s', '%d', '%s', '%s', '%d')
          );
        }
      //}
    }
    
    private function buildAd( $args = null, $useCodes = false ) {
      if(is_null($args)) return '';
      if(empty($args['id']) && empty($args['name'])) return '';
      if( is_null($this->clauses) ) return '';
      
      $settings = $this->getSettings();
      if($settings['adCycle'] == 0) $cycle = 1000;
      else $cycle = $settings['adCycle'];
      $el = (integer)$settings['errorlogFS'];
      
      global $wpdb/*, $current_user*/;
      $pTable = $wpdb->prefix . "sam_places";          
      $aTable = $wpdb->prefix . "sam_ads";
      $eTable = $wpdb->prefix . "sam_errors";

      $whereClause = $this->clauses['WC'];
      $whereClauseT = $this->clauses['WCT'];
      $whereClauseW = $this->clauses['WCW'];
      $whereClause2W = $this->clauses['WC2W'];
      
      if(!empty($args['id'])) $pId = "$pTable.id = {$args['id']}";
      else $pId = "$pTable.name = '{$args['name']}'";
      
      $pSql = "SELECT
                  $pTable.id,
                  $pTable.name,                  
                  $pTable.description,
                  $pTable.code_before,
                  $pTable.code_after,
                  $pTable.place_size,
                  $pTable.place_custom_width,
                  $pTable.place_custom_height,
                  $pTable.patch_img,
                  $pTable.patch_link,
                  $pTable.patch_code,
                  $pTable.patch_adserver,
                  $pTable.patch_dfp,                  
                  $pTable.patch_source,
                  $pTable.trash,
                  (SELECT COUNT(*) FROM $aTable WHERE $aTable.pid = $pTable.id AND $aTable.trash IS FALSE) AS ad_count,
                  (SELECT COUNT(*) FROM $aTable WHERE $aTable.pid = $pTable.id AND $aTable.trash IS FALSE AND $whereClause $whereClauseT $whereClause2W) AS ad_logic_count,
                  (SELECT COUNT(*) FROM $aTable WHERE $aTable.pid = $pTable.id AND $aTable.trash IS FALSE AND $whereClause $whereClauseT $whereClauseW) AS ad_full_count
                FROM $pTable
                WHERE $pId AND $pTable.trash IS FALSE;";
      
      $place = $wpdb->get_row($pSql, ARRAY_A);

      if(!$place) {
        if($el) self::errorWrite($eTable, $pTable, $pSql, $place, $wpdb->last_error);
        return '';
      }
      
      if($place['patch_source'] == 2) {
        if(($settings['useDFP'] == 1) && !empty($settings['dfpPub'])) {
          $output = "<!-- {$place['patch_dfp']} -->"."\n";
          $output .= "<script type='text/javascript'>"."\n";
          $output .= "  GA_googleFillSlot('{$place['patch_dfp']}');"."\n";
          $output .= "</script>"."\n";
          if(is_array($useCodes)) $output = $useCodes['before'].$output.$useCodes['after'];
          elseif($useCodes) $output = $place['code_before'].$output.$place['code_after'];
        }
        else $output = '';
        if(!$this->crawler)
          $wpdb->query("UPDATE {$pTable} SET {$pTable}.patch_hits = {$pTable}.patch_hits+1 WHERE {$pTable}.id = {$place['id']}");
        return $output;
      }
      
      if(($place['patch_source'] == 1) && (abs($place['patch_adserver']) == 1)) {
        $output = $place['patch_code'];
        if(is_array($useCodes)) $output = $useCodes['before'].$output.$useCodes['after'];
        elseif($useCodes) $output = $place['code_before'].$output.$place['code_after'];
        if(!$this->crawler)
          $wpdb->query("UPDATE $pTable SET $pTable.patch_hits = $pTable.patch_hits+1 WHERE $pTable.id = {$place['id']}");
        return $output;
      }
                                     
      if((abs($place['ad_count']) == 0) || (abs($place['ad_logic_count']) == 0)) {
        if($place['patch_source'] == 0) {
          $aStart ='';
          $aEnd ='';
          $iTag = '';
          if(!empty($settings['adDisplay'])) $target = '_'.$settings['adDisplay'];
          else $target = '_blank';  
          if(!empty($place['patch_link'])) {
            $aStart = "<a href='{$place['patch_link']}' target='$target'>";
            $aEnd = "</a>";
          }
          if(!empty($place['patch_img'])) $iTag = "<img src='{$place['patch_img']}' />";
          $output = $aStart.$iTag.$aEnd;
        }
        else $output = $place['patch_code'];
        if(!$this->crawler)
          $wpdb->query("UPDATE $pTable SET $pTable.patch_hits = $pTable.patch_hits+1 WHERE $pTable.id = {$place['id']}");
      }
      
      if((abs($place['ad_logic_count']) > 0) && (abs($place['ad_full_count']) == 0)) {
        $wpdb->update($aTable, array('ad_weight_hits' => 0), array('pid' => $place['id']), array("%d"), array("%d"));
      }
      
      $aSql = "SELECT
                  $aTable.id,
                  $aTable.pid,
                  $aTable.code_mode,
                  $aTable.ad_code,
                  $aTable.ad_img,
                  $aTable.ad_alt,
                  $aTable.ad_no,
                  $aTable.ad_target,
                  $aTable.ad_swf,
                  $aTable.ad_swf_flashvars,
                  $aTable.ad_swf_params,
                  $aTable.ad_swf_attributes,
                  $aTable.count_clicks,
                  $aTable.code_type,
                  $aTable.ad_hits,
                  $aTable.ad_weight_hits,
                  IF($aTable.ad_weight, ($aTable.ad_weight_hits*10/($aTable.ad_weight*$cycle)), 0) AS ad_cycle
                FROM $aTable
                WHERE $aTable.pid = {$place['id']} AND $aTable.trash IS FALSE AND $whereClause $whereClauseT $whereClauseW
                ORDER BY ad_cycle
                LIMIT 1;";

      if(abs($place['ad_logic_count']) > 0) {
        $ad = $wpdb->get_row($aSql, ARRAY_A);

        if($ad === false) {
          if($el) self::errorWrite($eTable, $aTable, $aSql, $ad, $wpdb->last_error);
          return '';
        }

        if($ad['code_mode'] == 0) {
          if((int)$ad['ad_swf']) {
            $id = "ad-".$ad['id'].'-'.rand(1111, 9999);
            $file = $ad['ad_img'];
            $sizes = self::getSize($place['place_size'], $place['place_custom_width'], $place['place_custom_height']);
            $width = $sizes['width'];
            $height = $sizes['height'];
            $flashvars = (!empty($ad['ad_swf_flashvars'])) ? $ad['ad_swf_flashvars'] : '{}';
            $params = (!empty($ad['ad_swf_params'])) ? $ad['ad_swf_params'] : '{}';
            $attributes = (!empty($ad['ad_swf_attributes'])) ? $ad['ad_swf_attributes'] : '{}';
            $text = __('Flash ad').' ID:'.$ad['id'];
            $output = "
            <script type='text/javascript'>
            var
              flashvars = $flashvars,
              params = $params,
              attributes = $attributes;
            attributes.id = '$id';
            attributes.styleclass = 'sam_ad';
            swfobject.embedSWF('$file', '$id', '$width', '$height', '9.0.0', '', flashvars, params, attributes);
            </script>
            <div id='$id'>$text</div>
            ";
          }
          else {
            $outId = ((int) $ad['count_clicks'] == 1) ? " id='a".rand(10, 99)."_".$ad['id']."' class='sam_ad'" : '';
            $aStart ='';
            $aEnd ='';
            $iTag = '';
            if(!empty($settings['adDisplay'])) $target = '_'.$settings['adDisplay'];
            else $target = '_blank';
            if(!empty($ad['ad_target'])) {
              //$aStart = ((in_array((integer)$ad['ad_no'], array(2,3))) ? '<noindex>' : '')."<a href='{$ad['ad_target']}' target='$target' ".((in_array((integer)$ad['ad_no'], array(1,3))) ? " rel='nofollow'" : '').">";
              //$aEnd = "</a>".(in_array((integer)$ad['ad_no'], array(2,3))) ? '</noindex>' : '';
              $aStart = "<a $outId href='{$ad['ad_target']}' target='$target' ".">";
              $aEnd = "</a>";
            }
            if(!empty($ad['ad_img'])) $iTag = "<img src='{$ad['ad_img']}' ".((!empty($ad['ad_alt'])) ? " alt='{$ad['ad_alt']}' " : " alt='' ")." />";
            $output = $aStart.$iTag.$aEnd;
          }
        }
        else {
          if($ad['code_type'] == 1) {
            ob_start();
            eval('?>'.$ad['ad_code'].'<?');
            $output = ob_get_contents();
            ob_end_clean();
          }
          else $output = $ad['ad_code'];
        }
        if(!$this->crawler && !is_admin())
          $wpdb->query("UPDATE $aTable SET $aTable.ad_hits = $aTable.ad_hits+1, $aTable.ad_weight_hits = $aTable.ad_weight_hits+1 WHERE $aTable.id = {$ad['id']}");
      }
      
      if(is_array($useCodes)) $output = $useCodes['before'].$output.$useCodes['after'];
      elseif($useCodes) $output = $place['code_before'].$output.$place['code_after'];
      return $output;
    }
  }
}

if(!class_exists('SamAdPlaceZone')) {
  class SamAdPlaceZone {
    private $args = array();
    private $useCodes = false;
    private $crawler = false;
    public $ad = '';
    
    public function __construct($args = null, $useCodes = false, $crawler = false) {
      $this->args = $args;
      $this->useCodes = $useCodes;
      $this->crawler = $crawler;
      $this->ad = self::buildZone($this->args, $this->useCodes, $this->crawler);
    }
    
    private function getCustomPostTypes() {
      $args = array('public' => true, '_builtin' => false);
      $output = 'names';
      $operator = 'and';
      $post_types = get_post_types($args, $output, $operator);
      
      return $post_types;
    }
    
    private function isCustomPostType() {
      return (in_array(get_post_type(), $this->getCustomPostTypes()));
    }
    
    private function buildZone($args = null, $useCodes = false, $crawler = false) {
      if(is_null($args)) return '';
      if(empty($args['id']) && empty($args['name'])) return '';
      
      global $wpdb;
      $zTable = $wpdb->prefix . "sam_zones";
      
      $id = 0; // None
      $output = '';
      
      if(!empty($args['id'])) $zId = "$zTable.id = {$args['id']}";
      else $zId = "$zTable.name = '{$args['name']}'";
      
      $zSql = "SELECT
                  $zTable.id,
                  $zTable.name,
                  $zTable.z_default,
                  $zTable.z_home,
                  $zTable.z_singular,
                  $zTable.z_single,
                  $zTable.z_ct,
                  $zTable.z_single_ct,
                  $zTable.z_page,
                  $zTable.z_attachment,
                  $zTable.z_search,
                  $zTable.z_404,
                  $zTable.z_archive,
                  $zTable.z_tax,
                  $zTable.z_category,
                  $zTable.z_cats,
                  $zTable.z_tag,
                  $zTable.z_author,
                  $zTable.z_authors,
                  $zTable.z_cts,
                  $zTable.z_archive_ct,
                  $zTable.z_date
                FROM $zTable
                WHERE $zId AND $zTable.trash IS FALSE;";
      $zone = $wpdb->get_row($zSql, ARRAY_A);
      if(!empty($zone)) {
        $cats = unserialize($zone['z_cats']);
        $authors = unserialize($zone['z_authors']);
        $singleCT = unserialize($zone['z_single_ct']);
        $archiveCT = unserialize($zone['z_archive_ct']);
        
        if((integer)$zone['z_home'] < 0) $zone['z_home'] = $zone['z_default'];
        if((integer)$zone['z_singular'] < 0) $zone['z_singular'] = $zone['z_default'];
        if((integer)$zone['z_single'] < 0) $zone['z_single'] = $zone['z_singular'];
        if((integer)$zone['z_ct'] < 0) $zone['z_ct'] = $zone['z_singular'];
        foreach($singleCT as $key => $value) {
          if($value < 0) $singleCT[$key] = $zone['z_ct'];
        }
        if((integer)$zone['z_page'] < 0) $zone['z_page'] = $zone['z_singular'];
        if((integer)$zone['z_attachment'] < 0) $zone['z_attachment'] = $zone['z_singular'];
        if((integer)$zone['z_search'] < 0) $zone['z_search'] = $zone['z_default'];
        if((integer)$zone['z_404'] < 0) $zone['z_404'] = $zone['z_default'];
        if((integer)$zone['z_archive'] < 0) $zone['z_archive'] = $zone['z_default'];
        if((integer)$zone['z_tax'] < 0) $zone['z_tax'] = $zone['z_archive'];
        if((integer)$zone['z_category'] < 0) $zone['z_category'] = $zone['z_tax'];
        foreach($cats as $key => $value) {
          if($value < 0) $cats[$key] = $zone['z_category'];
        }
        if((integer)$zone['z_tag'] < 0) $zone['z_tag'] = $zone['z_tax'];
        if((integer)$zone['z_author'] < 0) $zone['z_author'] = $zone['z_archive'];
        foreach($authors as $key => $value) {
          if($value < 0) $authors[$key] = $zone['z_author'];
        }
        if((integer)$zone['z_cts'] < 0) $zone['z_cts'] = $zone['z_archive'];
        foreach($archiveCT as $key => $value) {
          if($value < 0) $archiveCT[$key] = $zone['z_cts'];
        }
        if((integer)$zone['z_date'] < 0) $zone['z_date'] = $zone['z_archive'];
        
        if(is_home() || is_front_page()) $id = $zone['z_home'];
        if(is_singular()) {
          $id = $zone['z_singular'];
          if(is_single()) {
            $id = $zone['z_single'];
            if($this->isCustomPostType()) {
              $id = $zone['z_ct'];
              foreach($singleCT as $key => $value) {
                if($key == get_post_type()) $id = $value;
              }
            }
          }
          if(is_page() && !is_front_page()) $id = $zone['z_page'];
          if(is_attachment()) $id = $zone['z_attachment'];
        }
        if(is_search()) $id = $zone['z_search'];
        if(is_404()) $id = $zone['z_404'];
        if(is_archive()) {
          $id = $zone['z_archive'];
          if(is_tax()) $id = $zone['z_tax'];
          if(is_category()) {
            $id = $zone['z_category'];
            foreach($cats as $key => $value) {
              if(is_category($key)) $id = $value;
            }                
          }
          if(is_tag()) $id = $zone['z_tag'];
          if(is_author()) {
            $id = $zone['z_author'];
            foreach($authors as $key => $value) {
              if(is_author($key)) $id = $value;
            }
          }
          if(is_post_type_archive()) {
            $id = $zone['z_cts'];
            foreach($archiveCT as $key => $value) {
              if(is_post_type_archive($key)) $id = $value;
            }
          }
          if(is_date()) $id = $zone['z_date'];
        }
      }
      
      if($id > 0) {
        $ad = new SamAdPlace(array('id' => $id), $useCodes, $crawler);
        $output = $ad->ad;
      }
      return $output;
    }
  }
}

if(!class_exists('SamAdBlock')) {
  class SamAdBlock {
    private $args = array();
    private $crawler = false;
    public $ad = '';
    
    public function __construct($args = null, $crawler = false) {
      $this->args = $args;
      $this->crawler = $crawler;
      $this->ad = self::buildBlock($this->args, $this->crawler);
    }
    
    private function buildBlock($args = null, $crawler = false) {
      if(is_null($args)) return 'X';
      if(empty($args['id']) && empty($args['name'])) return 'Y';
      
      global $wpdb;
      $bTable = $wpdb->prefix . "sam_blocks";
      $output = '';
      
      if(!empty($args['id'])) $bId = "$bTable.id = {$args['id']}";
      else $bId = "$bTable.name = '{$args['name']}'";
      
      $bSql = "SELECT
                 $bTable.id, 
                 $bTable.name,
                 $bTable.b_lines,
                 $bTable.b_cols,
                 $bTable.block_data,
                 $bTable.b_margin,
                 $bTable.b_padding,
                 $bTable.b_background,
                 $bTable.b_border,
                 $bTable.i_margin,
                 $bTable.i_padding,
                 $bTable.i_background,
                 $bTable.i_border,
                 $bTable.trash
               FROM $bTable
               WHERE $bId AND $bTable.trash IS FALSE;";
               
      $block = $wpdb->get_row($bSql, ARRAY_A);
      if(!empty($block)) {
        $ads = unserialize($block['block_data']);
        $lines = (integer) $block['b_lines'];
        $cols = (integer) $block['b_cols'];
        $blockDiv = "<div style='margin: ".$block['b_margin']."; padding: ".$block['b_padding']."; background: ".$block['b_background']."; border: ".$block['b_border']."'>";
        $lineDiv = "<div style='margin: 0px; padding: 0px;'>";
        $itemDiv = "<div style='display: inline-block; margin: ".$block['i_margin']."; padding: ".$block['i_padding']."; background: ".$block['i_background']."; border: ".$block['i_border']."'>";

        for($i = 1; $i <= $lines; $i++) {
          $lDiv = '';
          for($j = 1; $j <= $cols; $j++) {
            $id = $ads[$i][$j]['id'];
            switch($ads[$i][$j]['type']) {
              case 'place':
                $place = new SamAdPlace(array('id' => $id), false, $crawler);
                $iDiv = $place->ad;
                break;
                
              case 'ad':
                $ad = new SamAd(array('id' => $id), false, $crawler);
                $iDiv = $ad->ad;
                break;
                
              case 'zone':
                $zone = new SamAdPlaceZone(array('id' => $id), false, $crawler);
                $iDiv = $zone->ad;
                break;
                
              default:
                $iDiv = '';
                break;
            }
            if(!empty($iDiv)) $lDiv .= $itemDiv.$iDiv."</div>";
          }
          if(!empty($lDiv)) $output .= $lineDiv.$lDiv."</div>";
        }
        $output = $blockDiv.$output."</div>";
      }
      else $output = '';
      
      return $output;
    }
  }
}
?>
