# DESIGN.md — Định hướng thiết kế

> **Nguồn sự thật về giao diện.** Mọi màn hình (khách và quản trị) theo tài liệu này. Muốn làm khác: sửa tài liệu trước, ghi lý do.
> Lập bằng taste skill (`.claude/skills/design-taste-frontend`, tasteskill v2) ngày 19/10/2026. Sheet hướng thiết kế lưu ở `.taste-skill/direction.json` (trang) và `.taste-skill/app/direction.json` (quản trị) — in lại bằng `node .claude/skills/design-taste-frontend/scripts/draw.mjs --show`.
> Khách hàng mục tiêu: nữ 20–40 tuổi tại Việt Nam, mua chủ yếu trên điện thoại. Thiết kế **mobile-first**: mọi quy tắc viết cho 390px trước, rồi mở rộng.

## Mục lục
1. Tinh thần thương hiệu và giọng văn
2. Hướng thiết kế và ý tưởng
3. Màu sắc (kèm bảng tương phản WCAG)
4. Chữ
5. Không gian, lưới, bo góc, viền, bóng
6. Hình ảnh
7. Icon
8. Chuyển động
9. Component
10. Bố cục theo trang
11. Điều cấm
12. Chỗ lệch có chủ đích so với taste skill
13. Gợi ý ánh xạ sang Bootstrap 5 (cho bước 3.2)
14. Hướng thay thế và tên thương hiệu

---

## 1. Tinh thần thương hiệu và giọng văn

**Ba từ khoá: Tĩnh · Mộc · Tinh.**

| Từ khoá | Nghĩa trong thiết kế |
|---|---|
| **Tĩnh** (tĩnh lặng) | Ít thứ trên màn hình, mỗi thứ lớn và rõ; khoảng trống có chủ đích; chuyển động chậm, không nhấp nháy. |
| **Mộc** (thảo mộc, chất liệu thật) | Màu lấy từ chất liệu Việt: lụa, giấy dó, sơn mài, men ngọc. Nói thật về thành phần và nơi trồng (Đà Lạt, Hội An, Đắk Lắk). |
| **Tinh** (tinh xảo) | Chữ serif sắc nét ở cỡ lớn, căn chỉnh như đồ in; chi tiết nhỏ (giá, dung tích, nhãn) chính xác, thẳng hàng. |

**Giọng văn**
- Xưng "bạn", giọng một người bán hàng hiểu sản phẩm: bình tĩnh, cụ thể, không nài nỉ.
- Câu ngắn. Tiêu đề tối đa 2 dòng ở mọi độ rộng (khoảng 32 ký tự).
- Nói công dụng có thể kiểm chứng và thời gian thật ("đều màu hơn sau 4 tuần"), ghi rõ hoạt chất và nồng độ.
- **Cấm:** từ phóng đại ("thần thánh", "trắng bật tông", "cấp tốc", "số 1"), chấm than liên tiếp, emoji, viết HOA cả câu, bịa đánh giá/số liệu/báo chí.
- Nút gọi tên hành động: "Thêm vào giỏ", "Thanh toán", "Xem đơn chăm sóc" — không "Khám phá ngay!", "Click here".

Ví dụ: ✅ "Serum niacinamide 10% cho da dầu, dùng sáng và tối." · ❌ "Siêu phẩm thần thánh giúp da trắng bật tông chỉ sau 3 ngày!!!"

## 2. Hướng thiết kế và ý tưởng

**Hướng được chia (taste skill):** *The page is the object* — trang được dựng như một vật in có thật trong thế giới của chủ đề (seed `20261019`, sector `ecommerce`, tone `luxury`). Chọn trong 3 hướng được chia (`split-stage`, `contact-sheet`, `page-is-the-object`) vì mỹ phẩm thảo mộc có sẵn vật in của riêng nó; `split-stage` được mượn cho trang chi tiết sản phẩm; `contact-sheet` bị loại (cần rất nhiều ảnh, đi ngược cảm giác tĩnh).

**Câu ý tưởng:** *Trên trang này, **nhãn sản phẩm ghi thành phần và nơi trồng từng nguyên liệu** (rau má, trà xanh Bảo Lộc, bưởi, hoa sứ Hội An, cà phê Đắk Lắk) trở thành **tấm nhãn in — mỗi section là một mảnh văn phòng phẩm của cùng bộ nhãn** (thẻ chính, cuống xé, nhãn treo).*

**Điều khách làm được (live thing) — TUỲ CHỌN, làm sau khi các chức năng chính xong:** *Đơn chăm sóc theo loại da* — chọn loại da (5 giá trị của `SkinType`: da dầu, da khô, da hỗn hợp, da nhạy cảm, mọi loại da) → hiện một "đơn chăm sóc" 5 bước bằng **sản phẩm thật trong catalog**, mỗi bước có nút thêm vào giỏ. Dùng tốt bằng một ngón tay trên điện thoại; không có JS vẫn hoạt động (form GET `?loai-da=`).
5 bước **ánh xạ theo danh mục con đã có, không đổi schema** (lấy sản phẩm `active()` có `skin_type` = loại da đã chọn hoặc `all`, ưu tiên `is_featured` rồi `rating_avg`):

| Bước | Danh mục con (slug) |
|---|---|
| 1. Làm sạch | `tay-trang`, `sua-rua-mat` |
| 2. Cân bằng | `toner` |
| 3. Đặc trị | `serum-tinh-chat` (thêm `mat-na` làm gợi ý 1–2 lần/tuần) |
| 4. Dưỡng ẩm | `kem-duong-am` |
| 5. Chống nắng | `chong-nang-da-mat` |

**Phạm vi áp quy tắc** (site có cả trang để đọc và màn hình để thao tác):

| Loại màn hình | Ví dụ | Theo luật |
|---|---|---|
| Trang đọc | Trang chủ, tin tức, giới thiệu, trang tĩnh | Luật "page" của taste skill (§11, §12) |
| Màn thao tác của khách | Danh mục có lọc, chi tiết SP, giỏ, thanh toán, tài khoản | Màu/chữ của trang + luật "app screen": được dùng viền ô nhập, lưới sản phẩm (nội dung lặp thật), nhãn nhỏ cỡ ≥ 13px |
| Quản trị `/admin` | Dashboard, danh sách, chi tiết, cài đặt | Sheet app "Framed sheet" (§10.6) + `references/app-screens.md` |

## 3. Màu sắc

Nguyên tắc của hướng: **in hai–ba màu** như một lệnh in giấy. Mở đầu và kết thúc trang trên nền **sơn mài** tối với chữ lụa; phần giữa là giấy sáng. Một màu nhấn duy nhất cho hành động. Màu lấy từ chất liệu Việt (giữ tỉ lệ của palette được chia "Walnut and tan", thay màu bằng chất liệu của chủ đề — đúng quy tắc của taste skill).

