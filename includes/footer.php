</main>

<footer style="background-color: var(--primary-dark); color: #94a3b8; padding: 40px 0; border-top: 1px solid var(--primary);">
    <div class="container">
        <div style="display: flex; flex-direction: column; md-flex-direction: row; justify-content: space-between; align-items: center; gap: 20px;">
            <div>
                <a href="<?= BASE_URL ?>index.php" style="color: #ffffff; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-anchor" style="color: var(--accent);"></i>
                    <span>BahariChain<span style="color: var(--accent); font-weight: 300;">Madura</span></span>
                </a>
                <p style="font-size: 12px; margin-top: 8px; max-width: 320px;">Connecting Local Vendors to Global Tourism. Mendukung pengembangan pariwisata berkelanjutan dan pemberdayaan UMKM lokal Madura.</p>
            </div>
            <div style="font-size: 13px; text-align: center; md-text-align: right;">
                <div style="display: flex; gap: 20px; justify-content: center; md-justify-content: flex-end; margin-bottom: 12px; flex-wrap: wrap;">
                    <a href="<?= BASE_URL ?>index.php?module=dashboard" style="color: #94a3b8; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#94a3b8'">
                        <i class="fa-solid fa-home"></i> Beranda
                    </a>
                    <a href="<?= BASE_URL ?>index.php?module=destinations" style="color: #94a3b8; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#94a3b8'">
                        <i class="fa-solid fa-map-marked-alt"></i> Destinasi
                    </a>
                    <a href="<?= BASE_URL ?>index.php?module=kemitraan" style="color: #94a3b8; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#94a3b8'">
                        <i class="fa-solid fa-handshake"></i> Kemitraan
                    </a>
                </div>
                <p>&copy; <?= date('Y') ?> BahariChain - Platform Reservasi Pariwisata Bahari. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
