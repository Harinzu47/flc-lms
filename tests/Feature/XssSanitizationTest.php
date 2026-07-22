<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\MaterialShow;
use App\Livewire\TaskShow;
use App\Models\Course;
use App\Models\Material;
use App\Models\Module;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression tests for XSS sanitization on description fields.
 *
 * Both task-show and material-show blade templates render description
 * via {!! Str::markdown($x, ['html_input' => 'escape', ...]) !!}.
 * These tests ensure that raw HTML (e.g. <script>) is always escaped,
 * while valid Markdown still renders correctly.
 */
final class XssSanitizationTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create a course → module hierarchy with no prerequisites/level gates.
     */
    private function createUnlockedModule(): Module
    {
        $course = Course::factory()->create([
            'min_level_required' => null,
            'prerequisite_course_id' => null,
            'is_published' => true,
        ]);

        return Module::factory()->create([
            'course_id' => $course->id,
            'sort_order' => 1,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Unit-level: Str::markdown escapes HTML
    // ─────────────────────────────────────────────────────────────────────────

    public function test_str_markdown_escapes_script_tags(): void
    {
        $malicious = '<script>alert("xss")</script>';

        $output = Str::markdown($malicious, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringNotContainsString('</script>', $output);
        // The escaped version should contain the entity-encoded form
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    public function test_str_markdown_escapes_img_onerror_payload(): void
    {
        $malicious = '<img src=x onerror=alert("xss")>';

        $output = Str::markdown($malicious, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        $this->assertStringNotContainsString('<img', $output);
        $this->assertStringContainsString('&lt;img', $output);
    }

    public function test_str_markdown_blocks_javascript_links(): void
    {
        $malicious = '[Click me](javascript:alert("xss"))';

        $output = Str::markdown($malicious, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        $this->assertStringNotContainsString('javascript:', $output);
    }

    public function test_str_markdown_renders_valid_markdown(): void
    {
        $markdown = "# Hello World\n\nThis is **bold** and *italic*.";

        $output = Str::markdown($markdown, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        $this->assertStringContainsString('<h1>', $output);
        $this->assertStringContainsString('<strong>bold</strong>', $output);
        $this->assertStringContainsString('<em>italic</em>', $output);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Integration: TaskShow Livewire renders escaped description
    // ─────────────────────────────────────────────────────────────────────────

    public function test_task_show_escapes_script_in_description(): void
    {
        $user = User::factory()->create(['total_xp' => 9999]);
        $module = $this->createUnlockedModule();
        $task = Task::factory()->create([
            'module_id' => $module->id,
            'description' => '<script>alert("xss")</script>',
            'days_limit' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(TaskShow::class, ['task' => $task])
            ->assertDontSeeHtml('<script>alert("xss")</script>')
            ->assertSeeHtml('&lt;script&gt;');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Integration: MaterialShow Livewire renders escaped description
    // ─────────────────────────────────────────────────────────────────────────

    public function test_material_show_escapes_script_in_description(): void
    {
        $user = User::factory()->create(['total_xp' => 9999]);
        $module = $this->createUnlockedModule();
        $material = Material::factory()->create([
            'module_id' => $module->id,
            'description' => '<script>alert("xss")</script>',
        ]);

        $this->actingAs($user);

        Livewire::test(MaterialShow::class, ['material' => $material])
            ->assertDontSeeHtml('<script>alert("xss")</script>')
            ->assertSeeHtml('&lt;script&gt;');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Edge: null / empty description gracefully renders empty
    // ─────────────────────────────────────────────────────────────────────────

    public function test_str_markdown_handles_empty_description(): void
    {
        $output = Str::markdown('', [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        // Should produce empty or whitespace-only output, no errors
        $this->assertNotNull($output);
    }

    public function test_material_modal_preview_escapes_script_in_description(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\CourseManager::class)
            ->set('materialType', 'article')
            ->set('materialDescription', '<script>alert("xss")</script>')
            ->assertDontSeeHtml('<script>alert("xss")</script>')
            ->assertSeeHtml('&lt;script&gt;');
    }
}
