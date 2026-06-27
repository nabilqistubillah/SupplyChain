</main>

<footer style="background-color: var(--primary-dark); color: #94a3b8; padding: 40px 0 20px 0; border-top: 4px solid var(--accent); margin-top: 40px;">
    <div class="container">
        
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 30px; margin-bottom: 30px;">
            
            <div style="flex: 1.5; min-width: 250px;">
                <a href="<?= BASE_URL ?>index.php" style="color: #ffffff; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; text-decoration: none; margin-bottom: 12px;">
                    <i class="fa-solid fa-anchor" style="color: var(--accent);"></i>
                    <span>BahariChain<span style="color: var(--accent); font-weight: 300;">Madura</span></span>
                </a>
                <p style="font-size: 13px; line-height: 1.6; margin-bottom: 16px; max-width: 320px;">
                    Platform reservasi pariwisata bahari terintegrasi. Mendukung pengembangan UMKM lokal dan mewujudkan ekosistem pariwisata Madura yang berkelanjutan ke kancah global.
                </p>
                <div style="display: flex; gap: 16px;">
                    <a href="#" style="color: #94a3b8; font-size: 16px; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='#94a3b8'"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" style="color: #94a3b8; font-size: 16px; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='#94a3b8'"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" style="color: #94a3b8; font-size: 16px; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='#94a3b8'"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <h5 style="color: white; font-weight: 600; margin-top: 0; margin-bottom: 16px; font-size: 15px;">Tautan Cepat</h5>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">
                    <li><a href="<?= BASE_URL ?? '' ?>index.php" style="color: #94a3b8; text-decoration: none; font-size: 13px;"><i class="fa-solid fa-angle-right" style="font-size: 11px; margin-right: 6px; color: var(--accent);"></i> Beranda</a></li>
                    <li><a href="<?= BASE_URL ?? '' ?>index.php?module=destinations" style="color: #94a3b8; text-decoration: none; font-size: 13px;"><i class="fa-solid fa-angle-right" style="font-size: 11px; margin-right: 6px; color: var(--accent);"></i> Destinasi Wisata</a></li>
                    <li><a href="<?= BASE_URL ?? '' ?>index.php?module=auth&action=register" style="color: #94a3b8; text-decoration: none; font-size: 13px;"><i class="fa-solid fa-angle-right" style="font-size: 11px; margin-right: 6px; color: var(--accent);"></i> Daftar Vendor</a></li>
                </ul>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <h5 style="color: white; font-weight: 600; margin-top: 0; margin-bottom: 16px; font-size: 15px;">Hubungi Kami</h5>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                    <li style="display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #94a3b8; line-height: 1.5;">
                        <i class="fa-solid fa-location-dot" style="margin-top: 3px; color: var(--accent); font-size: 14px;"></i>
                        <span>Pusat Pariwisata Bahari<br>Pamekasan, Jawa Timur, Indonesia</span>
                    </li>
                    <li style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #94a3b8;">
                        <i class="fa-solid fa-envelope" style="color: var(--accent); font-size: 14px;"></i>
                        <span>halo@baharichain.id</span>
                    </li>
                    <li style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #94a3b8;">
                        <i class="fa-solid fa-phone" style="color: var(--accent); font-size: 14px;"></i>
                        <span>+62 812 3456 7890</span>
                    </li>
                </ul>
            </div>
        </div>

        <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div style="font-size: 13px;">
                <p style="margin: 0;">&copy; <?= date('Y') ?> BahariChain - Platform Reservasi Pariwisata Bahari. All Rights Reserved.</p>
            </div>
            <div style="display: flex; gap: 20px; font-size: 13px;">
                <a href="#" style="color: #94a3b8; text-decoration: none;">Syarat & Ketentuan</a>
                <a href="#" style="color: #94a3b8; text-decoration: none;">Kebijakan Privasi</a>
            </div>
        </div>
        
    </div>
</footer>

</body>
</html>