| Token | Tên | HEX | Vai trò | Tỉ lệ diện tích |
|---|---|---|---|---|
| `--c-ground` | Lụa | `#F5F2EC` | Nền chính, "giấy" của các tấm nhãn | ~55% |
| `--c-surface` | Giấy dó | `#EBE5DA` | Bậc bề mặt: nền ô ảnh sản phẩm, dải nhãn, panel lọc | ~10% |
| `--c-ink` | Sơn mài | `#1E1B18` | Chữ chính; **cũng là nền tối** cho hero, CTA đảo màu, footer | ~20% (chữ + mảng tối) |
| `--c-ink-2` | Sơn mài 2 | `#2B241E` | Bề mặt trên nền tối (mega menu trên hero tối, thẻ trong footer) | ít |
| `--c-muted` | Mực nhạt | `#5C544C` | Chữ phụ trên nền sáng (thương hiệu, dung tích, ghi chú) | — |
| `--c-muted-dark` | Mực nhạt trên tối | `#B8AE9F` | Chữ phụ trên nền sơn mài | — |
| `--c-accent` | Men ngọc | `#2E5A4C` | **Màu nhấn duy nhất trên nền sáng:** nút chính, link đang chọn, vòng focus, trạng thái thành công | ~3% |
| `--c-accent-hover` | Men ngọc đậm | `#234539` | Hover/active của nút chính | — |
| `--c-field` | Ngọc nhạt | `#D3DDD4` | Mảng nền cho một section ở giữa trang (thay "tan" của sheet) | ~5% |
| `--c-champagne` | Champagne | `#C2A878` | **Chỉ làm mảng/nút trên nền tối** (nút duy nhất trên hero và CTA), vòng focus trên nền tối | ~2% |
| `--c-sale` | Đỏ son | `#8E2F23` | Giá khuyến mãi, nhãn "−20%", lỗi form | <1% |
| `--c-sale-dark` | Đỏ son trên tối | `#E39A8C` | Giá khuyến mãi khi nằm trên nền sơn mài | <1% |
| `--c-error-bg` | Đỏ son nhạt | `#F1DEDA` | Nền flash/khung báo lỗi (chữ đỏ son hoặc sơn mài) | <1% |
| `--c-warning` | Hổ phách | `#7A5410` | "Sắp hết hàng", cảnh báo | <1% |
| `--c-line` | Viền ô nhập | `#8A8174` | **Chỉ** viền ô nhập, checkbox, radio, đường kẻ bảng | — |

**Quy tắc dùng màu**
- Chữ đọc dài luôn là `--c-ink` trên nền sáng (hoặc `--c-ground` trên nền tối). Xám chỉ cho ghi chú.
- Mỗi màn hình **một** nút màu nhấn: men ngọc trên nền sáng, champagne (chữ sơn mài) trên nền tối.
- Phân tách bề mặt bằng **bậc màu** (lụa → giấy dó → ngọc nhạt), không bằng đường kẻ hay bóng.
- Một section có thể đổi cả mảng nền (lụa → ngọc nhạt → sơn mài), không chuyển dần.
- Trạng thái: thành công = men ngọc; lỗi = đỏ son; cảnh báo = hổ phách; thông tin = mực.

**Bảng tương phản WCAG 2.x** (tính bằng script công thức độ chói tương đối, không ước lượng):

| Chữ / thành phần | Nền | Dùng cho | Tỉ lệ | Yêu cầu | Kết quả |
|---|---|---|---|---|---|
| sơn mài `#1E1B18` | lụa `#F5F2EC` | Chữ chính trên nền | 15.34:1 | 4.5:1 | AAA |
| sơn mài `#1E1B18` | giấy dó `#EBE5DA` | Chữ trên bề mặt (thẻ, ô ảnh) | 13.67:1 | 4.5:1 | AAA |
| sơn mài `#1E1B18` | ngọc nhạt `#D3DDD4` | Chữ trên mảng ngọc nhạt | 12.29:1 | 4.5:1 | AAA |
| sơn mài `#1E1B18` | champagne `#C2A878` | Chữ trên mảng / nút champagne | 7.48:1 | 4.5:1 | AAA |
| mực nhạt `#5C544C` | lụa `#F5F2EC` | Chữ phụ trên nền | 6.65:1 | 4.5:1 | AA |
| mực nhạt `#5C544C` | giấy dó `#EBE5DA` | Chữ phụ trên bề mặt | 5.92:1 | 4.5:1 | AA |
| mực nhạt `#5C544C` | ngọc nhạt `#D3DDD4` | Chữ phụ trên mảng ngọc nhạt | 5.33:1 | 4.5:1 | AA |
| men ngọc `#2E5A4C` | lụa `#F5F2EC` | Link, chữ nhấn trên nền | 7.01:1 | 4.5:1 | AAA |
| men ngọc `#2E5A4C` | giấy dó `#EBE5DA` | Link trên bề mặt | 6.24:1 | 4.5:1 | AA |
| lụa `#F5F2EC` | men ngọc `#2E5A4C` | Chữ trên nút chính | 7.01:1 | 4.5:1 | AAA |
| lụa `#F5F2EC` | men ngọc đậm `#234539` | Chữ trên nút chính khi hover | 9.48:1 | 4.5:1 | AAA |
| đỏ son `#8E2F23` | lụa `#F5F2EC` | Giá khuyến mãi, lỗi trên nền | 7.28:1 | 4.5:1 | AAA |
| đỏ son `#8E2F23` | giấy dó `#EBE5DA` | Giá khuyến mãi trên bề mặt | 6.49:1 | 4.5:1 | AA |
| lụa `#F5F2EC` | đỏ son `#8E2F23` | Chữ trên nhãn "−20%" | 7.28:1 | 4.5:1 | AAA |
| đỏ son `#8E2F23` | đỏ son nhạt `#F1DEDA` | Chữ trong flash/khung báo lỗi | 6.28:1 | 4.5:1 | AA |
| hổ phách `#7A5410` | lụa `#F5F2EC` | Cảnh báo "sắp hết hàng" | 6.06:1 | 4.5:1 | AA |
| lụa `#F5F2EC` | sơn mài `#1E1B18` | Chữ trên nền tối (hero, CTA, footer) | 15.34:1 | 4.5:1 | AAA |
| lụa `#F5F2EC` | sơn mài 2 `#2B241E` | Chữ trên bề mặt tối | 13.68:1 | 4.5:1 | AAA |
| mực nhạt trên tối `#B8AE9F` | sơn mài `#1E1B18` | Chữ phụ trên nền tối | 7.83:1 | 4.5:1 | AAA |
| champagne `#C2A878` | sơn mài `#1E1B18` | Chữ champagne trên nền tối | 7.48:1 | 4.5:1 | AAA |
| đỏ son trên tối `#E39A8C` | sơn mài `#1E1B18` | Giá khuyến mãi trên nền tối | 7.56:1 | 4.5:1 | AAA |
| viền ô nhập `#8A8174` | lụa `#F5F2EC` | Viền ô nhập (thành phần giao diện) | 3.43:1 | 3:1 | Đạt (≥ 3:1) |
| viền ô nhập `#8A8174` | trắng `#FFFFFF` | Viền ô nhập trên nền trắng | 3.84:1 | 3:1 | Đạt (≥ 3:1) |
| men ngọc `#2E5A4C` | lụa `#F5F2EC` | Vòng focus trên nền sáng | 7.01:1 | 3:1 | Đạt (≥ 3:1) |
| champagne `#C2A878` | sơn mài `#1E1B18` | Vòng focus / nút trên nền tối | 7.48:1 | 3:1 | Đạt (≥ 3:1) |
| sơn mài `#1E1B18` | khung admin `#EEF1EE` | Admin: chữ trên khung | 15.06:1 | 4.5:1 | AAA |
| mực nhạt `#5C544C` | khung admin `#EEF1EE` | Admin: chữ phụ trên khung | 6.53:1 | 4.5:1 | AA |
| mực nhạt `#5C544C` | trắng `#FFFFFF` | Admin: chữ phụ trên tờ trắng | 7.43:1 | 4.5:1 | AAA |
| men ngọc `#2E5A4C` | trắng `#FFFFFF` | Admin: gạch chân tab, link | 7.83:1 | 4.5:1 | AAA |
| champagne `#C2A878` | lụa `#F5F2EC` | CẤM: chữ champagne trên nền sáng | 2.05:1 | 4.5:1 | **Không đạt** |
| men ngọc `#2E5A4C` | sơn mài `#1E1B18` | CẤM: nút men ngọc trên nền tối | 2.19:1 | 3:1 | **Không đạt** |

