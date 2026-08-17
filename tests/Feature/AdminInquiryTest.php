<?php

namespace Tests\Feature;

use App\Filament\Resources\InquiryResource;
use App\Filament\Resources\InquiryResource\Pages\EditInquiry;
use App\Filament\Resources\InquiryResource\Pages\ListInquiries;
use App\Filament\Resources\InquiryResource\Pages\ViewInquiry;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminInquiryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/inquiries')->assertRedirect('/admin/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/inquiries')
            ->assertForbidden();
    }

    public function test_admin_can_list_inquiries(): void
    {
        Inquiry::factory()->create(['name' => 'Jane Traveller']);

        Livewire::actingAs($this->admin())
            ->test(ListInquiries::class)
            ->assertSee('Jane Traveller');
    }

    public function test_admin_can_view_an_inquiry(): void
    {
        $inquiry = Inquiry::factory()->create(['message' => 'Please send details.']);

        Livewire::actingAs($this->admin())
            ->test(ViewInquiry::class, ['record' => $inquiry->getKey()])
            ->assertFormSet(['message' => 'Please send details.']);
    }

    public function test_admin_can_edit_an_inquiry(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'new']);

        Livewire::actingAs($this->admin())
            ->test(EditInquiry::class, ['record' => $inquiry->getKey()])
            ->fillForm([
                'name' => $inquiry->name,
                'email' => $inquiry->email,
                'message' => $inquiry->message,
                'status' => 'resolved',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(InquiryResource::getUrl('index'));

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => 'resolved']);
    }

    public function test_admin_can_delete_an_inquiry(): void
    {
        $inquiry = Inquiry::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(EditInquiry::class, ['record' => $inquiry->getKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
    }
}
