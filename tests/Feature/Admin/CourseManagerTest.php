<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Module;
use App\Models\Material;
use App\Models\Task;
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
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.courses'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_course_management(): void
    {
        $student = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($student)->get(route('admin.courses'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\CourseManager::class)
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
        $admin = User::factory()->create(['role' => 'admin']);
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

        // Test swap up: moving module2 up should swap orders
        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\CourseManager::class)
            ->set('selectedCourseId', $course->id)
            ->call('moveModuleUp', $module2)
            ->assertDispatched('notify');

        $this->assertEquals(2, $module1->fresh()->sort_order);
        $this->assertEquals(1, $module2->fresh()->sort_order);
    }

    public function test_deleting_material_cleans_up_related_xp_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'member']);
        
        $course = Course::create(['title' => 'Test', 'difficulty_level' => 'beginner']);
        $module = Module::create(['course_id' => $course->id, 'title' => 'M1', 'sort_order' => 1]);
        $material = Material::create([
            'module_id' => $module->id,
            'title' => 'Readings',
            'type' => 'article',
            'xp_reward' => 10,
        ]);

        // Simulate student reading material and earning XP
        XpLog::create([
            'user_id' => $student->id,
            'action' => 'material_read',
            'xp_earned' => 10,
            'reference_id' => $material->id,
        ]);

        // Admin deletes the material
        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\CourseManager::class)
            ->set('selectedCourseId', $course->id)
            ->call('deleteMaterial', $material)
            ->assertDispatched('notify');

        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        
        // Verify that XpLog is also deleted (cascaded by static model listener)
        $this->assertDatabaseMissing('xp_logs', [
            'action' => 'material_read',
            'reference_id' => $material->id,
        ]);
    }
}
