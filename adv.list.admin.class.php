<?php
/**
 * Created by PhpStorm.
 * Author: minimus
 * Date: 13.05.2015
 * Time: 7:27
 */

if( ! class_exists( 'SamAdvertisersList' ) ) {
	class SamAdvertisersList {
		private $settings;
		private $advertiser = null;
		private $mode;
		private $view;
		private $apage;
		private $action;
		private $mailSent = false;

		public function __construct( $options ) {
			$this->settings = $options;
			$this->mode = (isset($_GET['mode'])) ? $_GET['mode'] : 'list';
			$this->view = (isset($_GET['view'])) ? $_GET['view'] : 'active';
			$this->advertiser = (isset($_GET['adv'])) ? $_GET['adv'] : null;
			$this->apage = (isset($_GET['apage'])) ? absint($_GET['apage']) : 1;
			$this->action = (isset($_GET['action'])) ? $_GET['action'] : 'view';
		}

		public function page() {
			global $wpdb;
			$aTable = $wpdb->prefix . "sam_ads";
			$pTable = $wpdb->prefix . "sam_places";
			$places_per_page = $this->settings['placesPerPage'];
			$items_per_page = $this->settings['itemsPerPage'];

			switch($this->mode) {
				case 'list':
					if($this->action == 'send') {
						include_once('sam.tools.php');
						$mailer = new SamMailer($this->settings);
						$this->mailSent = $mailer->sendMail($this->advertiser, 'nick');
					}
					$advNum     = $wpdb->get_var( "SELECT COUNT(DISTINCT wsa.adv_nick) FROM {$aTable} wsa WHERE wsa.adv_nick IS NOT NULL AND wsa.adv_nick <> '';" );
					$start      = $offset = ( $this->apage - 1 ) * $places_per_page;
					$page_links = paginate_links( array(
						'base'      => admin_url( 'admin.php' ) . '?page=sam-adverts&apage=%#%',
						'format'    => '&apage=%#%',
						'prev_text' => __( '&laquo;' ),
						'next_text' => __( '&raquo;' ),
						'total'     => ceil( $advNum / $places_per_page ),
						'current'   => $this->apage
					) );
					?>
					<div class='wrap'>
						<h2><?php _e( 'Advertisers', SAM_DOMAIN ); ?></h2>
						<?php
						if($this->action == 'send') {
							if($this->mailSent) {
								$class = 'updated below-h2';
								$mess = __('The report has been sent to', SAM_DOMAIN) . ' ' . $this->advertiser . '.';
							}
							else {
								$class = 'error below-h2';
								$mess = __('Unexpected error. The report has not been sent to') . ' ' . $this->advertiser . '.';
							}
							echo "<div class='{$class}'><p>{$mess}</p></div>";
						}
						?>
						<div class="tablenav">
							<div class="tablenav-pages">
								<?php
								$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', SAM_DOMAIN ) . '</span>%s',
									number_format_i18n( $start + 1 ),
									number_format_i18n( min( $this->apage * $places_per_page, $advNum ) ),
									'<span class="total-type-count">' . number_format_i18n( $advNum ) . '</span>',
									$page_links
								);
								echo $page_links_text;
								?>
							</div>
						</div>
						<div class="clear"></div>
						<table class="widefat fixed" cellpadding="0">
							<thead>
							<tr>
								<th id="t-idg" class="manage-column column-title" style="width:25%;"
								    scope="col"><?php _e( 'Nick Name', SAM_DOMAIN ); ?></th>
								<th id="t-name" class="manage-column column-title" style="width:60%;"
								    scope="col"><?php _ex( 'Name', 'User Name', SAM_DOMAIN );?></th>
								<th id="t-size" class="manage-column column-title" style="width:15%; text-align: center"
								    scope="col"><?php _ex( 'Ads', 'User Ads', SAM_DOMAIN ); ?></th>
							</tr>
							</thead>
							<tfoot>
							<tr>
								<th id="b-idg" class="manage-column column-title" style="width:25%;"
								    scope="col"><?php _e( 'Nick Name', SAM_DOMAIN ); ?></th>
								<th id="b-name" class="manage-column column-title" style="width:60%;"
								    scope="col"><?php _ex( 'Name', 'User Name', SAM_DOMAIN );?></th>
								<th id="b-size" class="manage-column column-title" style="width:15%; text-align: center"
								    scope="col"><?php _ex( 'Ads', 'User Ads', SAM_DOMAIN ); ?></th>
							</tr>
							</tfoot>
							<tbody>
							<?php
							$sql  = "SELECT wsa.adv_nick, wsa.adv_name, COUNT(wsa.adv_nick) AS ads FROM {$aTable} wsa WHERE wsa.adv_nick IS NOT NULL AND wsa.adv_nick <> '' GROUP BY wsa.adv_nick ORDER BY wsa.adv_nick LIMIT {$offset}, {$places_per_page};";
							$rows = $wpdb->get_results( $sql, ARRAY_A );
							$i    = 0;
							if ( ! is_array( $rows ) || empty( $rows ) ) {
								echo "<tr class=\"no-items\"><th class=\"colspanchange\" colspan='7'>" . __( 'There are no data ...', SAM_DOMAIN ) . "</th></tr>";
							} else {
								foreach ( $rows as $row ) {
									?>
									<tr id="<?php echo $row['adv_nick']; ?>"
									    class="<?php echo( ( $i & 1 ) ? 'alternate' : '' ); ?> author-self status-publish iedit"
									    valign="top">
										<th class="post-title column-title">
											<strong style='display: inline;'><a
													href="<?php echo admin_url( 'admin.php' ); ?>?page=sam-adverts&mode=ads&adv=<?php echo $row['adv_nick']; ?>"><?php echo $row['adv_nick']; ?></a></strong>

											<div class="row-actions">
												<span class="edit">
													<a href="<?php echo admin_url( 'admin.php' ); ?>?page=sam-adverts&mode=ads&adv=<?php echo $row['adv_nick']; ?>"
														title="<?php _e( 'View List of Ads', SAM_DOMAIN ) ?>">
														<?php _e( 'View Ads', SAM_DOMAIN ); ?>
													</a> | </span>
												<span class="mail">
													<a href="<?php echo admin_url('admin.php'); ?>?page=sam-adverts<?php if($this->apage > 1) echo "&apage={$this->apage}" ?>&action=send&adv=<?php echo $row['adv_nick']; ?>"
														title="<?php echo __('Send Report to', SAM_DOMAIN) . ' ' . $row['adv_nick']; ?>">
														<?php _e('Send Report', SAM_DOMAIN); ?>
													</a>
												</span>
											</div>
										</th>
										<th class="post-title column-title"><?php echo $row['adv_name']; ?></th>
										<th class="post-title column-title">
											<div class="post-com-count-wrapper" style="text-align: center;"><?php echo $row['ads']; ?></div>
										</th>
									</tr>
									<?php
									$i ++;
								}
							}
							?>
							</tbody>
						</table>
						<div class="tablenav">
							<div class="tablenav-pages">
								<?php
								$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', SAM_DOMAIN ) . '</span>%s',
									number_format_i18n( $start + 1 ),
									number_format_i18n( min( $this->apage * $places_per_page, $advNum ) ),
									'<span class="total-type-count">' . number_format_i18n( $advNum ) . '</span>',
									$page_links
								);
								echo $page_links_text;
								?>
							</div>
						</div>
					</div>
					<?php
					break;
				case 'ads':
					$trash_num  = $wpdb->get_var( "SELECT COUNT(*) FROM {$aTable} WHERE (trash = TRUE) AND (adv_nick = \"{$this->advertiser}\");" );
					$active_num = $wpdb->get_var( "SELECT COUNT(*) FROM {$aTable} WHERE (trash = FALSE) AND (adv_nick = \"{$this->advertiser}\");" );
					if ( is_null( $active_num ) ) {
						$active_num = 0;
					}
					if ( is_null( $trash_num ) ) {
						$trash_num = 0;
					}
					$all_num = $trash_num + $active_num;
					//$places = $wpdb->get_row("SELECT id, name, trash FROM $pTable WHERE id = $this->advertiser", ARRAY_A);

					$total = ( ( $this->view !== 'all' ) ? ( ( $this->view === 'trash' ) ? $trash_num : $active_num ) : $all_num );
					$start = $offset = ( $this->apage - 1 ) * $items_per_page;

					$page_links = paginate_links( array(
						'base'      => admin_url( 'admin.php' ) . '?page=sam-adverts&apage=%#%',
						'format'    => '&apage=%#%',
						'prev_text' => __( '&laquo;' ),
						'next_text' => __( '&raquo;' ),
						'total'     => ceil( $total / $items_per_page ),
						'current'   => $this->apage
					) );
					?>
					<div class="wrap">
						<h2><?php echo __( 'Ads associated with the advertiser ', SAM_DOMAIN ) . "<strong>" . $this->advertiser . "</strong>"; ?></h2>
						<ul class="subsubsub">
							<li><a <?php if($this->view === 'all') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-adverts&mode=ads&view=all&adv=<?php echo $this->advertiser ?>"><?php _e('All', SAM_DOMAIN); ?></a> (<?php echo $all_num; ?>) | </li>
							<li><a <?php if($this->view === 'active') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-adverts&mode=ads&view=active&adv=<?php echo $this->advertiser ?>"><?php _e('Active', SAM_DOMAIN); ?></a> (<?php echo $active_num; ?>) | </li>
							<li><a <?php if($this->view === 'trash') echo 'class="current"';?> href="<?php echo admin_url('admin.php'); ?>?page=sam-adverts&mode=ads&view=trash&adv=<?php echo $this->advertiser ?>"><?php _e('Trash', SAM_DOMAIN); ?></a> (<?php echo $trash_num; ?>)</li>
						</ul>
						<div class="tablenav">
							<div class="alignleft">
								<a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-adverts"><?php _e('Back to Advertisers List', SAM_DOMAIN); ?></a>
							</div>
							<div class="tablenav-pages">
								<?php
								$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', SAM_DOMAIN ) . '</span>%s',
									number_format_i18n( $start + 1 ),
									number_format_i18n( min( $this->apage * $items_per_page, $total ) ),
									'<span class="total-type-count">' . number_format_i18n( $total ) . '</span>',
									$page_links
								);
								echo $page_links_text;
								?>
							</div>
						</div>
						<div class="clear"></div>
						<table class="widefat fixed" cellpadding="0">
							<thead>
							<tr>
								<th id="t-id" class="manage-column column-title" style="width:5%;" scope="col"><?php _e('ID', SAM_DOMAIN); ?></th>
								<th id="t-ad" class='manage-column column-title' style="width:50%;" scope="col"><?php _e('Advertisement', SAM_DOMAIN); ?></th>
								<th id="t-place" class="manage-column column-title" style="width:35%;" scope="col"><?php _e('Ads Place', SAM_DOMAIN);?></th>
								<th id="t-size" class="manage-column column-title" style="width:10%;" scope="col"><?php _e('Size', SAM_DOMAIN);?></th>
							</tr>
							</thead>
							<tfoot>
							<tr>
								<th id="b-id" class="manage-column column-title" style="width:5%;" scope="col"><?php _e('ID', SAM_DOMAIN); ?></th>
								<th id="b-ad" class='manage-column column-title' style="width:50%;" scope="col"><?php _e('Advertisement', SAM_DOMAIN); ?></th>
								<th id="b-place" class="manage-column column-title" style="width:35%;" scope="col"><?php _e('Ads Place', SAM_DOMAIN);?></th>
								<th id="b-size" class="manage-column column-title" style="width:10%;" scope="col"><?php _e('Size', SAM_DOMAIN);?></th>
							</tr>
							</tfoot>
							<tbody>
							<?php
							$where = (($this->view !== 'all') ? " AND wsa.trash = " . (($this->view === 'trash') ? '1' : '0') : '');
							$sql = "SELECT
  wsa.id,
  wsa.pid,
  wsa.name AS ad,
  wsa.description,
  wsp.name AS place,
  wsa.trash,
  IF(wsp.place_size = 'custom', CONCAT(wsp.place_custom_width, 'x', wsp.place_custom_height), wsp.place_size) AS size
  FROM {$aTable} wsa
  INNER JOIN {$pTable} wsp ON wsa.pid = wsp.id
  WHERE wsa.adv_nick = '{$this->advertiser}' {$where}
  LIMIT {$offset}, {$items_per_page};";
							$rows = $wpdb->get_results($sql, ARRAY_A);
							$i = 0;
							if(!is_array($rows) || empty($rows)) {
								echo "<tr class=\"no-items\" valign=\"top\"><th class=\"colspanchange\" colspan='6'>" . __('There are no data ...', SAM_DOMAIN) . "</th></tr>";
							}
							else {
								foreach($rows as $row) {
							?>
								<tr id="<?php echo $row['id'];?>" class="<?php echo (($i & 1) ? 'alternate' : ''); ?> author-self status-publish iedit" valign="top">
									<th class="post-title column-title"><?php echo $row['id']; ?></th>
									<td class="column-icon column-title">
										<strong><a href="<?php echo admin_url('admin.php'); ?>?page=sam-edit&action=edit&mode=item&item=<?php echo $row['id']; ?>"><?php echo $row['ad'];?></a><?php echo ((($row['trash'] == true) && ($this->view === 'all')) ? '<span class="post-state"> - '.__('in Trash', SAM_DOMAIN).'</span>' : ''); ?></strong><br/><?php echo $row['description'];?>
										<div class="row-actions">
											<span class="edit"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-edit&action=edit&mode=item&item=<?php echo $row['id'] ?>" title="<?php _e('Edit this Item of Ads Place', SAM_DOMAIN) ?>"><?php _e('Edit', SAM_DOMAIN); ?></a> | </span>
											<?php
											if($row['trash'] == true) {
												?>
												<span class="untrash"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=<?php echo $this->view ?>&iaction=untrash&item=<?php echo $row['pid'] ?>&iitem=<?php echo $row['id'] ?>" title="<?php _e('Restore this Ad from the Trash', SAM_DOMAIN) ?>"><?php _e('Restore', SAM_DOMAIN); ?></a> | </span>
												<span class="delete"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=<?php echo $this->view ?>&iaction=kill&item=<?php echo $row['pid'] ?>&iitem=<?php echo $row['id'] ?>" title="<?php _e('Remove this Ad permanently', SAM_DOMAIN) ?>"><?php _e('Remove permanently', SAM_DOMAIN); ?></a> </span>
											<?php } else { ?><span class="delete"><a href="<?php echo admin_url('admin.php'); ?>?page=sam-list&action=items&mode=<?php echo $this->view ?>&iaction=delete&item=<?php echo $row['pid'] ?>&iitem=<?php echo $row['id'] ?>" title="<?php _e('Move this item to the Trash', SAM_DOMAIN) ?>"><?php _e('Delete', SAM_DOMAIN); ?></a> </span><?php } ?>
										</div>
									</td>
									<td class="post-title column-title"><?php echo $row['place'];?></td>
									<td class="post-title column-title"><?php echo $row['size'];?></td>
								</tr>
								<?php
								$i++; }}
								?>
							</tbody>
						</table>
						<div class="tablenav">
							<div class="alignleft">
								<a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=sam-adverts"><?php _e('Back to Advertisers List', SAM_DOMAIN); ?></a>
							</div>
							<div class="tablenav-pages">
								<?php
								$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', SAM_DOMAIN ) . '</span>%s',
									number_format_i18n( $start + 1 ),
									number_format_i18n( min( $this->apage * $items_per_page, $total ) ),
									'<span class="total-type-count">' . number_format_i18n( $total ) . '</span>',
									$page_links
								);
								echo $page_links_text;
								?>
							</div>
						</div>
					</div>
					<?php
					break;

			}
		}
	}
}