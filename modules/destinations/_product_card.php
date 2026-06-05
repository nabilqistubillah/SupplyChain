<?php
// modules/destinations/_product_card.php
// Berkas ini di-include di dalam loop daftar produk di halaman detail destinasi.
?>
<div class="card" style="display: flex; flex-direction: column; overflow: hidden; padding: 0;">
    <!-- Icon representing category -->
    <div style="height: 120px; background: linear-gradient(135deg, #f1f5f9, #e2e8f0); display: flex; align-items: center; justify-content: center; position: relative;">
        <?php if ($p['category_name'] === 'Transportasi'): ?>
            <i class="fa-solid fa-car-side" style="font-size: 48px; color: var(--primary); opacity: 0.4;"></i>
        <?php elseif ($p['category_name'] === 'Akomodasi'): ?>
            <i class="fa-solid fa-bed" style="font-size: 48px; color: var(--primary); opacity: 0.4;"></i>
        <?php else: ?>
            <i class="fa-solid fa-cookie-bite" style="font-size: 48px; color: var(--primary); opacity: 0.4;"></i>
        <?php endif; ?>
        
        <div style="position: absolute; bottom: 8px; left: 12px; background: white; padding: 2px 8px; border-radius: 4px; border: 1px solid var(--border); font-size: 11px; font-weight: 500;">
            <i class="fa-solid fa-star" style="color: #f59e0b; margin-right: 3px;"></i><?= number_format($p['vendor_rating'], 1) ?>
        </div>
    </div>
    
    <div style="padding: 16px; display: flex; flex-direction: column; flex: 1;">
        <span style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase; font-weight: 600;"><?= esc($p['business_name']) ?></span>
        <h4 style="font-weight: 700; color: var(--primary); margin-top: 4px; margin-bottom: 8px; font-size: 15px;"><?= esc($p['name']) ?></h4>
        <p style="font-size: 13px; color: var(--text-secondary); flex: 1; margin-bottom: 16px;">
            <?= esc(substr($p['description'], 0, 80)) ?>...
        </p>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; border-top: 1px solid var(--border); padding-top: 12px;">
            <div>
                <span style="font-size: 10px; color: var(--text-secondary); display: block; text-transform: uppercase;">Harga</span>
                <span style="font-weight: 700; color: var(--primary-light); font-size: 15px;">Rp <?= number_format($p['price'], 0, ',', '.') ?></span>
            </div>
            
            <?php if (is_authenticated() && $_SESSION['role'] === 'buyer'): ?>
                <!-- Form pemesanan untuk Buyer (B2B Supply Chain) -->
                <form action="<?= BASE_URL ?>index.php?module=orders&action=add_to_cart" method="POST" style="margin: 0;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="destination_id" value="<?= $destId ?>">
                    <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px; border-radius: 6px;">
                        <i class="fa-solid fa-cart-plus"></i> Pesan B2B
                    </button>
                </form>
            <?php else: ?>
                <!-- Tombol kontak vendor untuk Wisatawan umum / guest -->
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $p['contact']) ?>" target="_blank" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; border-radius: 6px; color: #25d366; border-color: #25d366; background: #fff;">
                    <i class="fa-brands fa-whatsapp"></i> Hubungi
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
