<?php
/**
 * ============================================
 * SGC - Footer commun
 * ============================================
 */
?>
    </div><!-- /.main-content -->
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Initialisation DataTables
        $(document).ready(function() {
            if ($('.data-table').length) {
                $('.data-table').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                    },
                    pageLength: 10,
                    responsive: true
                });
            }
        });
        
        // Confirmation de suppression
        function confirmDelete(url, message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
            Swal.fire({
                title: 'Confirmation',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d32f2f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
        
        // Notifications toast
        function showToast(type, message) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            Toast.fire({
                icon: type,
                title: message
            });
        }
        
        // Afficher les messages de session
        <?php if (isset($_SESSION['success'])): ?>
            showToast('success', '<?= addslashes($_SESSION['success']) ?>');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            showToast('error', '<?= addslashes($_SESSION['error']) ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>