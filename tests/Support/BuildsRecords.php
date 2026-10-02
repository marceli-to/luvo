<?php

namespace Tests\Support;

use App\Models\Assistant;
use App\Models\AssistantImage;
use App\Models\Contact;
use App\Models\ContactImage;
use App\Models\Home;
use App\Models\HomeImage;
use App\Models\Publication;
use App\Models\Team;
use App\Models\TeamImage;
use App\Models\TeamMember;
use App\Models\TeamMemberImage;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Minimal records for feature tests. Every name starts with "QA-".
 */
trait BuildsRecords
{
    protected function t(string $text): array
    {
        $translate = fn (string $locale) => str_contains($text, '</p>')
            ? str_replace('</p>', " {$locale}</p>", $text)
            : "{$text} {$locale}";

        return ['de' => $translate('DE'), 'fr' => $translate('FR'), 'en' => $translate('EN')];
    }

    protected function user(array $attributes = []): User
    {
        return User::forceCreate($attributes + [
            'firstname' => 'QA',
            'name' => 'Admin',
            'email' => 'qa-' . uniqid() . '@luvo.test',
            'password' => Hash::make('qa-password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    protected function admin(): User
    {
        return $this->user();
    }

    protected function team(string $slug = 'luks', array $attributes = []): Team
    {
        return Team::forceCreate($attributes + [
            'slug' => $slug,
            'title' => $this->t("QA-Team {$slug}"),
            'text' => $this->t("<p>QA-Team text {$slug}</p>"),
            'order' => 0,
            'publish' => 1,
        ]);
    }

    /**
     * Both teams, as the public menus and the contact page expect them.
     */
    protected function teams(): array
    {
        return ['luks' => $this->team('luks'), 'vogt' => $this->team('vogt', ['order' => 1])];
    }

    protected function member(Team $team, array $attributes = []): TeamMember
    {
        return TeamMember::forceCreate($attributes + [
            'firstname' => 'QA-' . uniqid(),
            'name' => 'Member',
            'credits' => $this->t('<p>QA-Info</p>'),
            'description' => $this->t('<p>QA-Beschreibung</p>'),
            'team_id' => $team->id,
            'order' => 0,
            'publish' => 1,
        ]);
    }

    protected function publication(TeamMember $member, array $attributes = []): Publication
    {
        return Publication::forceCreate($attributes + [
            'title' => $this->t('QA-Publikation ' . uniqid()),
            'description' => $this->t('<p>QA-Beschreibung</p>'),
            'articles' => $this->t('<p>QA-Artikel</p>'),
            'team_member_id' => $member->id,
            'order' => 0,
            'publish' => 1,
        ]);
    }

    protected function home(array $attributes = []): Home
    {
        return Home::forceCreate($attributes + [
            'title' => $this->t('QA-Home'),
            'text' => $this->t('<p>QA-Home text</p>'),
            'publish' => 1,
        ]);
    }

    protected function contact(array $attributes = []): Contact
    {
        return Contact::forceCreate($attributes + [
            'address' => $this->t('<p>QA-Adresse</p>'),
            'imprint' => $this->t('<p>QA-Impressum</p>'),
            'privacy' => ['de' => '<p>QA-Datenschutz DE</p>', 'fr' => null, 'en' => null],
            'map_uri' => 'https://maps.example.com/qa',
            'publish' => 1,
        ]);
    }

    protected function assistant(Team $team, array $attributes = []): Assistant
    {
        return Assistant::forceCreate($attributes + [
            'description' => $this->t('<p>QA-Assistenz Beschreibung</p>'),
            'assistants' => $this->t('<p>QA-Assistenten</p>'),
            'team_id' => $team->id,
            'publish' => 1,
        ]);
    }

    /**
     * An image row for the given parent; $class is one of the *Image models.
     */
    protected function image(string $class, string $foreignKey, int $parentId, string $name, array $attributes = []): object
    {
        $defaults = [
            'name' => $name,
            'caption' => in_array($class, [HomeImage::class, TeamImage::class], true) ? $this->t('QA-Legende') : 'QA-Legende',
            'orientation' => 'l',
            'publish' => 1,
            'order' => 0,
            $foreignKey => $parentId,
        ];
        if (!in_array($class, [HomeImage::class], true)) {
            $defaults['device'] = 'desktop';
        }

        return $class::forceCreate($attributes + $defaults);
    }

    protected function memberImage(TeamMember $member, string $name, array $attributes = []): TeamMemberImage
    {
        return $this->image(TeamMemberImage::class, 'team_member_id', $member->id, $name, $attributes);
    }

    protected function teamImage(Team $team, string $name, array $attributes = []): TeamImage
    {
        return $this->image(TeamImage::class, 'team_id', $team->id, $name, $attributes);
    }

    protected function homeImage(Home $home, string $name, array $attributes = []): HomeImage
    {
        return $this->image(HomeImage::class, 'home_id', $home->id, $name, $attributes);
    }

    protected function contactImage(Contact $contact, string $name, array $attributes = []): ContactImage
    {
        return $this->image(ContactImage::class, 'contact_id', $contact->id, $name, $attributes);
    }

    protected function assistantImage(Assistant $assistant, string $name, array $attributes = []): AssistantImage
    {
        return $this->image(AssistantImage::class, 'assistant_id', $assistant->id, $name, $attributes);
    }
}
