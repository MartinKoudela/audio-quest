<?php

namespace Tests\Feature;

use App\Enums\ScanStatus;
use App\Jobs\GenerateMusicRecommendations;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_create_a_scan(): void
    {
        $response = $this->postJson('/scans', ['scene_description' => 'A rainy night drive through the city']);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_a_scan_and_dispatches_a_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/scans', [
            'scene_description' => 'A rainy night drive through the city',
            'royalty_free_only' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', ScanStatus::Pending->value);

        $scan = Scan::findOrFail($response->json('id'));

        $this->assertSame($user->id, $scan->user_id);
        $this->assertSame('A rainy night drive through the city', $scan->description);
        $this->assertTrue($scan->royalty_free_only);
        $this->assertSame(ScanStatus::Pending, $scan->status);

        Queue::assertPushed(GenerateMusicRecommendations::class, fn ($job) => $job->scanId === $scan->id);
    }

    public function test_scene_description_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/scans', []);

        $response->assertUnprocessable()->assertJsonValidationErrors('scene_description');
    }

    public function test_owner_can_view_their_scan(): void
    {
        $user = User::factory()->create();
        $scan = Scan::create([
            'user_id' => $user->id,
            'job_id' => 'job-1',
            'status' => ScanStatus::Done,
            'description' => 'Late night coding session',
            'royalty_free_only' => false,
            'result' => ['music_suggestions' => []],
        ]);

        $response = $this->actingAs($user)->getJson("/scans/{$scan->id}");

        $response->assertOk()
            ->assertJsonPath('status', ScanStatus::Done->value)
            ->assertJsonPath('result.music_suggestions', []);
    }

    public function test_user_cannot_view_another_users_scan(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $scan = Scan::create([
            'user_id' => $owner->id,
            'job_id' => 'job-2',
            'status' => ScanStatus::Pending,
            'description' => 'A cozy autumn afternoon',
        ]);

        $response = $this->actingAs($intruder)->getJson("/scans/{$scan->id}");

        $response->assertForbidden();
    }
}
