<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppModule;
use App\Enums\FormIdentity;
use App\Enums\FormStatus;
use App\Enums\PayType;
use App\Enums\QuestionType;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Enrollment;
use App\Models\Form;
use App\Models\FormLink;
use App\Models\FormSection;
use App\Models\FormSubmission;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Lookup;
use App\Models\Payslip;
use App\Models\Position;
use App\Models\Review;
use App\Models\User;
use App\Support\FormTemplates;
use App\Support\LookupKind;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaterPhasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_person_can_be_removed_from_a_department_module_but_not_given_a_closed_one(): void
    {
        $department = Department::factory()->create(['modules' => [AppModule::Survey->value]]);
        $root = Position::factory()->create();
        $child = Position::factory()->create(['parent_id' => $root->id]);
        $boss = $this->seat('Ali', 'Müdür', $root, $department);
        $staff = $this->seat('Ece', 'Uzman', $child, $department);

        $this->actingAs($staff->user)->get('/')->assertSee('Anket');

        $this->actingAs($boss->user)->put('/personel/'.$staff->id.'/moduller', [
            'hidden_modules' => [AppModule::Survey->value],
        ])->assertRedirect();

        $this->actingAs($staff->user->fresh())->get('/')->assertDontSee('Anket');
        $this->actingAs($boss->user)->put('/personel/'.$staff->id.'/moduller', [
            'hidden_modules' => [AppModule::Training->value],
        ])->assertSessionHasErrors('hidden_modules');
        $this->assertSame([AppModule::Survey->value], $staff->user->fresh()->hidden_modules);
    }

    public function test_an_empty_parent_seat_warns_before_anyone_is_placed(): void
    {
        $admin = User::factory()->admin()->create();
        $root = Position::factory()->create(['name' => 'Genel Müdür']);
        Position::factory()->create(['name' => 'Uzman', 'parent_id' => $root->id]);

        $this->actingAs($admin)->get('/sema')->assertOk()->assertSee('Önce o pozisyona personel atanmalıdır')->assertSee('Genel Müdür');
    }

    public function test_the_twelve_templates_exist_and_the_exit_form_opens_with_a_token(): void
    {
        FormTemplates::install();
        FormTemplates::install();

        $this->assertSame(12, Form::query()->whereNotNull('template')->count());
        $this->assertSame(count(LookupKind::labels()), 53);

        $form = Form::query()->where('template', 'exit_employee')->firstOrFail();
        $employee = Employee::factory()->create();
        $link = FormLink::query()->create(['form_id' => $form->id, 'employee_id' => $employee->id]);

        $this->get('/baglanti/'.$link->token)->assertOk()->assertSee('Personel çıkış görüşmesi')->assertSee('Ayrılma gerekçen nedir?');
    }

    public function test_a_candidate_becomes_personnel_when_hired(): void
    {
        $admin = User::factory()->admin()->create([
            'department_id' => Department::factory()->create(['modules' => [AppModule::Recruitment->value]])->id,
        ]);
        $position = Position::factory()->create(['name' => 'Uzman', 'headcount' => 1]);
        $candidate = Employee::factory()->create([
            'first_name' => 'Kerem',
            'last_name' => 'Aday',
            'is_candidate' => true,
            'position_id' => null,
        ]);

        $this->actingAs($admin)->get('/personel')->assertOk()->assertDontSee('Kerem Aday');
        $this->actingAs($admin)->post('/secme/aday/'.$candidate->id.'/ise-al', [
            'position_id' => $position->id,
        ])->assertRedirect(route('personnel.show', $candidate));

        $this->assertFalse($candidate->fresh()->is_candidate);
        $this->actingAs($admin)->get('/personel')->assertOk()->assertSee('Kerem Aday');

        $second = Employee::factory()->create(['first_name' => 'İkinci', 'last_name' => 'Aday', 'is_candidate' => true]);
        $this->actingAs($admin)->post('/secme/aday/'.$second->id.'/ise-al', [
            'position_id' => $position->id,
        ])->assertSessionHasErrors('position_id');
        $this->assertTrue($second->fresh()->is_candidate);
    }

    public function test_a_participant_solves_the_pre_and_post_tests(): void
    {
        $department = Department::factory()->create(['modules' => [AppModule::Training->value]]);
        $staff = $this->seat('Ece', 'Uzman', Position::factory()->create(), $department);
        $pre = $this->openForm('Ön test');
        $post = $this->openForm('Son test');
        $course = Course::query()->create(['name' => 'Oryantasyon', 'department_id' => $department->id]);
        $session = CourseSession::query()->create([
            'course_id' => $course->id,
            'held_on' => now()->toDateString(),
            'pre_form_id' => $pre->id,
            'post_form_id' => $post->id,
        ]);
        Enrollment::query()->create(['course_session_id' => $session->id, 'employee_id' => $staff->id]);

        $this->actingAs($staff->user)->post('/formlar/'.$pre->public_id, [
            'answers' => [$pre->sections->first()->questions->first()->id => 'Önce'],
        ])->assertRedirect();
        $this->actingAs($staff->user)->post('/formlar/'.$post->public_id, [
            'answers' => [$post->sections->first()->questions->first()->id => 'Sonra'],
        ])->assertRedirect();

        $this->assertSame(2, $staff->user->employee->fresh()->id > 0 ? FormSubmission::query()->where('employee_id', $staff->id)->count() : 0);
        $this->actingAs($staff->user)->get('/egitim')->assertOk()->assertSee('Ön test')->assertSee('Son test');
    }

    public function test_a_manager_approves_a_review_and_it_lands_on_the_report(): void
    {
        $department = Department::factory()->create(['modules' => [AppModule::Competency->value, AppModule::Report->value]]);
        $root = Position::factory()->create(['department_id' => $department->id, 'collar' => 'white']);
        $child = Position::factory()->create(['parent_id' => $root->id, 'department_id' => $department->id, 'collar' => 'blue']);
        $boss = $this->seat('Ali', 'Müdür', $root, $department);
        $staff = $this->seat('Ece', 'Uzman', $child, $department);

        $this->actingAs($boss->user)->post('/performans', [
            'employee_id' => $staff->id,
            'period' => '2026-10',
            'body' => 'İşini bitirdi',
        ])->assertRedirect();

        $review = Review::query()->firstOrFail();
        $this->assertSame('blue', $review->collar);
        $this->actingAs($staff->user)->post('/performans/'.$review->id.'/onay')->assertForbidden();
        $this->actingAs($boss->user)->post('/performans/'.$review->id.'/onay')->assertRedirect();
        $this->actingAs($boss->user)->get('/rapor')->assertOk()->assertSee('Performans değerlendirme')->assertSee('1');
    }

    public function test_hourly_leave_drops_only_the_hour_balance_after_approval_and_payroll_stays_downward(): void
    {
        $department = Department::factory()->create();
        $root = Position::factory()->create(['department_id' => $department->id]);
        $child = Position::factory()->create(['parent_id' => $root->id, 'department_id' => $department->id]);
        $peerSeat = Position::factory()->create(['parent_id' => $root->id, 'department_id' => $department->id]);
        $boss = $this->seat('Ali', 'Müdür', $root, $department);
        $staff = $this->seat('Ece', 'Uzman', $child, $department);
        $peer = $this->seat('Can', 'Satış', $peerSeat, $department);
        $staff->update(['pay_type' => PayType::Hourly, 'pay_amount' => 100]);
        $boss->update(['pay_type' => PayType::Monthly, 'pay_amount' => 99999]);
        $type = Lookup::query()->create(['kind' => 'leave_type', 'name' => 'Mazeret', 'meta' => ['unit' => 'both']]);
        LeaveBalance::query()->create(['employee_id' => $staff->id, 'lookup_id' => $type->id, 'unit' => 'hour', 'amount' => 8]);
        LeaveBalance::query()->create(['employee_id' => $staff->id, 'lookup_id' => $type->id, 'unit' => 'day', 'amount' => 10]);

        $this->actingAs($staff->user)->post('/izin', [
            'lookup_id' => $type->id,
            'mode' => 'hour',
            'starts_on' => '2026-10-06',
            'starts_at' => '09:00',
            'ends_at' => '11:00',
        ])->assertRedirect();

        $leave = LeaveRequest::query()->firstOrFail();
        $this->assertSame('2.00', $leave->hours);
        $this->actingAs($peer->user)->put('/izin/'.$leave->id, ['status' => 'approved'])->assertForbidden();
        $this->actingAs($boss->user)->put('/izin/'.$leave->id, ['status' => 'approved'])->assertRedirect();
        $this->assertSame('6.00', LeaveBalance::query()->where('unit', 'hour')->first()->amount);
        $this->assertSame('10.00', LeaveBalance::query()->where('unit', 'day')->first()->amount);

        $this->actingAs($boss->user)->post('/bordro', [
            'employee_id' => $staff->id,
            'year' => 2026,
            'month' => 10,
            'hours' => 3,
            'deduction' => 10,
        ])->assertRedirect();
        $this->actingAs($staff->user)->post('/bordro', [
            'employee_id' => $staff->id,
            'year' => 2026,
            'month' => 11,
            'hours' => 1,
        ])->assertForbidden();

        $slip = Payslip::query()->first();
        $this->assertSame('300.00', $slip->gross);
        $this->assertSame('290.00', $slip->net);
        Payslip::query()->create([
            'employee_id' => $boss->id,
            'year' => 2026,
            'month' => 9,
            'gross' => 99999,
            'deduction' => 0,
            'net' => 99999,
        ]);

        $this->actingAs($staff->user)->get('/bordro')->assertOk()->assertSee('290.00')->assertDontSee('99999');
        $this->actingAs($boss->user)->get('/bordro')->assertOk()->assertSee('99999');
    }

    public function test_chat_reaches_the_subtree_and_the_direct_manager_only(): void
    {
        $department = Department::factory()->create(['modules' => [AppModule::Report->value]]);
        $root = Position::factory()->create(['department_id' => $department->id]);
        $child = Position::factory()->create(['parent_id' => $root->id, 'department_id' => $department->id]);
        $peerSeat = Position::factory()->create(['parent_id' => $root->id, 'department_id' => $department->id]);
        $boss = $this->seat('Ali', 'Müdür', $root, $department);
        $staff = $this->seat('Ece', 'Uzman', $child, $department);
        $peer = $this->seat('Can', 'Satış', $peerSeat, $department);
        $boss->update(['pay_type' => PayType::Monthly, 'pay_amount' => 99999]);
        Payslip::query()->create([
            'employee_id' => $boss->id,
            'year' => 2026,
            'month' => 1,
            'gross' => 99999,
            'deduction' => 0,
            'net' => 99999,
        ]);

        $this->actingAs($staff->user)->post('/sohbet/'.$peer->user_id, ['body' => 'Yan dal'])->assertForbidden();
        $this->actingAs($staff->user)->post('/sohbet/'.$boss->user_id, ['body' => 'Amire'])->assertRedirect();
        $this->actingAs($boss->user)->post('/sohbet/'.$staff->user_id, ['body' => 'Asta'])->assertRedirect();
        $this->actingAs($staff->user)->get('/sohbet/'.$boss->user_id)->assertOk()->assertSee('Amire')->assertSee('Asta');
        $this->actingAs($staff->user)->get('/sohbet')->assertOk()->assertSee('Genel oda yok');
        $this->actingAs($staff->user)->get('/sohbet/999999')->assertNotFound();
        $this->actingAs($staff->user)->get('/rapor')->assertOk()->assertDontSee('99999');
    }

    public function test_inventory_and_the_personnel_file_stay_on_the_card(): void
    {
        Storage::fake('local');
        $department = Department::factory()->create();
        $root = Position::factory()->create(['department_id' => $department->id]);
        $child = Position::factory()->create(['parent_id' => $root->id, 'department_id' => $department->id]);
        $peerSeat = Position::factory()->create(['parent_id' => $root->id, 'department_id' => $department->id]);
        $boss = $this->seat('Ali', 'Müdür', $root, $department);
        $staff = $this->seat('Ece', 'Uzman', $child, $department);
        $peer = $this->seat('Can', 'Satış', $peerSeat, $department);

        $this->actingAs($peer->user)->post('/personel/'.$staff->id.'/envanter', ['name' => 'Laptop'])->assertForbidden();
        $this->actingAs($boss->user)->post('/personel/'.$staff->id.'/envanter', ['name' => 'Laptop'])->assertRedirect();
        $this->actingAs($staff->user)->post('/personel/'.$staff->id.'/evrak', [
            'file' => UploadedFile::fake()->create('kimlik.pdf', 20, 'application/pdf'),
        ])->assertRedirect();

        $document = EmployeeDocument::query()->firstOrFail();
        $this->actingAs($staff->user)->post('/personel/'.$staff->id.'/evrak/'.$document->id.'/dogrula')->assertForbidden();
        $this->actingAs($boss->user)->post('/personel/'.$staff->id.'/evrak/'.$document->id.'/dogrula')->assertRedirect();
        $this->actingAs($boss->user)->post('/personel/'.$staff->id.'/saglik', ['note' => 'Kan grubu A+'])->assertRedirect();
        $this->actingAs($boss->user)->post('/personel/'.$staff->id.'/yakin', ['name' => 'Ayşe', 'kinship' => 'Anne'])->assertRedirect();
        $this->actingAs($boss->user)->post('/personel/'.$staff->id.'/not', ['kind' => 'uyari', 'body' => 'Geç kaldı'])->assertRedirect();
        $this->actingAs($staff->user)->post('/personel/'.$staff->id.'/isg', [
            'body' => 'Tatbikat',
            'recorded_on' => '2026-10-05',
        ])->assertForbidden();
        $this->actingAs($boss->user)->post('/personel/'.$staff->id.'/isg', [
            'body' => 'Tatbikat',
            'recorded_on' => '2026-10-05',
        ])->assertRedirect();

        $this->actingAs($staff->user)->get('/personel/'.$staff->id)
            ->assertOk()
            ->assertSee('Laptop')
            ->assertSee('Doğrulandı')
            ->assertSee('Kan grubu A+')
            ->assertSee('Ayşe')
            ->assertSee('Geç kaldı')
            ->assertSee('Tatbikat');
        $this->actingAs($staff->user)->get('/personel/'.$staff->id.'/evrak/'.$document->id)->assertDownload('kimlik.pdf');
    }

    public function test_the_phone_token_opens_today_leave_and_a_document(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['email' => 'ece@firma.test', 'password' => 'gizli-sifre']);
        Employee::factory()->create(['user_id' => $user->id, 'first_name' => 'Ece', 'last_name' => 'Uzman']);

        $token = $this->postJson('/api/giris', [
            'email' => 'ece@firma.test',
            'password' => 'gizli-sifre',
        ])->assertOk()->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/bugun')->assertOk()->assertJsonPath('name', $user->name);
        $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/izin')->assertOk();
        $this->withHeader('Authorization', 'Bearer '.$token)->post('/api/evrak', [
            'file' => UploadedFile::fake()->create('kimlik.pdf', 20, 'application/pdf'),
        ])->assertCreated();
        $this->flushHeaders();
        $this->getJson('/api/bugun')->assertUnauthorized();
    }

    public function test_the_daily_command_writes_a_birthday_and_a_trial_notice(): void
    {
        $department = Department::factory()->create();
        $root = Position::factory()->create(['department_id' => $department->id]);
        $child = Position::factory()->create(['parent_id' => $root->id, 'department_id' => $department->id]);
        $boss = $this->seat('Ali', 'Müdür', $root, $department);
        $staff = $this->seat('Ece', 'Uzman', $child, $department);
        $staff->update([
            'birth_date' => now()->toDateString(),
            'started_on' => now()->subWeeks(6)->toDateString(),
        ]);

        Artisan::call('hr:gunluk');

        $this->assertDatabaseHas('notices', ['user_id' => $staff->user_id, 'body' => 'Doğum günün kutlu olsun.']);
        $this->assertDatabaseHas('notices', ['user_id' => $boss->user_id]);
    }

    private function seat(string $first, string $last, Position $position, Department $department): Employee
    {
        return Employee::factory()->create([
            'first_name' => $first,
            'last_name' => $last,
            'position_id' => $position->id,
            'user_id' => User::factory()->create([
                'name' => $first.' '.$last,
                'department_id' => $department->id,
            ])->id,
        ]);
    }

    private function openForm(string $name): Form
    {
        $form = Form::factory()->create([
            'name' => $name,
            'identity' => FormIdentity::Named,
            'status' => FormStatus::Published,
        ]);
        $section = FormSection::query()->create(['form_id' => $form->id, 'title' => 'Sorular', 'sort' => 1]);
        $section->questions()->create([
            'type' => QuestionType::ShortText,
            'prompt' => $name,
            'sort' => 1,
        ]);

        return $form->load('sections.questions');
    }
}
