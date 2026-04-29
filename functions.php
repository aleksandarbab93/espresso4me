<?php

// Enqueue child theme style.css (sadrži CSS za widgete).
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('child-style', get_stylesheet_uri(), [], wp_get_theme()->get('Version'));

    if (is_rtl()) {
        wp_enqueue_style('mylisting-rtl', get_template_directory_uri() . '/rtl.css', [], wp_get_theme()->get('Version'));
    }
}, 500);



if (! function_exists('ml_child_is_plugin_active')) {
    function ml_child_is_plugin_active($plugin_file)
    {
        $plugin_file = (string) $plugin_file;

        if ('' === $plugin_file) {
            return false;
        }

        $active_plugins = (array) get_option('active_plugins', []);

        if (in_array($plugin_file, $active_plugins, true)) {
            return true;
        }

        if (is_multisite()) {
            $network_active_plugins = array_keys((array) get_site_option('active_sitewide_plugins', []));

            return in_array($plugin_file, $network_active_plugins, true);
        }

        return false;
    }
}

if (ml_child_is_plugin_active('stringify-helpers/stringify-helpers.php') && file_exists(WP_CONTENT_DIR . '/plugins/stringify-helpers/stringify-helpers.php')) {
    require_once WP_CONTENT_DIR . '/plugins/stringify-helpers/stringify-helpers.php';
}

// Override teme - ukloni post-category prefiks iz URL-ova kategorija
add_filter('option_category_base', function() {
    return '';
}, 20);