**Cặp bị cấm và cách thay:** champagne **không bao giờ** là màu chữ trên nền sáng (2.05:1) — dùng làm mảng nền, đặt chữ sơn mài lên. Nút men ngọc không đặt trên nền sơn mài (viền nút chỉ 2.19:1, dưới 3:1 của WCAG 1.4.11) — trên nền tối dùng nút champagne. Thêm màu mới: chạy lại script tương phản và bổ sung bảng.

## 4. Chữ

**Hai họ chữ, đều từ Google Fonts, đều có subset `vietnamese`** (kiểm bằng CSS API của Google Fonts):

| Vai trò | Font | Độ đậm | Dùng ở |
|---|---|---|---|
| Hiển thị (serif) | **Cormorant Garamond** | 600 (tiêu đề), 500 (câu tuyên bố lớn) | Chỉ ở cỡ **≥ 28px**: tiêu đề trang/section, tên sản phẩm ở trang chi tiết, chữ thương hiệu |
| Nội dung (sans) | **Be Vietnam Pro** | 400 (đoạn văn), 500 (nhãn, nút, giá) | Mọi chữ **≤ 24px**: đoạn văn, menu, nút, form, giá, nhãn |

- Cormorant ở cỡ nhỏ quá mảnh → không dùng dưới 28px. Be Vietnam Pro được thiết kế riêng cho tiếng Việt.
- Đã loại: Noto Serif Display (quá rộng, "Kể chuyện" xuống dòng ở cùng cỡ, nét nặng). **Không có tiếng Việt** nên cấm dùng: Bodoni Moda, Jost, Albert Sans, Gilda Display, Marcellus, Libre Caslon Display.
- **Kiểm chứng dấu xếp chồng** (ảnh chụp trang thử bằng Edge headless, chuỗi "Kể chuyện / Ữ Ỗ Ẫ ệ" ở 64px): Cormorant hiển thị đúng mọi dấu chồng; ở line-height 0.95 dấu ngã của "Ẫ" sát dòng trên, từ **1.05** đã tách rõ ⇒ chốt **line-height tối thiểu 1.1** cho chữ hiển thị.
- Tải font: `<link rel="preconnect">` tới fonts.googleapis.com/gstatic, `display=swap`, chỉ tải 4 file: Cormorant 500/600, Be Vietnam Pro 400/500. (Ở bước triển khai có thể tự host bằng file woff2 subset vietnamese + latin để không phụ thuộc mạng ngoài.)

**Thang cỡ chữ — 5 bậc, không hơn** (mobile → desktop, dùng `clamp()`):

| Bậc | Token | Mobile | Desktop | Font | Line-height | Letter-spacing | Dùng cho |
|---|---|---|---|---|---|---|---|
| Lớn nhất | `--fs-display` | 40px | 88px (≈6vw) | Cormorant 600 | 1.1 | −0.02em | `h1` trang chủ, câu CTA đảo màu, chữ thương hiệu footer |
| Tiêu đề | `--fs-title` | 28px | 40px | Cormorant 600 | 1.15 | −0.01em | `h2` section, tên SP ở trang chi tiết |
| Nhấn | `--fs-lead` | 20px | 24px | Be Vietnam Pro 500 | 1.35 | 0 | Câu dẫn dưới tiêu đề, giá ở trang chi tiết, `h3` |
| Nội dung | `--fs-body` | 16px | 17px | Be Vietnam Pro 400 | 1.55 | 0 | Đoạn văn, mô tả, form |
| Nhỏ | `--fs-small` | 13px | 14px | Be Vietnam Pro 500 | 1.4 | 0 | Thương hiệu trên thẻ SP, dung tích, breadcrumb, chú thích — **không nhỏ hơn 13px** |

- Tỉ lệ tiêu đề/nội dung trên desktop ≈ 5.2 (88/17): hai cỡ làm việc chính, một rất lớn, một nhỏ.
- Đoạn văn 45–75 ký tự/dòng (`max-width: 65ch`). Căn trái mặc định; chỉ một khoảnh khắc căn giữa mỗi trang (thẻ nhãn ở hero).
- Số (giá, số lượng, mã đơn): `font-variant-numeric: tabular-nums`. Giá định dạng `259.000 ₫` (khoảng trắng không ngắt — đã có `App\Support\Money`).
- Sentence case. **Không** chữ in hoa nhỏ giãn khoảng (kiểu "NEW ARRIVALS"), không italic/màu riêng cho một từ trong tiêu đề, không monospace.

## 5. Không gian, lưới, bo góc, viền, bóng

