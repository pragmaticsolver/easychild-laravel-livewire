<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateContractorUser extends Command
{
    protected $signature = 'user:contractor';
    protected $description = 'Create a contractor user';

    public function handle()
    {
        $givenNames = $this->ask('What is the contractor first name?');
        $lastName = $this->ask('What is the contractor last name?');
        $email = $this->ask('What is the contractor email?');

        $organizations = $this->ask('Linked Organizations (separated by comma)!');

        $organizationItems = collect(explode(',', $organizations))
            ->map(fn ($item) => (string) Str::of($item)->trim())
            ->map(fn ($item) => (int) $item)
            ->filter(fn ($item) => $item > 0)
            ->all();

        $password = Str::random(16);

        $user = User::create([
            'given_names' => $givenNames,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
            'role' => 'Contractor',
        ]);

        $user->organizations()->sync($organizationItems);

        $this->info('Contractor created!');
        $this->table(
            ['First Name', 'Last Name', 'Email', 'Password'],
            [[$user->given_names, $user->last_name, $user->email, $password]]
        );
    }
}
