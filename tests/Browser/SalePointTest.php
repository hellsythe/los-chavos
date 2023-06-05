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
                    ->select('@service0.service', 1)
                    ->screenshot('servicio')
                    ->click('@service-next')
                    // ->click('@service-new-service')
                    ->assertSee('Laravel');
        });
    }
}
