<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Qa;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    protected array $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->site = $this->site();
    }

    /**
     * Both teams with a member each, home, contact and both assistants:
     * what every public page and the menus need.
     */
    protected function site(): array
    {
        $teams = $this->teams();
        $contact = $this->contact();
        // The contact page needs a published image per device (F14)
        $this->contactImage($contact, $this->upload('qa-contact-desktop.jpg'));
        $this->contactImage($contact, $this->upload('qa-contact-mobile.jpg'), ['device' => 'mobile']);

        return [
            'teams' => $teams,
            'home' => $this->home(),
            'contact' => $contact,
            'member' => $this->member($teams['luks'], ['firstname' => 'QA-Anna', 'name' => 'Muster']),
            'assistants' => [
                'luks' => $this->assistant($teams['luks']),
                'vogt' => $this->assistant($teams['vogt']),
            ],
        ];
    }

    protected function memberUrl(string $locale, $member = null): string
    {
        $member ??= $this->site['member'];

        return "/{$locale}/team-{$member->team->slug}/" . \Str::slug($member->firstname . '-' . $member->name) . "/{$member->id}";
    }

    public static function pageTypes(): array
    {
        $pages = [];
        foreach (['de', 'fr', 'en'] as $locale) {
            $contact = ['de' => 'kontakt', 'fr' => 'contacter', 'en' => 'contact'][$locale];
            $assistant = ['de' => 'assistenz', 'fr' => 'assistence', 'en' => 'assistenz'][$locale];
            $pages += [
                "home {$locale}" => [$locale, "/{$locale}/home"],
                "team luks {$locale}" => [$locale, "/{$locale}/team-luks"],
                "team vogt {$locale}" => [$locale, "/{$locale}/team-vogt"],
                "member {$locale}" => [$locale, 'member'],
                "assistant luks {$locale}" => [$locale, "/{$locale}/team-luks/{$assistant}"],
                "assistant vogt {$locale}" => [$locale, "/{$locale}/team-vogt/{$assistant}"],
                "contact {$locale}" => [$locale, "/{$locale}/{$contact}"],
            ];
        }

        return $pages;
    }

    protected function page(string $locale, string $url)
    {
        return $this->get($url === 'member' ? $this->memberUrl($locale) : $url);
    }

    #[Qa('pg-home-urls')]
    public function test_home_urls_load(): void
    {
        foreach (['/', '/de', '/fr', '/en', '/de/home', '/fr/home', '/en/home'] as $url) {
            $this->get($url)->assertOk()->assertSee('QA-Home', false);
        }
    }

    #[Qa('pg-lang-attr', 'pp-member-all', 'pp-assistant', 'pp-contact')]
    #[DataProvider('pageTypes')]
    public function test_every_page_type_loads_with_its_lang_attribute(string $locale, string $url): void
    {
        $this->page($locale, $url)->assertOk()->assertSee("<html lang=\"{$locale}\">", false);
    }

    #[Qa('pg-localized-urls')]
    public function test_localized_slugs(): void
    {
        foreach (['/de/kontakt', '/fr/contacter', '/en/contact', '/de/team-vogt/assistenz', '/fr/team-luks/assistence'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    #[Qa('pg-404')]
    public function test_unknown_url_shows_the_styled_404(): void
    {
        $this->get('/de/gibt-es-nicht')
            ->assertNotFound()
            ->assertSee(__('page.header-404'))
            ->assertDontSee('Symfony');
        $this->get('/de/team-luks/qa/999999')->assertNotFound();
    }

    #[Qa('pg-seo')]
    #[DataProvider('pageTypes')]
    public function test_title_and_description(string $locale, string $url): void
    {
        $html = $this->page($locale, $url)->getContent();
        $this->assertMatchesRegularExpression('~<title>[^<]*' . preg_quote(config('seo.title'), '~') . '</title>~u', $html);

        $description = fn () => $this->assertMatchesRegularExpression('~<meta name="description" content="[^"]{5,}">~u', $html);
        // Pages without their own description use config('seo.description_' . locale); there's none for fr
        if ($locale === 'fr' && preg_match('~assist|contact~', $url)) {
            $this->knownFinding('F15', 'no default meta description for fr', $description);
        }
        $description();
    }

    #[Qa('pg-seo')]
    public function test_member_description_uses_seo_beschreibung(): void
    {
        $member = $this->site['member'];
        $member->setTranslation('meta_description', 'de', 'QA-SEO Beschreibung')->save();

        $this->get($this->memberUrl('de'))
            ->assertSee('<title>QA-Anna Muster – ' . config('seo.title') . '</title>', false)
            ->assertSee('<meta name="description" content="QA-SEO Beschreibung">', false);

        // Falls back to the first 30 words of the description
        $member->setTranslation('meta_description', 'de', null)->save();
        $this->get($this->memberUrl('de'))->assertSee('<meta name="description" content="QA-Beschreibung DE">', false);
    }

    #[Qa('pg-member-menu')]
    public function test_member_menu_lists_published_members_in_order_plus_assistenz(): void
    {
        $luks = $this->site['teams']['luks'];
        $this->site['member']->forceFill(['order' => 2])->save();
        $first = $this->member($luks, ['firstname' => 'QA-Erste', 'order' => 0]);
        $second = $this->member($luks, ['firstname' => 'QA-Zweite', 'order' => 1]);
        $this->member($luks, ['firstname' => 'QA-Versteckt', 'order' => 3, 'publish' => 0]);
        $this->member($this->site['teams']['vogt'], ['firstname' => 'QA-Vogt']);

        $html = $this->get('/de/team-luks')->getContent();
        preg_match('~<nav class="menu-team">.*?</nav>~s', $html, $menu);
        preg_match_all('~title="([^"]+)"~', $menu[0], $titles);

        $this->assertSame(
            ['Team Luks', 'QA-Erste Member', 'QA-Zweite Member', 'QA-Anna Muster', 'Assistenz'],
            $titles[1]
        );
    }

    #[Qa('pg-member-menu')]
    public function test_member_menu_marks_the_current_member(): void
    {
        $html = $this->get($this->memberUrl('de'))->getContent();
        $this->assertMatchesRegularExpression('~title="QA-Anna Muster"\s+class="is-active"~', $html);
    }

    #[Qa('pp-team-unpublished')]
    public function test_unpublished_team_images_do_not_appear(): void
    {
        $luks = $this->site['teams']['luks'];
        $this->teamImage($luks, $this->upload('qa-shown.jpg'));
        $this->teamImage($luks, $this->upload('qa-hidden.jpg'), ['publish' => 0]);

        $this->get('/de/team-luks')->assertSee('/img/crop/qa-shown.jpg/', false)->assertDontSee('qa-hidden.jpg');
    }

    #[Qa('pp-home-hero')]
    public function test_home_hero_carries_the_saved_crop(): void
    {
        $this->homeImage($this->site['home'], $this->upload('qa-hero.jpg'), [
            'coords_w' => 800, 'coords_h' => 500, 'coords_x' => 100, 'coords_y' => 50,
        ]);

        $html = $this->get('/de')->getContent();
        foreach (['2400/1500', '1600/1000', '1200/750', '900/560'] as $size) {
            $this->assertStringContainsString("/img/crop/qa-hero.jpg/{$size}/800,500,100,50", $html);
        }
    }

    #[Qa('pp-member-crops')]
    public function test_crops_anchored_top_left_are_applied(): void
    {
        // As stored by the API: 0 becomes NULL
        $this->memberImage($this->site['member'], $this->upload('qa-topleft.jpg'), [
            'coords_w' => 600, 'coords_h' => 720, 'coords_x' => null, 'coords_y' => null,
        ]);

        $this->get($this->memberUrl('de'))->assertSee('/img/crop/qa-topleft.jpg/1600/1920/600,720,0,0', false);
    }

    #[Qa('pp-member-all')]
    public function test_unpublished_member_page_is_not_public(): void
    {
        $hidden = $this->member($this->site['teams']['luks'], ['publish' => 0]);

        $this->knownFinding('F7', 'unpublished member pages are public', fn () => $this->get($this->memberUrl('de', $hidden))->assertNotFound());
    }

    #[Qa('pp-member-sections')]
    public function test_member_sections_show_when_filled_and_hide_when_empty(): void
    {
        $member = $this->site['member'];
        $member->setTranslation('area', 'de', '<p>QA-Gebiete</p>')
            ->setTranslation('languages', 'de', '<p>QA-Sprachen</p>')
            ->setTranslation('biography', 'de', '<p>QA-Werdegang</p>')
            ->setTranslation('membership', 'de', '<p>QA-Mitgliedschaften</p>')
            ->setTranslation('publication', 'de', '<p>QA-Publikationsliste</p>')
            ->save();
        $this->publication($member);

        $this->get($this->memberUrl('de'))
            ->assertSee(['QA-Info DE', 'QA-Beschreibung DE', 'Tätigkeitsgebiete', 'QA-Gebiete', 'Sprachen', 'QA-Sprachen',
                'Werdegang', 'QA-Werdegang', 'Mitgliedschaften', 'QA-Mitgliedschaften', 'QA-Publikationsliste', 'QA-Artikel DE'], false);

        $bare = $this->member($this->site['teams']['luks'], ['credits' => null, 'description' => null]);
        $this->get($this->memberUrl('de', $bare))
            ->assertDontSee(['Tätigkeitsgebiete', 'Werdegang', 'Mitgliedschaften', 'Publikationen', 'member__credits', 'member__description'], false);
    }

    #[Qa('pp-member-sections')]
    public function test_member_description_shows_without_info(): void
    {
        $member = $this->member($this->site['teams']['luks'], ['credits' => null]);

        $this->knownFinding('F2', 'Beschreibung only shows when Info is filled', fn () => $this->get($this->memberUrl('de', $member))->assertSee('<div class="member__description">', false));
    }

    #[Qa('pp-member-pubs')]
    public function test_publications_in_admin_order(): void
    {
        $member = $this->site['member'];
        $this->publication($member, ['title' => ['de' => 'QA-Pub B'], 'order' => 1]);
        $this->publication($member, ['title' => ['de' => 'QA-Pub A'], 'order' => 0]);
        $this->publication($member, ['title' => ['de' => 'QA-Pub C'], 'order' => 2]);

        $this->get($this->memberUrl('de'))->assertSeeInOrder(['QA-Pub A', 'QA-Pub B', 'QA-Pub C']);
    }

    #[Qa('pp-member-pubs')]
    public function test_unpublished_publications_are_hidden(): void
    {
        $member = $this->site['member'];
        $this->publication($member, ['title' => ['de' => 'QA-Pub sichtbar']]);
        $this->publication($member, ['title' => ['de' => 'QA-Pub versteckt'], 'publish' => 0]);

        $this->knownFinding('F1', 'unpublished publications are listed', fn () => $this->get($this->memberUrl('de'))->assertSee('QA-Pub sichtbar')->assertDontSee('QA-Pub versteckt'));
    }

    #[Qa('pp-assistant')]
    public function test_assistant_pages_show_description_list_and_images(): void
    {
        $this->assistantImage($this->site['assistants']['vogt'], $this->upload('qa-assist.jpg'));

        foreach (['de' => 'assistenz', 'fr' => 'assistence', 'en' => 'assistenz'] as $locale => $slug) {
            $upper = strtoupper($locale);
            $this->get("/{$locale}/team-vogt/{$slug}")
                ->assertOk()
                ->assertSee(["QA-Assistenz Beschreibung {$upper}", "QA-Assistenten {$upper}"])
                ->assertSee('/img/crop/qa-assist.jpg/', false);
        }
    }

    #[Qa('pp-contact')]
    public function test_contact_shows_address_maps_imprint_privacy_and_images(): void
    {
        $this->get('/de/kontakt')
            ->assertOk()
            ->assertSee(['QA-Adresse DE', 'QA-Impressum DE', 'QA-Datenschutz DE', 'Impressum', 'Datenschutz', 'QA-Anna Muster'])
            ->assertSee('href="https://maps.example.com/qa"', false)
            ->assertSee(['/img/crop/qa-contact-desktop.jpg/', '/img/crop/qa-contact-mobile.jpg/'], false);
    }

    #[Qa('pp-contact', 'pg-404')]
    public function test_contact_loads_with_images_for_one_device_only(): void
    {
        \App\Models\ContactImage::where('device', 'mobile')->delete();

        $this->knownFinding('F14', 'contact page 500s without a published image per device', fn () => $this->get('/de/kontakt')->assertOk());
    }

    #[Qa('pp-privacy-lang')]
    public function test_privacy_falls_back_to_german_until_translated(): void
    {
        $this->get('/fr/contacter')->assertSee('QA-Datenschutz DE');
        $this->get('/en/contact')->assertSee('QA-Datenschutz DE');

        $this->site['contact']->setTranslation('privacy', 'fr', '<p>QA-Protection FR</p>')
            ->setTranslation('privacy', 'en', '<p>QA-Privacy EN</p>')
            ->save();

        $this->get('/fr/contacter')->assertSee('QA-Protection FR')->assertDontSee('QA-Datenschutz DE');
        $this->get('/en/contact')->assertSee('QA-Privacy EN')->assertDontSee('QA-Datenschutz DE');
    }

    #[Qa('ah-list')]
    public function test_unpublished_home_is_not_shown(): void
    {
        $this->site['home']->forceFill(['publish' => 0])->save();

        $this->knownFinding('F4', 'publish flag does not hide the page', fn () => $this->get('/de')->assertDontSee('QA-Home DE'));
    }

    #[Qa('at-teams-list')]
    public function test_unpublished_team_page_is_not_public(): void
    {
        $this->site['teams']['luks']->forceFill(['publish' => 0])->save();

        $this->knownFinding('F4', 'publish flag does not hide the page', fn () => $this->get('/de/team-luks')->assertNotFound());
    }

    #[Qa('at-assist-list')]
    public function test_unpublished_assistant_page_is_not_public(): void
    {
        $this->site['assistants']['luks']->forceFill(['publish' => 0])->save();

        $this->get('/de/team-luks')->assertDontSee('/de/team-luks/assistenz', false);
        $this->knownFinding('F4', 'publish flag does not hide the page', fn () => $this->get('/de/team-luks/assistenz')->assertNotFound());
    }

    #[Qa('ak-list')]
    public function test_unpublished_contact_is_not_shown(): void
    {
        $this->site['contact']->forceFill(['publish' => 0])->save();

        $this->knownFinding('F4', 'publish flag does not hide the page', fn () => $this->get('/de/kontakt')->assertDontSee('QA-Adresse DE'));
    }
}
