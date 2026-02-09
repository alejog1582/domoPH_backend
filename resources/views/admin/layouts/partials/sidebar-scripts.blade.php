<script>
    // Función para alternar submenús
    function toggleSubmenu(menuId) {
        const menu = document.getElementById(menuId);
        const icon = document.getElementById(menuId + '-icon');
        
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            if (icon) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        } else {
            menu.classList.add('hidden');
            if (icon) {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
    }

    // Mantener abierto el submenú si la ruta activa está dentro de él
    document.addEventListener('DOMContentLoaded', function() {
        @if(request()->routeIs('admin.unidades.*') || request()->routeIs('admin.copropiedad.*') || request()->routeIs('admin.residentes.*') || request()->routeIs('admin.mascotas.*') || request()->routeIs('admin.zonas-sociales.*'))
            const copropiedadMenu = document.getElementById('copropiedad-menu');
            const copropiedadIcon = document.getElementById('copropiedad-menu-icon');
            if (copropiedadMenu) {
                copropiedadMenu.classList.remove('hidden');
                if (copropiedadIcon) {
                    copropiedadIcon.classList.remove('fa-chevron-down');
                    copropiedadIcon.classList.add('fa-chevron-up');
                }
            }
        @endif
        
        @if(request()->routeIs('admin.cuotas-administracion.*') || request()->routeIs('admin.cartera.*') || request()->routeIs('admin.cuentas-cobro.*') || request()->routeIs('admin.recaudos.*') || request()->routeIs('admin.acuerdos-pagos.*'))
            const carteraMenu = document.getElementById('cartera-menu');
            const carteraIcon = document.getElementById('cartera-menu-icon');
            if (carteraMenu) {
                carteraMenu.classList.remove('hidden');
                if (carteraIcon) {
                    carteraIcon.classList.remove('fa-chevron-down');
                    carteraIcon.classList.add('fa-chevron-up');
                }
            }
        @endif

        @if(request()->routeIs('admin.comunicados.*') || request()->routeIs('admin.correspondencias.*') || request()->routeIs('admin.visitas.*') || request()->routeIs('admin.autorizaciones.*'))
            const gestionMenu = document.getElementById('gestion-menu');
            const gestionIcon = document.getElementById('gestion-menu-icon');
            if (gestionMenu) {
                gestionMenu.classList.remove('hidden');
                if (gestionIcon) {
                    gestionIcon.classList.remove('fa-chevron-down');
                    gestionIcon.classList.add('fa-chevron-up');
                }
            }
        @endif

        @if(request()->routeIs('admin.llamados-atencion.*') || request()->routeIs('admin.pqrs.*'))
            const convivenciaMenu = document.getElementById('convivencia-menu');
            const convivenciaIcon = document.getElementById('convivencia-menu-icon');
            if (convivenciaMenu) {
                convivenciaMenu.classList.remove('hidden');
                if (convivenciaIcon) {
                    convivenciaIcon.classList.remove('fa-chevron-down');
                    convivenciaIcon.classList.add('fa-chevron-up');
                }
            }
        @endif

        @if(request()->routeIs('admin.ecommerce.*') || request()->routeIs('admin.ecommerce-categorias.*'))
            const ecommerceMenu = document.getElementById('ecommerce-menu');
            const ecommerceIcon = document.getElementById('ecommerce-menu-icon');
            if (ecommerceMenu) {
                ecommerceMenu.classList.remove('hidden');
                if (ecommerceIcon) {
                    ecommerceIcon.classList.remove('fa-chevron-down');
                    ecommerceIcon.classList.add('fa-chevron-up');
                }
            }
        @endif

        @if(request()->routeIs('admin.configuraciones-propiedad.*'))
            const configuracionesMenu = document.getElementById('configuraciones-menu');
            const configuracionesIcon = document.getElementById('configuraciones-menu-icon');
            if (configuracionesMenu) {
                configuracionesMenu.classList.remove('hidden');
                if (configuracionesIcon) {
                    configuracionesIcon.classList.remove('fa-chevron-down');
                    configuracionesIcon.classList.add('fa-chevron-up');
                }
            }
        @endif
    });
</script>
