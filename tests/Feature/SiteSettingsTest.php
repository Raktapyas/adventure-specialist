<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/manage-site-settings')->assertRedirect('/admin/login');
    }

    public function test_settings_page_renders_for_master_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/manage-site-settings')
            ->assertOk()
            ->assertSee('Homepage Stats Strip')
            ->assertSee('CTA Strip')
            ->assertSee('Footer Contact Block');
    }

    public function test_migration_seeds_current_live_values(): void
    {
        $settings = SiteSetting::current();

        $this->assertSame('Let us arrange your Himalayan holiday.', $settings->cta_title);
        $this->assertSame('ADVENTURE SPECIALIST TRAVEL', $settings->contact_company);
        $this->assertCount(3, $settings->statsRows());
        $this->assertSame(2013, $settings->statsRows()[0]['value']);
    }

    public function test_saving_settings_updates_homepage_and_footer(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'cta_eyebrow' => 'Start today',
                'cta_title' => 'Your Himalayan story begins here',
                'cta_button_label' => 'Get in touch',
                'cta_button_url' => '/contact/',
                'contact_email' => 'hello@ast.com.np',
                'stats' => [
                    ['value' => 1999, 'suffix' => '', 'label' => 'Serving Since'],
                    ['value' => 500, 'suffix' => '+', 'label' => 'Happy Trekkers'],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $home = $this->get('/');
        $home->assertOk()
            ->assertSee('Your Himalayan story begins here')
            ->assertSee('Start today')
            ->assertSee('Get in touch')
            ->assertSee('Serving Since')
            ->assertDontSee('Established Year');

        // Footer renders site-wide; asserted on "/" because /gallery has a
        // canonical trailing-slash redirect that loops in the test client.
        $this->get('/')
            ->assertOk()
            ->assertSee('hello@ast.com.np');
    }

    public function test_empty_cta_fields_are_hidden_on_homepage(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(ManageSiteSettings::class)
            ->fillForm([
                'cta_eyebrow' => '',
                'cta_title' => '',
                'cta_button_label' => '',
                'cta_button_url' => '',
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Ready when you are')
            ->assertDontSee('Let us arrange your Himalayan holiday.');
    }
}
