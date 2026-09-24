<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductInventory;
use App\Models\RawMaterial;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $rawMaterials = [
            ['name' => 'Tinta Plastisol Hitam', 'category' => 'Tinta', 'unit' => 'Kg', 'stock' => 12.5, 'min_stock_alert' => 5],
            ['name' => 'Tinta Plastisol Putih', 'category' => 'Tinta', 'unit' => 'Kg', 'stock' => 15.0, 'min_stock_alert' => 5],
            ['name' => 'Tinta Rubber Transparan', 'category' => 'Tinta', 'unit' => 'Kg', 'stock' => 8.0, 'min_stock_alert' => 3],
            ['name' => 'Cairan Emulsi (Obat Afdruk)', 'category' => 'Chemical', 'unit' => 'Liter', 'stock' => 2.5, 'min_stock_alert' => 1],
            ['name' => 'Cairan M3 (Pembersih Screen)', 'category' => 'Chemical', 'unit' => 'Liter', 'stock' => 5.0, 'min_stock_alert' => 2],
            ['name' => 'Lakban Bening 2 Inch', 'category' => 'Packaging', 'unit' => 'Roll', 'stock' => 24, 'min_stock_alert' => 10],
            ['name' => 'Plastik Packing Zip Lock', 'category' => 'Packaging', 'unit' => 'Pack', 'stock' => 50, 'min_stock_alert' => 15],
            ['name' => 'Benang Jahit Hitam', 'category' => 'Alat Jahit', 'unit' => 'Cone', 'stock' => 30, 'min_stock_alert' => 10],
        ];

        foreach ($rawMaterials as $rm) {
            RawMaterial::create($rm);
        }


        $catPakaian = Category::create([
            'name' => 'Pakaian',
            'slug' => 'pakaian',
            'icon' => 'checkroom',
            'description' => 'Kaos, Kemeja, dan Jaket'
        ]);

        // ─── KAOS PREMIUM ───────────────────────────────────────────
        $kaos = Product::create([
            'category_id' => $catPakaian->id,
            'name' => 'Kaos Premium',
            'slug' => 'kaos-premium',
            'icon' => 'apparel',
            'description' => 'Kaos custom dengan berbagai pilihan lengan dan ukuran.',
            'base_price' => 65000,
        ]);
        $attrBahanKaos = ProductAttribute::create(['product_id' => $kaos->id, 'name' => 'Bahan', 'input_type' => 'radio']);
        ProductAttributeValue::create(['product_attribute_id' => $attrBahanKaos->id, 'value' => 'Cotton Combed 30s']);
        ProductAttributeValue::create(['product_attribute_id' => $attrBahanKaos->id, 'value' => 'Cotton Combed 24s', 'price_adjustment' => 10000]);

        $attrLenganKaos = ProductAttribute::create(['product_id' => $kaos->id, 'name' => 'Jenis Lengan', 'input_type' => 'radio']);
        ProductAttributeValue::create(['product_attribute_id' => $attrLenganKaos->id, 'value' => 'Lengan Pendek']);
        ProductAttributeValue::create(['product_attribute_id' => $attrLenganKaos->id, 'value' => 'Lengan Panjang', 'price_adjustment' => 5000]);

        $attrSizeKaos = ProductAttribute::create(['product_id' => $kaos->id, 'name' => 'Ukuran', 'input_type' => 'number_group']);
        foreach (['S', 'M', 'L', 'XL', 'XXL'] as $s) {
            ProductAttributeValue::create(['product_attribute_id' => $attrSizeKaos->id, 'value' => $s]);
        }

        // ─── KEMEJA CUSTOM ──────────────────────────────────────────
        $kemeja = Product::create([
            'category_id' => $catPakaian->id,
            'name' => 'Kemeja Custom',
            'slug' => 'kemeja-custom',
            'icon' => 'dry_cleaning',
            'description' => 'Kemeja kustom untuk seragam kerja atau organisasi.',
            'base_price' => 95000,
        ]);
        $attrBahanKemeja = ProductAttribute::create(['product_id' => $kemeja->id, 'name' => 'Bahan', 'input_type' => 'radio']);
        ProductAttributeValue::create(['product_attribute_id' => $attrBahanKemeja->id, 'value' => 'American Drill']);
        ProductAttributeValue::create(['product_attribute_id' => $attrBahanKemeja->id, 'value' => 'Nagata Drill', 'price_adjustment' => 15000]);

        $attrLenganKemeja = ProductAttribute::create(['product_id' => $kemeja->id, 'name' => 'Jenis Lengan', 'input_type' => 'radio']);
        ProductAttributeValue::create(['product_attribute_id' => $attrLenganKemeja->id, 'value' => 'Lengan Pendek']);
        ProductAttributeValue::create(['product_attribute_id' => $attrLenganKemeja->id, 'value' => 'Lengan Panjang', 'price_adjustment' => 10000]);

        $attrSizeKemeja = ProductAttribute::create(['product_id' => $kemeja->id, 'name' => 'Ukuran', 'input_type' => 'number_group']);
        foreach (['S', 'M', 'L', 'XL', 'XXL'] as $s) {
            ProductAttributeValue::create(['product_attribute_id' => $attrSizeKemeja->id, 'value' => $s]);
        }

        // ─── SERAGAM PDL ────────────────────────────────────────────
        $pdl = Product::create([
            'category_id' => $catPakaian->id,
            'name' => 'Seragam PDL',
            'slug' => 'seragam-pdl',
            'icon' => 'engineering',
            'description' => 'Pakaian Dinas Lapangan yang tangguh.',
            'base_price' => 120000,
        ]);
        $attrBahanPdl = ProductAttribute::create(['product_id' => $pdl->id, 'name' => 'Bahan', 'input_type' => 'radio']);
        ProductAttributeValue::create(['product_attribute_id' => $attrBahanPdl->id, 'value' => 'Ripstop']);
        ProductAttributeValue::create(['product_attribute_id' => $attrBahanPdl->id, 'value' => 'Nagata Drill']);

        $attrAksesorisPdl = ProductAttribute::create(['product_id' => $pdl->id, 'name' => 'Aksesoris', 'input_type' => 'radio']);
        ProductAttributeValue::create(['product_attribute_id' => $attrAksesorisPdl->id, 'value' => 'Standar']);
        ProductAttributeValue::create(['product_attribute_id' => $attrAksesorisPdl->id, 'value' => 'Pita Reflektor', 'price_adjustment' => 10000]);

        $attrSizePdl = ProductAttribute::create(['product_id' => $pdl->id, 'name' => 'Ukuran', 'input_type' => 'number_group']);
        foreach (['M', 'L', 'XL', 'XXL'] as $s) {
            ProductAttributeValue::create(['product_attribute_id' => $attrSizePdl->id, 'value' => $s]);
        }

        // ─── BAJU POLO ──────────────────────────────────────────────
        $polo = Product::create([
            'category_id' => $catPakaian->id,
            'name' => 'Baju Polo',
            'slug' => 'baju-polo',
            'icon' => 'styler',
            'description' => 'Polo shirt elegan dengan bahan berpori.',
            'base_price' => 85000, 
        ]);
        $attrBahanPolo = ProductAttribute::create(['product_id' => $polo->id, 'name' => 'Bahan', 'input_type' => 'radio']);
        ProductAttributeValue::create(['product_attribute_id' => $attrBahanPolo->id, 'value' => 'Lacoste PE']);
        ProductAttributeValue::create(['product_attribute_id' => $attrBahanPolo->id, 'value' => 'Lacoste CVC', 'price_adjustment' => 10000]);

        $attrLenganPolo = ProductAttribute::create(['product_id' => $polo->id, 'name' => 'Jenis Lengan', 'input_type' => 'radio']);
        ProductAttributeValue::create(['product_attribute_id' => $attrLenganPolo->id, 'value' => 'Lengan Pendek']);

        $attrSizePolo = ProductAttribute::create(['product_id' => $polo->id, 'name' => 'Ukuran', 'input_type' => 'number_group']);
        foreach (['S', 'M', 'L', 'XL'] as $s) {
            ProductAttributeValue::create(['product_attribute_id' => $attrSizePolo->id, 'value' => $s]);
        }

        // ─── JERSEY PRINTING ────────────────────────────────────────
        $jersey = Product::create([
            'category_id' => $catPakaian->id,
            'name' => 'Jersey Printing',
            'slug' => 'jersey-printing',
            'icon' => 'sports_gymnastics',
            'description' => 'Jersey olahraga full printing sublimasi.',
            'base_price' => 110000, 
        ]);
        $attrBahanJersey = ProductAttribute::create(['product_id' => $jersey->id, 'name' => 'Bahan', 'input_type' => 'radio']);
        ProductAttributeValue::create(['product_attribute_id' => $attrBahanJersey->id, 'value' => 'Dryfit Milano']);
        ProductAttributeValue::create(['product_attribute_id' => $attrBahanJersey->id, 'value' => 'Dryfit Brazil']);

        // ─── KATEGORI AKSESORIS ─────────────────────────────────────
        $catAksesoris = Category::create([
            'name' => 'Aksesoris',
            'slug' => 'aksesoris',
            'icon' => 'category',
            'description' => 'Bendera, Topi, dan Stiker'
        ]);

        $bendera = Product::create([
            'category_id' => $catAksesoris->id,
            'name' => 'Bendera / Banner',
            'slug' => 'bendera-banner',
            'icon' => 'flag',
            'description' => 'Cetak bendera komunitas atau instansi.',
            'base_price' => 50000, 
        ]);

        $topi = Product::create([
            'category_id' => $catAksesoris->id,
            'name' => 'Topi Custom',
            'slug' => 'topi-custom',
            'icon' => 'sports_baseball',
            'description' => 'Topi dengan bordir atau sablon custom.',
            'base_price' => 55000, 
        ]);

        $stiker = Product::create([
            'category_id' => $catAksesoris->id,
            'name' => 'Stiker Merchandise',
            'slug' => 'stiker-merchandise',
            'icon' => 'sticky_note_2',
            'description' => 'Cetak stiker berkualitas tinggi.',
            'base_price' => 5000,
        ]);

        // ==========================================
        // 3. INVENTORY FISIK (BARANG GUDANG)
        // ==========================================

        // ── Inventory Kaos ───────────────────────────────────────
        $kaosBasePrice = 75000; // Rp 75.000 kaos S/M/L
        $kaosSizeExtra = ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 5000];
        $colors = ['Hitam', 'Putih', 'Navy'];
        $sizes = ['S', 'M', 'L', 'XL'];
        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                ProductInventory::create([
                    'product_id'   => $kaos->id,
                    'sku'          => 'KOS-' . strtoupper(substr($color, 0, 3)) . '-' . $size . '-P',
                    'variant_name' => "Kaos Combed 30s Pendek - $color - $size",
                    'stock'        => rand(15, 150),
                    'harga_jual'   => $kaosBasePrice + ($kaosSizeExtra[$size] ?? 0),
                    'image_path'   => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&q=80',
                ]);

                ProductInventory::create([
                    'product_id'   => $kaos->id,
                    'sku'          => 'KOS-' . strtoupper(substr($color, 0, 3)) . '-' . $size . '-PJ',
                    'variant_name' => "Kaos Combed 30s Panjang - $color - $size",
                    'stock'        => rand(5, 80),
                    'harga_jual'   => $kaosBasePrice + ($kaosSizeExtra[$size] ?? 0) + 5000,
                    'image_path'   => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&q=80',
                ]);
            }
        }

        // ── Inventory Kemeja ─────────────────────────────────────
        $kemejaBP = 110000;
        $kemejaSizeEx = ['M' => 0, 'L' => 0, 'XL' => 5000];
        foreach (['Khaki', 'Hitam', 'Army'] as $color) {
            foreach (['M', 'L', 'XL'] as $size) {
                ProductInventory::create([
                    'product_id'   => $kemeja->id,
                    'sku'          => 'KMJ-' . strtoupper(substr($color, 0, 3)) . '-' . $size . '-P',
                    'variant_name' => "Kemeja Am. Drill Pendek - $color - $size",
                    'stock'        => rand(5, 50),
                    'harga_jual'   => $kemejaBP + ($kemejaSizeEx[$size] ?? 0),
                    'image_path'   => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=400&q=80',
                ]);

                ProductInventory::create([
                    'product_id'   => $kemeja->id,
                    'sku'          => 'KMJ-' . strtoupper(substr($color, 0, 3)) . '-' . $size . '-PJ',
                    'variant_name' => "Kemeja Am. Drill Panjang - $color - $size",
                    'stock'        => rand(5, 30),
                    'harga_jual'   => $kemejaBP + ($kemejaSizeEx[$size] ?? 0) + 10000,
                    'image_path'   => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=400&q=80',
                ]);
            }
        }

        // ── Inventory Polo ───────────────────────────────────────
        $poloBP = 95000;
        $poloSizeEx = ['M' => 0, 'L' => 0, 'XL' => 5000];
        foreach (['Hitam', 'Abu', 'Navy'] as $color) {
            foreach (['M', 'L', 'XL'] as $size) {
                ProductInventory::create([
                    'product_id' => $polo->id,
                    'sku' => 'POL-' . strtoupper(substr($color, 0, 3)) . '-' . $size,
                    'variant_name' => "Polo Pendek - $color - $size",
                    'stock' => rand(10, 80),
                    'harga_jual' => $poloBP + ($poloSizeEx[$size] ?? 0),
                    'image_path' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=400&q=80',
                ]);
            }
        }

        $pdlBP = 135000;
        $pdlSizeEx = ['M' => 0, 'L' => 0, 'XL' => 5000, 'XXL' => 10000];
        foreach (['Hitam', 'Army', 'Navy'] as $color) {
            foreach (['M', 'L', 'XL', 'XXL'] as $size) {
                ProductInventory::create([
                    'product_id' => $pdl->id,
                    'sku' => 'PDL-' . strtoupper(substr($color, 0, 3)) . '-' . $size,
                    'variant_name' => "PDL Ripstop - $color - $size",
                    'stock' => rand(5, 40),
                    'harga_jual' => $pdlBP + ($pdlSizeEx[$size] ?? 0),
                    'image_path' => 'https://images.unsplash.com/photo-1604644401890-0bd678c83788?w=400&q=80',
                ]);
            }
        }

        // ── Inventory Jersey ─────────────────────────────────────
        $jerseyBP = 125000;
        $jerseySizeEx = ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 5000, 'XXL' => 10000];
        foreach (['Hitam', 'Putih', 'Merah', 'Biru'] as $color) {
            foreach (['S', 'M', 'L', 'XL'] as $size) {
                ProductInventory::create([
                    'product_id' => $jersey->id,
                    'sku' => 'JRS-' . strtoupper(substr($color, 0, 3)) . '-' . $size,
                    'variant_name' => "Jersey Dryfit Milano - $color - $size",
                    'stock' => rand(10, 60),
                    'harga_jual' => $jerseyBP + ($jerseySizeEx[$size] ?? 0),
                    'image_path' => 'https://images.unsplash.com/photo-1518002054494-3a6f94352e9d?w=400&q=80',
                ]);
            }
        }

        // ── Inventory Bendera / Banner ───────────────────────────
        $ukuranBendera = [
            ['label' => '60x90cm', 'harga' => 55000],
            ['label' => '90x135cm', 'harga' => 80000],
            ['label' => '120x180cm', 'harga' => 120000],
        ];
        foreach ($ukuranBendera as $uk) {
            ProductInventory::create([
                'product_id' => $bendera->id,
                'sku' => 'BND-' . str_replace(['x', 'cm'], ['X', ''], $uk['label']),
                'variant_name' => "Bendera Parasut - " . $uk['label'],
                'stock' => rand(10, 100),
                'harga_jual' => $uk['harga'],
                'image_path' => 'https://images.unsplash.com/photo-1555685812-4b943f1cb0eb?w=400&q=80',
            ]);
        }

        // ── Inventory Stiker ─────────────────────────────────────
        $ukuranStiker = [
            ['label' => '5x5cm', 'harga' => 3000],
            ['label' => '10x10cm', 'harga' => 6000],
            ['label' => 'A5', 'harga' => 12000],
            ['label' => 'A4', 'harga' => 20000],
        ];
        foreach ($ukuranStiker as $uk) {
            ProductInventory::create([
                'product_id' => $stiker->id,
                'sku' => 'STK-' . strtoupper(str_replace(['x', 'cm', ' '], ['X', '', ''], $uk['label'])),
                'variant_name' => "Stiker Vinyl - " . $uk['label'],
                'stock' => rand(50, 500),
                'harga_jual' => $uk['harga'],
                'image_path' => 'https://images.unsplash.com/photo-1572375992501-4b0892d50c69?w=400&q=80',
            ]);
        }

        // ── Inventory Topi ───────────────────────────────────────
        foreach (['Hitam', 'Putih', 'Navy'] as $color) {
            ProductInventory::create([
                'product_id' => $topi->id,
                'sku' => 'TOP-TRK-' . strtoupper(substr($color, 0, 3)),
                'variant_name' => "Topi Trucker Jaring - $color",
                'stock' => rand(20, 200),
                'harga_jual' => 65000,
                'image_path' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=400&q=80',
            ]);
        }
    }
}
