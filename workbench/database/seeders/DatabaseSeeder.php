<?php

namespace Workbench\Database\Seeders;

use Alumkit\Alumkit\Database\Seeders\AlumkitRolesAndPermissionsSeeder;
use Alumkit\Alumkit\Database\Seeders\AlumkitUserSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AlumkitRolesAndPermissionsSeeder::class,
            AlumkitUserSeeder::class,
            ContentSeeder::class,
        ]);

        // Seed a profile for the admin user created by AlumkitUserSeeder.
        $admin = User::where('email', config('alumkit.seeder.admin_email', 'admin@example.com'))->first();

        if ($admin && ! $admin->profile) {
            $admin->profile()->create([
                'date_of_birth' => '1990-01-15',
                'gender' => 'male',
                'blood_group' => 'O+',
                'present_address' => '23/A Dhanmondi R/A, Dhaka 1205',
                'permanent_address' => 'Village: Baroaria, P.B. Rajshahi',
                'social_links' => [
                    'facebook' => 'facebook.com/admin.user',
                    'linkedin' => 'linkedin.com/in/admin-user',
                ],
                'website' => 'admin.example.com',
                'emergency_contact' => [
                    'name' => 'Admin Emergency',
                    'phone' => '+880 1800 000 000',
                    'relation' => 'Spouse',
                ],
            ]);
        }

        // Idempotent: a re-seed must not duplicate users or trip the
        // email/profile unique constraints, so the block runs once only.
        if (! User::where('email', 'verified@example.com')->exists()) {
            $verified = UserFactory::new()->pending()->create([
                'name' => 'Verified User',
                'email' => 'verified@example.com',
                'phone' => '+880 1712 345 678',
            ]);

            $verified->profile()->create();

            $verified->educations()->create([
                'level' => 'Honors',
                'institution' => 'University of Dhaka',
                'subject' => 'Information Technology',
                'start_year' => 2013,
                'end_year' => 2017,
            ]);

            UserFactory::new()->approved()->create([
                'name' => 'Approved User',
                'email' => 'approved@example.com',
                'phone' => '+880 1812 345 678',
            ]);

            UserFactory::new()->unverified()->create([
                'name' => 'Unverified User',
                'email' => 'unverified@example.com',
                'phone' => '+880 1912 345 678',
            ]);

            $featured = UserFactory::new()->approved()->create([
                'name' => 'Ayesha Rahman',
                'email' => 'ayesha.rahman@example.com',
                'phone' => '+880 1612 345 678',
            ]);

            $featured->profile()->create([
                'date_of_birth' => '1994-03-12',
                'gender' => 'female',
                'blood_group' => 'B+',
                'present_address' => '12 Lakeview Road, Dhaka 1212',
                'permanent_address' => 'Village: Shikarpur, P.O. Narsingdi Sadar',
                'social_links' => [
                    'facebook' => 'facebook.com/ayesha.rahman',
                    'linkedin' => 'linkedin.com/in/ayesha-rahman',
                ],
                'website' => 'ayesharahman.dev',
                'emergency_contact' => [
                    'name' => 'Tanvir Rahman',
                    'phone' => '+880 1711 000 000',
                    'relation' => 'Brother',
                ],
            ]);

            $featured->educations()->create([
                'level' => 'Honors',
                'institution' => 'University of Dhaka',
                'subject' => 'Computer Science',
                'start_year' => 2012,
                'end_year' => 2016,
            ]);
            $featured->educations()->create([
                'level' => 'Masters',
                'institution' => 'North South University',
                'subject' => 'Software Engineering',
                'start_year' => 2017,
                'end_year' => 2019,
            ]);
            $featured->careers()->create([
                'job_title' => 'Senior Software Engineer',
                'company' => 'Bongo Tech',
                'employment_type' => 'full_time',
                'industry' => 'Software',
                'location' => 'Dhaka',
                'start_year' => 2022,
                'is_current' => true,
                'description' => 'Leading the payments platform team: architecture, code review, and mentoring four engineers.',
            ]);
            $featured->careers()->create([
                'job_title' => 'Software Engineer',
                'company' => 'Robi Axiata',
                'employment_type' => 'full_time',
                'industry' => 'Telecom',
                'location' => 'Dhaka',
                'start_year' => 2019,
                'end_year' => 2022,
            ]);
            $featured->careers()->create([
                'job_title' => 'Freelance Web Developer',
                'company' => 'Self-employed',
                'employment_type' => 'freelance',
                'industry' => 'Software',
                'start_year' => 2016,
                'end_year' => 2019,
            ]);
        }
    }
}
