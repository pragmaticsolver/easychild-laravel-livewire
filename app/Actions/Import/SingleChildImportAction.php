<?php

namespace App\Actions\Import;

use App\Actions\User\AssignParentToChildAction;
use App\Models\Contract;
use App\Models\Group;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsObject;

class SingleChildImportAction
{
    use AsObject;

    public function handle($child, $organizationId, $userData)
    {
        $userDataAvailable = false;
        $contactDataAvailable = false;

        if (isset($userData['given_name']) && $userData['given_name']) {
            $userDataAvailable = true;
        }

        if (isset($userData['contact_name']) && $userData['contact_name']) {
            $contactDataAvailable = true;
        }

        if ($userDataAvailable) {
            $contract = null;

            if (isset($userData['contract']) && $userData['contract']) {
                $contract = $this->getContract($organizationId, $userData['contract']);
            }

            $child = User::create([
                'given_names' => $userData['given_name'],
                'last_name' => $userData['last_name'],
                'role' => 'User',
                'organization_id' => $organizationId,
                'customer_no' => $userData['customer_no'],
                'dob' => $userData['dob'],
                'contract_id' => $contract ? $contract->id : null,
                'settings' => [
                    'allergies' => $userData['allergies'],
                    'client_no' => $userData['client_no'],
                    'eats_onsite' => $this->getEatsOnsite($userData),
                ],
            ]);

            if (isset($userData['group']) && $userData['group']) {
                $this->assignGroup($child, $userData['group']);
            }

            if (isset($userData['parent_email_1']) && $userData['parent_email_1']) {
                $this->addParent($userData['parent_email_1'], $child);
            }

            if (isset($userData['parent_email_2']) && $userData['parent_email_2']) {
                $this->addParent($userData['parent_email_2'], $child);
            }
        }

        if ($contactDataAvailable && $child) {
            $contactData = [
                'name' => $userData['contact_name'],
                'relationship' => $userData['contact_relationship'],
                'address' => $userData['contact_address'],
                'landline' => $userData['contact_landline'],
                'mobile' => $userData['contact_mobile'],
                'job' => $userData['contact_job'],
                'notes' => $userData['contact_note'],
                'user_id' => $child->id,
                'legal' => $this->getBooleanForField($userData, 'contact_legal'),
                'emergency_contact' => $this->getBooleanForField($userData, 'contact_emergency'),
                'can_collect' => $this->getBooleanForField($userData, 'contact_authorized'),
            ];

            if (isset($userData['contact_legal']) && $userData['contact_legal'] == 'yes') {
                $contactData['legal'] = true;
            }

            StoreChildContactAction::run($contactData);
        }

        return $child;
    }

    private function getBooleanForField($userData, $userDataKey, $defaultValue = false)
    {
        $value = $defaultValue;

        if (isset($userData[$userDataKey]) && strtolower($userData[$userDataKey]) == 'yes') {
            $value = true;
        }

        return $value;
    }

    private function getEatsOnsite($data)
    {
        return collect([
            'breakfast' => $this->getBooleanForField($data, 'breakfast'),
            'lunch' => $this->getBooleanForField($data, 'lunch'),
            'dinner' => $this->getBooleanForField($data, 'dinner'),
        ])
            ->map(fn ($value, $key) => $value ? null : false)
            ->all();
    }

    private function assignGroup($child, $groupName)
    {
        $group = Group::firstOrCreate([
            'organization_id' => $child->organization_id,
            'name' => $groupName,
        ]);

        if (! $group) {
            // get first group in organization
            return;
        }

        $group->users()->attach($child);
    }

    private function getContract($orgId, $contractTitle)
    {
        $contract = Contract::query()
            ->where('organization_id', $orgId)
            ->where('title', $contractTitle)
            ->first();

        if (! $contract) {
            $contract = Contract::create([
                'title' => $contractTitle,
                'organization_id' => $orgId,
                'time_per_day' => 4,
            ]);
        }

        return $contract;
    }

    private function addParent($parentEmail, User $child)
    {
        if (! $parent = User::where('email', $parentEmail)->first()) {
            $parent = User::make([
                'email' => $parentEmail,
                'role' => 'Parent',
            ]);
        }

        AssignParentToChildAction::run($child, $parent);
    }
}
