<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\CourseManager;
use App\Livewire\Admin\UserManager;
use App\Livewire\GamifiedDashboard;
use App\Livewire\GradingStation;
use App\Livewire\LanguageTrackOnboarding;
use App\Livewire\Library;
use App\Models\Course;
use App\Models\Material;
use App\Models\Module;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use App\Models\XpLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityAuditRemediationTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_cannot_select_or_edit_unassigned_course(): void
    {
        $instrukturA = User::factory()->create(['role' => User::ROLE_INSTRUKTUR]);
        $instrukturB = User::factory()->create(['role' => User::ROLE_INSTRUKTUR]);

        $courseB = Course::factory()->create(['title' => 'Course B']);
        $courseB->users()->attach($instrukturB->id);

        // Instruktur A attempting to select Course B in CourseManager should be forbidden (403)
        $this->actingAs($instrukturA);

        Livewire::test(CourseManager::class)
            ->call('selectCourse', $courseB->id)
            ->assertForbidden();

        // Instruktur A attempting to edit Course B should be forbidden (403)
        Livewire::test(CourseManager::class)
            ->call('editCourse', $courseB->id)
            ->assertForbidden();

        // Instruktur A attempting to delete Course B should be forbidden (403)
        Livewire::test(CourseManager::class)
            ->call('deleteCourse', $courseB->id)
            ->assertForbidden();
    }

    public function test_instructor_cannot_modify_modules_materials_or_tasks_of_unassigned_course(): void
    {
        $instrukturA = User::factory()->create(['role' => User::ROLE_INSTRUKTUR]);
        $instrukturB = User::factory()->create(['role' => User::ROLE_INSTRUKTUR]);

        $courseB = Course::factory()->create();
        $courseB->users()->attach($instrukturB->id);

        $moduleB = Module::factory()->create(['course_id' => $courseB->id]);
        $materialB = Material::factory()->create(['module_id' => $moduleB->id]);
        $taskB = Task::factory()->create(['module_id' => $moduleB->id]);

        $this->actingAs($instrukturA);

        // Attempting to edit module of Course B
        Livewire::test(CourseManager::class)
            ->call('editModule', $moduleB->id)
            ->assertForbidden();

        // Attempting to delete module of Course B
        Livewire::test(CourseManager::class)
            ->call('deleteModule', $moduleB->id)
            ->assertForbidden();

        // Attempting to edit material of Course B
        Livewire::test(CourseManager::class)
            ->call('editMaterial', $materialB->id)
            ->assertForbidden();

        // Attempting to delete material of Course B
        Livewire::test(CourseManager::class)
            ->call('deleteMaterial', $materialB->id)
            ->assertForbidden();

        // Attempting to edit task of Course B
        Livewire::test(CourseManager::class)
            ->call('editTask', $taskB->id)
            ->assertForbidden();

        // Attempting to delete task of Course B
        Livewire::test(CourseManager::class)
            ->call('deleteTask', $taskB->id)
            ->assertForbidden();
    }

    public function test_instructor_cannot_grade_or_flag_submissions_from_unassigned_course(): void
    {
        $instrukturA = User::factory()->create(['role' => User::ROLE_INSTRUKTUR]);
        $instrukturB = User::factory()->create(['role' => User::ROLE_INSTRUKTUR]);
        $student = User::factory()->create(['role' => User::ROLE_PESERTA]);

        $courseB = Course::factory()->create();
        $courseB->users()->attach($instrukturB->id);

        $moduleB = Module::factory()->create(['course_id' => $courseB->id]);
        $taskB = Task::factory()->create(['module_id' => $moduleB->id, 'base_xp' => 100]);

        $submission = Submission::create([
            'task_id' => $taskB->id,
            'user_id' => $student->id,
            'status' => 'pending',
            'is_flagged' => false,
            'answer_text' => 'Sample submission answer text.',
        ]);

        $this->actingAs($instrukturA);

        // Instruktur A cannot toggle flag
        Livewire::test(GradingStation::class)
            ->call('toggleFlag', $submission->id)
            ->assertForbidden();

        // Instruktur B (assigned) CAN toggle flag with valid comment
        $this->actingAs($instrukturB);

        Livewire::test(GradingStation::class)
            ->set('reviewComment', 'Perlu perbaikan pada bagian pendahuluan.')
            ->call('toggleFlag', $submission->id)
            ->assertHasNoErrors();

        $this->assertTrue($submission->fresh()->is_flagged);
    }

    public function test_gamified_dashboard_redirects_roles_correctly(): void
    {
        $instruktur = User::factory()->create(['role' => User::ROLE_INSTRUKTUR]);
        $this->actingAs($instruktur);

        Livewire::test(GamifiedDashboard::class)
            ->assertRedirect(route('admin.courses'));

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        Livewire::test(GamifiedDashboard::class)
            ->assertRedirect(route('admin.users'));

        $peserta = User::factory()->create(['role' => User::ROLE_PESERTA]);
        $this->actingAs($peserta);

        Livewire::test(GamifiedDashboard::class)
            ->assertOk()
            ->assertViewIs('livewire.gamified-dashboard');
    }

    public function test_language_track_onboarding_validates_tracks(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_PESERTA,
            'peminatan_bahasa' => null,
        ]);

        $this->actingAs($student);

        // Empty selection should fail
        Livewire::test(LanguageTrackOnboarding::class)
            ->set('selectedTracks', [])
            ->call('save')
            ->assertHasErrors(['selectedTracks']);

        // Invalid track value should fail
        Livewire::test(LanguageTrackOnboarding::class)
            ->set('selectedTracks', ['invalid_language'])
            ->call('save')
            ->assertHasErrors(['selectedTracks.0']);

        // Valid selection should succeed
        Livewire::test(LanguageTrackOnboarding::class)
            ->set('selectedTracks', ['inggris'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(['inggris'], $student->fresh()->peminatan_bahasa);
    }

    public function test_student_cannot_enroll_into_disallowed_language_track(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_PESERTA,
            'peminatan_bahasa' => ['inggris'],
        ]);

        $arabicCourse = Course::factory()->create([
            'kategori_bahasa' => 'arab',
            'is_published' => true,
        ]);

        $this->actingAs($student);

        Livewire::test(Library::class)
            ->call('enroll', $arabicCourse->id)
            ->assertForbidden();

        $this->assertFalse($arabicCourse->isEnrolledByUser($student));
    }

    public function test_audit_logs_are_preserved_when_tasks_or_materials_are_deleted(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PESERTA, 'total_xp' => 100]);
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $material = Material::factory()->create(['module_id' => $module->id]);
        $task = Task::factory()->create(['module_id' => $module->id]);

        $matLog = XpLog::create([
            'user_id' => $user->id,
            'action' => 'material_read',
            'xp_earned' => 10,
            'reference_id' => $material->id,
        ]);

        $taskLog = XpLog::create([
            'user_id' => $user->id,
            'action' => 'task_graded',
            'xp_earned' => 50,
            'reference_id' => $task->id,
        ]);

        // Delete material and task
        $material->delete();
        $task->delete();

        // Logs must still exist in database
        $this->assertDatabaseHas('xp_logs', ['id' => $matLog->id]);
        $this->assertDatabaseHas('xp_logs', ['id' => $taskLog->id]);
    }

    public function test_admin_cannot_delete_themselves_or_the_last_admin(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        // Self-deletion attempt
        Livewire::test(UserManager::class)
            ->call('delete', $admin->id)
            ->assertDispatched('notify', message: 'Anda tidak dapat menghapus akun Anda sendiri.');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }
}
