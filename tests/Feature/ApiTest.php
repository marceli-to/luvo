<?php

namespace Tests\Feature;

use App\Models\Assistant;
use App\Models\Publication;
use App\Models\Team;
use App\Models\TeamImage;
use App\Models\TeamMember;
use App\Models\TeamMemberImage;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Qa;
use Tests\TestCase;

class ApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->admin());
    }

    public static function requiredFields(): array
    {
        // endpoint, ids, [attribute => [field, message]]
        return [
            'home' => ['/api/home', ['ah-required'], [
                'title.de' => ['title', 'Titel wird benötigt!'],
                'text.de' => ['text', 'Text wird benötigt!'],
            ]],
            'team' => ['/api/team', ['at-team-form'], [
                'slug' => ['slug', null],
                'title.de' => ['title', null],
                'text.de' => ['text', null],
            ]],
            'team member' => ['/api/team/member', ['am-required'], [
                'firstname' => ['firstname', null],
                'name' => ['name', null],
                'team_id' => ['team_id', null],
            ]],
            // C3: Artikel is required too, not only Titel
            'publication' => ['/api/publication', ['am-pub-create'], [
                'team_member_id' => ['team_member_id', null],
                'title.de' => ['title', null],
                'articles.de' => ['articles', null],
            ]],
            // C4: Beschreibung is required too, not only Team
            'assistant' => ['/api/assistant', ['aa-required'], [
                'description.de' => ['description', null],
                'team_id' => ['team_id', null],
            ]],
            // C5: Adresse in DE
            'contact' => ['/api/contact', ['ak-fields'], [
                'address.de' => ['address', null],
            ]],
        ];
    }

    #[DataProvider('requiredFields')]
    #[Qa('ah-required', 'at-team-form', 'am-required', 'am-pub-create', 'aa-required', 'ak-fields')]
    public function test_required_fields_return_422_in_the_dashboard_shape(string $endpoint, array $ids, array $fields): void
    {
        $response = $this->postJson($endpoint, ['title' => ['fr' => 'QA-nur FR']])->assertUnprocessable();

        $this->assertEqualsCanonicalizing(array_keys($fields), array_keys($response->json('errors')));
        foreach ($fields as $attribute => [$field, $message]) {
            $error = $response->json('errors')[$attribute][0];
            $this->assertSame($field, $error['field']);
            $this->assertNotEmpty($error['error']);
            if ($message) {
                $this->assertSame($message, $error['error']);
            }
        }
    }

    #[Qa('at-teams-list')]
    public function test_team_create_and_delete(): void
    {
        $id = $this->postJson('/api/team', [
            'slug' => 'luks',
            'title' => $this->t('QA-Team'),
            'text' => $this->t('<p>QA-Text</p>'),
            'publish' => 1,
            'images' => [['name' => 'qa.jpg', 'caption' => ['de' => 'QA'], 'coords_w' => 0, 'coords_h' => 0, 'coords_x' => 0,
                'coords_y' => 0, 'publish' => 1, 'device' => 'desktop', 'orientation' => 'l']],
        ])->assertOk()->json('teamId');

        $this->getJson('/api/team')->assertJsonFragment(['id' => $id]);
        $this->assertSame(1, TeamImage::where('team_id', $id)->count());

        $this->member(Team::find($id));
        $this->deleteJson("/api/team/{$id}")->assertOk();
        $this->assertNull(Team::find($id));
        $this->assertSame(0, TeamMember::where('team_id', $id)->count(), 'members cascade');
    }

    #[Qa('at-assist-list')]
    public function test_assistant_create_toggle_and_delete(): void
    {
        $team = $this->team();
        $id = $this->postJson('/api/assistant', [
            'team_id' => $team->id,
            'description' => $this->t('<p>QA-Beschreibung</p>'),
            'assistants' => $this->t('<p>QA-Assistenten</p>'),
            'publish' => 1,
        ])->assertOk()->json('assistantId');

        $this->getJson("/api/assistant/state/{$id}")->assertOk()->assertExactJson([0]);
        $this->deleteJson("/api/assistant/{$id}")->assertOk();
        $this->assertNull(Assistant::find($id));
    }

    protected function memberPayload(Team $team): array
    {
        $payload = ['firstname' => 'QA-Vorname', 'name' => 'QA-Name', 'team_id' => $team->id, 'publish' => 1, 'images' => []];
        foreach (['credits', 'description', 'area', 'languages', 'biography', 'membership', 'publication'] as $field) {
            $payload[$field] = $this->t("<p>QA-{$field}</p>");
        }
        $payload['meta_description'] = $this->t('QA-SEO');

        return $payload;
    }

    #[Qa('am-fields')]
    public function test_member_update_saves_every_field_per_language(): void
    {
        $team = $this->team();
        $member = $this->member($team);

        $this->putJson("/api/team/member/{$member->id}", $this->memberPayload($team))->assertOk();

        $stored = $this->getJson("/api/team/member/{$member->id}")->assertOk();
        foreach (['credits', 'description', 'area', 'languages', 'biography', 'membership', 'publication'] as $field) {
            $this->assertEquals($this->t("<p>QA-{$field}</p>"), $stored->json($field), $field);
        }
        $this->assertEquals($this->t('QA-SEO'), $stored->json('meta_description'));
    }

    #[Qa('am-fields')]
    public function test_member_create_saves_the_seo_description(): void
    {
        $id = $this->postJson('/api/team/member', $this->memberPayload($this->team()))->assertOk()->json('teamMemberId');

        $this->knownFinding('F6', 'SEO Beschreibung is dropped on create', fn () => $this->assertEquals($this->t('QA-SEO'), TeamMember::find($id)->getTranslations('meta_description')));
    }

    #[Qa('at-members-crud', 'am-pub-edit')]
    public function test_deleting_a_member_removes_its_images_and_publications(): void
    {
        $member = $this->member($this->team());
        $this->memberImage($member, 'qa.jpg');
        $this->publication($member);

        $this->deleteJson("/api/team/member/{$member->id}")->assertOk();
        $this->assertSame(0, TeamMemberImage::where('team_member_id', $member->id)->count());
        $this->assertSame(0, Publication::where('team_member_id', $member->id)->count());
    }

    #[Qa('at-members-drag', 'am-pub-drag')]
    public function test_order_endpoints_store_the_order(): void
    {
        $team = $this->team();
        [$a, $b] = [$this->member($team, ['order' => 0]), $this->member($team, ['order' => 1])];
        $this->postJson('/api/team/member/order', ['members' => [['id' => $b->id, 'order' => 0], ['id' => $a->id, 'order' => 1]]])->assertOk();
        $this->assertSame([$b->id, $a->id], TeamMember::orderBy('order')->pluck('id')->all());

        [$p, $q] = [$this->publication($a, ['order' => 0]), $this->publication($a, ['order' => 1])];
        $this->postJson('/api/publication/order', ['publications' => [['id' => $q->id, 'order' => 0], ['id' => $p->id, 'order' => 1]]])->assertOk();
        $this->assertSame([$q->id, $p->id], Publication::orderBy('order')->pluck('id')->all());
    }

    #[Qa('rb-special')]
    public function test_special_characters_round_trip(): void
    {
        $home = $this->home();
        $text = 'QA-Ümläute «Guillemets» & Donaudampfschifffahrtsgesellschaftskapitän – ‹›';

        $this->putJson("/api/home/{$home->id}", [
            'title' => ['de' => $text, 'fr' => $text, 'en' => $text],
            'text' => ['de' => "<p>{$text}</p>", 'fr' => null, 'en' => null],
            'publish' => 1,
        ])->assertOk();

        $this->assertSame($text, $this->getJson("/api/home/{$home->id}")->json('title.de'));
        $this->teams();
        $this->get('/de')->assertSee($text);
    }

    #[Qa('im-upload')]
    public function test_image_upload_stores_the_file_and_reports_orientation(): void
    {
        $file = UploadedFile::fake()->image('QA Bild (1).JPG', 600, 900);

        $response = $this->post('/api/image/upload', ['file' => $file])->assertOk();

        $this->assertMatchesRegularExpression('~^luksundvogt-[0-9a-f]+_qa-bild-1.jpg$~', $response->json('name'));
        $this->assertSame('p', $response->json('orientation'));
        $this->assertFileExists(storage_path('app/public/uploads/' . $response->json('name')));
    }

    #[Qa('im-reject')]
    public function test_image_upload_rejects_other_types_on_the_server(): void
    {
        $file = UploadedFile::fake()->createWithContent('qa.php', '<?php echo "qa";');

        $this->knownFinding('F8', 'the server accepts any file type and size', fn () => $this->post('/api/image/upload', ['file' => $file])->assertUnprocessable());
    }

    #[Qa('md-upload')]
    public function test_file_upload_and_store(): void
    {
        $name = $this->post('/api/file/upload', ['file' => UploadedFile::fake()->create('QA Merkblatt.pdf', 20, 'application/pdf')])
            ->assertOk()
            ->assertJsonPath('type', 'pdf')
            ->json('name');

        $id = $this->postJson('/api/file/store', ['name' => $name, 'size' => '20 KB', 'type' => 'pdf'])->assertOk()->json('id');

        $this->getJson('/api/files')->assertJsonFragment(['title' => $name, 'value' => "/storage/uploads/files/{$name}"]);
        $this->deleteJson("/api/file/{$id}")->assertOk();
        $this->getJson('/api/files/fetch')->assertJsonMissing(['id' => $id]);
    }

    #[Qa('md-reject')]
    public function test_file_upload_rejects_other_types_on_the_server(): void
    {
        $file = UploadedFile::fake()->createWithContent('qa.html', '<script>alert(1)</script>');

        $this->knownFinding('F8', 'the server accepts any file type and size', fn () => $this->post('/api/file/upload', ['file' => $file])->assertUnprocessable());
    }
}
