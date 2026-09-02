<?php

namespace Tests\Feature;

use App\Models\Item;
use Database\Seeders\ItemCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class ItemCatalogSeederTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_catalog_is_complete_idempotent_and_uses_existing_images(): void
    {
        $this->seed(ItemCatalogSeeder::class);
        $this->seed(ItemCatalogSeeder::class);

        $this->assertSame(25, Item::count());
        $this->assertDatabaseHas('items', [
            'name' => 'Poulet rôti',
            'price' => 32,
            'image_path' => '/images/items/foods/poulet-roti.png',
            'category' => Item::CATEGORY_FOOD,
        ]);
        $this->assertDatabaseHas('items', [
            'name' => 'Mélange de légumes',
            'image_path' => '/images/items/foods/mix-legumes.png',
        ]);
        $this->assertDatabaseHas('items', [
            'name' => Item::FAMILY_PROTECTION_NAME,
            'price' => 10,
            'units_per_purchase' => 20,
            'category' => Item::CATEGORY_HYGIENE_AND_WELLBEING,
            'image_path' => '/images/items/hygiene/preservatif.png',
        ]);
        $this->assertDatabaseHas('item_effects', [
            'item_id' => Item::where('name', 'Crème pour le corps')->value('id'),
            'gauge' => 'happiness',
            'effect' => 12,
        ]);
        $this->assertDatabaseHas('items', [
            'name' => 'Cigarette',
            'category' => Item::CATEGORY_TOBACCO_AND_ALCOHOL,
            'usage_tag' => 'tobacco',
            'image_path' => '/images/items/foods/cigarette.png',
        ]);
        $this->assertDatabaseHas('items', [
            'name' => 'Whisky',
            'category' => Item::CATEGORY_TOBACCO_AND_ALCOHOL,
            'usage_tag' => 'alcohol',
            'image_path' => '/images/items/foods/whiskey.png',
        ]);
        $this->assertDatabaseMissing('items', ['name' => "Jus d'orange"]);
        $this->assertDatabaseMissing('items', ['name' => 'Brocoli']);
        $this->assertDatabaseMissing('items', ['name' => 'Carottes']);

        Item::query()->each(function (Item $item) {
            $this->assertFileExists(public_path(ltrim($item->image_path, '/')));
        });
    }

    public function test_legacy_items_are_merged_without_losing_inventory_quantities(): void
    {
        [, $lifer] = $this->createUserWithLifer();
        $broccoli = $this->legacyItem('Brocoli');
        $carrots = $this->legacyItem('Carottes');

        foreach ([$broccoli->id => 2, $carrots->id => 3] as $itemId => $quantity) {
            DB::table('inventory_items')->insert([
                'inventory_id' => $lifer->id,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->seed(ItemCatalogSeeder::class);

        $mixedVegetables = Item::where('name', 'Mélange de légumes')->firstOrFail();
        $this->assertDatabaseHas('inventory_items', [
            'inventory_id' => $lifer->id,
            'item_id' => $mixedVegetables->id,
            'quantity' => 5,
        ]);
        $this->assertDatabaseMissing('items', ['name' => 'Brocoli']);
        $this->assertDatabaseMissing('items', ['name' => 'Carottes']);
    }

    private function legacyItem(string $name): Item
    {
        return Item::create([
            'name' => $name,
            'price' => 1,
            'description' => 'Ancien article à fusionner.',
            'category' => Item::CATEGORY_FOOD,
        ]);
    }
}
