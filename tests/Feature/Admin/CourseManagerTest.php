<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\CourseManager;
use App\Models\Course;
use App\Models\Material;
use App\Models\Module;
use App\Models\User;
use App\Models\XpLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CourseManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_course_management(): void
    {
        $admin = User::factory()->create(['role' => 'instruktur']);

        $response = $this->actingAs($admin)->get(route('admin.courses'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_course_management(): void
    {
        $student = User::factory()->create(['role' => 'peserta']);

        $response = $this->actingAs($student)->get(route('admin.courses'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_course(): void
    {
        $admin = User::factory()->create(['role' => 'instruktur']);

        Livewire::actingAs($admin)
            ->test(CourseManager::class)
            ->set('courseTitle', 'English For Science')
            ->set('courseDescription', 'Belajar bahasa Inggris ilmiah.')
            ->set('courseDifficultyLevel', 'intermediate')
            ->set('courseIsPublished', true)
            ->call('saveCourse')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertDatabaseHas('courses', [
            'title' => 'English For Science',
            'difficulty_level' => 'intermediate',
            'is_published' => true,
        ]);
    }

    public function test_admin_can_create_module_and_swap_orders(): void
    {
        $admin = User::factory()->create(['role' => 'instruktur']);
        $course = Course::create([
            'title' => 'Biology 101',
            'description' => 'Intro to biology.',
            'difficulty_level' => 'beginner',
            'is_published' => true,
        ]);

        $module1 = Module::create([
            'course_id' => $course->id,
            'title' => 'Module 1',
            'sort_order' => 1,
        ]);

        $module2 = Module::create([
            'course_id' => $course->id,
            'title' => 'Module 2',
            'sort_order' => 2,
        ]);

        $admin->courses()->attach($course);

        // Test swap up: moving module2 up should swap orders
        Livewire::actingAs($admin)
            ->test(CourseManager::class)
            ->set('selectedCourseId', $course->id)
            ->call('moveModuleUp', $module2)
            ->assertDispatched('notify');

        $this->assertEquals(2, $module1->fresh()->sort_order);
        $this->assertEquals(1, $module2->fresh()->sort_order);
    }

    public function test_deleting_material_preserves_historical_xp_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'peserta']);

        $course = Course::create(['title' => 'Sample Course', 'difficulty_level' => 'beginner']);
        $module = Module::create(['course_id' => $course->id, 'title' => 'Sample Module', 'sort_order' => 1]);
        $material = Material::create(['module_id' => $module->id, 'title' => 'Sample Material', 'type' => 'document']);

        XpLog::create([
            'user_id' => $student->id,
            'action' => 'material_read',
            'xp_earned' => 10,
            'reference_id' => $material->id,
        ]);

        $admin->courses()->attach($course);

        // Admin deletes the material
        Livewire::actingAs($admin)
            ->test(CourseManager::class)
            ->set('selectedCourseId', $course->id)
            ->call('deleteMaterial', $material)
            ->assertDispatched('notify');

        $this->assertDatabaseMissing('materials', ['id' => $material->id]);

        // Verify that XpLog is preserved as an append-only audit trail
        $this->assertDatabaseHas('xp_logs', [
            'action' => 'material_read',
            'reference_id' => $material->id,
        ]);
    }

    public function test_instruktur_can_only_see_assigned_courses(): void
    {
        $instruktur = User::factory()->create(['role' => 'instruktur']);

        $assignedCourse = Course::create(['title' => 'Assigned', 'difficulty_level' => 'beginner']);
        $unassignedCourse = Course::create(['title' => 'Unassigned', 'difficulty_level' => 'beginner']);

        $instruktur->courses()->attach($assignedCourse);

        Livewire::actingAs($instruktur)
            ->test(CourseManager::class)
            ->assertSee('Assigned')
            ->assertViewHas('courses', function ($courses) {
                return $courses->count() === 1 && $courses->first()->title === 'Assigned';
            });
    }

    public function test_admin_can_assign_instruktur_to_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instruktur = User::factory()->create(['role' => 'instruktur']);

        $course = Course::create(['title' => 'Physics 101', 'difficulty_level' => 'beginner']);

        Livewire::actingAs($admin)
            ->test(CourseManager::class)
            ->call('openAssignInstrukturModal', $course->id)
            ->set('selectedInstrukturIds', [$instruktur->id])
            ->call('saveInstrukturAssignments')
            ->assertDispatched('notify');

        $this->assertTrue($course->instruktur()->where('users.id', $instruktur->id)->exists());
    }
}
