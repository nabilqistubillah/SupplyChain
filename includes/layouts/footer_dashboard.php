        </main><!-- /.dash-content -->

    </div><!-- /.dash-main -->

</div><!-- /.dash-shell -->

<!-- Sidebar overlay (mobile) -->
<div id="sidebar-overlay" onclick="toggleSidebar()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:99;"></div>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('open');
    overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
}

// Auto-mark active sidebar links
document.addEventListener('DOMContentLoaded', function () {
    const url = window.location.href;
    document.querySelectorAll('.sidebar-nav a').forEach(function (link) {
        if (link.href && url.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
});
</script>

</body>
</html>