// Cities Grid Elementor Widget
add_action('elementor/widgets/register', function($widgets_manager) {
    if (!class_exists('\Elementor\Widget_Base')) return;

    class Cities_Grid_Widget extends \Elementor\Widget_Base {

        public function get_name() { return 'cities-grid'; }
        public function get_title() { return '<strong>27</strong> > Gradovi'; }
        public function get_icon() { return 'eicon-posts-grid'; }
        public function get_categories() { return ['my-listing']; }

        protected function register_controls() {
            $this->start_controls_section('section_cities', ['label' => 'Gradovi']);

            $this->add_control('explore_page', [
                'label'       => 'URL explore stranice',
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '/explore/',
                'description' => 'Npr. /explore/',
            ]);

            $this->add_control('listing_type', [
                'label'   => 'Listing type slug',
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => 'coffee-shop',
            ]);

            $this->add_control('label_suffix', [
                'label'   => 'Sufiks broja (npr. "mjesta za kafu")',
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => 'mjesta za kafu',
            ]);

            $this->add_control('columns', [
                'label'   => 'Kolone',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '4',
                'options' => ['2' => '2', '3' => '3', '4' => '4'],
            ]);

            $repeater = new \Elementor\Repeater();

            $repeater->add_control('city_name', [
                'label'   => 'Naziv grada',
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => 'Podgorica',
            ]);

            $repeater->add_control('region_slug', [
                'label'       => 'Region slug',
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'podgorica',
                'description' => 'Slug iz taksonomije region',
            ]);

            $repeater->add_control('city_image', [
                'label' => 'Slika grada',
                'type'  => \Elementor\Controls_Manager::MEDIA,
            ]);

            $this->add_control('cities', [
                'label'       => 'Gradovi',
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    ['city_name' => 'Podgorica', 'region_slug' => 'podgorica'],
                    ['city_name' => 'Budva',     'region_slug' => 'budva'],
                    ['city_name' => 'Bar',       'region_slug' => 'bar'],
                    ['city_name' => 'Nikšić',    'region_slug' => 'niksic'],
                    ['city_name' => 'Herceg Novi','region_slug' => 'herceg-novi'],
                    ['city_name' => 'Kotor',     'region_slug' => 'kotor'],
                    ['city_name' => 'Tivat',     'region_slug' => 'tivat'],
                    ['city_name' => 'Ulcinj',    'region_slug' => 'ulcinj'],
                ],
                'title_field' => '{{{ city_name }}}',
            ]);

            $this->end_controls_section();

            // Style sekcija
            $this->start_controls_section('section_style', [
                'label' => 'Stil',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]);

            $this->add_control('card_height', [
                'label'      => 'Visina kartice (px)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => ['px' => ['min' => 100, 'max' => 500]],
                'default'    => ['unit' => 'px', 'size' => 220],
                'selectors'  => ['{{WRAPPER}} .cg-city-card' => 'height: {{SIZE}}{{UNIT}};'],
            ]);

            $this->add_control('card_border_radius', [
                'label'      => 'Zaobljeni uglovi (px)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => ['px' => ['min' => 0, 'max' => 40]],
                'default'    => ['unit' => 'px', 'size' => 12],
                'selectors'  => ['{{WRAPPER}} .cg-city-card' => 'border-radius: {{SIZE}}{{UNIT}};'],
            ]);

            $this->add_control('overlay_color', [
                'label'     => 'Boja overlay-a',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => 'rgba(0,0,0,0.45)',
                'selectors' => ['{{WRAPPER}} .cg-city-overlay' => 'background: {{VALUE}};'],
            ]);

            $this->end_controls_section();
        }

        protected function render() {
            $settings = $this->get_settings_for_display();
            $cities   = $settings['cities'] ?? [];
            $type     = $settings['listing_type'];
            $suffix   = $settings['label_suffix'];
            $columns  = intval($settings['columns']);

            if (empty($cities)) return;

            $uid         = 'cg-' . $this->get_id();
            $cols_tablet = min($columns, 2);

            // Dohvati sve region terme i svu meta odjednom (jedna DB query za meta)
            $slugs = array_column($cities, 'region_slug');
            $terms_by_slug = [];
            foreach (get_terms([
                'taxonomy'               => 'region',
                'slug'                   => $slugs,
                'hide_empty'             => false,
                'update_term_meta_cache' => true,
            ]) as $term) {
                $terms_by_slug[$term->slug] = $term;
            }

            // CSS custom properties za dinamičan broj kolona — statički CSS je u style.css
            echo '<div id="' . esc_attr($uid) . '" class="cg-cities-grid" style="--cg-cols:' . $columns . ';--cg-cols-tablet:' . $cols_tablet . ';">';

            foreach ($cities as $city) {
                $slug = $city['region_slug'];
                $term = $terms_by_slug[$slug] ?? null;

                if (!$term) continue;

                $name = esc_html($city['city_name'] ?: $term->name);
                $url  = esc_url(get_term_link($term));

                // Slika: koristi sliku iz widgeta ako je postavljena, inače sliku regiona
                $img = '';
                if (!empty($city['city_image']['url'])) {
                    $img = esc_url($city['city_image']['url']);
                } else {
                    $img_id = get_term_meta($term->term_id, 'image', true);
                    if ($img_id) {
                        $img = esc_url(wp_get_attachment_image_url($img_id, 'large'));
                    }
                }

                // Count: iz listings_full_count meta (tačan po listing type-u)
                $counts_raw = get_term_meta($term->term_id, 'listings_full_count', true);
                $counts_arr = $counts_raw ? json_decode($counts_raw, true) : [];
                $count      = $counts_arr[$type] ?? $term->count;

                $bg = $img
                    ? 'background-image:url(' . $img . ');background-size:cover;background-position:center;'
                    : 'background:#c8a97e;';

                echo '<a href="' . $url . '" class="cg-city-card" style="position:relative;display:flex;align-items:flex-end;text-decoration:none;overflow:hidden;' . $bg . '">';
                echo '<div class="cg-city-overlay" style="position:absolute;inset:0;"></div>';
                echo '<div class="cg-city-info" style="position:relative;z-index:1;padding:18px 20px;color:#fff;width:100%;">';
                echo '<div class="cg-city-name" style="font-size:18px;font-weight:700;line-height:1.2;">' . $name . '</div>';
                echo '<div class="cg-city-count" style="font-size:13px;opacity:0.85;margin-top:4px;">' . $count . ' ' . esc_html($suffix) . '</div>';
                echo '</div>';
                echo '</a>';
            }

            echo '</div>';
        }
    }

    $widgets_manager->register(new Cities_Grid_Widget());
}, 15);