**Khoảng cách** — thang 4/8: `4 · 8 · 12 · 16 · 24 · 32 · 48 · 64 · 96 · 128 · 160` (token `--sp-1` … `--sp-11`).
- Khoảng giữa các section lớn hơn rõ rệt mọi khoảng bên trong: **96px mobile / 160px desktop**.
- Tiêu đề → câu dẫn 24–32px; câu dẫn → nút 32px.

**Lưới và container**
| | Mobile (< 768) | Tablet (768–1199) | Desktop (≥ 1200) |
|---|---|---|---|
| Lề ngoài (hai bên bằng nhau) | 16px | 24px | 32px |
| Cột | 4 | 8 | 12 |
| Khoảng cách cột | 12px | 16px | 24px |
| Container nội dung | 100% | 100% | tối đa 1320px; ảnh lớn được tràn mép |

- Một lưới, một cặp lề cho toàn trang. Bất đối xứng nằm **trong** lưới; nửa trái có chữ thì nửa phải có ảnh/cột/danh sách, không để trống.
- Lưới sản phẩm: 2 cột mobile (gap 12px), 3 cột tablet, 4 cột desktop.

**Bo góc — quyết định một lần: `0`** cho nút, ảnh, thẻ, panel, mega menu, mini-cart. Ngoại lệ duy nhất: ô nhập/select **2px**; badge số trên giỏ hàng tròn hẳn (`50%`). Mép "vật in" tạo bằng `clip-path` (răng cưa, khía vé, đục lỗ chấm ≥ 4px), **không** bằng viền.

**Viền mảnh** — chỉ 1px `--c-line` ở: ô nhập, checkbox/radio, bảng (đường kẻ ngang giữa dòng), dòng tổng trong tóm tắt đơn. **Không** viền quanh thẻ, section, ảnh; không đường kẻ phân cách trang trí.

**Bóng — chỉ cho thứ thật sự nổi** trên trang: mega menu, mini-cart (ngăn kéo), dialog, gợi ý tìm kiếm.
`--shadow-float: 0 12px 32px rgba(30, 27, 24, 0.14), 0 2px 6px rgba(30, 27, 24, 0.08);`
Thẻ sản phẩm, nút, panel ở trạng thái nghỉ: **không bóng**.

## 6. Hình ảnh

| Loại | Tỉ lệ | Cách xử lý |
|---|---|---|
| Ảnh sản phẩm (thẻ, chi tiết) | **4:5** | Sản phẩm đặt giữa trên nền giấy dó phẳng, ánh sáng mềm từ trái, không đổ bóng giả; góc vuông; mọi ảnh cùng cách xử lý |
| Ảnh phụ trang chi tiết | 4:5 (chính) + 1:1 (kết cấu, thành phần) | Ảnh kết cấu cận cảnh (giọt serum, vệt kem) |
| Banner trang chủ | Desktop **8:3** (1920×720), mobile **4:5** (800×1000) | Ảnh khung cảnh/chất liệu (rừng thông Đà Lạt, phố Hội An, vườn cà phê) — không chữ in sẵn trong ảnh; chữ đặt bên cạnh, không đè lên ảnh |
| Ảnh danh mục | 1:1 | Chất liệu đặc trưng của nhóm |
| Ảnh bài viết | 16:9 | |

- Mọi `<img>` có `width`, `height`, `alt` mô tả thật ("Serum Niacinamide 10% Sáng Da, chai 30ml"), `loading="lazy"` (trừ ảnh hero), `decoding="async"`. Ảnh lưu trong dự án — **không hotlink**.
- **Khi chỉ có placeholder** (hiện tại): placeholder là **mảng màu phẳng** theo palette (giấy dó/ngọc nhạt) với tên sản phẩm bằng Cormorant, cùng tỉ lệ ảnh thật — **không gradient**. ⚠️ `database/seeders/Support/PlaceholderImage.php` hiện sinh SVG có `linearGradient` → sửa thành mảng phẳng ở bước 3.2/3.3.
- Hình minh hoạ sản phẩm (khi chưa có ảnh chụp) được **vẽ bằng SVG phẳng** theo màu thật của sản phẩm (dáng chai, hũ, tuýp), không làm giả ảnh chụp.
- Ảnh "thế giới của sản phẩm": tranh thực vật cổ (botanical plate) về rau má, trà, bưởi, hoa sứ, cà phê; bưu ảnh cũ Đà Lạt/Hội An — tìm bằng `node .claude/skills/design-taste-frontend/scripts/assets.mjs "<từ khoá>"` (chỉ giấy phép cho phép dùng thương mại), tải về bằng `--get`, ghi nguồn ở footer. Không bao giờ trình bày như ảnh sản phẩm.
- **Thay ảnh thật:** chép ảnh vào `storage/app/public/seed/` rồi chạy lại seed (README đã ghi). Cần chụp: 48 sản phẩm × 3–4 ảnh theo tỉ lệ 4:5.

## 7. Icon

- **SVG inline**, không cài package icon (muốn thêm phải hỏi trước). Đặt trong Blade component `<x-icon name="..."/>` (một file Blade chứa các `<symbol>` hoặc mỗi icon một partial).
- Lưới 24×24, nét **1.5px**, đầu nét tròn (`stroke-linecap="round"`), không tô, `stroke="currentColor"` để ăn theo màu chữ. Cỡ hiển thị 20px (trong menu, nút) hoặc 24px (header).
- Bộ cần có: tìm kiếm, túi mua sắm, tài khoản, trái tim, sao (rỗng/đầy/nửa), mũi tên trái/phải/xuống, đóng (×), menu (≡), cộng, trừ, kiểm tra (✓), chấm thông tin.
- Icon đứng riêng (không có chữ) phải có `aria-label` hoặc chữ ẩn `visually-hidden`; icon trang trí có `aria-hidden="true"`.
- **Cấm:** emoji làm icon, icon đặt trong ô tròn/vuông tô màu, icon trên đầu các thẻ tính năng.

## 8. Chuyển động

**Token** (theo `references/motion.md` của taste skill):
```css
--ease-out: cubic-bezier(0.22, 1, 0.36, 1);   /* vào, hover có di chuyển, UI < 600ms */
--ease-expo: cubic-bezier(0.16, 1, 0.3, 1);   /* reveal ≥ 800ms */
--ease-in-out: cubic-bezier(0.76, 0, 0.24, 1); /* ngăn kéo, panel */
--ease-soft: cubic-bezier(0.33, 1, 0.68, 1);  /* ảnh phóng/trôi chậm */
--t-press: 140ms; --t-hover: 180ms; --t-ui: 240ms; --t-drawer: 360ms; --t-reveal: 800ms;
--stagger-item: 70ms; --stagger-line: 90ms;
```

