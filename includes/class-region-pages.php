<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kreira i ažurira WordPress stranice za svaki region ("Gdje na kafu u…"),
 * postavlja Yoast SEO meta podatke, page template i linkuje region taxonomy
 * terme na stranice via term meta.
 *
 * Admin UI: Listings > Region Stranice
 */
class ML_Region_Pages {

	public function __construct() {
		add_action( 'admin_menu',                           [ $this, 'add_admin_menu' ] );
		add_action( 'admin_post_ml_import_regions',         [ $this, 'handle_import' ] );
		add_action( 'admin_post_ml_export_regions',         [ $this, 'handle_export' ] );
		add_action( 'admin_post_ml_import_city_images',     [ $this, 'handle_city_images' ] );
		add_filter( 'term_link',                            [ $this, 'filter_region_term_link' ], 10, 3 );
	}

	// =========================================================================
	// Slike gradova — Wikimedia Commons (CC-BY-SA / javna domena)
	// =========================================================================

	private static function get_city_images() {
		return [
			// Gradovi koji NE MAJU sliku u term meta — importujemo sa Wikimedia
			'cetinje'     => [
				'url'     => 'https://upload.wikimedia.org/wikipedia/commons/2/27/Royal_city_of_Cetinje.jpg',
				'title'   => 'Cetinje — kraljevski grad',
				'caption' => 'Cetinje, Crna Gora (Wikimedia Commons, CC-BY-SA)',
			],
			'bijelo-polje' => [
				'url'     => 'https://upload.wikimedia.org/wikipedia/commons/6/62/Panorama_Bijelog_Polja.jpg',
				'title'   => 'Bijelo Polje — panorama',
				'caption' => 'Bijelo Polje, Crna Gora (Wikimedia Commons, CC-BY-SA)',
			],
		];
	}

	// =========================================================================
	// Region data
	// =========================================================================

	public static function get_regions() {
		return [
			[ 'region' => 'Podgorica',   'locative' => 'Podgorici',    'region_slug' => 'podgorica',    'slug' => 'gdje-na-kafu-u-podgorici' ],
			[ 'region' => 'Budva',       'locative' => 'Budvi',        'region_slug' => 'budva',        'slug' => 'gdje-na-kafu-u-budvi' ],
			[ 'region' => 'Bar',         'locative' => 'Baru',         'region_slug' => 'bar',          'slug' => 'gdje-na-kafu-u-baru' ],
			[ 'region' => 'Nikšić',      'locative' => 'Nikšiću',      'region_slug' => 'niksic',       'slug' => 'gdje-na-kafu-u-niksicu' ],
			[ 'region' => 'Herceg Novi', 'locative' => 'Herceg Novom', 'region_slug' => 'herceg-novi',  'slug' => 'gdje-na-kafu-u-herceg-novom' ],
			[ 'region' => 'Kotor',       'locative' => 'Kotoru',       'region_slug' => 'kotor',        'slug' => 'gdje-na-kafu-u-kotoru' ],
			[ 'region' => 'Tivat',       'locative' => 'Tivtu',        'region_slug' => 'tivat',        'slug' => 'gdje-na-kafu-u-tivtu' ],
			[ 'region' => 'Ulcinj',      'locative' => 'Ulcinju',      'region_slug' => 'ulcinj',       'slug' => 'gdje-na-kafu-u-ulcinju' ],
			[ 'region' => 'Cetinje',     'locative' => 'Cetinju',      'region_slug' => 'cetinje',      'slug' => 'gdje-na-kafu-u-cetinju' ],
			[ 'region' => 'Bijelo Polje','locative' => 'Bijelom Polju','region_slug' => 'bijelo-polje', 'slug' => 'gdje-na-kafu-u-bijelom-polju' ],
		];
	}

