<?php

namespace Tests\Feature\Storefront;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'shipping',
            'name' => 'Jane Doe',
            'phone' => '9876543210',
            'address_line_1' => '221B Baker Street',
            'address_line_2' => 'Near Central Park',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'country' => 'India',
            'pincode' => '110001',
            'is_default' => '1',
        ], $overrides);
    }

    public function test_guest_cannot_view_the_address_page(): void
    {
        $this->get('/account-setting')->assertRedirect(route('sign-in'));
    }

    public function test_guest_cannot_create_an_address(): void
    {
        $response = $this->post('/addresses', $this->validPayload());

        $response->assertRedirect(route('sign-in'));
        $this->assertDatabaseCount('addresses', 0);
    }

    public function test_authenticated_customer_can_create_an_address(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/addresses', $this->validPayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'name' => 'Jane Doe',
            'city' => 'Delhi',
            'pincode' => '110001',
            'is_default' => true,
        ]);
    }

    public function test_missing_required_fields_are_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/addresses', $this->validPayload(['name' => '', 'pincode' => '']));

        $response->assertSessionHasErrors(['name', 'pincode']);
        $this->assertDatabaseCount('addresses', 0);
    }

    public function test_setting_a_new_address_as_default_unsets_the_previous_default(): void
    {
        $user = User::factory()->create();
        $first = Address::create(array_merge(['user_id' => $user->id], $this->validPayload(['is_default' => true])));

        $this->actingAs($user)->post('/addresses', $this->validPayload(['name' => 'Second Address', 'is_default' => '1']));

        $this->assertDatabaseHas('addresses', ['id' => $first->id, 'is_default' => false]);
        $this->assertDatabaseHas('addresses', ['user_id' => $user->id, 'name' => 'Second Address', 'is_default' => true]);
    }

    public function test_customer_can_update_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::create(array_merge(['user_id' => $user->id], $this->validPayload()));

        $response = $this->actingAs($user)->put('/addresses/'.$address->id, $this->validPayload(['city' => 'Mumbai']));

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', ['id' => $address->id, 'city' => 'Mumbai']);
    }

    public function test_customer_cannot_update_another_customers_address(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $address = Address::create(array_merge(['user_id' => $owner->id], $this->validPayload()));

        $response = $this->actingAs($attacker)->put('/addresses/'.$address->id, $this->validPayload(['city' => 'Hacked']));

        $response->assertNotFound();
        $this->assertDatabaseHas('addresses', ['id' => $address->id, 'city' => 'Delhi']);
    }

    public function test_customer_can_delete_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::create(array_merge(['user_id' => $user->id], $this->validPayload()));

        $response = $this->actingAs($user)->delete('/addresses/'.$address->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_customer_cannot_delete_another_customers_address(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $address = Address::create(array_merge(['user_id' => $owner->id], $this->validPayload()));

        $response = $this->actingAs($attacker)->delete('/addresses/'.$address->id);

        $response->assertNotFound();
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    public function test_account_setting_page_lists_the_customers_own_addresses(): void
    {
        $user = User::factory()->create();
        Address::create(array_merge(['user_id' => $user->id], $this->validPayload(['name' => 'My Home'])));
        $other = User::factory()->create();
        Address::create(array_merge(['user_id' => $other->id], $this->validPayload(['name' => 'Someone Elses Home'])));

        $response = $this->actingAs($user)->get('/account-setting');

        $response->assertOk();
        $response->assertSee('My Home');
        $response->assertDontSee('Someone Elses Home');
    }
}
