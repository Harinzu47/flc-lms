<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class SubmissionDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_admin_can_download_any_student_submission(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'member']);
        
        $filePath = 'submissions/sample.pdf';
        Storage::disk('local')->put($filePath, 'sample content');
        
        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'file_url' => $filePath,
        ]);

        // Act
        $response = $this->actingAs($admin)
            ->get(route('submissions.download', $submission));

        // Assert
        $response->assertStatus(200);
        $this->assertEquals('sample content', $response->streamedContent());
    }

    public function test_student_can_download_their_own_submission(): void
    {
        // Arrange
        $student = User::factory()->create(['role' => 'member']);
        
        $filePath = 'submissions/student_doc.pdf';
        Storage::disk('local')->put($filePath, 'my work');
        
        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'file_url' => $filePath,
        ]);

        // Act
        $response = $this->actingAs($student)
            ->get(route('submissions.download', $submission));

        // Assert
        $response->assertStatus(200);
        $this->assertEquals('my work', $response->streamedContent());
    }

    public function test_student_cannot_download_other_student_submission(): void
    {
        // Arrange
        $student1 = User::factory()->create(['role' => 'member']);
        $student2 = User::factory()->create(['role' => 'member']);
        
        $filePath = 'submissions/secret.pdf';
        Storage::disk('local')->put($filePath, 'confidential');
        
        $submission = Submission::factory()->create([
            'user_id' => $student2->id,
            'file_url' => $filePath,
        ]);

        // Act
        $response = $this->actingAs($student1)
            ->get(route('submissions.download', $submission));

        // Assert
        $response->assertStatus(403);
    }

    public function test_unauthenticated_guests_cannot_download_any_submission(): void
    {
        // Arrange
        $student = User::factory()->create(['role' => 'member']);
        $submission = Submission::factory()->create(['user_id' => $student->id]);

        // Act
        $response = $this->get(route('submissions.download', $submission));

        // Assert
        $response->assertRedirect('/login');
    }

    public function test_returns_404_if_file_does_not_exist(): void
    {
        // Arrange
        $student = User::factory()->create(['role' => 'member']);
        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'file_url' => 'submissions/missing.pdf', // File does not exist on fake disk
        ]);

        // Act
        $response = $this->actingAs($student)
            ->get(route('submissions.download', $submission));

        // Assert
        $response->assertStatus(404);
    }

    public function test_prevents_path_traversal_attempts(): void
    {
        // Arrange
        $student = User::factory()->create(['role' => 'member']);
        $submission = Submission::factory()->create([
            'user_id' => $student->id,
            'file_url' => 'submissions/../../database/database.sqlite',
        ]);

        // Act
        $response = $this->actingAs($student)
            ->get(route('submissions.download', $submission));

        // Assert
        $response->assertStatus(400); // Bad Request / Blocked
    }
}
