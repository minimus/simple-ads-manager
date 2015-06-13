<?php
if ( ! class_exists( 'SamAd' ) ) {
	class SamAd {
		private $args = array();
		private $useCodes = false;
		private $crawler = false;
		public $id = null;
		public $pid = null;
		public $cid = null;
		public $ad = '';

		public function __construct( $args = null, $useCodes = false, $crawler = false ) {
			if ( ! defined( 'SAM_OPTIONS_NAME' ) ) {
				define( 'SAM_OPTIONS_NAME', 'samPluginOptions' );
			}
			$this->args     = $args;
			$this->useCodes = $useCodes;
			$this->crawler  = $crawler;
			$this->ad       = $this->buildAd( $this->args, $this->useCodes );
		}

		public function init( $args = null, $useCodes = false, $crawler = false ) {
			if ( ! defined( 'SAM_OPTIONS_NAME' ) ) {
				define( 'SAM_OPTIONS_NAME', 'samPluginOptions' );
			}
			$this->args     = $args;
			$this->useCodes = $useCodes;
			$this->crawler  = $crawler;
			$this->ad       = $this->buildAd( $this->args, $this->useCodes );
		}

		private function getSettings() {
			$options = get_option( SAM_OPTIONS_NAME, '' );

			return $options;
		}

		private function getSize( $ss, $width, $height ) {
			if ( $ss == 'custom' ) {
				return array( 'width' => $width, 'height' => $height );
			} else {
				$aSize = explode( "x", $ss );

				return array( 'width' => $aSize[0], 'height' => $aSize[1] );
			}
		}

		private function buildAd( $args = null, $useCodes = false ) {
			if ( is_null( $args ) ) {
				return '';
			}
			if ( empty( $args['id'] ) && empty( $args['name'] ) ) {
				return '';
			}

			global $wpdb;
			$pTable = $wpdb->prefix . "sam_places";
			$aTable = $wpdb->prefix . "sam_ads";

			$settings = self::getSettings();
			$rId      = rand( 1111, 9999 );
			if ( ! empty( $args['id'] ) ) {
				$wid = "sa.id = {$args['id']}";
			} else {
				$wid = "sa.name = '{$args['name']}'";
			}

			$output = '';

			$aSql = "SELECT
                  sa.id,
                  sa.pid,
                  sa.code_mode,
                  sa.ad_code,
                  sa.ad_img,
                  sa.ad_alt,
                  sa.ad_no,
                  sa.ad_target,
                  sa.ad_swf,
                  sa.ad_swf_flashvars,
                  sa.ad_swf_params,
                  sa.ad_swf_attributes,
                  sa.ad_swf_fallback,
                  sa.count_clicks,
                  sa.code_type,
                  sp.code_before,
                  sp.code_after,
                  sp.place_size,
                  sp.place_custom_width,
                  sp.place_custom_height
                FROM $aTable sa
                  INNER JOIN $pTable sp
                    ON sa.pid = sp.id
                WHERE $wid;";
			$ad   = $wpdb->get_row( $aSql, ARRAY_A );

			$this->id  = $ad['id'];
			$this->pid = $ad['pid'];
			$this->cid = "c{$rId}_{$ad['id']}_{$ad['pid']}";

			if ( $ad['code_mode'] == 0 ) {
				$outId  = ( (int) $ad['count_clicks'] == 1 ) ? " id='a" . $rId . "_" . $ad['id'] . "' class='sam_ad'"
					: '';
				$aStart = '';
				$aEnd   = '';
				$iTag   = '';
				if ( ! empty( $settings['adDisplay'] ) ) {
					$target = '_' . $settings['adDisplay'];
				} else {
					$target = '_blank';
				}
				if ( ! empty( $ad['ad_target'] ) ) {
					//$aStart = ((in_array((integer)$ad['ad_no'], array(2,3))) ? '<noindex>' : '')."<a href='{$ad['ad_target']}' target='_blank' ".((in_array((integer)$ad['ad_no'], array(1,3))) ? " rel='nofollow'" : '').">";
					//$aEnd = "</a>".(in_array((integer)$ad['ad_no'], array(2,3))) ? '</noindex>' : '';
					$aStart = "<a $outId href='{$ad['ad_target']}' target='$target' " . ">";
					$aEnd   = "</a>";
				}
				if ( (int) $ad['ad_swf'] ) {
					$id          = "ad-" . $ad['id'] . '-' . $rId;
					$file        = $ad['ad_img'];
					$sizes       = self::getSize( $ad['place_size'], $ad['place_custom_width'], $ad['place_custom_height'] );
					$width       = $sizes['width'];
					$height      = $sizes['height'];
					$flashvars   = ( ! empty( $ad['ad_swf_flashvars'] ) ) ? $ad['ad_swf_flashvars'] : '{}';
					$params      = ( ! empty( $ad['ad_swf_params'] ) ) ? $ad['ad_swf_params'] : '{}';
					$attributes  = ( ! empty( $ad['ad_swf_attributes'] ) ) ? $ad['ad_swf_attributes'] : '{}';
					$text        = ( isset( $ad['ad_swf_fallback'] ) ) ? $ad['ad_swf_fallback']
						: __( 'Flash ad' ) . ' ID:' . $ad['id'];
					$blank_image = plugin_dir_url( __FILE__ ) . "images/blank.gif";

					$output = <<<HTML
<script type="text/javascript">
	var flashvars = $flashvars, params = $params, attributes = $attributes;
	attributes.id = "$id";
	attributes.styleclass = "sam_ad";
	swfobject.embedSWF("$file", "$id", "$width", "$height", "9.0.0", "",
	                   flashvars, params, attributes);
</script>
HTML;

					$banner_html = "<div id=\"$id\">$text</div>";

					if ( $aStart ) {
						$banner_html = <<<HTML
<div class="sam-swf-container" style="position: relative;">
	<div class="sam-flash-overlay" style="position:absolute; z-index:100;">
		$aStart
		<img src="$blank_image" width="$width" height="$height" alt="" style="max-width:100%;" />
		$aEnd
	</div>
	$banner_html
</div>
HTML;
						$output .= $banner_html;
					}
				} else {
					if ( ! empty( $ad['ad_img'] ) ) {
						$iTag =
							"<img src='{$ad['ad_img']}' " . ( ( ! empty( $ad['ad_alt'] ) ) ? " alt='{$ad['ad_alt']}' "
								: '' ) . " />";
					}
					$output = $aStart . $iTag . $aEnd;
				}
			} else {
				if ( $ad['code_type'] == 1 ) {
					ob_start();
					eval( '?>' . $ad['ad_code'] . '<?' );
					$output = ob_get_contents();
					ob_end_clean();
				} else {
					$output = $ad['ad_code'];
				}
			}

			$container = ( $settings['samClasses'] == 'custom' && ! empty( $settings['containerClass'] ) )
				? $settings['containerClass'] : 'sam-container';
			$samAd     = ( $settings['samClasses'] == 'custom' && ! empty( $settings['adClass'] ) )
				? $settings['adClass'] : 'sam-ad';

			$output = "<div id='c{$rId}_{$ad['id']}_{$ad['pid']}' class='{$container} {$samAd}'>{$output}</div>";
			//if(!$this->crawler && !is_admin())
			//$wpdb->query("UPDATE $aTable SET $aTable.ad_hits = $aTable.ad_hits+1 WHERE $aTable.id = {$ad['id']};");

			if ( is_array( $useCodes ) ) {
				$output = $useCodes['before'] . $output . $useCodes['after'];
			} elseif ( $useCodes ) {
				$output = $ad['code_before'] . $output . $ad['code_after'];
			}

			return $output;
		}
	}
}

