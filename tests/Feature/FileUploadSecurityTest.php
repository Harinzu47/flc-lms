<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\LMS\SubmitTaskAction;
use App\Livewire\TaskShow;
use App\Models\Course;
use App\Models\Module;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

/**
 * Regression tests for file upload security in SubmitTaskAction.
 *
 * Ensures MIME type validation (actual file signature/content) is enforced,
 * preventing spoofing attacks where an executable file is renamed (e.g. hack.php to hack.pdf).
 */
final class FileUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    private SubmitTaskAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new SubmitTaskAction();
        Storage::fake('local');
    }

    /**
     * Helper to create an unlocked course module task.
     */
    private function createUnlockedTask(string $type = 'file_upload'): Task
    {
        $course = Course::factory()->create([
            'min_level_required'     => null,
            'prerequisite_course_id' => null,
            'is_published'           => true,
        ]);

        $module = Module::factory()->create([
            'course_id' => $course->id,
        ]);

        return Task::factory()->create([
            'module_id'  => $module->id,
            'type'       => $type,
            'days_limit' => null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Direct Action Tests (MIME Spoofing prevention)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_rejects_php_file_masquerading_as_pdf(): void
    {
        $user = User::factory()->create(['total_xp' => 9999]);
        $task = $this->createUnlockedTask();

        // Create a fake file with PHP content but pdf extension
        $maliciousFile = UploadedFile::fake()->createWithContent(
            'malicious.pdf',
            '<?php echo "hacked"; ?>'
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Security Validation Failed: File content does not match an allowed type.');

        $this->action->execute($user, $task, null, $maliciousFile);
    }

    public function test_accepts_valid_pdf_content_and_extension(): void
    {
        $user = User::factory()->create(['total_xp' => 9999]);
        $task = $this->createUnlockedTask();

        // Create a fake PDF with actual PDF header content
        $validPdf = UploadedFile::fake()->createWithContent(
            'document.pdf',
            "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF"
        );

        $submission = $this->action->execute($user, $task, null, $validPdf);

        $this->assertNotNull($submission);
        $this->assertNotNull($submission->file_url);
        Storage::disk('local')->assertExists($submission->file_url);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Livewire Layer Integration Tests
    // ─────────────────────────────────────────────────────────────────────────

    public function test_livewire_rejects_invalid_file_extension(): void
    {
        $user = User::factory()->create(['total_xp' => 9999]);
        $task = $this->createUnlockedTask('file_upload');

        $this->actingAs($user);

        // Upload a raw PHP file directly - should fail Livewire validation
        $maliciousFile = UploadedFile::fake()->createWithContent(
            'hack.php',
            '<?php phpinfo();'
        );

        Livewire::test(TaskShow::class, ['task' => $task])
            ->set('uploadedFile', $maliciousFile)
            ->call('submitTask')
            ->assertHasErrors(['uploadedFile']);
    }

    public function test_livewire_submits_valid_file_successfully(): void
    {
        $user = User::factory()->create(['total_xp' => 9999]);
        $task = $this->createUnlockedTask('file_upload');

        $this->actingAs($user);

        $validPdf = UploadedFile::fake()->createWithContent(
            'assignment.pdf',
            "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF"
        );

        Livewire::test(TaskShow::class, ['task' => $task])
            ->set('uploadedFile', $validPdf)
            ->call('submitTask')
            ->assertHasNoErrors()
            ->assertSet('uploadedFile', null);

        $this->assertDatabaseHas('submissions', [
            'user_id' => $user->id,
            'task_id' => $task->id,
            'status'  => 'pending',
        ]);
    }
}