| Chuyển động | Cách làm |
|---|---|
| **Chữ ký (signature): crossfade chậm** | Ảnh hero đổi qua 3–4 ảnh cùng bố cục, mờ dần 1.6s, giữ 5–6s mỗi ảnh — như ánh sáng đổi trong phòng. Dừng khi tab ẩn. |
| Reveal khi cuộn | Mỗi section: tiêu đề → chữ → ảnh, cách nhau 70–90ms, nâng 24px + opacity, `--t-reveal` `--ease-expo`, **chạy một lần** (IntersectionObserver). Nội dung hiển thị sẵn nếu JS lỗi (chỉ thêm class ẩn khi JS chạy). |
| Hover thẻ sản phẩm | Ảnh phóng 1.03 trong 600ms `--ease-soft`; nếu có ảnh thứ hai thì crossfade sang ảnh đó. |
| Nút | Nhấn: `scale(0.98)` 140ms; hover: đổi màu nền 180ms `ease`. |
| Link trong đoạn văn | **Luôn gạch chân khi nghỉ** (1px, cách chữ 0.18em): màu men ngọc so với chữ sơn mài xung quanh chỉ 2,19:1, dưới mức 3:1 của WCAG 1.4.1, nên không được phân biệt link chỉ bằng màu. Hover/focus: gạch chân dày 2px + đổi sang men ngọc đậm, 180ms `ease`. |
| Link điều hướng (menu, footer, breadcrumb) | Được bỏ gạch chân khi nghỉ (vị trí đã cho biết là link); hover/focus: gạch chân chạy từ trái sang (`scaleX`) 240ms `--ease-out`. |
| Mega menu, gợi ý tìm kiếm | Mở 240ms (opacity + nâng 8px), đóng 170ms. |
| Mini-cart, menu mobile | Trượt vào 360ms `--ease-in-out`, nền mờ (scrim) `rgba(30,27,24,.4)` — không blur. |
| Thêm vào giỏ | Badge số trên túi nảy nhẹ (scale 1 → 1.2 → 1, 300ms). |

- Chỉ dùng `transform` và `opacity`.
- **`prefers-reduced-motion: reduce`:** giữ nội dung, bỏ di chuyển — reveal hiện ngay, crossfade chỉ hiện ảnh đầu, ngăn kéo hiện tức thì (chỉ fade 1ms), không phóng ảnh.

## 9. Component

Mỗi component là một Blade component trong `resources/views/components/`. Quy tắc chung: token màu/chữ/khoảng cách ở §3–§5, góc vuông, không bóng khi nghỉ, focus rõ (`outline: 2px solid` men ngọc, lệch 2px; champagne trên nền tối).

