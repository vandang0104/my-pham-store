<?php

use App\Enums\OrderStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create(['email' => 'admin@example.com', 'password' => 'MatKhau123']);
    $this->staff = User::factory()->staff()->create(['email' => 'staff@example.com', 'password' => 'MatKhau123']);
    $this->category = Category::factory()->create(['name' => 'Chăm sóc da']);
    $this->brand = Brand::factory()->create();
});

/** @param array<string, mixed> $overrides */
function sanPhamHopLe($test, array $overrides = []): array
{
    return [
        'category_id' => $test->category->id, 'brand_id' => $test->brand->id,
        'name' => 'Sữa rửa mặt Đào Đỏ', 'sku' => 'sp-moi-01', 'price' => 250000, 'sale_price' => 199000,
        'stock' => 12, 'is_active' => '1', 'is_featured' => '0', ...$overrides,
    ];
}

describe('đăng nhập quản trị', function () {
    it('admin và staff đăng nhập ở /admin/login vào dashboard', function (string $email) {
        $this->post(route('admin.login.store'), ['email' => $email, 'password' => 'MatKhau123'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    })->with(['admin@example.com', 'staff@example.com']);

    it('khách hàng đăng nhập ở /admin/login bị từ chối bằng thông báo chung, không giữ phiên', function () {
        User::factory()->customer()->create(['email' => 'lan@example.com', 'password' => 'MatKhau123']);

        $this->from(route('admin.login'))->post(route('admin.login.store'), ['email' => 'lan@example.com', 'password' => 'MatKhau123'])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['email' => 'Email hoặc mật khẩu không đúng.']);
        $this->assertGuest();
    });

    it('khách chưa đăng nhập vào /admin/* được chuyển tới /admin/login; /dang-nhap vẫn dùng cho khách hàng', function () {
        $this->get(route('admin.products.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.login'))->assertOk()->assertSee('Đăng nhập quản trị');
        $this->get(route('account.dashboard'))->assertRedirect(route('login'));
    });
});

describe('dashboard', function () {
    it('staff thấy số liệu vận hành nhưng không thấy doanh thu; admin thấy doanh thu; menu theo quyền', function () {
        Order::factory()->status(OrderStatus::Pending)->count(2)->create();
        Product::factory()->create(['name' => 'Sắp hết', 'stock' => 2]);

        $this->actingAs($this->staff)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-stat="pending">2<', false)
            ->assertSee('Sắp hết')
            ->assertDontSee('data-stat="revenue-month"', false)
            ->assertDontSee('href="'.route('admin.reports.index').'"', false);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertSee('data-stat="revenue-month"', false)
            ->assertSee('href="'.route('admin.reports.index').'"', false);
    });
});

describe('danh mục', function () {
    it('thêm danh mục con, slug tự sinh không dấu và duy nhất', function () {
        Category::factory()->create(['slug' => 'serum-duong-da']);

        $this->actingAs($this->staff)->post(route('admin.categories.store'), [
            'name' => 'Serum dưỡng da', 'parent_id' => $this->category->id, 'sort_order' => 2, 'is_active' => '1',
        ])->assertRedirect(route('admin.categories.index'));

        $moi = Category::query()->where('name', 'Serum dưỡng da')->sole();
        expect($moi->slug)->toBe('serum-duong-da-2')->and($moi->parent_id)->toBe($this->category->id);
    });

    it('sửa danh mục; không chọn được chính nó làm cha', function () {
        $this->actingAs($this->admin)->put(route('admin.categories.update', $this->category), ['name' => 'Chăm sóc da mặt', 'is_active' => '1'])
            ->assertRedirect(route('admin.categories.index'));
        expect($this->category->fresh()->name)->toBe('Chăm sóc da mặt');

        $this->put(route('admin.categories.update', $this->category), ['name' => 'X', 'parent_id' => $this->category->id])
            ->assertSessionHasErrors('parent_id');
    });

    it('admin xoá được danh mục trống; còn sản phẩm thì bị chặn kèm lý do; staff không xoá được', function () {
        $trong = Category::factory()->create();
        Product::factory()->for($this->category)->create();

        $this->actingAs($this->staff)->delete(route('admin.categories.destroy', $trong))->assertForbidden();

        $this->actingAs($this->admin)->from(route('admin.categories.index'))->delete(route('admin.categories.destroy', $this->category))
            ->assertSessionHasErrors('delete');
        expect(Category::query()->find($this->category->id))->not->toBeNull();

        $this->delete(route('admin.categories.destroy', $trong))->assertRedirect(route('admin.categories.index'));
        expect(Category::query()->find($trong->id))->toBeNull();
    });
});

describe('sản phẩm', function () {
    it('staff thêm sản phẩm hợp lệ: slug tự sinh, SKU viết hoa, ảnh chính lưu vào disk public', function () {
        $this->actingAs($this->staff)->post(route('admin.products.store'), sanPhamHopLe($this, [
            'image' => UploadedFile::fake()->create('anh.jpg', 120, 'image/jpeg'),
        ]))->assertRedirect();

        $product = Product::query()->sole();
        expect($product->slug)->toBe('sua-rua-mat-dao-do')
            ->and($product->sku)->toBe('SP-MOI-01')
            ->and($product->sale_price)->toBe(199000);

        $image = $product->images()->sole();
        expect($image->is_primary)->toBeTrue();
        Storage::disk('public')->assertExists($image->path);
    });

    it('dữ liệu không hợp lệ bị từ chối: giá KM ≥ giá, SKU trùng, thiếu danh mục', function (array $overrides, string $field) {
        Product::factory()->create(['sku' => 'SP-TRUNG']);

        $this->actingAs($this->staff)->post(route('admin.products.store'), sanPhamHopLe($this, $overrides))->assertSessionHasErrors($field);
        expect(Product::query()->where('name', 'Sữa rửa mặt Đào Đỏ')->exists())->toBeFalse();
    })->with([
        'giá KM bằng giá' => [['sale_price' => 250000], 'sale_price'],
        'giá KM lớn hơn giá' => [['sale_price' => 300000], 'sale_price'],
        'SKU trùng' => [['sku' => 'sp-trung'], 'sku'],
        'thiếu danh mục' => [['category_id' => ''], 'category_id'],
    ]);

    it('sửa sản phẩm và thay ảnh chính (giữ một ảnh chính)', function () {
        $product = Product::factory()->for($this->category)->for($this->brand)->create();
        ProductImage::factory()->for($product)->create(['path' => 'products/cu.jpg', 'is_primary' => true]);
        Storage::disk('public')->put('products/cu.jpg', 'x');

        $this->actingAs($this->staff)->put(route('admin.products.update', $product), sanPhamHopLe($this, [
            'name' => 'Tên mới', 'sku' => $product->sku, 'image' => UploadedFile::fake()->create('moi.png', 80, 'image/png'),
        ]))->assertRedirect(route('admin.products.edit', $product));

        $primary = $product->images()->where('is_primary', true)->sole();
        expect($product->fresh()->name)->toBe('Tên mới')->and($primary->path)->not->toBe('products/cu.jpg');
        Storage::disk('public')->assertMissing('products/cu.jpg');
    });

    it('staff không xoá được; admin xoá mềm rồi khôi phục', function () {
        $product = Product::factory()->create();

        $this->actingAs($this->staff)->delete(route('admin.products.destroy', $product))->assertForbidden();

        $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product))->assertRedirect(route('admin.products.index'));
        expect($product->fresh()->trashed())->toBeTrue();
        $this->get(route('products.show', $product))->assertNotFound();

        $this->patch(route('admin.products.restore', $product))->assertRedirect();
        expect($product->fresh()->trashed())->toBeFalse();
    });

    it('danh sách tìm theo tên/SKU và lọc đã xoá', function () {
        Product::factory()->create(['name' => 'Kem dưỡng ẩm', 'sku' => 'KEM-01']);
        Product::factory()->create(['name' => 'Son môi đỏ'])->delete();

        $this->actingAs($this->staff)->get(route('admin.products.index', ['q' => 'KEM-01']))->assertSee('Kem dưỡng ẩm')->assertDontSee('Son môi đỏ');
        $this->get(route('admin.products.index', ['trang-thai' => 'da-xoa']))->assertSee('Son môi đỏ')->assertDontSee('Kem dưỡng ẩm');
    });
});
