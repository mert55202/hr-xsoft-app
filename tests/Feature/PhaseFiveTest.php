<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppModule;
use App\Enums\FormIdentity;
use App\Enums\FormStatus;
use App\Enums\QuestionType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Form;
use App\Models\FormLink;
use App\Models\FormQuestion;
use App\Models\FormSection;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_anonymous_response_keeps_no_respondent_for_anyone(): void
    {
        $department = Department::factory()->create([
            'name' => 'İK',
            'modules' => [AppModule::Survey->value],
        ]);
        $admin = User::factory()->admin()->create(['department_id' => $department->id]);
        $person = User::factory()->create(['name' => 'Gizli Kişi', 'department_id' => $department->id]);
        Employee::factory()->create([
            'first_name' => 'Gizli',
            'last_name' => 'Kişi',
            'user_id' => $person->id,
        ]);
        $form = $this->textForm(FormIdentity::Anonymous);

        $this->actingAs($person)->post('/a/'.$form->public_id, [
            'answers' => [$form->sections->first()->questions->first()->id => 'Sessiz yanıt'],
        ])->assertRedirect();

        $this->assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
            'employee_id' => null,
            'department_id' => $department->id,
        ]);
        $this->assertDatabaseMissing('form_submissions', ['employee_id' => $person->employee->id]);

        $this->actingAs($admin)
            ->get('/anket/'.$form->public_id.'/sonuc')
            ->assertOk()
            ->assertSee('Sessiz yanıt')
            ->assertDontSee('Gizli Kişi')
            ->assertDontSee('Kim yanıtladı');

        $this->actingAs($person)
            ->get('/anket/'.$form->public_id.'/sonuc')
            ->assertOk()
            ->assertDontSee('Kim yanıtladı');
    }

    public function test_a_named_response_is_visible_downward_only(): void
    {
        $department = Department::factory()->create(['modules' => [AppModule::Survey->value]]);
        $root = Position::factory()->create(['name' => 'Genel Müdür']);
        $child = Position::factory()->create(['name' => 'İK Müdürü', 'parent_id' => $root->id]);
        $boss = Employee::factory()->create([
            'first_name' => 'Ali',
            'last_name' => 'Müdür',
            'position_id' => $root->id,
            'user_id' => User::factory()->create(['name' => 'Ali Müdür', 'department_id' => $department->id])->id,
        ]);
        $staff = Employee::factory()->create([
            'first_name' => 'Ece',
            'last_name' => 'Uzman',
            'position_id' => $child->id,
            'user_id' => User::factory()->create(['name' => 'Ece Uzman', 'department_id' => $department->id])->id,
        ]);
        $form = $this->textForm(FormIdentity::Named);
        $questionId = $form->sections->first()->questions->first()->id;

        $this->actingAs($staff->user)->post('/formlar/'.$form->public_id, [
            'answers' => [$questionId => 'Alttan'],
        ])->assertRedirect();
        $this->actingAs($boss->user)->post('/formlar/'.$form->public_id, [
            'answers' => [$questionId => 'Üstten'],
        ])->assertRedirect();

        $this->actingAs($boss->user)->get('/anket/'.$form->public_id.'/sonuc')->assertOk()->assertSee('Ece Uzman')->assertSee('Ali Müdür');
        $this->actingAs($staff->user)->get('/anket/'.$form->public_id.'/sonuc')->assertOk()->assertSee('Ece Uzman')->assertDontSee('Ali Müdür');
    }

    public function test_a_published_form_does_not_lose_questions_or_gain_an_employee_pick_when_anonymous(): void
    {
        $admin = User::factory()->admin()->create([
            'department_id' => Department::factory()->create(['modules' => [AppModule::Survey->value]])->id,
        ]);
        $form = $this->textForm(FormIdentity::Anonymous);
        $question = $form->sections->first()->questions->first();

        $this->actingAs($admin)
            ->delete('/anket/'.$form->public_id.'/soru/'.$question->id)
            ->assertNotFound();
        $this->assertModelExists($question);

        $this->actingAs($admin)->post('/anket/'.$form->public_id.'/bolum/'.$form->sections->first()->id.'/soru', [
            'prompt' => 'Kimi seçersin',
            'type' => QuestionType::EmployeePick->value,
        ])->assertSessionHasErrors('prompt');
    }

    public function test_a_draft_rejects_an_employee_question_on_an_anonymous_form(): void
    {
        $admin = User::factory()->admin()->create([
            'department_id' => Department::factory()->create(['modules' => [AppModule::Survey->value]])->id,
        ]);
        $form = Form::factory()->create(['identity' => FormIdentity::Anonymous, 'status' => FormStatus::Draft]);
        $section = $form->sections()->create(['title' => 'Sorular', 'sort' => 1]);

        $this->actingAs($admin)->post('/anket/'.$form->public_id.'/bolum/'.$section->id.'/soru', [
            'prompt' => 'Kimi seçersin',
            'type' => QuestionType::EmployeePick->value,
        ])->assertSessionHasErrors('type');

        $this->assertDatabaseMissing('form_questions', ['prompt' => 'Kimi seçersin']);
    }

    public function test_a_closed_module_cannot_build_forms_and_a_draft_cannot_be_filled(): void
    {
        $user = User::factory()->create();
        $form = Form::factory()->create(['status' => FormStatus::Draft]);

        $this->actingAs($user)->get('/anket')->assertForbidden();
        $this->actingAs($user)->get('/formlar/'.$form->public_id)->assertNotFound();
    }

    public function test_questions_can_be_reordered_while_the_form_is_a_draft(): void
    {
        $admin = User::factory()->admin()->create([
            'department_id' => Department::factory()->create(['modules' => [AppModule::Survey->value]])->id,
        ]);
        $form = Form::factory()->create();
        $section = $form->sections()->create(['title' => 'Sorular', 'sort' => 1]);
        $first = $section->questions()->create(['type' => QuestionType::ShortText, 'prompt' => 'Bir', 'sort' => 1]);
        $second = $section->questions()->create(['type' => QuestionType::ShortText, 'prompt' => 'İki', 'sort' => 2]);

        $this->actingAs($admin)->postJson('/anket/'.$form->public_id.'/bolum/'.$section->id.'/sira', [
            'question_ids' => [$second->id, $first->id],
        ])->assertOk();

        $this->assertSame(2, $first->fresh()->sort);
        $this->assertSame(1, $second->fresh()->sort);
    }

    public function test_a_question_can_be_moved_into_another_section(): void
    {
        $admin = User::factory()->admin()->create([
            'department_id' => Department::factory()->create(['modules' => [AppModule::Survey->value]])->id,
        ]);
        $form = Form::factory()->create();
        $first = $form->sections()->create(['title' => 'Bir', 'sort' => 1]);
        $second = $form->sections()->create(['title' => 'İki', 'sort' => 2]);
        $question = $first->questions()->create(['type' => QuestionType::ShortText, 'prompt' => 'Taşınan', 'sort' => 1]);

        $this->actingAs($admin)->postJson('/anket/'.$form->public_id.'/bolum/'.$second->id.'/sira', [
            'question_ids' => [$question->id],
        ])->assertOk();

        $this->assertSame($second->id, $question->fresh()->form_section_id);
    }

    public function test_the_builder_edits_a_question_in_place_and_shows_a_preview(): void
    {
        $admin = User::factory()->admin()->create([
            'department_id' => Department::factory()->create(['modules' => [AppModule::Survey->value]])->id,
        ]);
        $form = Form::factory()->create(['name' => 'Nabız']);
        $section = $form->sections()->create(['title' => 'Sorular', 'sort' => 1]);

        $this->actingAs($admin)->post('/anket/'.$form->public_id.'/bolum/'.$section->id.'/soru', [
            'prompt' => 'Soru',
            'type' => QuestionType::MultipleChoice->value,
        ])->assertRedirect();

        $question = FormQuestion::query()->first();
        $this->assertSame(2, $question->options()->count());

        $this->actingAs($admin)->putJson('/anket/'.$form->public_id.'/soru/'.$question->id, [
            'prompt' => 'Bugün nasılsın?',
            'type' => QuestionType::MultipleChoice->value,
            'required' => true,
        ])->assertOk();

        $this->actingAs($admin)
            ->get('/anket/'.$form->public_id)
            ->assertOk()
            ->assertSee('data-form-builder', false)
            ->assertSee('Seçenek ekle')
            ->assertSee('Önizleme')
            ->assertSee('Bugün nasılsın?');
    }

    public function test_a_personal_link_is_single_use_and_stores_that_person(): void
    {
        $employee = Employee::factory()->create(['first_name' => 'Link', 'last_name' => 'Kişi']);
        $form = $this->textForm(FormIdentity::Link);
        $link = FormLink::query()->create(['form_id' => $form->id, 'employee_id' => $employee->id]);
        $questionId = $form->sections->first()->questions->first()->id;

        $this->post('/baglanti/'.$link->token, [
            'answers' => [$questionId => 'Tek'],
        ])->assertRedirect();

        $this->assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
            'employee_id' => $employee->id,
        ]);
        $this->post('/baglanti/'.$link->token, [
            'answers' => [$questionId => 'İkinci'],
        ])->assertNotFound();
    }

    private function textForm(FormIdentity $identity): Form
    {
        $form = Form::factory()->create([
            'identity' => $identity,
            'status' => FormStatus::Published,
        ]);
        $section = FormSection::query()->create([
            'form_id' => $form->id,
            'title' => 'Sorular',
            'sort' => 1,
        ]);
        FormQuestion::query()->create([
            'form_section_id' => $section->id,
            'type' => QuestionType::ShortText,
            'prompt' => 'Not',
            'sort' => 1,
        ]);

        return $form->load('sections.questions');
    }
}
