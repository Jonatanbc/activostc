<?php

namespace Tests\Feature\Damages;

use App\Models\Asset;
use App\Models\AssetDamage;
use App\Models\DamageType;
use App\Models\PurchaseRequest;
use App\Models\User;
use Tests\TestCase;

class DamagesModuleTest extends TestCase
{
    public function test_permission_required_to_view_damages_module()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('damages.list'))
            ->assertForbidden();
    }

    public function test_damages_module_page_renders_with_cards()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('damages.list'))
            ->assertOk()
            ->assertSeeText(trans('admin/damages/general.card_damaged_components'))
            ->assertSeeText(trans('admin/damages/general.generate_purchase_request'));
    }

    public function test_generating_a_purchase_request_groups_selected_damages()
    {
        $asset = Asset::factory()->create();
        $type = DamageType::create(['name' => 'Pantalla', 'default_cost' => 100]);

        $a = AssetDamage::create(['asset_id' => $asset->id, 'damage_type_id' => $type->id, 'quantity' => 1, 'status' => 'reported']);
        $b = AssetDamage::create(['asset_id' => $asset->id, 'damage_type_id' => $type->id, 'quantity' => 2, 'status' => 'quoted']);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('purchase-requests.store'), ['ids' => [$a->id, $b->id]])
            ->assertRedirect();

        $pr = PurchaseRequest::firstOrFail();

        $this->assertEquals($pr->id, $a->fresh()->purchase_request_id);
        $this->assertEquals($pr->id, $b->fresh()->purchase_request_id);
        $this->assertEquals('purchase_request', $a->fresh()->status);
        $this->assertEquals(2, $pr->damages()->count());
        $this->assertEquals(3, $pr->components_count);
    }

    public function test_purchase_requests_index_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('purchase-requests.index'))
            ->assertOk()
            ->assertSeeText(trans('admin/damages/general.purchase_requests'));
    }

    public function test_generating_quotation_marks_open_request_as_quoted()
    {
        $pr = PurchaseRequest::create(['status' => 'open']);

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('purchase-requests.print', $pr))
            ->assertOk();

        $this->assertEquals('quoted', $pr->fresh()->status);
    }

    public function test_generating_quotation_does_not_downgrade_received_request()
    {
        $pr = PurchaseRequest::create(['status' => 'received']);

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('purchase-requests.print', $pr))
            ->assertOk();

        $this->assertEquals('received', $pr->fresh()->status);
    }

    public function test_removing_an_item_releases_the_damage()
    {
        $asset = Asset::factory()->create();
        $type = DamageType::create(['name' => 'Teclado', 'default_cost' => 50]);
        $pr = PurchaseRequest::create(['status' => 'open']);
        $damage = AssetDamage::create([
            'asset_id' => $asset->id, 'damage_type_id' => $type->id, 'quantity' => 1,
            'status' => 'purchase_request', 'purchase_request_id' => $pr->id,
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->delete(route('purchase-requests.items.remove', [$pr, $damage]))
            ->assertRedirect(route('purchase-requests.show', $pr));

        $this->assertNull($damage->fresh()->purchase_request_id);
    }

    public function test_deleting_a_request_releases_its_damages()
    {
        $asset = Asset::factory()->create();
        $type = DamageType::create(['name' => 'Batería', 'default_cost' => 70]);
        $pr = PurchaseRequest::create(['status' => 'open']);
        $damage = AssetDamage::create([
            'asset_id' => $asset->id, 'damage_type_id' => $type->id, 'quantity' => 1,
            'status' => 'purchase_request', 'purchase_request_id' => $pr->id,
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->delete(route('purchase-requests.destroy', $pr))
            ->assertRedirect(route('purchase-requests.index'));

        $this->assertNull($damage->fresh()->purchase_request_id);
        $this->assertSoftDeleted($pr);
    }
}
