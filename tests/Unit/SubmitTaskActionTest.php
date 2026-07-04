<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\LMS\SubmitTaskAction;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

final class SubmitTaskActionTest extends TestCase
{
    use RefreshDatabase;

    private SubmitTaskAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new SubmitTaskAction();
        Storage::fake('local');
    }

    public function test_creates_new_submission_successfully(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        $file = UploadedFile::fake()->create('assignment.pdf', 500);

        $submission = $this->action->execute($user, $task, 'My answer essay', $file);

        $this->assertNotNull($submission);
        $this->assertEquals($user->id, $submission->user_id);
        $this->assertEquals($task->id, $submission->task_id);
        $this->assertEquals('My answer essay', $submission->answer_text);
        $this->assertEquals('pending', $submission->status);
        $this->assertFalse($submission->is_flagged);
        $this->assertNotNull($submission->file_url);

        Storage::disk('local')->assertExists($submission->file_url);
    }

    public function test_blocks_new_submission_if_already_submitted_and_not_flagged(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        
        // Existing clean submission
        Submission::factory()->create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'is_flagged' => false,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You have already submitted this task.');

        $this->action->execute($user, $task, 'Another answer', null);
    }

    public function test_allows_resubmission_when_flagged_for_revision(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        
        // Old file
        $oldFileUrl = 'submissions/old.pdf';
        Storage::disk('local')->put($oldFileUrl, 'old content');

        $submission = Submission::factory()->create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'file_url' => $oldFileUrl,
            'is_flagged' => true,
            'status' => 'pending',
            'review_comment' => 'Please revise.',
        ]);

        $newFile = UploadedFile::fake()->create('revised.pdf', 600);

        $updated = $this->action->execute($user, $task, 'Revised essay', $newFile);

        $this->assertEquals($submission->id, $updated->id);
        $this->assertEquals('Revised essay', $updated->answer_text);
        $this->assertFalse($updated->is_flagged);
        $this->assertNull($updated->review_comment);
        $this->assertEquals('pending', $updated->status);

        // Assert old file deleted and new file exists
        Storage::disk('local')->assertMissing($oldFileUrl);
        Storage::disk('local')->assertExists($updated->file_url);
    }

    public function test_throws_exception_on_unsupported_file_extension(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        $file = UploadedFile::fake()->create('malicious.sh', 100);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Security Validation Failed: Unsupported file extension.');

        $this->action->execute($user, $task, 'Malicious code upload', $file);
    }
}
