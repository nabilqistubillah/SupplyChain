<?php
// modules/destinasi/index.php
// BahariChain: Halaman Eksplorasi Destinasi Wisata (UI Premium)

$pageTitle = "Eksplorasi Destinasi Wisata Bahari Madura";

// Data Dummy Destinasi
$destinations = [
    [
        'id' => 1,
        'name' => 'Pulau Gili Labak',
        'location' => 'Sumenep',
        'category' => 'Pulau',
        'rating' => 4.9,
        'price' => 20000,
        'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80',
        'desc' => 'Surga bawah laut tersembunyi dengan pasir putih bersih dan air kristal.'
    ],
    [
        'id' => 2,
        'name' => 'Pantai Lombang',
        'location' => 'Sumenep',
        'category' => 'Pantai',
        'rating' => 4.7,
        'price' => 15000,
        'image' => 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=600&q=80',
        'desc' => 'Dikenal dengan barisan pohon cemara udang yang unik di sepanjang bibir pantai.'
    ],
    [
        'id' => 3,
        'name' => 'Pantai Slopeng',
        'location' => 'Sumenep',
        'category' => 'Pantai',
        'rating' => 4.5,
        'price' => 10000,
        'image' => 'https://images.unsplash.com/photo-1506929113675-b55f9d3bb55c?auto=format&fit=crop&w=600&q=80',
        'desc' => 'Pantai dengan gundukan pasir putih yang luas menyerupai gurun pasir.'
    ],
    [
        'id' => 4,
        'name' => 'Pulau Gili Iyang',
        'location' => 'Sumenep',
        'category' => 'Pulau',
        'rating' => 4.8,
        'price' => 25000,
        'image' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=600&q=80',
        'desc' => 'Pulau dengan kadar oksigen tertinggi kedua di dunia setelah Yordania.'
    ],
    [
        'id' => 5,
        'name' => 'Pantai Camplong',
        'location' => 'Sampang',
        'category' => 'Pantai',
        'rating' => 4.4,
        'price' => 15000,
        'image' => 'https://images.unsplash.com/photo-1520942702018-0862200e6873?auto=format&fit=crop&w=600&q=80',
        'desc' => 'Destinasi wisata keluarga populer dengan fasilitas lengkap dan sunset yang indah.'
    ],
    [
        'id' => 6,
        'name' => 'Pantai Siring Kemuning',
        'location' => 'Bangkalan',
        'category' => 'Pantai',
        'rating' => 4.3,
        'price' => 10000,
        'image' => 'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?auto=format&fit=crop&w=600&q=80',
        'desc' => 'Pantai tenang yang dikelilingi perbukitan hijau, cocok untuk relaksasi.'
    ]
];

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- HEADER & SEARCH FILTER SECTION -->
<div style="margin-bottom: 40px; text-align: center;">
    <h1 style="font-size: 36px; font-weight: 800; color: var(--primary); margin-bottom: 30px; letter-spacing: -1px;">
        Eksplorasi Destinasi Wisata <span style="color: var(--accent);">Bahari Madura</span>
    </h1>

    <div class="card" style="padding: 24px; box-shadow: var(--shadow-md); border: none; background: #fff;">
        <form style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 15px; align-items: end;">
            <!-- Search Text -->
            <div style="text-align: left;">
                <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Cari Destinasi</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" placeholder="Cari nama destinasi atau lokasi..." 
                           style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; focus: border-color: var(--accent);">
                </div>
            </div>

            <!-- Kategori -->
            <div style="text-align: left;">
                <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Kategori</label>
                <select style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; background: #fff;">
                    <option value="">Semua Kategori</option>
                    <option value="Pantai">Pantai</option>
                    <option value="Pulau">Pulau</option>
                    <option value="Budaya">Budaya</option>
                </select>
            </div>

            <!-- Lokasi -->
            <div style="text-align: left;">
                <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Lokasi (Kabupaten)</label>
                <select style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; background: #fff;">
                    <option value="">Semua Kabupaten</option>
                    <option value="Bangkalan">Bangkalan</option>
                    <option value="Sampang">Sampang</option>
                    <option value="Pamekasan">Pamekasan</option>
                    <option value="Sumenep">Sumenep</option>
                </select>
            </div>

            <!-- Urutan -->
            <div style="text-align: left;">
                <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; display: block;">Urutkan</label>
                <select style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; background: #fff;">
                    <option value="terpopuler">Terpopuler</option>
                    <option value="terbaru">Terbaru</option>
                    <option value="harga_rendah">Harga Terendah</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- GRID KARTU DESTINASI SECTION -->
