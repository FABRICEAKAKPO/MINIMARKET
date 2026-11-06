<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\StockLot;
use App\Models\Unit;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'catalog.view',
            'catalog.manage',
            'inventory.view',
            'inventory.manage',
            'sales.create',
            'sales.cancel',
            'sales.discount',
            'cash.open',
            'cash.close',
            'reports.view',
            'users.manage',
            'audit.view',
            'purchasing.view',
            'purchasing.manage',
            'sales.create',
            'sales.cancel',
            'sales.discount',
            'cash.open',
            'cash.close',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'Propriétaire' => $permissions,
            'Gérant' => [
                'dashboard.view', 'catalog.view', 'catalog.manage',
                'inventory.view', 'inventory.manage', 'sales.create',
                'sales.cancel', 'sales.discount', 'cash.open', 'cash.close',
                'reports.view', 'audit.view',
                'purchasing.view', 'purchasing.manage',
            ],
            'Caissier' => [
                'dashboard.view', 'catalog.view', 'sales.create', 'cash.open',
                'cash.close',
            ],
            'Magasinier' => [
                'dashboard.view', 'catalog.view', 'inventory.view', 'inventory.manage', 'purchasing.view', 'purchasing.manage',
            ],
            'Comptable' => [
                'dashboard.view', 'reports.view', 'audit.view',
            ],
        ];

        foreach ($roles as $name => $rolePermissions) {
            $role = Role::findOrCreate($name, 'web');
            $role->syncPermissions($rolePermissions);
        }

        $owner = User::query()
            ->whereIn('email', ['proprietaire@minimarket.tg', 'admin@gmail.com'])
            ->first() ?? new User;
        $owner->forceFill([
            'name' => 'Administrateur',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin1234'),
            'email_verified_at' => now(),
        ])->save();
        $owner->syncRoles('Propriétaire');

        $this->createRoleUser('gerant@minimarket.tg', 'Gérant', 'Gérant');
        $this->createRoleUser('caissier@minimarket.tg', 'Caissier', 'Caissier');
        $this->createRoleUser('magasinier@minimarket.tg', 'Magasinier', 'Magasinier');
        $this->createRoleUser('comptable@minimarket.tg', 'Comptable', 'Comptable');

        $this->seedCatalog();
        Supplier::firstOrCreate(
            ['name' => 'Grossiste Lomé Centre'],
            [
                'contact_name' => 'Service commercial',
                'phone' => '+228 90 00 00 00',
                'address' => 'Lomé, Togo',
                'tax_number' => 'NIF-FICTIF-001',
                'is_active' => true,
            ],
        );
    }

    private function createRoleUser(string $email, string $name, string $role): void
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('ChangeMe!2026'),
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles($role);
    }

    private function seedCatalog(): void
    {
        $units = [
            ['name' => 'Pièce', 'short_name' => 'pce', 'allows_decimals' => false],
            ['name' => 'Kilogramme', 'short_name' => 'kg', 'allows_decimals' => true],
            ['name' => 'Litre', 'short_name' => 'L', 'allows_decimals' => true],
            ['name' => 'Carton', 'short_name' => 'ctn', 'allows_decimals' => false],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['short_name' => $unit['short_name']], $unit);
        }

        $categoryNames = ['Alimentation', 'Boissons', 'Hygiène et entretien'];
        foreach ($categoryNames as $categoryName) {
            Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'is_active' => true],
            );
        }

        foreach (['Fan Milk', 'Voltic', 'Mixa', 'Sans marque'] as $brandName) {
            Brand::firstOrCreate(['name' => $brandName], ['is_active' => true]);
        }

        $products = [
            ['name' => 'Riz parfumé 5 kg', 'sku' => 'RIZ-5KG', 'category' => 'Alimentation', 'brand' => 'Sans marque', 'unit' => 'kg', 'purchase' => 6500, 'selling' => 8000, 'stock' => 20, 'expiry' => true],
            ['name' => 'Huile végétale 1 L', 'sku' => 'HUILE-1L', 'category' => 'Alimentation', 'brand' => 'Sans marque', 'unit' => 'L', 'purchase' => 900, 'selling' => 1200, 'stock' => 35, 'expiry' => false],
            ['name' => 'Eau minérale 1,5 L', 'sku' => 'EAU-15L', 'category' => 'Boissons', 'brand' => 'Voltic', 'unit' => 'pce', 'purchase' => 350, 'selling' => 500, 'stock' => 60, 'expiry' => false],
            ['name' => 'Savon de toilette', 'sku' => 'SAVON-TOIL', 'category' => 'Hygiène et entretien', 'brand' => 'Mixa', 'unit' => 'pce', 'purchase' => 450, 'selling' => 600, 'stock' => 25, 'expiry' => false],
        ];

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => Category::where('name', $data['category'])->value('id'),
                    'brand_id' => Brand::where('name', $data['brand'])->value('id'),
                    'unit_id' => Unit::where('short_name', $data['unit'])->value('id'),
                    'name' => $data['name'],
                    'purchase_price_xof' => $data['purchase'],
                    'selling_price_xof' => $data['selling'],
                    'tax_rate_basis_points' => 1800,
                    'has_expiry' => $data['expiry'],
                    'low_stock_threshold' => 5,
                    'is_active' => true,
                ],
            );

            ProductBarcode::firstOrCreate(
                ['barcode' => '600'.$product->id.'0001'],
                ['product_id' => $product->id, 'is_primary' => true],
            );

            StockLot::firstOrCreate(
                ['product_id' => $product->id, 'lot_number' => 'INITIAL-'.$product->id],
                [
                    'quantity' => $data['stock'],
                    'unit_cost_xof' => $data['purchase'],
                    'expires_at' => $data['expiry'] ? now()->addMonths(12)->toDateString() : null,
                ],
            );
        }
    }
}
