<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin menu — top-level page + "Setup" submenu rename
 */
add_action( 'admin_menu', function () {
    add_menu_page(
            'IAR Basic Setup',
            'IAR Basic Setup',
            'manage_options',
            'iar-basic-setup-settings',
            'iar_basic_setup_render_page',
            IAR_PLUGIN_URL . 'assets/images/settings.png',
            80
    );

    // Rename first submenu item
    add_submenu_page(
            'iar-basic-setup-settings',
            'IAR Basic Setup',
            'Setup',
            'manage_options',
            'iar-basic-setup-settings',
            'iar_basic_setup_render_page'
    );
} );

/**
 * Save sidebar collapsed state (AJAX)
 */
add_action( 'wp_ajax_iar_save_sidebar_state', function () {
    check_ajax_referer( 'iar_sidebar_nonce', 'nonce' );
    update_user_meta( get_current_user_id(), 'iar_basic_sidebar_closed', ! empty( $_POST['closed'] ) ? '1' : '0' );
    wp_send_json_success();
} );

/**
 * Enqueue admin assets on plugin pages
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( strpos( $hook, 'iar-basic-setup' ) === false ) {
        return;
    }

    wp_enqueue_style( 'iar-basic-setup-admin', IAR_PLUGIN_URL . 'assets/style/admin/admin.css', [], IAR_PLUGIN_VERSION );

    wp_enqueue_style(
        'material-symbols-outlined',
        'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200',
        [],
        null
    );

    wp_register_script( 'iar-basic-setup-admin-js', false, [], false, true );
    wp_enqueue_script( 'iar-basic-setup-admin-js' );

    wp_localize_script( 'iar-basic-setup-admin-js', 'iarAdminData', [
        'nonce'   => wp_create_nonce( 'iar_sidebar_nonce' ),
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
    ] );

    wp_add_inline_script(
        'iar-basic-setup-admin-js',
        "(function(){
            // ── Save bar: mark unsaved changes ──────────────────────
            var form = document.querySelector('.iar-wrap form');
            var dot  = document.querySelector('.iar-save-bar__dot');
            var msg  = document.querySelector('.iar-save-bar__status span');

            if ( form ) {
                if ( dot ) {
                    form.addEventListener('change', function () {
                        dot.classList.add('has-changes');
                        if ( msg ) msg.textContent = 'Unsaved changes.';
                    });
                }

                // Update compact security list status labels on toggle
                form.querySelectorAll('.iar-sec-list-label input[data-status-target]').forEach(function(input) {
                    input.addEventListener('change', function() {
                        var target = document.getElementById(input.dataset.statusTarget);
                        if ( ! target ) return;
                        var slug = input.dataset.settingsSlug;
                        target.className = 'iar-sec-list-status ' + (input.checked ? 'is-on' : 'is-off');
                        if ( slug && input.checked ) {
                            target.innerHTML = '<a href=\"admin.php?page=' + slug + '\">CONFIG</a>';
                        } else {
                            target.textContent = input.checked ? 'ON' : 'OFF';
                        }
                    });
                });
            }

            // ── Sidebar collapse toggle ──────────────────────────────
            var toggleBtn = document.querySelector('.iar-sidebar-toggle');
            if ( ! toggleBtn ) return;

            var toggleIcon = toggleBtn.querySelector('.material-symbols-outlined');

            toggleBtn.addEventListener('click', function() {
                var collapsed = document.body.classList.toggle('iar-sidebar-collapsed');
                if ( toggleIcon ) {
                    toggleIcon.textContent = collapsed ? 'menu' : 'menu_open';
                }

                var fd = new FormData();
                fd.append('action', 'iar_save_sidebar_state');
                fd.append('nonce',  iarAdminData.nonce);
                fd.append('closed', collapsed ? '1' : '0');
                fetch( iarAdminData.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' } );
            });
        })();"
    );
} );

/**
 * Add body class on all IAR admin pages so CSS can target .wrap on subpages
 */
add_filter( 'admin_body_class', function ( string $classes ): string {
    $screen = get_current_screen();
    if ( $screen && strpos( $screen->id, 'iar-basic-setup' ) !== false ) {
        $classes .= ' iar-admin-page';
    }
    return $classes;
} );

/**
 * Menu icon styling + hide subpage items from WP sidebar
 */
add_action( 'admin_head', function () {
    $hidden_slugs = [
        'iar-post-cloner', 'iar-enable-debug', 'iar-custom-login-url',
        'iar-custom-login-logo', 'iar-limit-login-attempts',
        'iar-maintenance-mode', 'iar-smtp-mail', 'iar-duplicate-menu',
    ];
    echo '<style>';
    echo '#adminmenu .toplevel_page_iar-basic-setup-settings .wp-menu-image img { padding-top: 7px; }';
    // Hide "Setup" first submenu item (duplicate of parent)
    echo '#adminmenu .toplevel_page_iar-basic-setup-settings > .wp-submenu > li.wp-first-item { display:none!important; }';
    foreach ( $hidden_slugs as $slug ) {
        $href = esc_attr( 'admin.php?page=' . $slug );
        echo '#adminmenu a[href="' . $href . '"] { display:none!important; }';
    }
    echo '</style>';
} );

