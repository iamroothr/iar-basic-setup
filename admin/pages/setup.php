<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once IAR_PLUGIN_PATH . 'includes/wp-config-editor.php';

/**
 * Revert wp-config.php debug constants when the enable-debug module is turned off.
 *
 * @param mixed $old_value Previous option value.
 * @param mixed $value     New option value.
 */
function iar_maybe_revert_wp_config_debug( $old_value, $value ): void {
	$was_enabled = is_array( $old_value ) && ! empty( $old_value['enable-debug'] );
	$is_enabled  = is_array( $value ) && ! empty( $value['enable-debug'] );

	if ( $was_enabled && ! $is_enabled ) {
		iar_wp_config_remove_debug();
	}
}
add_action( 'update_option_iar_basic_setup_options', 'iar_maybe_revert_wp_config_debug', 10, 2 );

/**
 * Register settings
 */
add_action( 'admin_init', function () {
	register_setting( 'iar_basic_setup_group', 'iar_basic_setup_options' );
} );

/**
 * Render page
 */
function iar_basic_setup_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options = get_option( 'iar_basic_setup_options', [] );
	$modules = iar_get_modules();

	$active_count = count( array_filter( (array) $options ) );
	$total_count  = count( $modules );

	// Security score — weighted sum of enabled security modules (max 100)
	$security_weights = [
		'disable-xmlrpc'          => 25,
		'limit-login-attempts'    => 25,
		'custom-login-url'        => 20,
		'disable-author-archives' => 15,
		'disable-rest-api-guests' => 15,
	];
	$security_score = 0;
	foreach ( $security_weights as $key => $weight ) {
		if ( ! empty( $options[ $key ] ) ) {
			$security_score += $weight;
		}
	}

	// Speed savings — estimated frontend ms saved by performance modules
	$speed_weights = [
		'disable-emojis'    => 25,
		'disable-gutenberg' => 20,
		'clean-head'        => 15,
		'hide-admin-bar'    => 12,
		'disable-comments'  => 8,
		'disable-rss-feeds' => 5,
	];
	$speed_savings = 0;
	foreach ( $speed_weights as $key => $ms ) {
		if ( ! empty( $options[ $key ] ) ) {
			$speed_savings += $ms;
		}
	}

	$essentials = [
		'disable-gutenberg' => 'edit_note',
		'disable-comments'  => 'chat_bubble_outline',
		'hide-admin-bar'    => 'visibility_off',
		'clean-head'        => 'cleaning_services',
		'disable-emojis'    => 'mood_bad',
		'svg-support'       => 'image',
		'post-cloner'       => 'content_copy',
		'duplicate-menu'    => 'menu_book',
	];

	$advanced = [
		'disable-auto-updates' => [ 'icon' => 'update_disabled', 'color' => 'amber' ],
		'enable-debug'         => [ 'icon' => 'bug_report',      'color' => 'rose' ],
		'maintenance-mode'     => [ 'icon' => 'construction',    'color' => 'indigo' ],
		'smtp-mail'            => [ 'icon' => 'mail',            'color' => 'sky' ],
		'disable-rss-feeds'    => [ 'icon' => 'rss_feed',        'color' => 'slate' ],
	];

	$security_cards = [
		'disable-xmlrpc'       => [ 'icon' => 'security', 'bar' => true ],
		'custom-login-url'     => [ 'icon' => 'lock',     'bar' => false ],
		'limit-login-attempts' => [ 'icon' => 'block',    'bar' => false ],
	];

	$security_list = [
		'custom-login-logo'       => 'iar-custom-login-logo',
		'disable-author-archives' => null,
		'disable-rest-api-guests' => null,
	];
	?>

	<div class="wrap iar-wrap">

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_basic_setup_group' ); ?>

			<!-- Page Header -->
			<div class="iar-page-header">
				<div class="iar-page-header__text">
					<h2 class="iar-page-title">Module Dashboard</h2>
					<p class="iar-page-subtitle">Optimize your WordPress editorial workflow by enabling or disabling specific core features and utilities.</p>
				</div>
				<span class="iar-system-badge">System Active</span>
			</div>

			<!-- Essentials Section -->
			<div class="iar-section">
				<div class="iar-section-heading">
					<div class="iar-section-heading__icon">
						<span class="material-symbols-outlined">auto_awesome</span>
					</div>
					<span class="iar-section-heading__label">Essentials</span>
				</div>
				<div class="iar-module-list">
					<?php foreach ( $essentials as $key => $icon ) :
						if ( ! isset( $modules[ $key ] ) ) continue;
						$module  = $modules[ $key ];
						$enabled = ! empty( $options[ $key ] );
						?>
						<div class="iar-module-row">
							<div class="iar-module-row__left">
								<span class="material-symbols-outlined iar-module-icon"><?php echo esc_html( $icon ); ?></span>
								<div class="iar-module-info">
									<h4><?php echo esc_html( $module['title'] ); ?></h4>
									<p><?php echo esc_html( $module['desc'] ); ?></p>
								</div>
							</div>
							<label class="iar-toggle">
								<input type="checkbox" name="iar_basic_setup_options[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $enabled ); ?>>
								<div class="iar-toggle-track"><div class="iar-toggle-dot"></div></div>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Advanced & Utility + Security: two-column grid -->
			<div class="iar-two-col">

				<!-- Advanced & Utility -->
				<div class="iar-col-main">
					<div class="iar-section-heading">
						<div class="iar-section-heading__icon">
							<span class="material-symbols-outlined">settings_suggest</span>
						</div>
						<span class="iar-section-heading__label">Advanced &amp; Utility</span>
					</div>
					<div class="iar-module-list">
						<?php foreach ( $advanced as $key => $meta ) :
							if ( ! isset( $modules[ $key ] ) ) continue;
							$module  = $modules[ $key ];
							$enabled = ! empty( $options[ $key ] );
							?>
							<div class="iar-module-row">
								<div class="iar-module-row__left">
									<span class="material-symbols-outlined iar-module-icon iar-module-icon--<?php echo esc_attr( $meta['color'] ); ?>"><?php echo esc_html( $meta['icon'] ); ?></span>
									<div class="iar-module-info">
										<h4><?php echo esc_html( $module['title'] ); ?></h4>
										<p><?php echo esc_html( $module['desc'] ); ?></p>
									</div>
								</div>
								<label class="iar-toggle">
									<input type="checkbox" name="iar_basic_setup_options[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $enabled ); ?>>
									<div class="iar-toggle-track"><div class="iar-toggle-dot"></div></div>
								</label>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Security -->
				<div class="iar-col-side">
					<div class="iar-section-heading">
						<div class="iar-section-heading__icon">
							<span class="material-symbols-outlined">shield</span>
						</div>
						<span class="iar-section-heading__label">Security</span>
					</div>

					<div class="iar-sec-cards">
						<?php foreach ( $security_cards as $key => $meta ) :
							if ( ! isset( $modules[ $key ] ) ) continue;
							$module  = $modules[ $key ];
							$enabled = ! empty( $options[ $key ] );
							?>
							<div class="iar-sec-card">
								<div class="iar-sec-card__header">
									<div class="iar-sec-card__info">
										<span class="iar-sec-card__title"><?php echo esc_html( $module['title'] ); ?></span>
										<span class="iar-sec-card__desc"><?php echo esc_html( $module['desc'] ); ?></span>
									</div>
									<label class="iar-toggle iar-toggle--sm">
										<input type="checkbox" name="iar_basic_setup_options[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $enabled ); ?>>
										<div class="iar-toggle-track"><div class="iar-toggle-dot"></div></div>
									</label>
								</div>
								<?php if ( $meta['bar'] ) : ?>
									<div class="iar-sec-card__bar">
										<div class="iar-sec-card__bar-fill"></div>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="iar-sec-list">
						<?php foreach ( $security_list as $key => $settings_slug ) :
							if ( ! isset( $modules[ $key ] ) ) continue;
							$module  = $modules[ $key ];
							$enabled = ! empty( $options[ $key ] );
							$has_settings = ! empty( $settings_slug ) && ! empty( $options[ $key ] );
							?>
							<div class="iar-sec-list-row">
								<label class="iar-sec-list-label">
									<input
										type="checkbox"
										class="screen-reader-text"
										name="iar_basic_setup_options[<?php echo esc_attr( $key ); ?>]"
										value="1"
										data-status-target="status-<?php echo esc_attr( $key ); ?>"
										data-settings-slug="<?php echo esc_attr( $settings_slug ?? '' ); ?>"
										<?php checked( $enabled ); ?>
									>
									<span class="iar-sec-list-name"><?php echo esc_html( $module['title'] ); ?></span>
								</label>
								<span class="iar-sec-list-status <?php echo $enabled ? 'is-on' : 'is-off'; ?>" id="status-<?php echo esc_attr( $key ); ?>">
									<?php if ( $has_settings ) : ?>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $settings_slug ) ); ?>">CONFIG</a>
									<?php else : ?>
										<?php echo $enabled ? 'ON' : 'OFF'; ?>
									<?php endif; ?>
								</span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

			</div><!-- .iar-two-col -->

			<!-- Footer Stats -->
			<div class="iar-stats-bar">
				<div class="iar-stat">
					<div class="iar-stat__icon-wrap iar-stat__icon-wrap--sky">
						<span class="material-symbols-outlined">electric_bolt</span>
					</div>
					<div>
						<p class="iar-stat__label">Est. Speed Savings</p>
						<p class="iar-stat__value">
							<?php if ( $speed_savings > 0 ) : ?>
								-<?php echo esc_html( $speed_savings ); ?>ms
								<?php if ( $speed_savings >= 30 ) : ?>
									<span class="iar-stat__tag">Optimized</span>
								<?php endif; ?>
							<?php else : ?>
								0ms
							<?php endif; ?>
						</p>
					</div>
				</div>
				<div class="iar-stat">
					<div class="iar-stat__icon-wrap iar-stat__icon-wrap--emerald">
						<span class="material-symbols-outlined">security</span>
					</div>
					<div>
						<p class="iar-stat__label">Security Score</p>
						<p class="iar-stat__value"><?php echo esc_html( $security_score ); ?>/100</p>
					</div>
				</div>
				<div class="iar-stat">
					<div class="iar-stat__icon-wrap iar-stat__icon-wrap--indigo">
						<span class="material-symbols-outlined">toggle_on</span>
					</div>
					<div>
						<p class="iar-stat__label">Active Modules</p>
						<p class="iar-stat__value"><?php echo esc_html( $active_count . ' / ' . $total_count ); ?></p>
					</div>
				</div>
			</div>

			<!-- Save Bar -->
			<div class="iar-save-bar">
				<div class="iar-save-bar__status">
					<span class="iar-save-bar__dot"></span>
					<span>All changes saved.</span>
				</div>
				<div class="iar-save-bar__actions">
					<button type="reset" class="iar-btn-discard">Discard</button>
					<?php submit_button( 'Save Changes', 'primary', 'submit', false, [ 'class' => 'iar-btn-save' ] ); ?>
				</div>
			</div>

		</form>
	</div>
	<?php
}
