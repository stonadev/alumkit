<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Tests\Feature\Toggled;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

class FeatureToggleTest extends FeatureToggleDisabledTestCase
{
    use RefreshDatabase;

    public function test_posts_routes_not_registered_when_disabled(): void
    {
        $this->assertFalse(Route::has('alumkit.posts.index'));
        $this->assertFalse(Route::has('alumkit.posts.thumbnail'));
    }

    public function test_committee_routes_not_registered_when_disabled(): void
    {
        $this->assertFalse(Route::has('alumkit.committee.index'));
        $this->assertFalse(Route::has('alumkit.committee.photo'));
        $this->assertFalse(Route::has('alumkit.positions.index'));
    }

    public function test_dashboard_hides_posts_and_committee_when_disabled(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->approved()->create();
        $user->profile()->create();
        $user->educations()->create([
            'level' => 'masters',
            'institution' => 'MIT',
            'subject' => 'Computer Science',
            'start_year' => 2015,
        ]);
        $user->careers()->create([
            'job_title' => 'Developer',
            'company' => 'Acme',
            'employment_type' => 'full_time',
            'start_year' => 2020,
        ]);

        $this->actingAs($user)
            ->get(route('alumkit.dashboard'))
            ->assertOk()
            ->assertDontSee(__('alumkit::post.posts'))
            ->assertDontSee(__('alumkit::committee.committee'));
    }
}
