<?php
if(!class_exists('SamUpdater')) {
  class SamUpdater {
    private $dbVersion;
    private $versionsData;
    private $options;

    public function __construct($dbVersion, $versionsData, $options = null) {
      $this->dbVersion = $dbVersion;
      $this->versionsData = $versionsData;
      $this->options = $options;
    }

    private function errorWrite($eTable, $rTable, $eSql = null, $eResult = null) {
      global $wpdb;

      if(!is_null($eResult)) {
        if($eResult === false) {
          $wpdb->insert(
            $eTable,
            array(
              'error_date' => current_time('mysql'),
              'table_name' => $rTable,
              'error_type' => 1,
              'error_msg' => __('An error occurred during updating process...', SAM_DOMAIN),
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
              'error_msg' => __('Updated...', SAM_DOMAIN),
              'error_sql' => $eSql,
              'resolved' => 1
            ),
            array('%s', '%s', '%d', '%s', '%s', '%d')
          );
        }
      }
    }

    public function update() {
      global $wpdb, $charset_collate;
      $pTable = $wpdb->prefix . "sam_places";
      $aTable = $wpdb->prefix . "sam_ads";
      $zTable = $wpdb->prefix . "sam_zones";
      $bTable = $wpdb->prefix . "sam_blocks";
      $eTable = $wpdb->prefix . "sam_errors";

      $options = $this->options;
      $el = (integer)$options['errorlog'];

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

      //$versions = $this->versions;
      $dbVersion = $this->dbVersion; //$versions['db'];
      $vData = $this->versionsData;

      $dbResult = null;

      if( $dbVersion != SAM_DB_VERSION ) {
        if($wpdb->get_var("SHOW TABLES LIKE '$eTable'") != $eTable) {
          $eSql = "CREATE TABLE $eTable (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    error_date datetime DEFAULT NULL,
                    table_name varchar(30) DEFAULT NULL,
                    error_type int(11) NOT NULL DEFAULT 0,
                    error_msg varchar(255) DEFAULT NULL,
                    error_sql text,
                    resolved tinyint(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id)
                    ) $charset_collate;";
          dbDelta($eSql);
        }

        if($wpdb->get_var("SHOW TABLES LIKE '$pTable'") != $pTable) {
          $pSql = "CREATE TABLE $pTable (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    name VARCHAR(255) NOT NULL,
                    description VARCHAR(255) DEFAULT NULL,
                    code_before VARCHAR(255) DEFAULT NULL,
                    code_after VARCHAR(255) DEFAULT NULL,
                    place_size VARCHAR(25) DEFAULT NULL,
                    place_custom_width INT(11) DEFAULT NULL,
                    place_custom_height INT(11) DEFAULT NULL,
                    patch_img VARCHAR(255) DEFAULT NULL,
                    patch_link VARCHAR(255) DEFAULT NULL,
                    patch_code TEXT DEFAULT NULL,
                    patch_adserver TINYINT(1) DEFAULT 0,
                    patch_dfp VARCHAR(255) DEFAULT NULL,
                    patch_source TINYINT(1) DEFAULT 0,
                    patch_hits INT(11) DEFAULT 0,
                    trash TINYINT(1) DEFAULT 0,
                    PRIMARY KEY  (id)
                   ) $charset_collate;";
          dbDelta($pSql);
        }
        elseif($dbVersion == '0.1' || $dbVersion == '0.2') {
          $pSql = "ALTER TABLE $pTable
                     CONVERT TO $charset_collate,
                     ADD COLUMN patch_dfp VARCHAR(255) DEFAULT NULL,
                     ADD COLUMN patch_adserver TINYINT(1) DEFAULT 0,
                     ADD COLUMN patch_hits INT(11) DEFAULT 0;";
          $dbResult = $wpdb->query($pSql);
        }
        elseif($vData['major'] < 2) {
          $pSql = "ALTER TABLE $pTable CONVERT TO $charset_collate;";
          $dbResult = $wpdb->query($pSql);
        }

        if($el) {
          self::errorWrite($eTable, $pTable, $pSql, $dbResult);
          $dbResult = null;
        }

        if($wpdb->get_var("SHOW TABLES LIKE '$aTable'") != $aTable) {
          $aSql = "CREATE TABLE $aTable (
                  id INT(11) NOT NULL AUTO_INCREMENT,
                  pid INT(11) NOT NULL,
                  name VARCHAR(255) DEFAULT NULL,
                  description VARCHAR(255) DEFAULT NULL,
                  code_type TINYINT(1) NOT NULL DEFAULT 0,
                  code_mode TINYINT(1) NOT NULL DEFAULT 1,
                  ad_code TEXT DEFAULT NULL,
                  ad_img TEXT DEFAULT NULL,
                  ad_alt TEXT DEFAULT NULL,
                  ad_title varchar(255) DEFAULT NULL,
                  ad_no TINYINT(1) NOT NULL DEFAULT 0,
                  ad_target TEXT DEFAULT NULL,
                  ad_swf tinyint(1) DEFAULT 0,
                  ad_swf_flashvars text,
                  ad_swf_params text,
                  ad_swf_attributes text,
                  count_clicks TINYINT(1) NOT NULL DEFAULT 0,
                  ad_users tinyint(1) DEFAULT 0,
                  ad_users_unreg tinyint(1) DEFAULT 0,
                  ad_users_reg tinyint(1) DEFAULT 0,
                  x_ad_users tinyint(1) DEFAULT NULL,
                  x_view_users varchar(255) DEFAULT NULL,
                  ad_users_adv tinyint(1) DEFAULT 0,
                  view_type INT(11) DEFAULT 1,
                  view_pages SET('isHome','isSingular','isSingle','isPage','isAttachment','isSearch','is404','isArchive','isTax','isCategory','isTag','isAuthor','isDate','isPostType','isPostTypeArchive') DEFAULT NULL,
                  view_id VARCHAR(255) DEFAULT NULL,
                  ad_cats TINYINT(1) DEFAULT 0,
                  view_cats VARCHAR(255) DEFAULT NULL,
                  ad_authors TINYINT(1) DEFAULT 0,
                  view_authors VARCHAR(255) DEFAULT NULL,
                  ad_tags TINYINT(1) DEFAULT 0,
                  view_tags VARCHAR(255) DEFAULT NULL,
                  ad_custom TINYINT(1) DEFAULT 0,
                  view_custom VARCHAR(255) DEFAULT NULL,
                  x_id TINYINT(1) DEFAULT 0,
                  x_view_id VARCHAR(255) DEFAULT NULL,
                  x_cats TINYINT(1) DEFAULT 0,
                  x_view_cats VARCHAR(255) DEFAULT NULL,
                  x_authors TINYINT(1) DEFAULT 0,
                  x_view_authors VARCHAR(255) DEFAULT NULL,
                  x_tags TINYINT(1) DEFAULT 0,
                  x_view_tags VARCHAR(255) DEFAULT NULL,
                  x_custom TINYINT(1) DEFAULT 0,
                  x_view_custom VARCHAR(255) DEFAULT NULL,
                  ad_schedule TINYINT(1) DEFAULT 0,
                  ad_start_date DATE DEFAULT NULL,
                  ad_end_date DATE DEFAULT NULL,
                  limit_hits TINYINT(1) DEFAULT 0,
                  hits_limit INT(11) DEFAULT 0,
                  limit_clicks TINYINT(1) DEFAULT 0,
                  clicks_limit INT(11) DEFAULT 0,
                  ad_hits INT(11) DEFAULT 0,
                  ad_clicks INT(11) DEFAULT 0,
                  ad_weight INT(11) DEFAULT 10,
                  ad_weight_hits INT(11) DEFAULT 0,
                  adv_nick varchar(50) DEFAULT NULL,
                  adv_name varchar(100) DEFAULT NULL,
                  adv_mail varchar(50) DEFAULT NULL,
                  cpm DECIMAL(10,2) UNSIGNED DEFAULT 0.00,
                  cpc DECIMAL(10,2) UNSIGNED DEFAULT 0.00,
                  per_month DECIMAL(10,2) UNSIGNED DEFAULT 0.00,
                  trash TINYINT(1) NOT NULL DEFAULT 0,
                  PRIMARY KEY  (id, pid)
                ) $charset_collate;";
          dbDelta($aSql);
        }
        elseif($dbVersion == '0.1') {
          $aSql = "ALTER TABLE $aTable
                      CONVERT TO $charset_collate,
                      MODIFY view_pages set('isHome','isSingular','isSingle','isPage','isAttachment','isSearch','is404','isArchive','isTax','isCategory','isTag','isAuthor','isDate','isPostType','isPostTypeArchive') default NULL,
                      ADD COLUMN ad_alt TEXT DEFAULT NULL,
                      ADD COLUMN ad_title varchar(255) DEFAULT NULL,
                      ADD COLUMN ad_no TINYINT(1) NOT NULL DEFAULT 0,
                      ADD COLUMN ad_swf tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_swf_flashvars text,
                      ADD COLUMN ad_swf_params text,
                      ADD COLUMN ad_swf_attributes text,
                      ADD COLUMN ad_users tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_users_unreg tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_users_reg tinyint(1) DEFAULT 0,
                      ADD COLUMN x_ad_users tinyint(1) DEFAULT NULL,
                      ADD COLUMN x_view_users varchar(255) DEFAULT NULL,
                      ADD COLUMN ad_users_adv tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_cats TINYINT(1) DEFAULT 0,
                      ADD COLUMN ad_authors TINYINT(1) DEFAULT 0,
                      ADD COLUMN view_authors VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN ad_tags TINYINT(1) DEFAULT 0,
                      ADD COLUMN view_tags VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN ad_custom TINYINT(1) DEFAULT 0,
                      ADD COLUMN view_custom VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN limit_hits TINYINT(1) DEFAULT 0,
                      ADD COLUMN hits_limit INT(11) DEFAULT 0,
                      ADD COLUMN limit_clicks TINYINT(1) DEFAULT 0,
                      ADD COLUMN clicks_limit INT(11) DEFAULT 0,
                      ADD COLUMN cpm DECIMAL(10,2) UNSIGNED DEFAULT 0.00,
                      ADD COLUMN cpc DECIMAL(10,2) UNSIGNED DEFAULT 0.00,
                      ADD COLUMN per_month DECIMAL(10,2) UNSIGNED DEFAULT 0.00,
                      ADD COLUMN x_id TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_id VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_cats TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_cats VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_authors TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_authors VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_tags TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_tags VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_custom TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_custom VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN adv_nick varchar(50) DEFAULT NULL,
                      ADD COLUMN adv_name varchar(100) DEFAULT NULL,
                      ADD COLUMN adv_mail varchar(50) DEFAULT NULL;";
          $dbResult = $wpdb->query($aSql);
          $aSqlU = "UPDATE LOW_PRIORITY $aTable
                      SET $aTable.ad_cats = 1,
                          $aTable.view_type = 0,
                          $aTable.view_pages = 4
                      WHERE $aTable.view_type = 3;";
          $wpdb->query($aSqlU);
        }
        elseif($dbVersion == '0.2' || $dbVersion == '0.3' || $dbVersion == '0.3.1') {
          $aSql = "ALTER TABLE $aTable
                      CONVERT TO $charset_collate,
                      MODIFY view_pages set('isHome','isSingular','isSingle','isPage','isAttachment','isSearch','is404','isArchive','isTax','isCategory','isTag','isAuthor','isDate','isPostType','isPostTypeArchive') default NULL,
                      ADD COLUMN ad_alt TEXT DEFAULT NULL,
                      ADD COLUMN ad_title varchar(255) DEFAULT NULL,
                      ADD COLUMN ad_no TINYINT(1) NOT NULL DEFAULT 0,
                      ADD COLUMN ad_swf tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_swf_flashvars text,
                      ADD COLUMN ad_swf_params text,
                      ADD COLUMN ad_swf_attributes text,
                      ADD COLUMN ad_users tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_users_unreg tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_users_reg tinyint(1) DEFAULT 0,
                      ADD COLUMN x_ad_users tinyint(1) DEFAULT NULL,
                      ADD COLUMN x_view_users varchar(255) DEFAULT NULL,
                      ADD COLUMN ad_users_adv tinyint(1) DEFAULT 0,
                      ADD COLUMN limit_hits TINYINT(1) DEFAULT 0,
                      ADD COLUMN hits_limit INT(11) DEFAULT 0,
                      ADD COLUMN limit_clicks TINYINT(1) DEFAULT 0,
                      ADD COLUMN clicks_limit INT(11) DEFAULT 0,
                      ADD COLUMN cpm DECIMAL(10,2) UNSIGNED DEFAULT 0.00,
                      ADD COLUMN cpc DECIMAL(10,2) UNSIGNED DEFAULT 0.00,
                      ADD COLUMN per_month DECIMAL(10,2) UNSIGNED DEFAULT 0.00,
                      ADD COLUMN ad_tags TINYINT(1) DEFAULT 0,
                      ADD COLUMN view_tags VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN ad_custom TINYINT(1) DEFAULT 0,
                      ADD COLUMN view_custom VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_id TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_id VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_cats TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_cats VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_authors TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_authors VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_tags TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_tags VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_custom TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_custom VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN adv_nick varchar(50) DEFAULT NULL,
                      ADD COLUMN adv_name varchar(100) DEFAULT NULL,
                      ADD COLUMN adv_mail varchar(50) DEFAULT NULL;";
          $dbResult = $wpdb->query($aSql);
        }
        elseif($dbVersion == '0.4' || $dbVersion == '0.5') {
          $aSql = "ALTER TABLE $aTable
                      CONVERT TO $charset_collate,
                      MODIFY view_pages set('isHome','isSingular','isSingle','isPage','isAttachment','isSearch','is404','isArchive','isTax','isCategory','isTag','isAuthor','isDate','isPostType','isPostTypeArchive') default NULL,
                      ADD COLUMN ad_alt TEXT DEFAULT NULL,
                      ADD COLUMN ad_title varchar(255) DEFAULT NULL,
                      ADD COLUMN ad_no TINYINT(1) NOT NULL DEFAULT 0,
                      ADD COLUMN ad_swf tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_swf_flashvars text,
                      ADD COLUMN ad_swf_params text,
                      ADD COLUMN ad_swf_attributes text,
                      ADD COLUMN ad_users tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_users_unreg tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_users_reg tinyint(1) DEFAULT 0,
                      ADD COLUMN x_ad_users tinyint(1) DEFAULT NULL,
                      ADD COLUMN x_view_users varchar(255) DEFAULT NULL,
                      ADD COLUMN ad_users_adv tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_tags TINYINT(1) DEFAULT 0,
                      ADD COLUMN view_tags VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN ad_custom TINYINT(1) DEFAULT 0,
                      ADD COLUMN view_custom VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_id TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_id VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_cats TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_cats VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_authors TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_authors VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_tags TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_tags VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_custom TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_custom VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN adv_nick varchar(50) DEFAULT NULL,
                      ADD COLUMN adv_name varchar(100) DEFAULT NULL,
                      ADD COLUMN adv_mail varchar(50) DEFAULT NULL;";
          $dbResult = $wpdb->query($aSql);
        }
        elseif($dbVersion == "0.5.1") {
          $aSql = "ALTER TABLE $aTable
                      CONVERT TO $charset_collate,
                      MODIFY view_pages set('isHome','isSingular','isSingle','isPage','isAttachment','isSearch','is404','isArchive','isTax','isCategory','isTag','isAuthor','isDate','isPostType','isPostTypeArchive') default NULL,
                      ADD COLUMN ad_alt TEXT DEFAULT NULL,
                      ADD COLUMN ad_title varchar(255) DEFAULT NULL,
                      ADD COLUMN ad_no TINYINT(1) NOT NULL DEFAULT 0,
                      ADD COLUMN ad_swf tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_swf_flashvars text,
                      ADD COLUMN ad_swf_params text,
                      ADD COLUMN ad_swf_attributes text,
                      ADD COLUMN ad_users tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_users_unreg tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_users_reg tinyint(1) DEFAULT 0,
                      ADD COLUMN x_ad_users tinyint(1) DEFAULT NULL,
                      ADD COLUMN x_view_users varchar(255) DEFAULT NULL,
                      ADD COLUMN ad_users_adv tinyint(1) DEFAULT 0,
                      ADD COLUMN ad_tags TINYINT(1) DEFAULT 0,
                      ADD COLUMN view_tags VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN ad_custom TINYINT(1) DEFAULT 0,
                      ADD COLUMN view_custom VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_tags TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_tags VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN x_custom TINYINT(1) DEFAULT 0,
                      ADD COLUMN x_view_custom VARCHAR(255) DEFAULT NULL,
                      ADD COLUMN adv_nick varchar(50) DEFAULT NULL,
                      ADD COLUMN adv_name varchar(100) DEFAULT NULL,
                      ADD COLUMN adv_mail varchar(50) DEFAULT NULL;";
          $dbResult = $wpdb->query($aSql);
        }
        elseif($vData['major'] < 2) {
          $aSql = "ALTER TABLE $aTable
                    CONVERT TO $charset_collate,
                    MODIFY view_pages set('isHome','isSingular','isSingle','isPage','isAttachment','isSearch','is404','isArchive','isTax','isCategory','isTag','isAuthor','isDate','isPostType','isPostTypeArchive') default NULL,
                    ADD COLUMN ad_swf tinyint(1) DEFAULT 0,
                    ADD COLUMN ad_swf_flashvars text,
                    ADD COLUMN ad_swf_params text,
                    ADD COLUMN ad_swf_attributes text,
                    ADD COLUMN ad_users tinyint(1) DEFAULT 0,
                    ADD COLUMN ad_users_unreg tinyint(1) DEFAULT 0,
                    ADD COLUMN ad_users_reg tinyint(1) DEFAULT 0,
                    ADD COLUMN x_ad_users tinyint(1) DEFAULT NULL,
                    ADD COLUMN x_view_users varchar(255) DEFAULT NULL,
                    ADD COLUMN ad_users_adv tinyint(1) DEFAULT 0,
                    ADD COLUMN ad_title varchar(255) DEFAULT NULL,
                    ADD COLUMN adv_nick varchar(50) DEFAULT NULL,
                    ADD COLUMN adv_name varchar(100) DEFAULT NULL,
                    ADD COLUMN adv_mail varchar(50) DEFAULT NULL;";
          $dbResult = $wpdb->query($aSql);
        }
        elseif($vData['major'] == 2 && $vData['minor'] == 0) {
          $aSql = "ALTER TABLE $aTable
                    MODIFY view_pages set('isHome','isSingular','isSingle','isPage','isAttachment','isSearch','is404','isArchive','isTax','isCategory','isTag','isAuthor','isDate','isPostType','isPostTypeArchive') default NULL,
                    ADD COLUMN ad_title varchar(255) DEFAULT NULL,
                    ADD COLUMN ad_swf tinyint(1) DEFAULT 0,
                    ADD COLUMN ad_swf_flashvars text,
                    ADD COLUMN ad_swf_params text,
                    ADD COLUMN ad_swf_attributes text,
                    ADD COLUMN ad_users tinyint(1) DEFAULT 0,
                    ADD COLUMN ad_users_unreg tinyint(1) DEFAULT 0,
                    ADD COLUMN ad_users_reg tinyint(1) DEFAULT 0,
                    ADD COLUMN x_ad_users tinyint(1) DEFAULT NULL,
                    ADD COLUMN x_view_users varchar(255) DEFAULT NULL,
                    ADD COLUMN ad_users_adv tinyint(1) DEFAULT 0,
                    ADD COLUMN adv_nick varchar(50) DEFAULT NULL,
                    ADD COLUMN adv_name varchar(100) DEFAULT NULL,
                    ADD COLUMN adv_mail varchar(50) DEFAULT NULL;";
          $dbResult = $wpdb->query($aSql);
        }
        elseif($vData['major'] == 2 && $vData['minor'] == 1) {
          $aSql = "ALTER TABLE $aTable
                    MODIFY view_pages set('isHome','isSingular','isSingle','isPage','isAttachment','isSearch','is404','isArchive','isTax','isCategory','isTag','isAuthor','isDate','isPostType','isPostTypeArchive') default NULL;";
          $dbResult = $wpdb->query($aSql);
        }

        if($el) {
          self::errorWrite($eTable, $aTable, $aSql, $dbResult);
          $dbResult = null;
        }

        if($vData['major'] < 2 || ($vData['major'] == 2 && $vData['minor'] == 0)) {
          $aTerms = array();
          $tTable = $wpdb->prefix . "terms";
          $termSql = "SELECT name, slug FROM $tTable;";
          $terms = $wpdb->get_results($termSql, OBJECT_K);
          if($terms) {
            foreach($terms as $term) {
              $aTerms[$term->slug] = $term->name;
            }
          }
          // Categories
          $aSql = "SELECT  $aTable.view_cats
                    FROM $aTable
                    WHERE $aTable.view_cats != '' AND $aTable.view_cats IS NOT NULL
                    GROUP BY $aTable.view_cats;";
          $rows = $wpdb->get_results($aSql, OBJECT_K);
          $numRows = $wpdb->num_rows;
          if($rows) {
            foreach($rows as $row) {
              $slugs = array();
              $cats = explode(',', $row->view_cats);
              foreach($cats as $cat) {
                $slug = array_search($cat, $aTerms);
                if($slug) array_push($slugs, $slug);
              }
              $aSlugs = implode(',', $slugs);
              $wpdb->update($aTable, array('view_cats' => $aSlugs), array('view_cats' => $row->view_cats), '%s', '%s');
            }
          }
          // XCategories
          $aSql = "SELECT  $aTable.x_view_cats
                    FROM $aTable
                    WHERE $aTable.x_view_cats != '' AND $aTable.x_view_cats IS NOT NULL
                    GROUP BY $aTable.x_view_cats;";
          $rows = $wpdb->get_results($aSql, OBJECT_K);
          $numRows = $wpdb->num_rows;
          if($rows) {
            foreach($rows as $row) {
              $slugs = array();
              $cats = explode(',', $row->x_view_cats);
              foreach($cats as $cat) {
                $slug = array_search($cat, $aTerms);
                if($slug) array_push($slugs, $slug);
              }
              $aSlugs = implode(',', $slugs);
              $wpdb->update($aTable, array('x_view_cats' => $aSlugs), array('x_view_cats' => $row->x_view_cats), '%s', '%s');
            }
          }
          // Tags
          $aSql = "SELECT  $aTable.view_tags
                    FROM $aTable
                    WHERE $aTable.view_tags != '' AND $aTable.view_tags IS NOT NULL
                    GROUP BY $aTable.view_tags;";
          $rows = $wpdb->get_results($aSql, OBJECT_K);
          $numRows = $wpdb->num_rows;
          if($rows) {
            foreach($rows as $row) {
              $slugs = array();
              $tags = explode(',', $row->view_tags);
              foreach($tags as $tag) {
                $slug = array_search($tag, $aTerms);
                if($slug) array_push($slugs, $slug);
              }
              $aSlugs = implode(',', $slugs);
              $wpdb->update($aTable, array('view_tags' => $aSlugs), array('view_tags' => $row->view_tags), '%s', '%s');
            }
          }
          // XTags
          $aSql = "SELECT  $aTable.x_view_tags
                    FROM $aTable
                    WHERE $aTable.x_view_tags != '' AND $aTable.x_view_tags IS NOT NULL
                    GROUP BY $aTable.x_view_tags;";
          $rows = $wpdb->get_results($aSql, OBJECT_K);
          $numRows = $wpdb->num_rows;
          if($rows) {
            foreach($rows as $row) {
              $slugs = array();
              $tags = explode(',', $row->x_view_tags);
              foreach($tags as $tag) {
                $slug = array_search($tag, $aTerms);
                if($slug) array_push($slugs, $slug);
              }
              $aSlugs = implode(',', $slugs);
              $wpdb->update($aTable, array('x_view_tags' => $aSlugs), array('x_view_tags' => $row->x_view_tags), '%s', '%s');
            }
          }
        }

        if($el) {
          self::errorWrite($eTable, $aTable, $aSql, $dbResult);
          $dbResult = null;
        }

        if($wpdb->get_var("SHOW TABLES LIKE '$zTable'") != $zTable) {
          $zSql = "CREATE TABLE $zTable (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    name VARCHAR(255) NOT NULL,
                    description VARCHAR(255) DEFAULT NULL,
                    z_default INT(11) DEFAULT 0,
                    z_home INT(11) DEFAULT 0,
                    z_singular INT(11) DEFAULT 0,
                    z_single INT(11) DEFAULT 0,
                    z_ct INT(11) DEFAULT 0,
                    z_single_ct LONGTEXT DEFAULT NULL,
                    z_page INT(11) DEFAULT 0,
                    z_attachment INT(11) DEFAULT 0,
                    z_search INT(11) DEFAULT 0,
                    z_404 INT(11) DEFAULT 0,
                    z_archive INT(11) DEFAULT 0,
                    z_tax INT(11) DEFAULT 0,
                    z_category INT(11) DEFAULT 0,
                    z_cats LONGTEXT DEFAULT NULL,
                    z_tag INT(11) DEFAULT 0,
                    z_author INT(11) DEFAULT 0,
                    z_authors LONGTEXT DEFAULT NULL,
                    z_date INT(11) DEFAULT 0,
                    z_cts INT(11) DEFAULT 0,
                    z_archive_ct LONGTEXT DEFAULT NULL,
                    trash TINYINT(1) DEFAULT 0,
                    PRIMARY KEY (id)
                  ) $charset_collate;";
          dbDelta($zSql);
        }
        elseif(in_array($dbVersion, array('0.1', '0.2', '0.3', '0.3.1', '0.4', '0.5', '0.5.1'))) {
          $zSql = "ALTER TABLE $zTable
                      CONVERT TO $charset_collate,
                      ADD COLUMN z_ct INT(11) DEFAULT 0,
                      ADD COLUMN z_cts INT(11) DEFAULT 0,
                      ADD COLUMN z_single_ct LONGTEXT DEFAULT NULL,
                      ADD COLUMN z_archive_ct LONGTEXT DEFAULT NULL;";
          $dbResult = $wpdb->query($zSql);
        }
        elseif($vData['major'] < 2) {
          $zSql = "ALTER TABLE $zTable CONVERT TO $charset_collate;";
          $dbResult = $wpdb->query($zSql);
        }

        if($el) {
          self::errorWrite($eTable, $zTable, $zSql, $dbResult);
          $dbResult = null;
        }

        if($wpdb->get_var("SHOW TABLES LIKE '$bTable'") != $bTable) {
          $bSql = "CREATE TABLE $bTable (
                      id INT(11) NOT NULL AUTO_INCREMENT,
                      name VARCHAR(255) NOT NULL,
                      description VARCHAR(255) DEFAULT NULL,
                      b_lines INT(11) DEFAULT 2,
                      b_cols INT(11) DEFAULT 2,
                      block_data LONGTEXT DEFAULT NULL,
                      b_margin VARCHAR(30) DEFAULT '5px 5px 5px 5px',
                      b_padding VARCHAR(30) DEFAULT '5px 5px 5px 5px',
                      b_background VARCHAR(30) DEFAULT '#FFFFFF',
                      b_border VARCHAR(30) DEFAULT '0px solid #333333',
                      i_margin VARCHAR(30) DEFAULT '5px 5px 5px 5px',
                      i_padding VARCHAR(30) DEFAULT '5px 5px 5px 5px',
                      i_background VARCHAR(30) DEFAULT '#FFFFFF',
                      i_border VARCHAR(30) DEFAULT '0px solid #333333',
                      trash TINYINT(1) DEFAULT 0,
                      PRIMARY KEY (id)
                  ) $charset_collate;";
          dbDelta($bSql);
        }
        elseif($vData['major'] < 2) {
          $bSql = "ALTER TABLE $bTable CONVERT TO $charset_collate;";
          $dbResult = $wpdb->query($bSql);
        }

        if($el) self::errorWrite($eTable, $pTable, $bSql, $dbResult);

        update_option('sam_db_version', SAM_DB_VERSION);
      }
      update_option('sam_version', SAM_VERSION);
      //$this->getVersions(true);
    }
  }
}
?>