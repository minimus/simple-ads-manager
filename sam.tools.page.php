<?php
/**
 * Created by PhpStorm.
 * Author: minimus
 * Date: 20.06.2015
 * Time: 7:08
 */

if ( ! class_exists( 'SamToolsPage' ) ) {
	class SamToolsPage {
		private $settings;
		private $action;
		private $error;
		private $message;

		public function __construct( $settings ) {
			$this->settings = $settings;
			$this->action   = ( isset( $_GET['action'] ) ) ? $_GET['action'] : 'view';
			if ( $this->action != 'view' ) {
				self::doAction( $this->action );
			}
		}

		private function doAction( $action ) {
			switch ( $action ) {
				case 'clear-stats':
					include_once( 'sam.tools.php' );
					$cleaner = new SamStatsCleaner( $this->settings );
					$msg = $cleaner->clear();
					$this->error = $msg['error'];
					$this->message = $msg['msg'];
					break;
				case 'kill-stats':
					include_once( 'sam.tools.php' );
					$cleaner = new SamStatsCleaner( $this->settings );
					$msg = $cleaner->kill();
					$this->error = $msg['error'];
					$this->message = $msg['msg'];
					break;
			}
		}

		public function page() {
			global $wpdb, $wp_version;
			$row = $wpdb->get_row('SELECT VERSION() AS ver', ARRAY_A);
			$sqlVersion = $row['ver'];
			$mem = ini_get('memory_limit');
			?>
			<div class="wrap">
				<h2><?php _e( 'SAM Tools', SAM_DOMAIN ); ?></h2>
				<?php
				if($this->action != 'view') {
					$class = ($this->error) ? 'sam2-warning' : 'sam2-info';
					echo "<div class='{$class}'><p>{$this->message}</p></div>";
				}
				?>
				<div id='poststuff' class='metabox-holder has-right-sidebar'>
					<div id="side-info-column" class="inner-sidebar">
						<div class='postbox opened'>
							<h3 class="hndle"><?php _e( 'System Info', SAM_DOMAIN ) ?></h3>
							<div class="inside">
								<p>
									<?php
									echo __('Wordpress Version', SAM_DOMAIN).': <strong>'.$wp_version.'</strong><br/>';
									echo __('SAM Version', SAM_DOMAIN).': <strong>'.SAM_VERSION.'</strong><br/>';
									echo __('SAM DB Version', SAM_DOMAIN).': <strong>'.SAM_DB_VERSION.'</strong><br/>';
									echo __('PHP Version', SAM_DOMAIN).': <strong>'.PHP_VERSION.'</strong><br/>';
									echo __('MySQL Version', SAM_DOMAIN).': <strong>'.$sqlVersion.'</strong><br/>';
									echo __('Memory Limit', SAM_DOMAIN).': <strong>'.$mem.'</strong>';
									?>
								</p>
								<p>
									<?php _e('Note! If you have detected a bug, include this data to bug report.', SAM_DOMAIN); ?>
								</p>
							</div>
						</div>
					</div>
					<div id="post-body">
						<div id="post-body-content">
							<div class='ui-sortable sam-section'>
								<div class='postbox opened'>
									<h3 class="hndle"><?php _e('Statistic', SAM_DOMAIN); ?></h3>
									<div class="inside">
										<p><strong><?php _e('Reset Statistical Data', SAM_DOMAIN); ?></strong></p>
										<p>
											<a href="<?php echo admin_url('admin.php'); ?>?page=sam-tools&action=clear-stats" class="button-secondary">
												<?php _e('Reset Statistics', SAM_DOMAIN); ?>
											</a>
										</p>
										<p><?php _e('Clearing statistics outside the keeping period. Use this button in case of problems with the automatic cleaning of the statistics table. The data within the keeping period specified by the plugin parameters will not be affected by this action.', SAM_DOMAIN); ?></p>
										<p><strong><?php _e('Remove Statistical Data', SAM_DOMAIN); ?></strong></p>
										<p>
											<a href="<?php echo admin_url('admin.php'); ?>?page=sam-tools&action=kill-stats" class="button-secondary">
												<?php _e('Remove Statistics', SAM_DOMAIN); ?>
											</a>
										</p>
										<p><?php _e('Complete removal of all statistical data from the statistics table.', SAM_DOMAIN); ?></p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
		}
	}
}