| Component | Quy tắc |
|---|---|
| **Header** | Thanh phẳng trên nền lụa, cao 64px (mobile 56px), dính đầu trang khi cuộn lên. Trái: wordmark. Giữa (desktop): 5 link chữ — Chăm sóc da · Trang điểm · Chăm sóc tóc & cơ thể · Nước hoa · Thương hiệu. Phải: icon tìm kiếm, tài khoản, túi có **badge số**. Không viền dưới; khi cuộn chỉ đổi sang nền giấy dó. Mobile: icon menu (≡) trái, wordmark giữa, tìm kiếm + túi phải. |
| **Mega menu** | Mở khi hover/focus link (desktop), panel full-width nền lụa có `--shadow-float`; 3–4 cột danh mục con bằng chữ 16px + 1 ô ảnh danh mục 1:1 bên phải. Đóng bằng Esc, rời chuột 200ms. Mobile: menu là ngăn kéo trái, danh mục mở dạng `details`. |
| **Tìm kiếm** | Mở thành ô nhập lớn dưới header (desktop) / toàn màn hình (mobile); gợi ý tự động (debounce 250ms) hiện tên + ảnh nhỏ + giá; Enter đi tới `/san-pham?q=`. |
| **Badge giỏ hàng** | Hình tròn 20px nền men ngọc, số Be Vietnam Pro 500 13px chữ lụa (7.01:1), `tabular-nums`; từ 10 trở lên badge giãn ngang (min-width 20px, đệm 6px); trên 99 hiện "99+"; ẩn khi 0. Có chữ ẩn "3 sản phẩm trong giỏ" cho trình đọc màn hình. |
| **Footer** | Mặt sau của tấm nhãn: nền sơn mài, chữ lụa. Chữ thương hiệu dựng lớn toàn chiều rộng (mỗi chữ cắt khỏi một khối màu, cao 60–100%, khe 4px). Bên dưới 56px: 4 cột link 15px (Mua sắm, Hỗ trợ, Chính sách, Về chúng tôi) + địa chỉ/hotline; hàng cuối: bản quyền + nguồn ảnh (credit). Không phải sơ đồ trang dày đặc. |
| **Product card** (nhãn treo) | Ảnh 4:5 nền giấy dó, góc vuông, **gắn liền bên dưới là dải nhãn** (như nhãn treo): dòng 1 thương hiệu (13px mực nhạt); dòng 2 tên SP (16px, tối đa 2 dòng); dòng 3 dung tích · loại da trái, giá phải. Màu dải nhãn xoay vòng giữa 2–3 cặp (giấy dó/sơn mài, ngọc nhạt/sơn mài, sơn mài/lụa) để lưới không đơn điệu. Nhãn "−20%" đỏ son ở góc trên trái ảnh. Nút tim ở góc trên phải. **Cấu trúc truy cập được:** thẻ là `<article>` (`position: relative`), link nằm ở tên sản phẩm và phủ cả thẻ bằng **stretched link** (`a::after { content: ""; position: absolute; inset: 0; }`); nút tim và nút "Thêm vào giỏ" là `<button>` **anh em** của link (KHÔNG lồng nút trong `<a>`), đặt `position: relative; z-index: 2` để nằm trên lớp phủ. Nút "Thêm vào giỏ" hiện khi hover/focus-within (desktop), luôn hiện dạng icon túi (mobile). Ảnh trong thẻ có `alt` là tên sản phẩm (link chỉ đọc tên một lần). Không viền, không bóng. |
| **Price** | Giá hiện hành Be Vietnam Pro 500, `tabular-nums`. Có KM: giá KM đỏ son + giá gốc gạch ngang mực nhạt nhỏ hơn một bậc + "−20%". Dùng accessor `formatted_price`/`formatted_original_price`/`discount_percent` của `Product`. Đọc cho trình đọc màn hình: "Giá khuyến mãi 279.000 ₫, giá gốc 345.000 ₫". |
| **Rating** | 5 sao SVG (đầy/nửa/rỗng) màu sơn mài, cỡ 16px + điểm "4,7" + "(23 đánh giá)" 13px. Dữ liệu từ `rating_avg`/`rating_count` (chỉ đánh giá đã duyệt). Không hiện sao khi chưa có đánh giá — ghi "Chưa có đánh giá". |
| **Breadcrumb** | Một dòng 13px mực nhạt, phân cách bằng "/" ; mục cuối là sơn mài, không phải link. Mobile: chỉ hiện mục cha gần nhất với mũi tên "‹". Có `aria-label="Breadcrumb"` + schema `BreadcrumbList`. |
| **Flash message** | Dải full-width ngay dưới header, nền **bậc màu** theo loại (thành công: ngọc nhạt + chữ sơn mài; lỗi: `--c-error-bg` + chữ đỏ son; thông tin: giấy dó + chữ sơn mài), chữ 16px, nút đóng ×, tự ẩn sau 6s (trừ lỗi). Không viền, không icon trong ô màu. `role="status"` (lỗi: `role="alert"`). |
| **Empty state** | Một câu nói thật bằng Cormorant `--fs-title` ("Giỏ hàng của bạn đang trống") + một câu hướng dẫn + **một** hành động ("Xem sản phẩm"). Có thể kèm một hình vẽ SVG phẳng của sản phẩm; không minh hoạ hoạt hình, không emoji. |
| **Pagination** | Số trang chữ 16px (trang hiện tại men ngọc gạch chân 2px), "Trước"/"Sau" dạng chữ; mobile chỉ "Trước · Trang 2/5 · Sau". Ô bấm tối thiểu 44×44px. Giữ query string khi lọc. |
| **Button** | Phẳng, **góc vuông**, cao 48px (mobile 52px), chữ Be Vietnam Pro 500 16px, đệm ngang 24px. **Chính:** nền men ngọc, chữ lụa (trên nền tối: nền champagne, chữ sơn mài). **Phụ:** chữ sơn mài có gạch chân, không khung. **Phá huỷ:** chữ đỏ son. Disabled: opacity .45 + `cursor: not-allowed`. Một nút chính mỗi màn hình. |
| **Form** | Nhãn ở trên ô (14px 500), ô cao 48px nền trắng, viền 1px `--c-line`, bo 2px; focus: viền men ngọc + vòng focus. Lỗi: viền đỏ son + câu lỗi 13px dưới ô (từ `lang/vi/validation.php`). Bắt buộc ghi "(bắt buộc)" bằng chữ, không chỉ dấu *. Checkbox/radio 20px tự vẽ. Gợi ý (help text) 13px mực nhạt. |
| **Badge** | Chỉ hai loại: **số đếm** (giỏ hàng, số đơn chờ trong admin) và **phần trăm giảm** ("−20%", nền đỏ son chữ lụa, góc vuông, 13px). Trạng thái đơn trong tài khoản/admin là chữ có chấm màu nhỏ (●), không phải viên thuốc màu. **Cấm** badge chữ "Mới", "Hot", "Best seller". |
| **Mini-cart** | Ngăn kéo từ phải, rộng 420px (mobile: toàn màn hình), nền lụa, `--shadow-float`. Đầu: "Giỏ hàng (3)" + ×. Danh sách: ảnh 4:5 64px, tên, dung tích, bộ +/− , giá; xoá bằng chữ "Xoá". Cuối dính: tạm tính, dòng "Còn 120.000 ₫ để được miễn phí giao hàng" (ngưỡng từ settings), nút chính "Thanh toán", link "Xem giỏ hàng". Khoá cuộn trang nền, focus trap, Esc để đóng. |
| **Đơn chăm sóc** (live thing — TUỲ CHỌN, làm sau; ánh xạ 5 bước ở §2) | Thẻ "đơn" nền lụa có mép răng cưa trên và dưới (`clip-path`), 5 dòng bước, mỗi dòng: tên bước, sản phẩm thật (ảnh nhỏ, tên, giá), nút thêm. Chọn loại da bằng 5 nút chữ (radio dạng nút, đang chọn gạch chân men ngọc). |

## 10. Bố cục theo trang

### 10.1 Trang chủ (theo sheet, đã điều chỉnh cho dự án)
Bắt buộc: 1, 2, 3, 4 (Đang ưu đãi), 7 và footer. **TUỲ CHỌN, làm sau** (khi các chức năng chính đã xong): 5 (Câu chuyện — Thư của người pha chế) và *Đơn chăm sóc* mà 6 dẫn tới.

1. **Hero — "Thẻ nhãn có sọc cạnh ảnh"**: desktop chia 50/50 (khe 12px, lề 12px hai bên). Phải: **ảnh lấy từ bảng `banners`** (`Banner::visible()->where('position', 'home_hero')->orderBy('sort_order')`, `image_desktop` / `image_mobile` qua `<picture>`) dính (`position: sticky`) toàn chiều cao; có từ 2 banner trở lên thì crossfade chậm giữa chúng (chữ ký chuyển động §8), tiêu đề/link của banner đang hiện dùng cho `h1` và nút. Không có banner nào → mảng ngọc nhạt phẳng. Trái: tấm nhãn nền lụa; trên cùng **dải sọc nhãn** cao 56px (các thanh 28px xen kẽ sơn mài/lụa — sọc của nhãn chai, xuất hiện lại 8px trên footer và trong nút khi hover); dưới đó wordmark ~300px, `h1` 2 dòng (Cormorant, `clamp(40px, 3.6vw, 56px)`), một câu dẫn 17px tối đa 36ch, một nút chính, ba link chữ ở chân thẻ. Nền phía sau là sơn mài. Mobile: ảnh trước (48svh), thẻ nhãn chồng lên mép dưới ảnh 24px.
2. **Sản phẩm nổi bật — "Đĩa vuông, chữ thưa"**: một mảng chia đôi; trái một ảnh vuông lớn; phải: hàng tab chữ (dung tích/biến thể), tên SP 2–4 từ ở `--fs-title`, mô tả 34ch, hai ô hành động dưới cùng (Thêm vào giỏ · Xem chi tiết).
3. **Danh mục — "Chữ khổng lồ, các thanh màu"**: một từ rất lớn ("Danh mục") lấp đầy cột 560–640px, bên dưới 8 thanh màu đặc cao 56px (danh mục cha) mở tại chỗ bằng `details` liệt kê danh mục con + số sản phẩm. Line-height chữ khổng lồ **1.1** (không 0.8 như sheet — dấu tiếng Việt).
4. **Đang ưu đãi — "Ảnh và nhãn treo"** (bắt buộc): lưới 4 cột (mobile 2) các sản phẩm `Product::active()->onSale()` (nạp sẵn `images`, `brand`), sắp theo `discount_percent` giảm dần, tối đa 8; product card như §9 với nhãn "−x%" và giá khuyến mãi đỏ son; tiêu đề "Đang ưu đãi" ở `--fs-title` + link chữ "Xem tất cả" tới `/san-pham?khuyen-mai=1`. Mảng nền giấy dó để tách khỏi section danh mục phía trên. Không có sản phẩm giảm giá → ẩn cả section (không để khung trống).
5. **Câu chuyện — "Thư của người pha chế"** (TUỲ CHỌN): một tờ thư nền lụa 640px trên mảng ngọc nhạt, mép dưới răng cưa, 4–5 đoạn ngôi thứ nhất về nguồn nguyên liệu; **cuống xé** bên dưới (hàng chấm tròn 5px) chứa ô đăng ký nhận tin. *(Nội dung mẫu — cần chủ cửa hàng duyệt; không bịa danh tính người thật.)*
6. **Lời mời đảo màu (CTA)**: mảng sơn mài full-bleed ≥ 90svh, câu ý tưởng quay lại ở `--fs-display` 2 dòng, chân mảng: một câu 20px + nút champagne. Khi *Đơn chăm sóc* (TUỲ CHỌN) chưa làm: câu "Chọn đúng cho làn da của bạn" + nút "Xem sản phẩm theo loại da" → `/san-pham` (lọc loại da); khi đã làm: "Đơn chăm sóc / cho làn da của bạn" → mở *Đơn chăm sóc*.
7. **Footer — "Thương hiệu dựng khối"** (§9), chữ "Mộc Lam".

