            </div> <!-- fechar container-fluid -->
        </div> <!-- fechar page-content-wrapper -->
    </div> <!-- fechar d-flex wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        if(menuToggle) {
            menuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('wrapper').classList.toggle('toggled');
            });
        }
    </script>
</body>
</html>
