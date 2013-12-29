<?php
  if(!class_exists('samErrors')) {
    class samErrors {
      public $errorString;
      
      public function __construct($rows = null) {
        $this->errorString = self::checkShell($rows);
      }
      
      public function getErrors($rows = array(), $dir = '') {
        global $wpdb;
        $errors = array(
          'dir' => false,
          'tables' => array(
            'places' => 0,
            'ads' => 0,
            'zones' => 0,
            'blocks' => 0
          ),
          'prefix' => $wpdb->prefix
        );
      
        if(is_null($dir)) $dir = SAM_AD_IMG;
        if(!is_dir($dir)) $errors['dir'] = true;
      
        foreach($rows as $key => $value) {
          $table = $wpdb->prefix . 'sam_' . $key;
          if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) $errors['tables'][$key] = 1;
          if($errors['tables'][$key] != 1) {
            $result = $wpdb->get_results("DESCRIBE $table", ARRAY_A);
            if($rows[$key] != count($result)) $errors['tables'][$key] = 2;
          }
        }
      
        return $errors;
      }
    
      public function checkShell($rows = null) {
        global $sam_tables_defs;

        $dirError = '';
        $oError = '';
        $output = '';
      
        if(is_null($rows))
          $rows = array(
            'places' => count($sam_tables_defs['places']),
            'ads' => count($sam_tables_defs['ads']),
            'zones' => count($sam_tables_defs['zones']),
            'blocks' => count($sam_tables_defs['blocks'])
          );
        $errors = self::getErrors($rows, SAM_AD_IMG);
        if($errors['dir']) {
          $dirError = '<p><strong>'.__("Simple Ads Manager Images Folder hasn't been created!", SAM_DOMAIN).'</strong></p>';
          $dirError .= '<p>'.__("Try to reactivate plugin or create folder manually.", SAM_DOMAIN).'<br/>'.__("Manually creation: Create folder 'sam-images' in 'wp-content/plugins' folder. Don't forget to set folder's permissions to 777.", SAM_DOMAIN).'</p>';
        }
      
        foreach($errors['tables'] as $key => $value) {
          $table = $errors['prefix'].'sam_'.$key;
          switch($value) {
            case 1:
              $oError .= '<p><strong>'.sprintf(__("Database table %s hasn't been created!", SAM_DOMAIN), $table).'</strong></p>';
              break;
            
            case 2:
              $oError .= '<p><strong>'.sprintf(__("Database table %s hasn't been upgraded!", SAM_DOMAIN), $table).'</strong></p>';
              break;
          
            default:
              $oError .= '';
              break;
          }
        }
      
        if(!empty($oError) || !empty($dirError))
          $output = '<div class="error below-h2">'.$dirError.$oError.'</div>';
        return $output;
      }
    }
  }
?>
