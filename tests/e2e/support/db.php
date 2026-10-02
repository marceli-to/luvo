<?php
/**
 * Database helper for the Playwright run, against the E2E database only
 * (DB_DATABASE from .env.e2e, which must end in _e2e).
 *
 *   php tests/e2e/support/db.php select "<SELECT ...>"         rows as JSON
 *   php tests/e2e/support/db.php snapshot <file> <table> <ids>  save rows (ids comma separated)
 *   php tests/e2e/support/db.php restore <file>                 put saved rows back (update, re-insert)
 *   php tests/e2e/support/db.php checksum                       md5 per content table, timestamps ignored
 *   php tests/e2e/support/db.php cleanup                        delete records named QA-…, print what went
 *   php tests/e2e/support/db.php set <table> <id> <column> <value>  set one column (states the admin can't set)
 *   php tests/e2e/support/db.php expire-sessions <email>      delete a user's sessions (server-side expiry)
 *   php tests/e2e/support/db.php members                        team members with their public path (Str::slug)
 */

require __DIR__ . '/../../../vendor/autoload.php';

$env = [];
foreach (file(__DIR__ . '/../../../.env.e2e', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if ($line[0] !== '#' && str_contains($line, '=')) {
        [$key, $value] = explode('=', $line, 2);
        $env[$key] = $value;
    }
}
if (!str_ends_with($env['DB_DATABASE'] ?? '', '_e2e')) {
    fwrite(STDERR, "Refusing: DB_DATABASE in .env.e2e must end in _e2e.\n");
    exit(1);
}

$pdo = new PDO(
    "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4",
    $env['DB_USERNAME'],
    $env['DB_PASSWORD'] ?? '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

const CONTENT_TABLES = [
    'home', 'home_images', 'teams', 'team_images', 'team_members', 'team_member_images',
    'publications', 'contacts', 'contact_images', 'assistants', 'assistant_images', 'files',
];

function out($data): void
{
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function table(string $name): string
{
    if (!in_array($name, [...CONTENT_TABLES, 'users'], true)) {
        throw new InvalidArgumentException("Unknown table {$name}");
    }

    return "`{$name}`";
}

[$_, $command] = $argv + [null, null];

switch ($command) {
    case 'select':
        if (!preg_match('/^\s*select\b/i', $argv[2])) {
            throw new InvalidArgumentException('select only');
        }
        out($pdo->query($argv[2])->fetchAll());
        break;

    case 'snapshot':
        [, , $file, $name, $ids] = $argv;
        $ids = array_map('intval', array_filter(explode(',', $ids), 'strlen'));
        $rows = $ids ? $pdo->query('SELECT * FROM ' . table($name) . ' WHERE id IN (' . implode(',', $ids) . ')')->fetchAll() : [];
        @mkdir(dirname($file), 0775, true);
        file_put_contents($file, json_encode(['table' => $name, 'rows' => $rows], JSON_UNESCAPED_UNICODE));
        out(count($rows));
        break;

    case 'restore':
        $snapshot = json_decode(file_get_contents($argv[2]), true);
        $table = table($snapshot['table']);
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach ($snapshot['rows'] as $row) {
            $columns = array_keys($row);
            $exists = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE id = ?");
            $exists->execute([$row['id']]);
            if ($exists->fetchColumn()) {
                $set = implode(', ', array_map(fn ($c) => "`{$c}` = :{$c}", $columns));
                $pdo->prepare("UPDATE {$table} SET {$set} WHERE id = :id")->execute($row);
            }
            else {
                $names = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));
                $values = implode(', ', array_map(fn ($c) => ":{$c}", $columns));
                $pdo->prepare("INSERT INTO {$table} ({$names}) VALUES ({$values})")->execute($row);
            }
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        out(count($snapshot['rows']));
        break;

    case 'checksum':
        $sums = [];
        foreach (CONTENT_TABLES as $name) {
            $columns = $pdo->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$name}' AND COLUMN_NAME NOT IN ('created_at', 'updated_at') ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_COLUMN);
            $rows = $pdo->query('SELECT `' . implode('`, `', $columns) . '` FROM ' . table($name) . ' ORDER BY id')->fetchAll();
            $sums[$name] = ['rows' => count($rows), 'md5' => md5(json_encode($rows))];
        }
        out($sums);
        break;

    case 'cleanup':
        $deleted = [];
        // Children first: contact_images and assistant_images have no ON DELETE CASCADE (F18)
        $qaHome = "SELECT id FROM home WHERE JSON_UNQUOTE(JSON_EXTRACT(title, '$.de')) LIKE 'QA-%'";
        $qaMembers = "SELECT id FROM team_members WHERE firstname LIKE 'QA-%' OR name LIKE 'QA-%'";
        $qaContacts = "SELECT id FROM contacts WHERE address LIKE '%QA-%'";
        $qaAssistants = "SELECT id FROM assistants WHERE description LIKE '%QA-%'";
        $queries = [
            'home_images' => "name LIKE '%qa-%' OR home_id IN (SELECT id FROM ({$qaHome}) q)",
            'team_images' => "name LIKE '%qa-%'",
            'team_member_images' => "name LIKE '%qa-%' OR team_member_id IN (SELECT id FROM ({$qaMembers}) q)",
            'contact_images' => "name LIKE '%qa-%' OR contact_id IN (SELECT id FROM ({$qaContacts}) q)",
            'assistant_images' => "name LIKE '%qa-%' OR assistant_id IN (SELECT id FROM ({$qaAssistants}) q)",
            'publications' => "JSON_UNQUOTE(JSON_EXTRACT(title, '$.de')) LIKE 'QA-%' OR team_member_id IN (SELECT id FROM ({$qaMembers}) q)",
            'home' => "id IN (SELECT id FROM ({$qaHome}) q)",
            'teams' => "JSON_UNQUOTE(JSON_EXTRACT(title, '$.de')) LIKE 'QA-%'",
            'team_members' => "id IN (SELECT id FROM ({$qaMembers}) q)",
            'contacts' => "id IN (SELECT id FROM ({$qaContacts}) q)",
            'assistants' => "id IN (SELECT id FROM ({$qaAssistants}) q)",
            'files' => "name LIKE '%qa-%'",
        ];
        foreach ($queries as $name => $where) {
            $count = $pdo->exec('DELETE FROM ' . table($name) . " WHERE {$where}");
            if ($count) {
                $deleted[$name] = $count;
            }
        }
        out($deleted);
        break;

    case 'set':
        [, , $name, $id, $column, $value] = $argv;
        if (!preg_match('/^[a-z_]+$/', $column)) {
            throw new InvalidArgumentException('column');
        }
        $statement = $pdo->prepare('UPDATE ' . table($name) . " SET `{$column}` = ? WHERE id = ?");
        $statement->execute([$value, (int) $id]);
        out($statement->rowCount());
        break;

    case 'expire-sessions':
        $statement = $pdo->prepare('DELETE FROM sessions WHERE user_id = (SELECT id FROM users WHERE email = ?)');
        $statement->execute([$argv[2]]);
        out($statement->rowCount());
        break;

    case 'members':
        $rows = $pdo->query('SELECT m.id, m.firstname, m.name, m.publish, m.`order`, m.team_id, t.slug AS team FROM team_members m JOIN teams t ON t.id = m.team_id ORDER BY m.team_id, m.`order`, m.id')->fetchAll();
        out(array_map(fn ($m) => $m + [
            'fullname' => "{$m['firstname']} {$m['name']}",
            'path' => "team-{$m['team']}/" . \Illuminate\Support\Str::slug("{$m['firstname']}-{$m['name']}") . "/{$m['id']}",
        ], $rows));
        break;

    default:
        fwrite(STDERR, "Unknown command\n");
        exit(1);
}