// Dodaj Lokal CTA Elementor Widget
add_action('elementor/widgets/register', function($widgets_manager) {
    if (!class_exists('\Elementor\Widget_Base')) return;

    class Dodaj_Lokal_Widget extends \Elementor\Widget_Base {

        public function get_name()       { return 'dodaj-lokal-cta'; }
        public function get_title()      { return 'Dodaj lokal (CTA)'; }
        public function get_icon()       { return 'eicon-call-to-action'; }
        public function get_categories() { return ['my-listing']; }

        protected function register_controls() {
            // --- Content ---
            $this->start_controls_section('section_content', ['label' => 'Sadržaj']);

            $this->add_control('heading', [
                'label'   => 'Naslov',
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => 'Imate lokal?',
            ]);

            $this->add_control('subheading', [
                'label'   => 'Podnaslov',
                'type'    => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'Dodajte ga na espresso4.me i budite vidljivi svima koji traže gdje na kafu u vašem gradu.',
                'rows'    => 3,
            ]);

            $this->add_control('button_text', [
                'label'   => 'Tekst dugmeta',
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => 'Dodaj lokal',
            ]);

            $this->add_control('button_url', [
                'label'         => 'URL dugmeta',
                'type'          => \Elementor\Controls_Manager::URL,
                'placeholder'   => '/dodaj-kafic/',
                'default'       => ['url' => '/dodaj-kafic/'],
                'show_external' => false,
            ]);

            $this->end_controls_section();

            // --- Style ---
            $this->start_controls_section('section_style', [
                'label' => 'Stil',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]);

            $this->add_control('bg_color', [
                'label'     => 'Pozadinska boja',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#f9f4ef',
                'selectors' => ['{{WRAPPER}} .dl-cta-wrap' => 'background-color: {{VALUE}};'],
            ]);

            $this->add_control('accent_color', [
                'label'   => 'Boja dugmeta',
                'type'    => \Elementor\Controls_Manager::COLOR,
                'default' => '#c8773a',
            ]);

            $this->add_control('text_color', [
                'label'     => 'Boja teksta',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#2b1e12',
                'selectors' => [
                    '{{WRAPPER}} .dl-cta-heading'    => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dl-cta-subheading' => 'color: {{VALUE}};',
                ],
            ]);

            $this->add_control('border_radius', [
                'label'      => 'Zaobljeni uglovi (px)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => ['px' => ['min' => 0, 'max' => 40]],
                'default'    => ['unit' => 'px', 'size' => 16],
                'selectors'  => ['{{WRAPPER}} .dl-cta-wrap' => 'border-radius: {{SIZE}}{{UNIT}};'],
            ]);

            $this->end_controls_section();
        }

        protected function render() {
            $s           = $this->get_settings_for_display();
            $heading     = esc_html($s['heading']);
            $subheading  = esc_html($s['subheading']);
            $btn_text    = esc_html($s['button_text']);
            $btn_url     = esc_url($s['button_url']['url'] ?? '/dodaj-kafic/');
            $accent      = esc_attr($s['accent_color'] ?: '#c8773a');

            echo '<div class="dl-cta-wrap" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:24px;padding:40px 48px;">';

            // Ikona + tekst
            echo '<div class="dl-cta-left" style="display:flex;align-items:center;gap:20px;flex:1;min-width:240px;">';
            echo '<div class="dl-cta-icon" style="flex-shrink:0;width:56px;height:56px;border-radius:50%;background:' . $accent . ';display:flex;align-items:center;justify-content:center;">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8h1a4 4 0 0 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>';
            echo '</div>';
            echo '<div>';
            echo '<div class="dl-cta-heading" style="font-size:22px;font-weight:700;line-height:1.2;margin-bottom:6px;">' . $heading . '</div>';
            echo '<div class="dl-cta-subheading" style="font-size:15px;line-height:1.5;opacity:0.8;max-width:520px;">' . $subheading . '</div>';
            echo '</div>';
            echo '</div>';

            // Dugme
            echo '<a href="' . $btn_url . '" class="dl-cta-button" style="flex-shrink:0;display:inline-flex;align-items:center;gap:8px;background:' . $accent . ';color:#fff;text-decoration:none;font-size:15px;font-weight:600;padding:14px 28px;border-radius:8px;transition:opacity .2s;">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>';
            echo $btn_text;
            echo '</a>';

            echo '</div>';
        }
    }

    $widgets_manager->register(new Dodaj_Lokal_Widget());
}, 15);
