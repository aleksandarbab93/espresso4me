<?php
/**
 * Template Name: Digitalni Meni — Landing Page
 * Template Post Type: page
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$register_url = home_url( '/dodaj-kafic/' );

$demo_listings = get_posts( [
	'post_type'      => 'job_listing',
	'posts_per_page' => 1,
	'post_status'    => 'publish',
	'meta_query'     => [ [ 'key' => '_edm_menu_data', 'compare' => 'EXISTS' ] ],
] );
if ( empty( $demo_listings ) ) {
	$demo_listings = get_posts( [ 'post_type' => 'job_listing', 'posts_per_page' => 1, 'post_status' => 'publish' ] );
}
$demo_url = ! empty( $demo_listings )
	? home_url( '/meni/' . $demo_listings[0]->post_name . '/' )
	: home_url( '/digitalni-meni/' );

get_header();
?>

<div class="edm-lp-page">

<!-- ── HERO ───────────────────────────────────────────────── -->
<section class="lp-hero">
	<div class="lp-hero-inner">

		<div class="lp-hero-content">
			<div class="lp-hero-badge">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
				Digitalni meni na espresso4.me
			</div>

			<h1>Digitalni meni<br>za <em>vaš lokal</em></h1>

			<p class="lp-hero-sub">
				Registrujte se na espresso4.me, kreirajte meni za nekoliko minuta i podijelite QR kod sa gostima — bez tehničke pomoći.
			</p>

			<div class="lp-hero-btns">
				<a href="<?php echo esc_url( $register_url ); ?>" class="lp-btn lp-btn--primary lp-btn--lg">
					Kreirajte besplatno
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
				</a>
				<a href="<?php echo esc_url( $demo_url ); ?>" class="lp-btn lp-btn--ghost lp-btn--lg" target="_blank" rel="noopener">Pogledaj demo</a>
			</div>
		</div>

		<div class="lp-iphone-wrap">
			<div class="lp-iphone">
				<div class="lp-iphone-mute"></div>
				<div class="lp-iphone-vol-up"></div>
				<div class="lp-iphone-vol-dn"></div>
				<div class="lp-iphone-power"></div>

				<div class="lp-iphone-screen">
					<div class="lp-iphone-statusbar">
						<span class="lp-iphone-statusbar-time">9:41</span>
						<div class="lp-iphone-statusbar-icons">
							<svg width="11" height="8" viewBox="0 0 17 12" fill="currentColor"><rect x="0" y="4" width="3" height="8" rx="1" opacity=".4"/><rect x="4.5" y="2.5" width="3" height="9.5" rx="1" opacity=".6"/><rect x="9" y="0.5" width="3" height="11.5" rx="1" opacity=".8"/><rect x="13.5" y="0" width="3" height="12" rx="1"/></svg>
							<svg width="11" height="8" viewBox="0 0 24 18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M1.5 6C5.5 2 10 0 12 0s6.5 2 10.5 6"/><path d="M4.5 9.5C7 7 9.5 6 12 6s5 1 7.5 3.5"/><path d="M7.5 13C9 11.5 10.5 11 12 11s3 .5 4.5 2"/><circle cx="12" cy="16" r="1.5" fill="currentColor"/></svg>
							<svg width="22" height="8" viewBox="0 0 33 12" fill="none"><rect x="0.5" y="0.5" width="28" height="11" rx="3.5" stroke="currentColor" stroke-opacity=".35"/><rect x="2" y="2" width="22" height="8" rx="2" fill="currentColor"/><path d="M30.5 4v4a2 2 0 0 0 0-4z" fill="currentColor" opacity=".4"/></svg>
						</div>
					</div>
					<div class="lp-iphone-di">
						<span class="lp-iphone-di-dot"></span>
						<span class="lp-iphone-di-cam"></span>
					</div>
					<div class="lp-phone-header">
						<div class="lp-phone-logo">
							<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8h1a4 4 0 0 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
						</div>
						<div class="lp-phone-info">
							<div class="lp-phone-name">Cafe Bar Espresso</div>
							<div class="lp-phone-sub">espresso4.me</div>
						</div>
						<div class="lp-phone-tag">Meni</div>
					</div>
					<div class="lp-phone-grid">
						<?php
						$phone_cards = [
							[ 'Kafa',        '<path d="M17 8h1a4 4 0 0 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>' ],
							[ 'Hladne Kafe', '<path d="M2 12h20M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>' ],
							[ 'Čajevi',      '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>' ],
							[ 'Sokovi',      '<path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>' ],
							[ 'Pivo',        '<path d="M17 11h1a3 3 0 0 1 0 6h-1"/><path d="M9 12v6"/><path d="M13 12v6"/><path d="M14 7.5c-1 0-1.44.5-3 .5s-2-.5-3-.5-1.44.5-3 .5"/><path d="M5 4h13a2 2 0 0 1 2 2v4H3V6a2 2 0 0 1 2-2z"/><path d="M5 20H19"/>' ],
							[ 'Hrana',       '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>' ],
						];
						foreach ( $phone_cards as $card ) : ?>
						<div class="lp-phone-card">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?php echo $card[1]; ?></svg>
							<div class="lp-phone-card-name"><?php echo esc_html( $card[0] ); ?></div>
						</div>
						<?php endforeach; ?>
					</div>
					<div class="lp-iphone-bar"></div>
				</div>
			</div>
		</div>

	</div>
</section>

<!-- ── FEATURES ───────────────────────────────────────────── -->
<section class="lp-section" id="usluge">
	<div class="lp-container">
		<div class="lp-section-head">
			<h2>Sve što vam treba za <em>digitalni meni</em></h2>
			<p>Jednostavan alat prilagođen kafićima, restoranima i barovima u Crnoj Gori</p>
		</div>

		<div class="lp-feat-cards">

			<div class="lp-feat-card">
				<div class="lp-feat-circle lp-feat-circle--1">
					<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="5" y="5" width="3" height="3"/><rect x="16" y="5" width="3" height="3"/><rect x="16" y="16" width="3" height="3"/></svg>
				</div>
				<h3>QR Kod &amp; Digitalni Meni</h3>
				<p>Svaki lokal dobija jedinstven QR kod koji gosti skeniraju i odmah vide vaš meni — bez aplikacije.</p>
				<ul class="lp-feat-list">
					<li>Automatski generisan QR kod</li>
					<li>Direktan link za goste</li>
					<li>Neograničene kategorije i stavke</li>
					<li>Uvijek ažuran, bez štampanja</li>
				</ul>
			</div>

			<div class="lp-feat-card">
				<div class="lp-feat-circle lp-feat-circle--2">
					<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
				</div>
				<h3>Lako kreiranje i ažuriranje</h3>
				<p>Upravljajte menijem kroz intuitivan admin panel. Promjene su vidljive gostima odmah, u realnom vremenu.</p>
				<ul class="lp-feat-list">
					<li>Kategorije i podkategorije</li>
					<li>Drag &amp; drop organizacija</li>
					<li>Opisi i cijene za svaku stavku</li>
					<li>Mobilni admin — radite s telefona</li>
				</ul>
			</div>

			<div class="lp-feat-card">
				<div class="lp-feat-circle lp-feat-circle--3">
					<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
				</div>
				<h3>Vidljivost na espresso4.me</h3>
				<p>Registrujte lokal na portalu i budite pronađeni od strane novih gostiju koji traže mjesta za kafa u Crnoj Gori.</p>
				<ul class="lp-feat-list">
					<li>Listing na mapi Crne Gore</li>
					<li>Kontakt info i radno vrijeme</li>
					<li>Recenzije i ocjene gostiju</li>
					<li>Besplatna osnovna registracija</li>
				</ul>
			</div>

		</div>
	</div>
</section>

<!-- ── HOW IT WORKS ───────────────────────────────────────── -->
<section class="lp-section lp-section--alt" id="kako-funkcionise">
	<div class="lp-container">
		<div class="lp-section-head">
			<h2>Tri koraka do <em>digitalnog menija</em></h2>
			<p>Postavite vaš digitalni meni brže nego što možete da popijete espresso</p>
		</div>

		<div class="lp-steps">
			<div class="lp-step">
				<div class="lp-step-num">1</div>
				<h3>Registrujte lokal</h3>
				<p>Kreirajte nalog na espresso4.me i dodajte vaš lokal za nekoliko minuta. Potreban vam je samo naziv, adresa i opis.</p>
			</div>
			<div class="lp-step">
				<div class="lp-step-num">2</div>
				<h3>Napravite meni</h3>
				<p>Kroz intuitivni admin panel dodajte kategorije, podkategorije i stavke sa cijenama. Povucite i prevucite da reorganizujete.</p>
			</div>
			<div class="lp-step">
				<div class="lp-step-num">3</div>
				<h3>Podijelite QR kod</h3>
				<p>Preuzmite vaš QR kod, odštampajte ga i postavite na stolove ili ulaz. Gosti skeniraju i odmah vide meni.</p>
			</div>
		</div>
	</div>
</section>

<!-- ── PRICING ────────────────────────────────────────────── -->
<section class="lp-section" id="cijene">
	<div class="lp-container">
		<div class="lp-section-head">
			<h2>Transparentne <em>cijene</em></h2>
			<p>Bez skrivenih troškova. Odaberite paket koji odgovara vašem lokalu.</p>
		</div>

		<div class="lp-pricing">
			<div class="lp-price-card">
				<div class="lp-price-name">Starter</div>
				<div class="lp-price-desc">Savršeno za lokale koji tek počinju</div>
				<div class="lp-price-amount"><sup>€</sup>—</div>
				<div class="lp-price-period">— / mjesečno</div>
				<hr class="lp-price-divider">
				<ul class="lp-price-list">
					<li>Digitalni meni za 1 lokal</li>
					<li>QR kod generisanje</li>
					<li>Neograničene kategorije</li>
					<li>Neograničene stavke</li>
					<li>Mobilno optimizovano</li>
				</ul>
				<a href="<?php echo esc_url( $register_url ); ?>" class="lp-btn lp-btn--outline">Počni besplatno</a>
			</div>

			<div class="lp-price-card lp-price-card--featured">
				<div class="lp-price-badge">Preporučeno</div>
				<div class="lp-price-name">Pro</div>
				<div class="lp-price-desc">Za ozbiljne lokale sa više potreba</div>
				<div class="lp-price-amount"><sup>€</sup>—</div>
				<div class="lp-price-period">— / mjesečno</div>
				<hr class="lp-price-divider">
				<ul class="lp-price-list">
					<li>Sve iz Starter paketa</li>
					<li>Više lokala</li>
					<li>Prioritetna podrška</li>
					<li>Branding (logo, boje)</li>
					<li>Napredna analitika</li>
				</ul>
				<a href="<?php echo esc_url( $register_url ); ?>" class="lp-btn lp-btn--primary">Odaberi Pro</a>
			</div>
		</div>
	</div>
</section>

<!-- ── CTA BOTTOM ─────────────────────────────────────────── -->
<section class="lp-cta-strip">
	<h2>Spremi za <em>digitalni meni</em>?</h2>
	<p>Pridružite se lokalima koji već koriste espresso4.me digitalni meni.</p>
	<div class="lp-cta-btns">
		<a href="<?php echo esc_url( $register_url ); ?>" class="lp-btn lp-btn--primary lp-btn--lg">Kreirajte besplatno</a>
		<a href="<?php echo esc_url( $demo_url ); ?>" class="lp-btn lp-btn--ghost lp-btn--lg" target="_blank" rel="noopener">Pogledaj demo</a>
	</div>
</section>

</div><!-- .edm-lp-page -->

<?php get_footer(); ?>