	private static function build_page_data( array $r ) {
		$name  = $r['region'];
		$loc   = $r['locative'];
		$title = "Gdje na kafu u {$loc}";

		return [
			'region'      => $name,
			'region_slug' => $r['region_slug'],
			'page_title'  => $title,
			'slug'        => $r['slug'],
			'content'     => "<!-- wp:paragraph -->\n<p>Pronađi kafiće i mjesta za kafu u {$loc}.</p>\n<!-- /wp:paragraph -->",
			'yoast'       => [
				'seo_title'        => $title,
				'meta_description' => "Pronađi najbolja mjesta gdje na kafu u {$loc}. Pogledaj preporuke za kafiće, lokale i omiljena mjesta za kafu u gradu.",
				'focus_keyphrase'  => "gdje na kafu {$name}",
			],
		];
	}

	// =========================================================================
	// Upsert
	// =========================================================================

	public static function upsert_page( array $data ) {
		$existing = get_page_by_path( $data['slug'], OBJECT, 'page' );

		$post_args = [
			'post_title'    => wp_strip_all_tags( $data['page_title'] ),
			'post_name'     => $data['slug'],
			'post_content'  => $data['content'],
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'page_template' => 'template-gdje-na-kafu.php',
		];

		if ( $existing ) {
			$post_args['ID'] = $existing->ID;
			$post_id         = wp_update_post( $post_args, true );
			$action          = 'updated';
		} else {
			$post_id = wp_insert_post( $post_args, true );
			$action  = 'created';
		}

		if ( is_wp_error( $post_id ) ) {
			return [ 'success' => false, 'region' => $data['region'] ?? '', 'message' => $post_id->get_error_message() ];
		}

		// Page template meta (WordPress čita ovo)
		update_post_meta( $post_id, '_wp_page_template', 'template-gdje-na-kafu.php' );

		// Region slug — template ga koristi za iframe URL i sliku
		if ( ! empty( $data['region_slug'] ) ) {
			update_post_meta( $post_id, '_edm_region_slug', $data['region_slug'] );
		}

		// Yoast SEO
		if ( ! empty( $data['yoast'] ) ) {
			update_post_meta( $post_id, '_yoast_wpseo_focuskw',  $data['yoast']['focus_keyphrase'] );
			update_post_meta( $post_id, '_yoast_wpseo_title',    $data['yoast']['seo_title'] );
			update_post_meta( $post_id, '_yoast_wpseo_metadesc', $data['yoast']['meta_description'] );
		}

		// Poveži region term sa stranicom
		if ( ! empty( $data['region_slug'] ) ) {
			$term = get_term_by( 'slug', $data['region_slug'], 'region' );
			if ( $term && ! is_wp_error( $term ) ) {
				update_term_meta( $term->term_id, '_edm_region_page_id', $post_id );
			}
		}

		return [ 'success' => true, 'action' => $action, 'post_id' => $post_id, 'region' => $data['region'] ?? '' ];
	}

	// =========================================================================
	// Import / Export
	// =========================================================================

	public static function run_import( $custom_data = null ) {
		$results = [];
		if ( $custom_data !== null ) {
			foreach ( $custom_data as $page_data ) {
				$results[] = self::upsert_page( $page_data );
			}
		} else {
			foreach ( self::get_regions() as $region ) {
				$results[] = self::upsert_page( self::build_page_data( $region ) );
			}
		}
		return $results;
	}

	public static function export_json() {
		$pages = [];
		foreach ( self::get_regions() as $region ) {
			$data     = self::build_page_data( $region );
			$existing = get_page_by_path( $data['slug'], OBJECT, 'page' );
			if ( $existing ) {
				$data['staging_page_id'] = $existing->ID;
			}
			$pages[] = $data;
		}
		return [ 'pages' => $pages ];
	}

	// =========================================================================
	// term_link filter — region taxonomy → stranica
	// =========================================================================

	public function filter_region_term_link( $url, $term, $taxonomy ) {
		if ( $taxonomy !== 'region' ) {
			return $url;
		}
		$page_id = (int) get_term_meta( $term->term_id, '_edm_region_page_id', true );
		if ( ! $page_id ) {
			return $url;
		}
		return get_permalink( $page_id ) ?: $url;
	}

