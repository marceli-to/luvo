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

        $this->assertEquals($this->t('QA-SEO'), TeamMember::find($id)->getTranslations('meta_description'));
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

    #[Qa('im-delete')]
    public function test_deleting_an_image_removes_its_upload_and_rendered_variants(): void
    {
        $member = $this->member($this->team());
        $kept = $this->memberImage($member, $this->upload('qa-kept.jpg'));
        $gone = $this->memberImage($member, $this->upload('qa-gone.jpg'));
        $this->get('/img/crop/qa-gone.jpg/900/600')->assertOk();

        $this->deleteJson('/api/team/member/image/qa-gone.jpg')->assertOk();

        $this->assertNull(TeamMemberImage::find($gone->id));
        $this->assertFileDoesNotExist(storage_path('app/public/uploads/qa-gone.jpg'));
        $this->assertSame([], glob(storage_path('app/.glide-cache/uploads/qa-gone.jpg/*')));
        $this->assertFileExists(storage_path('app/public/uploads/qa-kept.jpg'));
        $this->assertNotNull($kept->fresh());
    }

    #[Qa('at-assist-list', 'ak-list')]
    public function test_assistant_and_contact_with_images_can_be_deleted(): void
    {
        $assistant = $this->assistant($this->team());
        $this->assistantImage($assistant, $this->upload('qa-assist.jpg'));
        $contact = $this->contact();
        $this->contactImage($contact, $this->upload('qa-contact.jpg'));

        $this->deleteJson("/api/assistant/{$assistant->id}")->assertOk();
        $this->deleteJson("/api/contact/{$contact->id}")->assertOk();

        $this->assertNull($assistant->fresh());
        $this->assertNull($contact->fresh());
        $this->assertFileDoesNotExist(storage_path('app/public/uploads/qa-assist.jpg'));
        $this->assertFileDoesNotExist(storage_path('app/public/uploads/qa-contact.jpg'));
    }

    #[Qa('at-teams-list', 'at-members-crud')]
    public function test_deleting_a_team_removes_the_uploads_of_its_and_its_members_images(): void
    {
        $team = $this->team();
        $this->teamImage($team, $this->upload('qa-team.jpg'));
        $this->memberImage($this->member($team), $this->upload('qa-member.jpg'));

        $this->deleteJson("/api/team/{$team->id}")->assertOk();

        $this->assertFileDoesNotExist(storage_path('app/public/uploads/qa-team.jpg'));
        $this->assertFileDoesNotExist(storage_path('app/public/uploads/qa-member.jpg'));
    }

    #[Qa('aa-images', 'ak-images', 'im-caption')]
    public function test_assistant_and_contact_save_with_image_captions(): void
    {
        $assistant = $this->assistant($this->team());
        $contact = $this->contact();
        foreach ([['assistant', $assistant, $this->assistantImage($assistant, 'qa-a.jpg')], ['contact', $contact, $this->contactImage($contact, 'qa-c.jpg')]] as [$entity, $record, $image]) {
            $payload = $this->getJson("/api/{$entity}/{$record->id}")->json();
            $payload['images'][0]['caption'] = 'QA-Legende';
            $this->putJson("/api/{$entity}/{$record->id}", $payload)->assertOk();
            $this->assertSame('QA-Legende', $image->fresh()->caption, $entity);
        }
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

    /**
     * A real file with a misleading name: its type is detected from the
     * content, as for a browser upload (fakes take it from the name).
     */
    protected function disguised(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'qa');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    #[Qa('im-reject')]
    public function test_image_upload_rejects_other_types_on_the_server(): void
    {
        $cases = [
            'php' => UploadedFile::fake()->createWithContent('qa.php', '<?php echo "qa";'),
            'php named .jpg' => $this->disguised('qa.jpg', '<?php echo "qa";'),
            'gif' => UploadedFile::fake()->image('qa.gif'),
            'pdf' => UploadedFile::fake()->create('qa.pdf', 10, 'application/pdf'),
            'over 8 MB' => UploadedFile::fake()->image('qa.jpg')->size(8193),
        ];
        foreach ($cases as $case => $file) {
            $this->postJson('/api/image/upload', ['file' => $file])->assertUnprocessable()->assertJsonValidationErrors('file');
        }
        $this->assertSame(['files'], array_map('basename', glob(storage_path('app/public/uploads/*'))), 'nothing was stored');
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
        $cases = [
            'html' => UploadedFile::fake()->createWithContent('qa.html', '<script>alert(1)</script>'),
            'html named .pdf' => $this->disguised('qa.pdf', '<script>alert(1)</script>'),
            'jpg' => UploadedFile::fake()->image('qa.jpg'),
            'over 16 MB' => UploadedFile::fake()->create('qa.pdf', 16385, 'application/pdf'),
        ];
        foreach ($cases as $case => $file) {
            $this->postJson('/api/file/upload', ['file' => $file])->assertUnprocessable()->assertJsonValidationErrors('file');
        }
        $this->assertSame([], glob(storage_path('app/public/uploads/files/*')), 'nothing was stored');
    }
}
