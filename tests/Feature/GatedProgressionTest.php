<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Level;
use App\Models\Module;
use App\Models\Material;
use App\Models\Task;
use App\Models\Submission;
use App\Models\User;
use App\Models\XpLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatedProgressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_courses_without_prerequisites_are_always_unlocked(): void
    {
        $user = User::factory()->create(['total_xp' => 100]);
        
        $course = Course::create([
            'title' => 'Basic English',
            'difficulty_level' => 'beginner',
            'min_level_required' => null,
            'prerequisite_course_id' => null,
            'is_published' => true,
        ]);

        $this->assertFalse($course->isLockedForUser($user));
    }

    public function test_courses_are_locked_if_user_has_insufficient_xp(): void
    {
        $level = Level::create([
            'name' => 'Level 2',
            'min_xp' => 500,
        ]);

        $user = User::factory()->create(['total_xp' => 499]);
        
        $course = Course::create([
            'title' => 'Intermediate English',
            'difficulty_level' => 'intermediate',
            'min_level_required' => $level->id,
            'prerequisite_course_id' => null,
            'is_published' => true,
        ]);

        $this->assertTrue($course->isLockedForUser($user));
    }

    public function test_courses_are_unlocked_if_user_has_sufficient_xp(): void
    {
        $level = Level::create([
            'name' => 'Level 2',
            'min_xp' => 500,
        ]);

        $user = User::factory()->create(['total_xp' => 500]);
        
        $course = Course::create([
            'title' => 'Intermediate English',
            'difficulty_level' => 'intermediate',
            'min_level_required' => $level->id,
            'prerequisite_course_id' => null,
            'is_published' => true,
        ]);

        $this->assertFalse($course->isLockedForUser($user));
    }

    public function test_course_locked_by_prerequisite_course(): void
    {
        $user = User::factory()->create(['total_xp' => 100]);

        $course1 = Course::create([
            'title' => 'Course One',
            'difficulty_level' => 'beginner',
            'is_published' => true,
        ]);

        $module1 = Module::create([
            'course_id' => $course1->id,
            'title' => 'Module One',
            'sort_order' => 1,
        ]);

        $material1 = Material::create([
            'module_id' => $module1->id,
            'title' => 'Material One',
            'type' => 'document',
            'xp_reward' => 10,
        ]);

        $course2 = Course::create([
            'title' => 'Course Two',
            'difficulty_level' => 'intermediate',
            'prerequisite_course_id' => $course1->id,
            'is_published' => true,
        ]);

        // Course 2 is locked because Course 1 has not been completed
        $this->assertTrue($course2->isLockedForUser($user));

        // Let's complete Course 1 for the user (by reading material1)
        XpLog::create([
            'user_id' => $user->id,
            'action' => 'material_read',
            'xp_earned' => 10,
            'reference_id' => $material1->id,
        ]);

        // Course 2 should be unlocked now
        $this->assertFalse($course2->isLockedForUser($user));
    }

    public function test_module_is_locked_sequentially(): void
    {
        $user = User::factory()->create(['total_xp' => 100]);

        $course = Course::create([
            'title' => 'Course One',
            'difficulty_level' => 'beginner',
            'is_published' => true,
        ]);

        $module1 = Module::create([
            'course_id' => $course->id,
            'title' => 'Module One',
            'sort_order' => 1,
        ]);

        $material1 = Material::create([
            'module_id' => $module1->id,
            'title' => 'Material One',
            'type' => 'document',
            'xp_reward' => 10,
        ]);

        $module2 = Module::create([
            'course_id' => $course->id,
            'title' => 'Module Two',
            'sort_order' => 2,
        ]);

        // Module 2 is locked because Module 1 is not completed
        $this->assertTrue($module2->isLockedForUser($user));

        // Complete Module 1
        XpLog::create([
            'user_id' => $user->id,
            'action' => 'material_read',
            'xp_earned' => 10,
            'reference_id' => $material1->id,
        ]);

        // Module 2 is now unlocked
        $this->assertFalse($module2->isLockedForUser($user));
    }

    public function test_accessing_locked_material_returns_forbidden(): void
    {
        $level = Level::create([
            'name' => 'Level 3',
            'min_xp' => 1000,
        ]);

        $user = User::factory()->create(['total_xp' => 500]);

        $course = Course::create([
            'title' => 'Advanced Course',
            'difficulty_level' => 'advanced',
            'min_level_required' => $level->id,
            'is_published' => true,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Module One',
            'sort_order' => 1,
        ]);

        $material = Material::create([
            'module_id' => $module->id,
            'title' => 'Advanced Lesson',
            'type' => 'document',
            'xp_reward' => 50,
        ]);

        $response = $this->actingAs($user)->get(route('materials.show', $material));
        $response->assertStatus(403);
    }

    public function test_accessing_unlocked_material_is_allowed(): void
    {
        $user = User::factory()->create(['total_xp' => 100]);

        $course = Course::create([
            'title' => 'Basic Course',
            'difficulty_level' => 'beginner',
            'is_published' => true,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Module One',
            'sort_order' => 1,
        ]);

        $material = Material::create([
            'module_id' => $module->id,
            'title' => 'Basic Lesson',
            'type' => 'document',
            'xp_reward' => 10,
        ]);

        $response = $this->actingAs($user)->get(route('materials.show', $material));
        $response->assertStatus(200);
    }

    public function test_article_material_type_is_accessible_and_renders_safely(): void
    {
        $user = User::factory()->create(['total_xp' => 100]);

        $course = Course::create([
            'title' => 'Basic Course',
            'difficulty_level' => 'beginner',
            'is_published' => true,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Module One',
            'sort_order' => 1,
        ]);

        $material = Material::create([
            'module_id' => $module->id,
            'title' => 'Markdown Lesson',
            'description' => '# Hello World Story',
            'type' => 'article',
            'xp_reward' => 10,
            'file_url' => 'https://example.com/attachment.pdf',
        ]);

        $response = $this->actingAs($user)->get(route('materials.show', $material));
        $response->assertStatus(200);
        $response->assertSee('Hello World Story');
        $response->assertSee('Resource Pendukung Kuliah');
        $response->assertSee('https://example.com/attachment.pdf');
    }
}
