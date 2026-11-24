<?php
namespace Tests\Feature;

use App\Support\Brand;

class BrandTest extends AfrenTestCase
{
    public function test_defaults_are_dark_blue_and_orange_and_reach_the_page(): void
    {
        $this->get('/')->assertOk()->assertSee('#12355b', false)->assertSee('#bf5410', false);
    }

    public function test_admin_can_change_brand_colours_and_the_site_and_illustrations_follow(): void
    {
        $this->actingAs($this->staff('admin'))->put('/admin/settings/brand', ['primary' => '#0b3d2e', 'accent' => '#c2410c'])->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/')->assertSee('#0b3d2e', false)->assertSee('#c2410c', false)->assertDontSee('#12355b', false);
        $svg = $this->get(Brand::svg('hero'))->assertOk();
        $this->assertStringContainsString('#0b3d2e', $svg->getContent());
        $this->assertStringNotContainsString('__P700__', $svg->getContent());
        $this->assertSame('image/svg+xml', $svg->headers->get('Content-Type'));
        $this->get('/brand/nope.svg')->assertNotFound();
    }

    public function test_invalid_or_unreadable_colours_are_rejected(): void
    {
        $admin = $this->actingAs($this->staff('admin'));
        $admin->put('/admin/settings/brand', ['primary' => 'blue', 'accent' => '#bf5410'])->assertSessionHasErrors('primary');
        $admin->put('/admin/settings/brand', ['primary' => '#a0c4ff', 'accent' => '#bf5410'])->assertSessionHasErrors('primary'); // too light for white text
        $admin->put('/admin/settings/brand', ['primary' => '#12355b', 'accent' => '#ffe08a'])->assertSessionHasErrors('accent');
        $this->get('/')->assertSee('#12355b', false);
    }

    public function test_reset_restores_defaults_and_only_admins_can_change(): void
    {
        $this->actingAs($this->staff('admin'))->put('/admin/settings/brand', ['primary' => '#0b3d2e', 'accent' => '#c2410c']);
        $this->post('/admin/settings/brand/reset')->assertRedirect();
        $this->get('/')->assertSee('#12355b', false);
        $this->actingAs($this->staff('recruiter'))->put('/admin/settings/brand', ['primary' => '#000000', 'accent' => '#000000'])->assertForbidden();
        $this->actingAs($this->staff('admin'))->get('/admin/settings')->assertOk()->assertSee('Brand colours');
    }

    public function test_top_bar_shows_configured_contact_details_and_socials_only(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Social media', false)->assertDontSee('tel:', false)->assertDontSee('Facebook'); // nothing configured yet
        $this->actingAs($this->staff('admin'))->put('/admin/settings/site', [
            'phone' => '+254 700 111 222', 'email' => 'hello@afren.africa', 'whatsapp' => '+254700111222',
            'facebook' => 'https://facebook.com/afren', 'linkedin' => 'https://linkedin.com/company/afren',
        ])->assertSessionHasNoErrors();
        auth()->logout();
        $this->get('/')->assertSee('tel:+254700111222', false)->assertSee('hello@afren.africa')->assertSee('https://wa.me/254700111222', false)
            ->assertSee('https://facebook.com/afren', false)->assertSee('Social media', false)->assertSee('aria-label="LinkedIn"', false)->assertDontSee('aria-label="Instagram"', false);
        $this->get('/contact')->assertSee('+254 700 111 222');
    }

    public function test_site_settings_are_validated_and_admin_only(): void
    {
        $admin = $this->actingAs($this->staff('admin'));
        $admin->put('/admin/settings/site', ['phone' => 'call me', 'facebook' => 'not-a-url'])->assertSessionHasErrors(['phone', 'facebook']);
        $admin->put('/admin/settings/site', ['facebook' => 'javascript:alert(1)'])->assertSessionHasErrors('facebook');
        $this->actingAs($this->staff('finance'))->put('/admin/settings/site', ['phone' => '+254700111222'])->assertForbidden();
    }

    public function test_views_use_no_coloured_decorative_borders(): void
    {
        $bad = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $f) {
            if (! str_ends_with($f, '.blade.php')) continue;
            if (preg_match('/(?<![:\w-])(border-[tlrb]-4|border(-[tlrb])?-(brand|accent)-\d)/', file_get_contents($f), $m)) { $bad[] = basename($f).': '.$m[0]; }
        }
        $this->assertSame([], $bad);
    }
}
