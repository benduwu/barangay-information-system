<?php

namespace Database\Seeders;

use App\Models\Purok;
use App\Models\Resident;
use App\Models\GroupInfo;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('username', 'admin')->first();
        $creatorId = $adminUser ? $adminUser->id : null;

        $purok1 = Purok::where('purok_name', 'Purok 1')->first();
        $purok2 = Purok::where('purok_name', 'Purok 2')->first();

        if (!$purok1 || !$purok2) {
            return;
        }

        // --- Household 1: Dela Cruz (Purok 1) ---
        // Head of Household
        $head1 = Resident::create([
            'purok_id' => $purok1->id,
            'head_of_household_id' => null,
            'last_name' => 'Dela Cruz',
            'first_name' => 'Juan',
            'date_of_birth' => '1981-05-15',
            'gender' => 'Male',
            'civil_status' => 'Married',
            'occupation' => 'Carpenter',
            'is_voter' => true,
            'is_indigent' => true,
            'is_pwd' => false,
            'is_senior_citizen' => false,
            'created_by' => $creatorId,
            'is_active' => true,
            'contact_number' => '09123456789',
            'email' => 'juan.delacruz@example.com',
        ]);

        GroupInfo::create([
            'resident_id' => $head1->id,
            'head_of_household_id' => $head1->id,
        ]);

        // Spouse
        $member1_1 = Resident::create([
            'purok_id' => $purok1->id,
            'head_of_household_id' => $head1->id,
            'last_name' => 'Dela Cruz',
            'first_name' => 'Maria',
            'date_of_birth' => '1984-11-20',
            'gender' => 'Female',
            'civil_status' => 'Married',
            'occupation' => 'Sari-Sari Store Owner',
            'is_voter' => true,
            'is_indigent' => true,
            'is_pwd' => false,
            'is_senior_citizen' => false,
            'created_by' => $creatorId,
            'is_active' => true,
            'contact_number' => '09187654321',
            'email' => 'maria.delacruz@example.com',
        ]);

        GroupInfo::create([
            'resident_id' => $member1_1->id,
            'head_of_household_id' => $head1->id,
        ]);

        // Child
        $member1_2 = Resident::create([
            'purok_id' => $purok1->id,
            'head_of_household_id' => $head1->id,
            'last_name' => 'Dela Cruz',
            'first_name' => 'Pedro',
            'date_of_birth' => '2008-08-08',
            'gender' => 'Male',
            'civil_status' => 'Single',
            'occupation' => 'Student',
            'is_voter' => false,
            'is_indigent' => true,
            'is_pwd' => false,
            'is_senior_citizen' => false,
            'created_by' => $creatorId,
            'is_active' => true,
            'contact_number' => '09223344556',
            'email' => 'pedro.delacruz@example.com',
        ]);

        GroupInfo::create([
            'resident_id' => $member1_2->id,
            'head_of_household_id' => $head1->id,
        ]);


        // --- Household 2: Santos (Purok 2) ---
        // Head of Household (Senior Citizen)
        $head2 = Resident::create([
            'purok_id' => $purok2->id,
            'head_of_household_id' => null,
            'last_name' => 'Santos',
            'first_name' => 'Alice',
            'date_of_birth' => '1959-04-12',
            'gender' => 'Female',
            'civil_status' => 'Widowed',
            'occupation' => 'Retired Teacher',
            'is_voter' => true,
            'is_indigent' => false,
            'is_pwd' => false,
            'is_senior_citizen' => true,
            'created_by' => $creatorId,
            'is_active' => true,
            'contact_number' => '09987654321',
            'email' => 'alice.santos@example.com',
        ]);

        GroupInfo::create([
            'resident_id' => $head2->id,
            'head_of_household_id' => $head2->id,
        ]);

        // Child (PWD)
        $member2_1 = Resident::create([
            'purok_id' => $purok2->id,
            'head_of_household_id' => $head2->id,
            'last_name' => 'Santos',
            'first_name' => 'Bobby',
            'date_of_birth' => '1996-01-25',
            'gender' => 'Male',
            'civil_status' => 'Single',
            'occupation' => 'Freelance Designer',
            'is_voter' => true,
            'is_indigent' => false,
            'is_pwd' => true,
            'is_senior_citizen' => false,
            'created_by' => $creatorId,
            'is_active' => true,
            'contact_number' => '09334455667',
            'email' => 'bobby.santos@example.com',
        ]);

        GroupInfo::create([
            'resident_id' => $member2_1->id,
            'head_of_household_id' => $head2->id,
        ]);
    }
}
