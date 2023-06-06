<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SalePointTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    public function testCreateEmbroderyNew(): void
    {
        $user = User::factory()->make();

        $this->browse(function (Browser $browser) use ($user){
            $browser->loginAs(User::find(1))
                    ->visit('/admin/sale-point')
                    ->type('@order-deadline', date('d-m-Y'))
                    ->type('@client-name', $user->name)
                    ->type('@client-phone', $user->phone)
                    ->type('@client-email', $user->email)
                    ->screenshot('cliente')
                    ->click('@client-next')
                    ->keys('@service0-service', ['b','{enter}'])
                    ->keys('@service0-subservice', ['{ARROW_DOWN}','{enter}'])
                    ->type('@service0-price', 200)
                    ->with('@service0-design', function (Browser $table) {
                        $table->keys('input', ['esc'])
                        ->pause(500)
                        ->keys('input', ['{enter}']);
                    })
                    ->with('@service0-garment', function (Browser $table) {
                        $table->keys('input', ['cami'])
                        ->pause(500)
                        ->keys('input', ['{enter}']);
                    })
                    ->type('@service0-garment-amount', 10)
                    ->screenshot('servicio')
                    ->click('@service-next')
                    ->assertSee('Laravel');
        });
    }
}
