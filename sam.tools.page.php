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

		private function getPointerContent( $pointer = false, $upgrade = true ) {
			if( $upgrade ) {
				$alt    = __( 'Upgrade Now', SAM_DOMAIN );
				$image  = SAM_URL . (($pointer) ? 'images/upgrade-380.jpg' : 'images/upgrade-sidebar.jpg');
				$about  = __( 'About SAM Pro Lite...', SAM_DOMAIN );
				$docs   = __( 'SAM Pro Lite Documentation', SAM_DOMAIN );
				$intro  = __( 'Get the extended feature set of the <strong>Simple Ads Manager</strong> plugin.', SAM_DOMAIN );
				$intro2 = __( 'Upgrade to the SAM Pro Lite now!', SAM_DOMAIN );
				$margin = ( ( $pointer ) ? " margin: 20px 15px 0;" : '' );

				$out =
						"<div style='text-align: center;{$margin}'>" .
						"<a href='http://codecanyon.net/item/sam-pro-lite/12721925?ref=minimus_simplelib' target='_blank'>" .
						"<img src='{$image}' alt='{$alt}'>" .
						"</a>" .
						"</div>" .
						"<p>{$intro}<br><a href='http://codecanyon.net/item/sam-pro-lite/12721925?ref=minimus_simplelib'><strong>{$intro2}</strong></a></p>" .
						"<p><a target='_blank' href='http://uncle-sam.info/sam-pro-lite/sam-pro-lite-info/features/'>{$about}</a><br>" .
						"<a href='http://uncle-sam.info/category/sam-pro-lite/sam-pro-lite-docs/' target='_blank'>{$docs}</a>";
			}
			else {
				$alt    = __( 'SAM Pro (Free Edition)', SAM_DOMAIN );
				$image  = SAM_URL . (($pointer) ? 'images/install-380.png' : 'images/install-sidebar.png');
				$about  = __( 'About SAM Pro (Free Edition)...', SAM_DOMAIN );
				$docs   = __( 'SAM Pro (Free Edition) Documentation', SAM_DOMAIN );
				$intro  = __( '<strong>Congratulations!</strong> Your server configuration allows you to install and use the plugin <strong>SAM Pro (Free Edition)</strong> instead of <strong>Simple Ads Manager</strong> plugin. Read the documentation and make the right decision.', SAM_DOMAIN );
				$intro2 = __( 'Install SAM Pro (Free Edition) now!', SAM_DOMAIN );
				$margin = ( ( $pointer ) ? " margin: 20px 15px 0;" : '' );
				$installLink = admin_url('plugin-install.php?tab=search&s=sam-pro-free');

				$out =
						"<div style='text-align: center;{$margin}'>" .
						"<a href='http://uncle-sam.info/sam-pro-free/sam-pro-free-info/features-3/' target='_blank'>" .
						"<img src='{$image}' alt='{$alt}'>" .
						"</a>" .
						"</div>" .
						"<p>{$intro}</p><p style='text-align: center;'><a href='{$installLink}' class='button-primary'><strong>{$intro2}</strong></a></p>" .
						"<p><a target='_blank' href='http://uncle-sam.info/sam-pro-free/sam-pro-free-info/features-3/'>{$about}</a><br>" .
						"<a href='http://uncle-sam.info/category/sam-pro-free/sam-pro-free-docs/' target='_blank'>{$docs}</a>";
			}
			return $out;
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
						<?php if(version_compare(PHP_VERSION, '5.3', '>=')) { ?>
							<div class="postbox opened">
								<h3 class="hndle"><?php _e('SAM Pro (Free Edition)', SAM_DOMAIN) ?></h3>
								<div class="inside">
									<?php echo self::getPointerContent(false, false); ?>
								</div>
							</div>
						<?php } ?>
						<div class='postbox opened'>
							<h3 class="hndle"><?php _e('Resources', SAM_DOMAIN) ?></h3>
							<div class="inside">
								<a href="http://codecanyon.net/item/sam-pro-lite/12721925" target="_blank">
									<img src="<?php echo SAM_IMG_URL; ?>upgrade-sidebar.jpg">
								</a>
								<p>
									<?php _e('New semi-professional version of the <strong>Simple Ads Manager</strong> plugin.', SAM_DOMAIN); ?><br>
									<strong><a href="http://uncle-sam.info/sam-pro-lite/sam-pro-lite-info/features/"><?php _e('Info', SAM_DOMAIN); ?></a></strong> |
									<strong><a href="http://uncle-sam.info/category/sam-pro-lite/sam-pro-lite-docs/"><?php _e('Documentation', SAM_DOMAIN); ?></a></strong> |
									<strong><a href="http://codecanyon.net/item/sam-pro-lite/12721925"><?php _e('Purchase', SAM_DOMAIN); ?></a></strong>
								</p>
								<ul>
									<li><a target="_blank" href="http://uncle-sam.info/"><?php _e('UncleSAM Project', SAM_DOMAIN); ?></a></li>
									<li><a target='_blank' href='http://wordpress.org/extend/plugins/simple-ads-manager/'><?php _e("Wordpress Plugin Page", SAM_DOMAIN); ?></a></li>
									<li><a target='_blank' href='http://www.simplelib.com/?p=480'><?php _e("Author Plugin Page", SAM_DOMAIN); ?></a></li>
									<li><a target='_blank' href='http://forum.simplelib.com/index.php?forums/simple-ads-manager.13/'><?php _e("Support Forum", SAM_DOMAIN); ?></a></li>
									<li><a target='_blank' href='http://www.simplelib.com/'><?php _e("Author's Blog", SAM_DOMAIN); ?></a></li>
								</ul>
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