	// =========================================================================
	// Admin POST handlers
	// =========================================================================

	public function handle_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Nemate dozvolu.' );
		}
		check_admin_referer( 'ml_import_regions' );

		$results = [];

		if ( ! empty( $_FILES['regions_json']['tmp_name'] ) ) {
			$json    = file_get_contents( sanitize_text_field( $_FILES['regions_json']['tmp_name'] ) );
			$decoded = json_decode( $json, true );
			if ( $decoded && ! empty( $decoded['pages'] ) ) {
				$results = self::run_import( $decoded['pages'] );
			} else {
				$results[] = [ 'success' => false, 'region' => 'JSON', 'message' => 'Nevažeći JSON format.' ];
			}
		} else {
			$results = self::run_import();
		}

		set_transient( 'ml_region_import_results', $results, 60 );
		wp_redirect( add_query_arg( [ 'page' => 'ml-region-pages', 'imported' => 1 ], admin_url( 'edit.php?post_type=job_listing' ) ) );
		exit;
	}

	public function handle_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Nemate dozvolu.' );
		}
		check_admin_referer( 'ml_export_regions' );

		$data = self::export_json();
		$json = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="region-pages-' . gmdate( 'Y-m-d' ) . '.json"' );
		header( 'Content-Length: ' . strlen( $json ) );
		nocache_headers();
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	// =========================================================================
	// Handler — import slika sa Wikimedia Commons
	// =========================================================================

	public function handle_city_images() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Nemate dozvolu.' );
		}
		check_admin_referer( 'ml_import_city_images' );

		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$city_images = self::get_city_images();
		$results     = [];

		foreach ( $city_images as $region_slug => $img ) {
			// Pronađi stranicu koja odgovara ovom regionu
			$page = null;
			foreach ( self::get_regions() as $r ) {
				if ( $r['region_slug'] === $region_slug ) {
					$page = get_page_by_path( $r['slug'], OBJECT, 'page' );
					break;
				}
			}

			if ( ! $page ) {
				$results[] = [ 'slug' => $region_slug, 'status' => 'skip', 'msg' => 'Stranica ne postoji — pokreni import stranica prvo.' ];
				continue;
			}

			// Ako stranica već ima featured image, preskoči
			if ( get_post_thumbnail_id( $page->ID ) ) {
				$results[] = [ 'slug' => $region_slug, 'status' => 'skip', 'msg' => 'Već ima featured image.' ];
				continue;
			}

			// Provjeri da li region term već ima image meta
			$term    = get_term_by( 'slug', $region_slug, 'region' );
			$term_img = $term ? get_term_meta( $term->term_id, 'image', true ) : null;
			if ( $term_img ) {
				// Term ima sliku — postavi je i kao featured image na stranici
				set_post_thumbnail( $page->ID, (int) $term_img );
				$results[] = [ 'slug' => $region_slug, 'status' => 'linked', 'msg' => 'Linked term image (ID ' . $term_img . ') kao featured image.' ];
				continue;
			}

			// Downloaduj i importuj sliku sa Wikimedia
			$attachment_id = media_sideload_image( $img['url'], $page->ID, $img['title'], 'id' );

			if ( is_wp_error( $attachment_id ) ) {
				$results[] = [ 'slug' => $region_slug, 'status' => 'error', 'msg' => $attachment_id->get_error_message() ];
				continue;
			}

			// Postavi caption i alt text
			wp_update_post( [
				'ID'           => $attachment_id,
				'post_excerpt' => $img['caption'],
			] );
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $img['title'] );

			// Postavi kao featured image stranice
			set_post_thumbnail( $page->ID, $attachment_id );

			// Postavi i kao term image za region term (za konzistentnost sa ostalim gradovima)
			if ( $term ) {
				update_term_meta( $term->term_id, 'image', $attachment_id );
			}

			$results[] = [ 'slug' => $region_slug, 'status' => 'ok', 'msg' => 'Importovana i postavljena (ID ' . $attachment_id . ').' ];
		}

		// Postavi i term images za preostalih 8 gradova koji imaju term image ali ne i featured image na stranici
		foreach ( self::get_regions() as $r ) {
			if ( isset( $city_images[ $r['region_slug'] ] ) ) {
				continue; // Već obrađeni gore
			}
			$page = get_page_by_path( $r['slug'], OBJECT, 'page' );
			if ( ! $page || get_post_thumbnail_id( $page->ID ) ) {
				continue;
			}
			$term = get_term_by( 'slug', $r['region_slug'], 'region' );
			if ( ! $term ) {
				continue;
			}
			$term_img = get_term_meta( $term->term_id, 'image', true );
			if ( $term_img ) {
				set_post_thumbnail( $page->ID, (int) $term_img );
				$results[] = [ 'slug' => $r['region_slug'], 'status' => 'linked', 'msg' => 'Term image linked kao featured image.' ];
			}
		}

		set_transient( 'ml_city_images_results', $results, 60 );
		wp_redirect( add_query_arg( [ 'page' => 'ml-region-pages', 'images' => 1 ], admin_url( 'edit.php?post_type=job_listing' ) ) );
		exit;
	}

	// =========================================================================
	// Admin page
	// =========================================================================

	public function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=job_listing',
			'Region Stranice — Gdje na kafu',
			'Region Stranice',
			'manage_options',
			'ml-region-pages',
			[ $this, 'render_admin_page' ]
		);
	}

	public function render_admin_page() {
		$regions      = self::get_regions();
		$city_images  = self::get_city_images();
		$results      = get_transient( 'ml_region_import_results' );
		$img_results  = get_transient( 'ml_city_images_results' );
		if ( $results )     { delete_transient( 'ml_region_import_results' ); }
		if ( $img_results ) { delete_transient( 'ml_city_images_results' ); }
		?>
		<div class="wrap">
			<h1>Region Stranice &mdash; &ldquo;Gdje na kafu u&hellip;&rdquo;</h1>
			<p class="description">Kreira i ažurira stranice za svaki region. Svaka stranica prikazuje hero sliku grada i explore mapu sa listom mjesta.</p>

			<?php if ( $results ) : ?>
			<div class="notice notice-success is-dismissible" style="margin-top:16px;">
				<p><strong>Import stranica završen:</strong></p>
				<ul style="margin-left:16px;list-style:disc;">
				<?php foreach ( $results as $r ) : ?>
					<?php if ( $r['success'] ) : ?>
						<li><strong><?php echo esc_html( $r['region'] ); ?></strong> &mdash; <?php echo $r['action'] === 'created' ? 'kreirana' : 'ažurirana'; ?> (ID: <?php echo (int) $r['post_id']; ?>)</li>
					<?php else : ?>
						<li style="color:#c00;"><strong><?php echo esc_html( $r['region'] ?? '?' ); ?></strong> &mdash; <?php echo esc_html( $r['message'] ?? '' ); ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>

			<?php if ( $img_results ) : ?>
			<div class="notice notice-success is-dismissible" style="margin-top:16px;">
				<p><strong>Import slika završen:</strong></p>
				<ul style="margin-left:16px;list-style:disc;">
				<?php foreach ( $img_results as $r ) :
					$icon = $r['status'] === 'ok' ? '&#10003;' : ( $r['status'] === 'linked' ? '&#8594;' : ( $r['status'] === 'error' ? '&#10007;' : '&#8212;' ) );
					$color = $r['status'] === 'error' ? 'color:#c00;' : '';
				?>
					<li style="<?php echo $color; ?>"><?php echo $icon; ?> <strong><?php echo esc_html( $r['slug'] ); ?></strong>: <?php echo esc_html( $r['msg'] ); ?></li>
				<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>

			<div style="display:flex;gap:12px;margin:24px 0;flex-wrap:wrap;align-items:flex-start;">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="ml_import_regions">
					<?php wp_nonce_field( 'ml_import_regions' ); ?>
					<button type="submit" class="button button-primary">&#9654; Kreiraj / Ažuriraj sve region stranice</button>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="ml_import_city_images">
					<?php wp_nonce_field( 'ml_import_city_images' ); ?>
					<button type="submit" class="button button-primary" style="background:#2ea04c;border-color:#239440;">
						&#128247; Postavi slike gradova
					</button>
					<p class="description" style="margin-top:6px;max-width:280px;">
						Preuzima slike sa Wikimedia Commons za gradove bez slike, a za ostale linkuje term image kao featured image.
					</p>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="ml_export_regions">
					<?php wp_nonce_field( 'ml_export_regions' ); ?>
					<button type="submit" class="button button-secondary">&#8595; Eksportuj JSON</button>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
					<input type="hidden" name="action" value="ml_import_regions">
					<?php wp_nonce_field( 'ml_import_regions' ); ?>
					<input type="file" name="regions_json" accept=".json" style="margin-bottom:6px;display:block;">
					<button type="submit" class="button button-secondary">&#8593; Importuj iz JSON</button>
				</form>
			</div>

			<h2>Status stranica i slika</h2>
			<table class="wp-list-table widefat fixed striped" style="max-width:1100px;">
				<thead>
					<tr>
						<th style="width:110px;">Region</th>
						<th style="width:200px;">Slug</th>
						<th style="width:100px;">Stranica</th>
						<th style="width:50px;">ID</th>
						<th style="width:80px;">Slika</th>
						<th style="width:80px;">Linked</th>
						<th style="width:120px;">Akcije</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $regions as $region ) :
					$data        = self::build_page_data( $region );
					$existing    = get_page_by_path( $data['slug'], OBJECT, 'page' );
					$term        = get_term_by( 'slug', $region['region_slug'], 'region' );
					$linked      = $existing && $term
						? ( (int) get_term_meta( $term->term_id, '_edm_region_page_id', true ) === $existing->ID )
						: false;

					// Status slike: featured image ili term image
					$feat_img   = $existing ? get_post_thumbnail_id( $existing->ID ) : null;
					$term_img   = $term ? get_term_meta( $term->term_id, 'image', true ) : null;
					$has_img    = $feat_img || $term_img;
					$img_source = $feat_img ? 'Featured' : ( $term_img ? 'Term img' : null );
				?>
					<tr>
						<td><strong><?php echo esc_html( $region['region'] ); ?></strong></td>
						<td><code style="font-size:11px;"><?php echo esc_html( $data['slug'] ); ?></code></td>
						<td>
							<?php if ( $existing ) : ?>
								<span style="color:#2ea04c;font-weight:600;">&#10003;</span>
							<?php else : ?>
								<span style="color:#999;">&#8212;</span>
							<?php endif; ?>
						</td>
						<td><?php echo $existing ? (int) $existing->ID : '&#8212;'; ?></td>
						<td>
							<?php if ( $has_img ) : ?>
								<span style="color:#2ea04c;" title="<?php echo esc_attr( $img_source ); ?>">&#10003; <?php echo esc_html( $img_source ); ?></span>
							<?php elseif ( isset( $city_images[ $region['region_slug'] ] ) ) : ?>
								<span style="color:#f0a500;">&#9888; Treba import</span>
							<?php else : ?>
								<span style="color:#c00;">&#10007; Nema</span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $term ) : ?>
								<span style="color:<?php echo $linked ? '#2ea04c' : '#f0a500'; ?>;">
									<?php echo $linked ? '&#10003;' : '&#9888;'; ?>
								</span>
							<?php else : ?>
								<span style="color:#999;">&#8212;</span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $existing ) : ?>
								<a href="<?php echo esc_url( get_permalink( $existing->ID ) ); ?>" target="_blank">Pregledaj</a>
								&nbsp;|&nbsp;
								<a href="<?php echo esc_url( get_edit_post_link( $existing->ID ) ); ?>">Uredi</a>
							<?php else : ?>
								&#8212;
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