/**
 * Inject secondary plugin sidebar on all IAR admin pages
 */
add_action( 'admin_footer', function () {
    $screen = get_current_screen();
    if ( ! $screen || strpos( $screen->id, 'iar-basic-setup' ) === false ) {
        return;
    }

    $options   = get_option( 'iar_basic_setup_options', [] );
    $current   = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : 'iar-basic-setup-settings';
    $collapsed = get_user_meta( get_current_user_id(), 'iar_basic_sidebar_closed', true ) === '1';

    if ( $collapsed ) {
        add_action( 'admin_footer', function () {
            echo '<script>
                document.body.classList.add("iar-sidebar-collapsed");
                var icon = document.querySelector(".iar-sidebar-toggle .material-symbols-outlined");
                if (icon) icon.textContent = "menu";
            </script>';
        }, 99 );
    }

    $nav_items = [
        [
            'slug'   => 'iar-basic-setup-settings',
            'label'  => 'Setup',
            'icon'   => 'dashboard',
            'always' => true,
        ],
        [
            'slug'   => 'iar-custom-login-logo',
            'label'  => 'Login Logo',
            'icon'   => 'photo_camera',
            'module' => 'custom-login-logo',
        ],
        [
            'slug'   => 'iar-custom-login-url',
            'label'  => 'Login URL',
            'icon'   => 'lock',
            'module' => 'custom-login-url',
        ],
        [
            'slug'   => 'iar-duplicate-menu',
            'label'  => 'Duplicate Menu',
            'icon'   => 'menu_book',
            'module' => 'duplicate-menu',
        ],
        [
            'slug'   => 'iar-enable-debug',
            'label'  => 'Debug Mode',
            'icon'   => 'bug_report',
            'module' => 'enable-debug',
        ],
        [
            'slug'   => 'iar-limit-login-attempts',
            'label'  => 'Login Attempts',
            'icon'   => 'block',
            'module' => 'limit-login-attempts',
        ],
        [
            'slug'   => 'iar-maintenance-mode',
            'label'  => 'Maintenance',
            'icon'   => 'construction',
            'module' => 'maintenance-mode',
        ],
        [
            'slug'   => 'iar-smtp-mail',
            'label'  => 'SMTP Mail',
            'icon'   => 'mail',
            'module' => 'smtp-mail',
        ],
        [
            'slug'   => 'iar-post-cloner',
            'label'  => 'Post Cloner',
            'icon'   => 'content_copy',
            'module' => 'post-cloner',
        ],
    ];
    ?>
    <aside class="iar-plugin-sidebar">
        <div class="iar-plugin-sidebar__header">
            <div class="iar-plugin-sidebar__brand">
                <span class="iar-plugin-sidebar__name-full">
                    <strong class="iar-plugin-sidebar__title">IAR Basic Setup</strong>
                    <span class="iar-plugin-sidebar__sub">Admin Core</span>
                </span>
                <strong class="iar-plugin-sidebar__name-short">IAR</strong>
            </div>
            <button type="button" class="iar-sidebar-toggle" aria-label="Toggle sidebar">
                <span class="material-symbols-outlined">menu_open</span>
            </button>
        </div>
        <nav class="iar-plugin-sidebar__nav">
            <?php foreach ( $nav_items as $item ) :
                $visible = ! empty( $item['always'] ) || ! empty( $options[ $item['module'] ] );
                if ( ! $visible ) {
                    continue;
                }
                $is_active = ( $current === $item['slug'] );
                ?>
                <a
                    href="<?php echo esc_url( admin_url( 'admin.php?page=' . $item['slug'] ) ); ?>"
                    class="iar-sidebar-link<?php echo $is_active ? ' is-active' : ''; ?>"
                    title="<?php echo esc_attr( $item['label'] ); ?>"
                >
                    <span class="material-symbols-outlined"><?php echo esc_html( $item['icon'] ); ?></span>
                    <span class="iar-sidebar-link__label"><?php echo esc_html( $item['label'] ); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="iar-plugin-sidebar__footer">
            <span class="iar-plugin-sidebar__version">v<?php echo esc_html( IAR_PLUGIN_VERSION ); ?></span>
        </div>
    </aside>
    <?php
} );

/**
 * Auto-load all page files
 */
foreach ( glob( __DIR__ . '/pages/*.php' ) as $page_file ) {
    require_once $page_file;
}