if ( ! class_exists( 'SamAdPlace' ) ) {
	class SamAdPlace {
		private $args = array();
		private $useCodes = false;
		private $crawler = false;
		public $ad = '';
		public $id = null;
		public $pid = null;
		public $cid = null;
		private $clauses;
		private $force;
		public $sql = '';

		public function __construct( $args = null, $useCodes = false, $crawler = false, $clauses = null, $ajax = false ) {
			global $SAM_Query;

			if ( ! defined( 'SAM_OPTIONS_NAME' ) ) {
				define( 'SAM_OPTIONS_NAME', 'samPluginOptions' );
			}
			$this->args     = $args;
			$this->useCodes = $useCodes;
			$this->crawler  = $crawler;
			if ( is_null( $clauses ) ) {
				$this->clauses = $SAM_Query['clauses'];
			} else {
				$this->clauses = $clauses;
			}
			$this->force = $ajax;

			$this->ad = $this->buildAd( $this->args, $this->useCodes );
		}

		public function init( $args = null, $useCodes = false, $crawler = false, $clauses = null, $ajax = false ) {
			global $SAM_Query;

			if ( ! defined( 'SAM_OPTIONS_NAME' ) ) {
				define( 'SAM_OPTIONS_NAME', 'samPluginOptions' );
			}
			$this->args     = $args;
			$this->useCodes = $useCodes;
			$this->crawler  = $crawler;
			if ( is_null( $clauses ) ) {
				$this->clauses = $SAM_Query['clauses'];
			} else {
				$this->clauses = $clauses;
			}
			$this->force = $ajax;

			$this->ad = $this->buildAd( $this->args, $this->useCodes );
		}

		private function getSettings() {
			$options = get_option( SAM_OPTIONS_NAME, '' );

			return $options;
		}

		private function prepareCodes( $codes, $ind = null ) {
			$index = ( is_null( $ind ) ) ? rand( 1111, 9999 ) : $ind;
			$out   = str_replace( '{samIndex}', $index, $codes );

			return $out;
		}

		private function getSize( $ss, $width, $height ) {
			if ( $ss == 'custom' ) {
				return array( 'width' => $width, 'height' => $height );
			} else {
				$aSize = explode( "x", $ss );

				return array( 'width' => $aSize[0], 'height' => $aSize[1] );
			}
		}

		private function errorWrite( $eTable, $rTable, $eSql = null, $eResult = null, $lastError = null ) {
			global $wpdb;

			//if(!is_null($eResult)) {
			if ( ! $eResult ) {
				$wpdb->insert( $eTable, array(
					'error_date' => current_time( 'mysql' ),
					'table_name' => $rTable,
					'error_type' => 2,
					'error_msg'  => $lastError,
					'error_sql'  => $eSql,
					'resolved'   => 0
				), array( '%s', '%s', '%d', '%s', '%s', '%d' ) );
			} else {
				$wpdb->insert( $eTable, array(
					'error_date' => current_time( 'mysql' ),
					'table_name' => $rTable,
					'error_type' => 0,
					'error_msg'  => __( 'Empty data...', SAM_DOMAIN ),
					'error_sql'  => $eSql,
					'resolved'   => 1
				), array( '%s', '%s', '%d', '%s', '%s', '%d' ) );
			}
			//}
		}

		private function buildAd( $args = null, $useCodes = false ) {
			if ( is_null( $args ) ) {
				return '';
			}
			if ( empty( $args['id'] ) && empty( $args['name'] ) ) {
				return '';
			}
			if ( is_null( $this->clauses ) ) {
				return '';
			}

			$settings = self::getSettings();
			$data     = intval( $useCodes );
			$rId      = rand( 1111, 9999 );
			if ( $settings['adCycle'] == 0 ) {
				$cycle = 1000;
			} else {
				$cycle = $settings['adCycle'];
			}
			$el = isset( $settings['errorlogFS'] );

			$container = ( $settings['samClasses'] == 'custom' && ! empty( $settings['containerClass'] ) )
				? $settings['containerClass'] : 'sam-container';
			$samAd     = ( $settings['samClasses'] == 'custom' && ! empty( $settings['adClass'] ) )
				? $settings['adClass'] : 'sam-ad';
			$samPlace  = ( $settings['samClasses'] == 'custom' && ! empty( $settings['placeClass'] ) )
				? $settings['placeClass'] : 'sam-place';

			global $wpdb;
			$pTable = $wpdb->prefix . "sam_places";
			$aTable = $wpdb->prefix . "sam_ads";
			$eTable = $wpdb->prefix . "sam_errors";

			$whereClause   = $this->clauses['WC'];
			$whereClauseT  = $this->clauses['WCT'];
			$whereClauseW  = $this->clauses['WCW'];
			$whereClause2W = $this->clauses['WC2W'];

			if ( ! empty( $args['id'] ) ) {
				$pId = "sp.id = {$args['id']}";
			} else {
				$pId = "sp.name = '{$args['name']}'";
			}

			$output = "";

			// SQL was changed. Bad way, incorrect logic, but it reflects when the cycle is exceed border values for some set of restrictions.
			$aSql = "
(SELECT
  @pid := sp.id AS pid,
  0 AS aid,
  sp.name,
  sp.patch_source AS code_mode,
  @code_before := sp.code_before AS code_before,
  @code_after := sp.code_after AS code_after,
  @ad_size := IF(sp.place_size = \"custom\", CONCAT(CAST(sp.place_custom_width AS CHAR), \"x\", CAST(sp.place_custom_height AS CHAR)), sp.place_size) AS ad_size,
  sp.patch_code AS ad_code,
  sp.patch_img AS ad_img,
  \"\" AS ad_alt,
  0 AS ad_no,
  sp.patch_link AS ad_target,
  0 AS ad_swf,
  \"\" AS ad_swf_flashvars,
  \"\" AS ad_swf_params,
  \"\" AS ad_swf_attributes,
  \"\" AS ad_swf_fallback,
  sp.patch_adserver AS ad_adserver,
  sp.patch_dfp AS ad_dfp,
  0 AS count_clicks,
  0 AS code_type,
  IF((sp.patch_source = 1 AND sp.patch_adserver) OR sp.patch_source = 2, -1, 1) AS ad_cycle,
  @aca := IFNULL((SELECT AVG(sa.ad_weight_hits*10/(sa.ad_weight*$cycle)) FROM $aTable sa WHERE sa.pid = @pid AND sa.trash IS NOT TRUE AND {$whereClause} {$whereClauseT} {$whereClause2W}), 0) AS aca
FROM {$pTable} sp
WHERE {$pId} AND sp.trash IS FALSE)
UNION
(SELECT
  sa.pid,
  sa.id AS aid,
  sa.name,
  sa.code_mode,
  @code_before AS code_before,
  @code_after AS code_after,
  @ad_size AS ad_size,
  sa.ad_code,
  sa.ad_img,
  sa.ad_alt,
  sa.ad_no,
  sa.ad_target,
  sa.ad_swf,
  sa.ad_swf_flashvars,
  sa.ad_swf_params,
  sa.ad_swf_attributes,
  sa.ad_swf_fallback,
  0 AS ad_adserver,
  0 AS ad_dfp,
  sa.count_clicks,
  sa.code_type,
  IF(sa.ad_weight, (sa.ad_weight_hits*10/(sa.ad_weight*$cycle)), 0) AS ad_cycle,
  @aca AS aca
FROM {$aTable} sa
WHERE sa.pid = @pid AND sa.trash IS FALSE AND {$whereClause} {$whereClauseT} {$whereClauseW})
ORDER BY ad_cycle
LIMIT 1;";

			$ad = $wpdb->get_row( $aSql, ARRAY_A );

			if ( $ad === false ) {
				if ( $el ) {
					self::errorWrite( $eTable, $aTable, $aSql, $ad, $wpdb->last_error );
				}

				return '';
			}

			if ( (integer) $ad['aca'] >= 1 ) {
				$wpdb->update( $aTable, array( 'ad_weight_hits' => 0 ), array( 'pid' => $ad['pid'] ), array( "%d" ), array( "%d" ) );
				$ad = $wpdb->get_row( $aSql, ARRAY_A );
			}

			$this->pid = $ad['pid'];
			$this->id  = $ad['aid'];
			$this->cid = "c{$rId}_{$this->id}_{$this->pid}";
			//$this->sql = $aSql;

			// DFP
			if ( $ad['code_mode'] == 2 ) {
				if ( $settings['dfpMode'] == 'gam' ) {
					if ( ( $settings['useDFP'] == 1 ) && ! empty( $settings['dfpPub'] ) ) {
						$output = "<!-- {$ad['ad_dfp']} -->" . "\n";
						$output .= "<script type='text/javascript'>" . "\n";
						$output .= "  GA_googleFillSlot('{$ad['ad_dfp']}');" . "\n";
						$output .= "</script>" . "\n";
						if ( $useCodes ) {
							$output = ( is_array( $useCodes ) ) ? $useCodes['before'] . $output . $useCodes['after']
								: $ad['code_before'] . $output . $ad['code_after'];
						}

						$output = "<div id='c{$rId}_{$ad['aid']}_{$ad['pid']}' class='{$container} {$samAd}'>{$output}</div>";
					} else {
						$output = '';
					}
				} elseif ( $settings['dfpMode'] == 'gpt' ) {
					if ( $settings['useDFP'] == 1 && ! empty( $settings['dfpNetworkCode'] ) ) {
						include_once( 'sam.functions.php' );
						$key    = array_search( $ad['ad_dfp'], array_column( $settings['dfpBlocks2'], 'name' ) );
						$block  = $settings['dfpBlocks2'][ $key ];
						$output = "<!-- /{$settings['dfpNetworkCode']}/{$block['name']} -->
<div id='{$block['div']}' style='height:{$block['size'][1]}px; width:{$block['size'][0]}px;'>
<script type='text/javascript'>
googletag.cmd.push(function() { googletag.display('{$block['div']}'); });
</script>
</div>";
					}
				}

				return $output;
			}

			// Ad Server (Blocking output of contained ads)
			if ( ( $ad['code_mode'] == 1 ) && ( abs( $ad['ad_adserver'] ) == 1 ) ) {
				$output = self::prepareCodes( $ad['ad_code'], $rId );
				if ( $useCodes ) {
					$output = ( is_array( $useCodes ) ) ? $useCodes['before'] . $output . $useCodes['after']
						: $ad['code_before'] . $output . $ad['code_after'];
				}
				$output = "<div id='c{$rId}_{$ad['aid']}_{$ad['pid']}' class='{$container} {$samAd}'>{$output}</div>";

				return $output;
			}

			// JS Loading
			if ( isset( $settings['adShow'] ) && ( $settings['adShow'] == 'js' ) && ! $this->force ) {
				return "<div id='c{$rId}_0_{$ad['pid']}' class='{$container} {$samPlace}' data-sam='{$data}'></div>";
			}

			// Image and Code Modes
			if ( $ad['code_mode'] == 0 ) {
				// generate link tag
				$outId  = ( (int) $ad['count_clicks'] == 1 ) ?
					" id='a" . rand( 10, 99 ) . "_" . $ad['aid'] . "' class='sam_ad'" : '';
				$aStart = '';
				$aEnd   = '';
				$iTag   = '';
				if ( ! empty( $ad['ad_target'] ) ) {
					$robo    = (integer) $ad['ad_no'];
					$rel     = ( ( in_array( $robo, array( 1, 2, 4 ) ) ) ? ( ( in_array( $robo, array( 1, 4 ) ) )
						? " rel='nofollow'" : " rel='dofollow'" ) : '' );
					$niStart = ( ( in_array( $robo, array( 3, 4 ) ) ) ? '<noindex>' : '' );
					$niEnd   = ( ( in_array( $robo, array( 3, 4 ) ) ) ? '</noindex>' : '' );
					if ( ! empty( $settings['adDisplay'] ) ) {
						$target = '_' . $settings['adDisplay'];
					} else {
						$target = '_blank';
					}
					//$aStart = ((in_array((integer)$ad['ad_no'], array(2,3))) ? '<noindex>' : '')."<a href='{$ad['ad_target']}' target='$target' ".((in_array((integer)$ad['ad_no'], array(1,3))) ? " rel='nofollow'" : '').">";
					//$aEnd = "</a>".(in_array((integer)$ad['ad_no'], array(2,3))) ? '</noindex>' : '';
					$aStart = "{$niStart}<a $outId href='{$ad['ad_target']}' target='{$target}'{$rel}>";
					$aEnd   = "</a>{$niEnd}";
				}

				if ( (int) $ad['ad_swf'] ) {
					$id          = "ad-" . $ad['aid'] . '-' . $rId;
					$file        = $ad['ad_img'];
					$sizes       = self::getSize( $ad['ad_size'], null, null );
					$width       = $sizes['width'];
					$height      = $sizes['height'];
					$flashvars   = ( ! empty( $ad['ad_swf_flashvars'] ) ) ? $ad['ad_swf_flashvars'] : '{}';
					$params      = ( ! empty( $ad['ad_swf_params'] ) ) ? $ad['ad_swf_params'] : '{}';
					$attributes  = ( ! empty( $ad['ad_swf_attributes'] ) ) ? $ad['ad_swf_attributes'] : '{}';
					$text        = ( isset( $ad['ad_swf_fallback'] ) ) ? $ad['ad_swf_fallback']
						: "Flash ad ID: {$ad['aid']}"; //__('Flash ad').' ID:'.$ad['aid'];
					$blank_image = plugin_dir_url( __FILE__ ) . "images/blank.gif";

					$output = <<<HTML
<script type="text/javascript">
	var flashvars = $flashvars, params = $params, attributes = $attributes;
	attributes.id = "$id";
	attributes.styleclass = "sam_ad";
	swfobject.embedSWF("$file", "$id", "$width", "$height", "9.0.0", "",
	                   flashvars, params, attributes);
</script>
HTML;

					$banner_html = "<div id=\"$id\">$text</div>";

					if ( $aStart ) {
						$banner_html = <<<HTML
<div class="sam-swf-container" style="position: relative;">
	<div class="sam-flash-overlay" style="position:absolute; z-index:100;">
		$aStart
		<img src="$blank_image" width="$width" height="$height" alt="" style="max-width:100%;" />
		$aEnd
	</div>
	$banner_html
</div>
HTML;
					}
					$output .= $banner_html;
				} else {
					if ( ! empty( $ad['ad_img'] ) ) {
						$iTag =
							"<img src='{$ad['ad_img']}' " . ( ( ! empty( $ad['ad_alt'] ) ) ? " alt='{$ad['ad_alt']}' "
								: " alt='' " ) . " />";
					}
					$output = $aStart . $iTag . $aEnd;
				}
			} elseif ( $ad['code_mode'] == 1 ) {
				if ( $ad['code_type'] == 1 ) {
					ob_start();
					eval( '?>' . $ad['ad_code'] . '<?' );
					$output = ob_get_contents();
					ob_end_clean();
				} else {
					$output = self::prepareCodes( $ad['ad_code'], $rId );
				}
			}

			//$this->sql = $output;

			$output = "<div id='c{$rId}_{$ad['aid']}_{$ad['pid']}' class='{$container} {$samPlace}' data-sam='{$data}'>{$output}</div>";

			if ( is_array( $useCodes ) ) {
				$output = $useCodes['before'] . $output . $useCodes['after'];
			} elseif ( $useCodes ) {
				$output = $ad['code_before'] . $output . $ad['code_after'];
			}

			// Updating Display Cycle
			if ( ! $this->crawler && ! is_admin() ) {
				$sSql = "UPDATE $aTable sa SET sa.ad_weight_hits = sa.ad_weight_hits + 1 WHERE sa.id = %d;";
				$wpdb->query( $wpdb->prepare( $sSql, $ad['aid'] ) );
			}

			return $output;
		}
	}
}
if ( ! class_exists( 'SamAdPlaceZone' ) ) {
	class SamAdPlaceZone {
		private $args = array();
		private $useCodes = false;
		private $crawler = false;
		private $clauses = null;
		public $ad = '';

		public function __construct( $args = null, $useCodes = false, $crawler = false, $clauses = null ) {
			$this->args     = $args;
			$this->useCodes = $useCodes;
			$this->crawler  = $crawler;
			$this->clauses  = $clauses;
			$this->ad       = self::buildZone( $this->args, $this->useCodes, $this->crawler );
		}

		private function getCustomPostTypes() {
			$args       = array( 'public' => true, '_builtin' => false );
			$output     = 'names';
			$operator   = 'and';
			$post_types = get_post_types( $args, $output, $operator );

			return $post_types;
		}

		private function isCustomPostType() {
			return ( in_array( get_post_type(), self::getCustomPostTypes() ) );
		}

		private function buildZone( $args = null, $useCodes = false, $crawler = false ) {
			if ( is_null( $args ) ) {
				return '';
			}
			if ( empty( $args['id'] ) && empty( $args['name'] ) ) {
				return '';
			}

			global $wpdb;
			$zTable = $wpdb->prefix . "sam_zones";

			$id     = 0; // None
			$output = '';

			if ( ! empty( $args['id'] ) ) {
				$zId = "sz.id = {$args['id']}";
			} else {
				$zId = "sz.name = '{$args['name']}'";
			}

			$zSql = "SELECT
                  sz.id,
                  sz.name,
                  sz.z_default,
                  sz.z_home,
                  sz.z_singular,
                  sz.z_single,
                  sz.z_ct,
                  sz.z_single_ct,
                  sz.z_page,
                  sz.z_attachment,
                  sz.z_search,
                  sz.z_404,
                  sz.z_archive,
                  sz.z_tax,
                  sz.z_taxes,
                  sz.z_category,
                  sz.z_cats,
                  sz.z_tag,
                  sz.z_author,
                  sz.z_authors,
                  sz.z_cts,
                  sz.z_archive_ct,
                  sz.z_date
                FROM $zTable sz
                WHERE $zId AND sz.trash IS FALSE;";
			$zone = $wpdb->get_row( $zSql, ARRAY_A );
			if ( ! empty( $zone ) ) {
				$taxes     = unserialize( $zone['z_taxes'] );
				$cats      = unserialize( $zone['z_cats'] );
				$authors   = unserialize( $zone['z_authors'] );
				$singleCT  = unserialize( $zone['z_single_ct'] );
				$archiveCT = unserialize( $zone['z_archive_ct'] );

				if ( (integer) $zone['z_home'] < 0 ) {
					$zone['z_home'] = $zone['z_default'];
				}
				if ( (integer) $zone['z_singular'] < 0 ) {
					$zone['z_singular'] = $zone['z_default'];
				}
				if ( (integer) $zone['z_single'] < 0 ) {
					$zone['z_single'] = $zone['z_singular'];
				}
				if ( (integer) $zone['z_ct'] < 0 ) {
					$zone['z_ct'] = $zone['z_singular'];
				}
				foreach ( $singleCT as $key => $value ) {
					if ( $value < 0 ) {
						$singleCT[ $key ] = $zone['z_ct'];
					}
				}
				if ( (integer) $zone['z_page'] < 0 ) {
					$zone['z_page'] = $zone['z_singular'];
				}
				if ( (integer) $zone['z_attachment'] < 0 ) {
					$zone['z_attachment'] = $zone['z_singular'];
				}
				if ( (integer) $zone['z_search'] < 0 ) {
					$zone['z_search'] = $zone['z_default'];
				}
				if ( (integer) $zone['z_404'] < 0 ) {
					$zone['z_404'] = $zone['z_default'];
				}
				if ( (integer) $zone['z_archive'] < 0 ) {
					$zone['z_archive'] = $zone['z_default'];
				}
				if ( (integer) $zone['z_tax'] < 0 ) {
					$zone['z_tax'] = $zone['z_archive'];
				}
				if ( ! empty( $taxes ) ) {
					foreach ( $taxes as $key => $value ) {
						if ( $value < 0 ) {
							$taxes[ $key ] = $zone['z_tax'];
						}
					}
				}
				if ( (integer) $zone['z_category'] < 0 ) {
					$zone['z_category'] = $zone['z_tax'];
				}
				foreach ( $cats as $key => $value ) {
					if ( $value < 0 ) {
						$cats[ $key ] = $zone['z_category'];
					}
				}
				if ( (integer) $zone['z_tag'] < 0 ) {
					$zone['z_tag'] = $zone['z_tax'];
				}
				if ( (integer) $zone['z_author'] < 0 ) {
					$zone['z_author'] = $zone['z_archive'];
				}
				foreach ( $authors as $key => $value ) {
					if ( $value < 0 ) {
						$authors[ $key ] = $zone['z_author'];
					}
				}
				if ( (integer) $zone['z_cts'] < 0 ) {
					$zone['z_cts'] = $zone['z_archive'];
				}
				if ( ! empty( $archiveCT ) ) {
					foreach ( $archiveCT as $key => $value ) {
						if ( $value < 0 ) {
							$archiveCT[ $key ] = $zone['z_cts'];
						}
					}
				}
				if ( (integer) $zone['z_date'] < 0 ) {
					$zone['z_date'] = $zone['z_archive'];
				}

				if ( is_home() || is_front_page() ) {
					$id = $zone['z_home'];
				}
				if ( is_singular() ) {
					$id = $zone['z_singular'];
					if ( is_single() ) {
						$id = $zone['z_single'];
						if ( $this->isCustomPostType() ) {
							$id = $zone['z_ct'];
							foreach ( $singleCT as $key => $value ) {
								if ( $key == get_post_type() ) {
									$id = $value;
								}
							}
						}
					}
					if ( is_page() && ! is_front_page() ) {
						$id = $zone['z_page'];
					}
					if ( is_attachment() ) {
						$id = $zone['z_attachment'];
					}
				}
				if ( is_search() ) {
					$id = $zone['z_search'];
				}
				if ( is_404() ) {
					$id = $zone['z_404'];
				}
				if ( is_archive() ) {
					$id = $zone['z_archive'];
					if ( is_tax() ) {
						$id = $zone['z_tax'];
						foreach ( $taxes as $key => $value ) {
							if ( is_tax( $value['tax'], $key ) ) {
								$id = $value['id'];
							}
						}
					}
					if ( is_category() ) {
						$id = $zone['z_category'];
						foreach ( $cats as $key => $value ) {
							if ( is_category( $key ) ) {
								$id = $value;
							}
						}
					}
					if ( is_tag() ) {
						$id = $zone['z_tag'];
					}
					if ( is_author() ) {
						$id = $zone['z_author'];
						foreach ( $authors as $key => $value ) {
							if ( is_author( $key ) ) {
								$id = $value;
							}
						}
					}
					if ( is_post_type_archive() ) {
						$id = $zone['z_cts'];
						foreach ( $archiveCT as $key => $value ) {
							if ( is_post_type_archive( $key ) ) {
								$id = $value;
							}
						}
					}
					if ( is_date() ) {
						$id = $zone['z_date'];
					}
				}
			}

			if ( $id > 0 ) {
				$ad     = new SamAdPlace( array( 'id' => $id ), $useCodes, $crawler, $this->clauses );
				$output = $ad->ad;
			}

			return $output;
		}
	}
}

if ( ! class_exists( 'SamAdBlock' ) ) {
	class SamAdBlock {
		private $args = array();
		private $crawler = false;
		private $clauses = null;
		public $ad = '';

		public function __construct( $args = null, $crawler = false, $clauses = null ) {
			$this->args    = $args;
			$this->crawler = $crawler;
			$this->clauses = $clauses;
			$this->ad      = self::buildBlock( $this->args, $this->crawler );
		}

		private function buildBlock( $args = null, $crawler = false ) {
			if ( is_null( $args ) ) {
				return 'X';
			}
			if ( empty( $args['id'] ) && empty( $args['name'] ) ) {
				return 'Y';
			}

			global $wpdb;
			$bTable = $wpdb->prefix . "sam_blocks";
			$output = '';

			if ( ! empty( $args['id'] ) ) {
				$bId = "sb.id = {$args['id']}";
			} else {
				$bId = "sb.name = '{$args['name']}'";
			}

			$bSql = "SELECT
                 sb.id,
                 sb.name,
                 sb.b_lines,
                 sb.b_cols,
                 sb.block_data,
                 sb.b_margin,
                 sb.b_padding,
                 sb.b_background,
                 sb.b_border,
                 sb.i_margin,
                 sb.i_padding,
                 sb.i_background,
                 sb.i_border,
                 sb.trash
               FROM $bTable sb
               WHERE $bId AND sb.trash IS FALSE;";

			$block = $wpdb->get_row( $bSql, ARRAY_A );
			if ( ! empty( $block ) ) {
				$ads      = unserialize( $block['block_data'] );
				$lines    = (integer) $block['b_lines'];
				$cols     = (integer) $block['b_cols'];
				$blockDiv = "<div style='margin: {$block['b_margin']}; padding: {$block['b_padding']}; background: {$block['b_background']}; border: {$block['b_border']}'>";
				$lineDiv  = "<div class='sam-block-line' style='margin: 0px; padding: 0px;'>";
				$itemDiv  = "<div class='sam-block-item' style='display: inline-block; margin: {$block['i_margin']}; padding: {$block['i_padding']}; background: {$block['i_background']}; border: {$block['i_border']}'>";

				for ( $i = 1; $i <= $lines; $i ++ ) {
					$lDiv = '';
					for ( $j = 1; $j <= $cols; $j ++ ) {
						$id = $ads[ $i ][ $j ]['id'];
						switch ( $ads[ $i ][ $j ]['type'] ) {
							case 'place':
								$place = new SamAdPlace( array( 'id' => $id ), false, $crawler, $this->clauses );
								$iDiv  = $place->ad;
								break;

							case 'ad':
								$ad   = new SamAd( array( 'id' => $id ), false, $crawler );
								$iDiv = $ad->ad;
								break;

							case 'zone':
								$zone = new SamAdPlaceZone( array( 'id' => $id ), false, $crawler, $this->clauses );
								$iDiv = $zone->ad;
								break;

							default:
								$iDiv = '';
								break;
						}
						if ( ! empty( $iDiv ) ) {
							$lDiv .= $itemDiv . $iDiv . "</div>";
						}
					}
					if ( ! empty( $lDiv ) ) {
						$output .= $lineDiv . $lDiv . "</div>";
					}
				}
				$output = $blockDiv . $output . "</div>";
			} else {
				$output = '';
			}

			return $output;
		}
	}
}