Thứ tự trên trang: 1 → 2 → 3 → 4 → (5) → 6 → 7. Tuân thủ: mỗi section có tiêu đề (trừ thư), hai section liền nhau không cùng bố cục, không khoảng trống > nửa màn hình, `h1` duy nhất ở hero.

### 10.2 Danh sách sản phẩm `/san-pham`, `/danh-muc/{slug}`
Màn thao tác. Đầu trang: breadcrumb, `h1` tên danh mục (Cormorant 40px) + số sản phẩm + mô tả 1 câu. Desktop: bộ lọc cột trái 280px (nền giấy dó, các nhóm `details`: khoảng giá, thương hiệu, loại da, đang giảm giá, còn hàng), lưới 3 cột bên phải; mobile: nút "Lọc (2)" mở ngăn kéo dưới, sắp xếp là `select`. Lọc đang áp dụng hiện thành các chữ có × phía trên lưới.

### 10.3 Chi tiết sản phẩm `/san-pham/{slug}` — mượn "split-stage"
Desktop 55/45: trái là thư viện ảnh 4:5 xếp dọc **dính** khi cuộn; phải là cột đọc: thương hiệu, tên (Cormorant `--fs-title`), rating, giá, chọn số lượng + nút "Thêm vào giỏ" (dính đáy màn hình trên mobile), rồi **tấm nhãn thành phần** (nền giấy dó, mép khía) liệt kê hoạt chất và nơi trồng — đây là ý tưởng của site trên trang sản phẩm; tiếp theo là mô tả, hướng dẫn dùng, đánh giá. Mobile: ảnh vuốt ngang trước, cột đọc sau.

### 10.4 Giỏ hàng, thanh toán
Màn thao tác, một cột trên mobile; desktop 2 cột (nội dung 8/12, tóm tắt đơn 4/12 dính). Tóm tắt đơn dùng đường kẻ 1px giữa các dòng tiền (được phép — bảng). Một nút chính "Đặt hàng". Không hiển thị gì gây xao nhãng (không banner, không gợi ý mua thêm ở bước thanh toán).

### 10.5 Tài khoản `/tai-khoan`
Mobile: danh sách mục dạng các dòng lớn 56px; desktop: menu trái 240px + nội dung. Danh sách đơn là bảng (mã, ngày, trạng thái dạng chữ + chấm màu, tổng tiền canh phải).

### 10.6 Quản trị `/admin` — sheet app "Framed sheet"
- Khung chrome hình chữ L (sidebar 228px + top bar 48px) **cùng một màu nhuộm** `#EEF1EE` (xám ngả men ngọc — "mặt" của admin), không đường kẻ giữa sidebar và top bar; nội dung trên **tờ trắng** `#FFFFFF` lùi 8px khỏi mép phải và mép dưới, viền 1px `#E6E8E6`, bo 16px (ngoại lệ bo góc chỉ trong admin), không bóng.
- Màu thương hiệu chiếm < 3% màn hình: logo + gạch chân tab 2px men ngọc. Dữ liệu mang màu.
- Tổng quan: KPI là các tab của biểu đồ (không thẻ KPI có icon); danh sách: 3–5 thẻ đếm làm bộ lọc (đơn chờ xác nhận, đang giao…); chi tiết: thẻ đầu là chính đối tượng; cài đặt: hai cột (nhãn 450px + panel 700px).
- Chữ: chỉ Be Vietnam Pro trong admin (tiêu đề trang 24px 600); Cormorant chỉ ở logo.
- Bảng: không sọc vằn, không dải tiêu đề màu, số canh phải; số 0 in là 0, thiếu dữ liệu in "—".

## 11. Điều cấm

- **Giao diện Bootstrap mặc định:** màu `#0d6efd`, bo 6px (`.rounded`), `.card` có bóng/viền, `.btn` mặc định, navbar mặc định, `.alert` có viền, `.badge` tròn viên thuốc. Mọi thứ phải đi qua token §3–§5.
- Gradient dưới mọi dạng (nền, nút, chữ, ảnh placeholder). Ngoại lệ duy nhất: lớp phủ trung tính dưới chữ đặt trên ảnh — mà theo §6 không đặt chữ lên ảnh.
- Emoji (kể cả làm icon), icon đặt trong ô màu, icon trên đầu thẻ tính năng.
- Bố cục khuôn mẫu "AI": hero căn giữa + 3 thẻ tính năng có icon + hàng logo + testimonial + bảng giá; nhãn nhỏ in hoa phía trên tiêu đề ("eyebrow"); đánh số 01/02/03; đường kẻ mảnh trang trí; glass/blur; thanh điều hướng dạng viên thuốc nổi.
- Bóng cho thứ đang nghỉ; viền 1px quanh thẻ/section.
- Chữ in hoa giãn khoảng, monospace, một từ tô màu/nghiêng trong tiêu đề, hơn 2 họ chữ, hơn 5 cỡ chữ trên một trang.
- Màu tím/chàm; nền kem ngả vàng đi với nâu đất/gạch ("bảng màu trợ lý").
- Chữ đè lên ảnh; ảnh hotlink; ảnh không có `alt`.
- Số liệu, đánh giá, báo chí, logo đối tác bịa.