<div class="grid grid-3" style="gap: 30px; margin-bottom: 50px;">
    <?php foreach ($destinations as $dest): ?>
        <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.3s ease, box-shadow 0.3s ease; border: none; box-shadow: var(--shadow-sm);" 
             onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='var(--shadow-lg)';" 
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-sm)';">
            
            <!-- Thumbnail -->
            <div style="height: 200px; width: 100%; position: relative; overflow: hidden;">
                <img src="<?= $dest['image'] ?>" alt="<?= $dest['name'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.9); padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 700; color: var(--primary);">
                    <i class="fa-solid fa-star" style="color: #f59e0b;"></i> <?= $dest['rating'] ?>
                </div>
                <div style="position: absolute; bottom: 12px; left: 12px; background: var(--accent); color: white; padding: 2px 10px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                    <?= $dest['category'] ?>
                </div>
            </div>

            <!-- Content -->
            <div style="padding: 24px; flex-grow: 1; display: flex; flex-direction: column;">
                <h3 style="font-size: 20px; font-weight: 700; color: var(--primary); margin-bottom: 8px;"><?= $dest['name'] ?></h3>
                
                <div style="display: flex; align-items: center; gap: 6px; color: var(--text-secondary); font-size: 13px; margin-bottom: 12px;">
                    <i class="fa-solid fa-location-dot" style="color: #ef4444;"></i> <?= $dest['location'] ?>, Madura
                </div>

                <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 20px; flex-grow: 1;">
                    <?= $dest['desc'] ?>
                </p>

                <div style="border-top: 1px solid #f1f5f9; padding-top: 15px; margin-top: auto; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <span style="font-size: 12px; color: var(--text-secondary);">Tiket Masuk:</span>
                    <span style="font-weight: 700; color: var(--primary); font-size: 16px;">Rp <?= number_format($dest['price'], 0, ',', '.') ?></span>
                </div>

                <a href="<?= BASE_URL ?>index.php?module=destinasi&action=detail&id=<?= $dest['id'] ?>" 
                   class="btn btn-primary" 
                   style="width: 100%; text-align: center; font-weight: 600; padding: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Lihat Detail
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- PAGINASI SECTION -->
<div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-bottom: 60px;">
    <button style="width: 40px; height: 40px; border: 1px solid #e2e8f0; background: #fff; border-radius: 8px; color: var(--text-secondary); cursor: not-allowed;" disabled>
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button style="width: 40px; height: 40px; background: var(--primary); color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">1</button>
    <button style="width: 40px; height: 40px; background: #fff; color: var(--text-secondary); border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 600; cursor: pointer;" 
            onmouseover="this.style.borderColor= 'var(--accent)'; this.style.color='var(--accent)';" 
            onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='var(--text-secondary)';">2</button>
    <button style="width: 40px; height: 40px; background: #fff; color: var(--text-secondary); border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 600; cursor: pointer;"
            onmouseover="this.style.borderColor= 'var(--accent)'; this.style.color='var(--accent)';" 
            onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='var(--text-secondary)';">3</button>
    <button style="width: 40px; height: 40px; border: 1px solid #e2e8f0; background: #fff; border-radius: 8px; color: var(--text-secondary); cursor: pointer;"
            onmouseover="this.style.borderColor= 'var(--accent)'; this.style.color='var(--accent)';" 
            onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='var(--text-secondary)';">
        <i class="fa-solid fa-chevron-right"></i>
    </button>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
