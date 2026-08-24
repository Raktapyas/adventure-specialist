<?php

namespace Tests\Feature;

use App\Filament\Resources\HeroSlideResource\Pages\CreateHeroSlide;
use App\Filament\Resources\HeroSlideResource\Pages\EditHeroSlide;
use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminHeroSlideCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['id' => 1, 'is_admin' => true]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/hero-slides')->assertRedirect('/admin/login');
        $this->get('/admin/hero-slides/create')->assertRedirect('/admin/login');
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/hero-slides')->assertForbidden();
        $this->actingAs($user)->get('/admin/hero-slides/create')->assertForbidden();
    }

    public function test_admin_can_list_hero_slides(): void
    {
        HeroSlide::factory()->create(['title' => 'The Himalayas, thoughtfully arranged.']);

        $this->actingAs($this->admin())
            ->get('/admin/hero-slides')
            ->assertOk()
            ->assertSee('The Himalayas, thoughtfully arranged.');
    }

    public function test_admin_can_create_a_hero_slide(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateHeroSlide::class)
            ->fillForm([
                'image_path' => '/assets/images/banners/1.jpg',
                'eyebrow' => 'Test eyebrow',
                'title' => 'A brand new slide',
                'lede' => 'A fresh lede.',
                'primary_cta_label' => 'Explore',
                'primary_cta_url' => '/ast-services/',
                'sort_order' => 9,
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoErrors();

        $slide = HeroSlide::where('title', 'A brand new slide')->first();
        $this->assertNotNull($slide);
        $this->assertSame('/assets/images/banners/1.jpg', $slide->image_path);
        $this->assertTrue($slide->is_published);
    }

    public function test_admin_can_edit_a_hero_slide(): void
    {
        $slide = HeroSlide::factory()->create(['title' => 'Original title']);
        $this->actingAs($this->admin());

        Livewire::test(EditHeroSlide::class, ['record' => $slide->getKey()])
            ->fillForm(['title' => 'Updated title'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Updated title', $slide->fresh()->title);
    }

    public function test_creation_requires_title_and_image(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateHeroSlide::class)
            ->fillForm(['title' => null, 'image_path' => null])
            ->call('create')
            ->assertHasFormErrors(['title', 'image_path']);
    }

    public function test_homepage_renders_published_slides_in_order(): void
    {
        HeroSlide::factory()->create(['title' => 'Second slide', 'sort_order' => 2]);
        HeroSlide::factory()->create(['title' => 'First slide', 'sort_order' => 1]);

        $home = $this->get('/');
        $home->assertOk()
            ->assertSee('First slide')
            ->assertSee('Second slide');

        $firstPos = mb_strpos($home->getContent(), 'First slide');
        $secondPos = mb_strpos($home->getContent(), 'Second slide');
        $this->assertLessThan($secondPos, $firstPos);
    }

    public function test_homepage_hides_unpublished_slides(): void
    {
        HeroSlide::factory()->create(['title' => 'Hidden slide', 'is_published' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Hidden slide');
    }
}
