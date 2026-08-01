<?php

use App\Support\PersonName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rental-contract signing used to create the client's login with the phone
 * number in the `name` column, and that number was then copied into the
 * customer record and the portal user. It surfaced as the customer on the
 * booking list, as the assignee in Task Manager, and as the greeting in
 * WhatsApp task messages.
 *
 * Each affected row is usually mirrored by a sibling record that does carry the
 * real name, so borrow it from there. Names are only ever taken across an
 * explicit link — `customers.user_id`, a shared email, a shared portal id —
 * never by matching phone numbers, because one number is often reused across
 * unrelated customers and that would hand somebody else's name to the record.
 *
 * Rows with no linked name are left as they are: the number still identifies
 * the person, and a placeholder would only hide that they need a real name.
 */
class RepairPhoneNumbersStoredAsNames extends Migration
{
    public function up()
    {
        // A company name is on the row itself, so it needs no link at all.
        $this->fillFrom('customers', 'name', function ($row) {
            return PersonName::pick($row->company_name);
        }, ['company_name']);

        // The POS user and its customer record are the same person either way
        // round, so whichever side kept the real name donates it to the other.
        $customerNames = $this->namesKeyedBy('customers', 'user_id', ['name', 'company_name']);
        $this->fillFrom('users', 'name', function ($row) use ($customerNames) {
            return isset($customerNames[$row->id]) ? $customerNames[$row->id] : '';
        });

        $userNames = $this->namesKeyedBy('users', 'id', ['name']);
        $this->fillFrom('customers', 'name', function ($row) use ($userNames) {
            return $row->user_id !== null && isset($userNames[$row->user_id]) ? $userNames[$row->user_id] : '';
        }, ['user_id']);

        // Portal users are created from a POS user or customer, keeping their
        // email — or, when they had none, an address that embeds the source id.
        $byEmail = $this->namesKeyedBy('customers', 'email', ['name', 'company_name'], true)
            + $this->namesKeyedBy('users', 'email', ['name'], true);
        $customerNamesById = $this->namesKeyedBy('customers', 'id', ['name', 'company_name']);

        $this->fillFrom('be_users', 'name', function ($row) use ($byEmail, $userNames, $customerNamesById) {
            $email = strtolower(trim((string) $row->email));
            if (isset($byEmail[$email])) {
                return $byEmail[$email];
            }
            if (preg_match('/^u(\d+)@users\./', $email, $m) && isset($userNames[$m[1]])) {
                return $userNames[$m[1]];
            }
            if (preg_match('/^c(\d+)@customers\./', $email, $m) && isset($customerNamesById[$m[1]])) {
                return $customerNamesById[$m[1]];
            }

            return '';
        }, ['email']);

        // A profile shares its primary key with its portal user.
        $beyondNames = $this->namesKeyedBy('be_users', 'id', ['name']);
        $this->fillFrom('be_profiles', 'full_name', function ($row) use ($beyondNames) {
            return isset($beyondNames[$row->id]) ? $beyondNames[$row->id] : '';
        });
    }

    public function down()
    {
        // The replaced values were phone numbers, never meaningful names.
    }

    /**
     * Rewrites every `$nameColumn` that is really a phone number with whatever
     * `$resolve` can find for that row.
     */
    private function fillFrom($table, $nameColumn, callable $resolve, array $extraColumns = [])
    {
        $columns = array_merge(['id', $nameColumn], $extraColumns);
        if (! $this->usable($table, $columns)) {
            return;
        }

        DB::table($table)->select($columns)->orderBy('id')->chunk(500, function ($rows) use ($table, $nameColumn, $resolve) {
            foreach ($rows as $row) {
                if (! PersonName::looksLikePhone($row->{$nameColumn})) {
                    continue;
                }

                $name = PersonName::pick($resolve($row));
                if ($name === '') {
                    continue;
                }

                DB::table($table)->where('id', $row->id)->update([$nameColumn => $name]);
            }
        });
    }

    /**
     * Real names from `$table`, keyed by `$keyColumn`. Rows whose name is itself
     * a phone number are skipped, so a lookup only ever returns something usable.
     */
    private function namesKeyedBy($table, $keyColumn, array $nameColumns, $lowercaseKey = false)
    {
        $columns = array_merge([$keyColumn], $nameColumns);
        if (! $this->usable($table, $columns)) {
            return [];
        }

        $names = [];
        DB::table($table)
            ->whereNotNull($keyColumn)
            ->select($columns)
            ->orderBy($keyColumn)
            ->chunk(500, function ($rows) use (&$names, $keyColumn, $nameColumns, $lowercaseKey) {
                foreach ($rows as $row) {
                    $key = trim((string) $row->{$keyColumn});
                    if ($lowercaseKey) {
                        $key = strtolower($key);
                    }
                    if ($key === '' || isset($names[$key])) {
                        continue;
                    }

                    $candidates = [];
                    foreach ($nameColumns as $column) {
                        $candidates[] = $row->{$column};
                    }

                    $name = PersonName::pick($candidates);
                    if ($name !== '') {
                        $names[$key] = $name;
                    }
                }
            });

        return $names;
    }

    private function usable($table, array $columns)
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }
}