## 12. Chỗ lệch có chủ đích so với taste skill

| Luật của skill | Ở dự án này | Lý do |
|---|---|---|
| Line-height chữ hiển thị 0.85–1.05 | **≥ 1.1** (chữ khổng lồ ở danh mục cũng 1.1) | Dấu tiếng Việt xếp chồng (Ữ, Ỗ, Ẫ) va dòng trên ở 0.95 — đã chụp ảnh kiểm chứng |
| Không lưới thẻ giống nhau | Lưới product card ở danh sách sản phẩm | Màn thao tác: các sản phẩm là nội dung lặp thật; giảm đơn điệu bằng dải nhãn đổi màu |
| Không viền mảnh | Viền 1px ở ô nhập, bảng, tóm tắt đơn | Đúng ngoại lệ của skill (form, bảng) và cần cho khả năng tiếp cận (WCAG 1.4.11) |
| Không badge | Badge số đếm và "−20%" | Thông tin mua hàng cần thiết; cấm badge trang trí ("Mới", "Hot") |
| Mega menu | Có mega menu ở header | Catalog 34 danh mục; menu là thứ nổi nên được có bóng |
| Một accent | Men ngọc trên nền sáng, champagne trên nền tối | Một vai trò "hành động" với hai nền; men ngọc trên sơn mài không đạt 3:1 |
| Ảnh dealt từ picsum.photos | Chỉ dùng nếu tải về dự án | Dự án cấm hotlink |

## 13. Gợi ý ánh xạ sang Bootstrap 5 (cho bước 3.2)

- Đổi biến Sass trước khi import: `$primary: #2E5A4C`, `$body-bg: #F5F2EC`, `$body-color: #1E1B18`, `$secondary: #5C544C`, `$danger: #8E2F23`, `$warning: #7A5410`, `$success: #2E5A4C`, `$font-family-sans-serif: 'Be Vietnam Pro', system-ui, sans-serif`, `$headings-font-family: 'Cormorant Garamond', Georgia, serif`, `$headings-font-weight: 600`, `$headings-line-height: 1.1`, `$border-radius: 0`, `$border-radius-sm: 0`, `$border-radius-lg: 0`, `$input-border-radius: 2px`, `$border-color: #8A8174`, `$enable-shadows: false`, `$box-shadow: none`, `$btn-padding-y`/`$btn-padding-x` cho cao 48px, `$link-decoration: underline` (link trong nội dung luôn gạch chân, `text-underline-offset: .18em` đặt trong `_typography.scss`; link điều hướng bỏ gạch chân bằng class riêng `.link-nav`), `$grid-gutter-width` theo §5.
- Chỉ import module cần: functions, variables, maps, mixins, root, reboot, type, images, containers, grid, utilities (+ api), forms, buttons, transitions, dropdown, offcanvas (mini-cart, menu mobile, bộ lọc), pagination, breadcrumb (rồi ghi đè), visually-hidden. **Không** import: card, navbar, alert, badge, carousel, toast mặc định — tự viết component theo §9.
- Token CSS (`--c-*`, `--fs-*`, `--sp-*`, `--ease-*`, `--t-*`) đặt ở `resources/scss/_tokens.scss`, mọi file khác chỉ tham chiếu token.

## 14. Quyết định chính thức, hướng thay thế và tên thương hiệu

**Đã chốt (19/10/2026):**
- **Hướng chính thức: A — Lụa · Sơn mài · Men ngọc** (toàn bộ §1–§13 của tài liệu này).
- **Tên thương hiệu: Mộc Lam** — `APP_NAME="Mộc Lam"`; wordmark theo ý tưởng ở bảng dưới (Cormorant Garamond 600, chữ thường "mộc lam", chấm men ngọc thay dấu nặng dưới "ộ"; favicon "m" + chấm).
- Hướng B, C và hai tên còn lại **giữ làm tham khảo**, không dùng.

**Hướng thay thế — tham khảo** (đã so sánh với hướng chính *Lụa · Sơn mài · Men ngọc*):

- **B — Kem mocha (ấm, gần gũi).** Mood: buổi sáng chậm, quầy mỹ phẩm ấm áp, thân thiện hơn hướng chính. Màu: nền kem `#F3EBDD`, chữ mocha `#6B4F3F` (6.30:1 — đạt), champagne `#C2A878` chỉ làm mảng, đen mềm `#2A2420` cho tiêu đề. Font: Playfair Display + Manrope (đều có tiếng Việt). Rủi ro: gần "bảng màu trợ lý" (kem + nâu đất) mà taste skill cảnh báo — dễ trông chung chung.
- **C — Phòng thí nghiệm trắng (dược mỹ phẩm, chính xác).** Mood: sạch, lạnh, đáng tin như một nhãn thuốc; hợp nếu muốn nhấn mạnh hoạt chất và nồng độ. Màu: nền `#F7F7F5`, mực `#111111`, nhấn xanh đá `#3D5A6C`, chữ phụ `#5E6266`. Font: Newsreader + Inter. Rủi ro: lạnh, ít chất "thảo mộc Việt".

**Ba tên thương hiệu hư cấu** (chưa tra cứu nhãn hiệu đã đăng ký — cần kiểm tra trước khi dùng thật):

| Tên | Ý nghĩa | Ý tưởng wordmark |
|---|---|---|
| **Mộc Lam** ✅ *đã chọn* | "Mộc" (cây cỏ, mộc mạc) + "Lam" (cao nguyên Lâm Viên, sắc xanh men) | Cormorant Garamond 600 chữ thường "mộc lam", khoảng chữ −0.02em; dấu nặng dưới "ộ" thay bằng một chấm men ngọc tròn — chi tiết nhận diện duy nhất, dùng lại làm favicon (chữ "m" + chấm). |
| **Sương Ngà** | Sương mai Đà Lạt + màu ngà của lụa | Chữ thường, Cormorant 500 khoảng chữ rộng vừa (+0.02em); cặp "Ng" nối nét đuôi; trong footer dựng khối hai từ chồng hai tầng. |
| **Lâm Thảo** | Rừng (lâm) và cỏ thuốc (thảo) — thẳng vào câu chuyện thảo mộc | Hai từ xếp tầng, căn trái, "Lâm" lớn hơn "Thảo" một bậc; dải sọc nhãn (§10.1) chạy dưới chữ như đường viền của nhãn chai — wordmark đồng thời là con tem. |

Đã cập nhật `APP_NAME` ở bước 3.2. Wordmark SVG ("mộc lam" + chấm men ngọc) làm ở bước 3.3 cùng header/footer, đặt trong `resources/views/components/`.
