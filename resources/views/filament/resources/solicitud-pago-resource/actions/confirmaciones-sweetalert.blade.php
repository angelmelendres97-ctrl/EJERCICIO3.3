<div
    x-data
    x-init="
        const loadSwal = () => new Promise((resolve, reject) => {
            if (window.Swal) return resolve(window.Swal);

            // Evita cargarlo 2 veces
            if (document.getElementById('swal2-cdn')) {
                const t = setInterval(() => {
                    if (window.Swal) { clearInterval(t); resolve(window.Swal); }
                }, 50);
                setTimeout(() => { clearInterval(t); reject(new Error('Swal no cargó')); }, 5000);
                return;
            }

            const s = document.createElement('script');
            s.id = 'swal2-cdn';
            s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js';
            s.async = true;
            s.onload = () => resolve(window.Swal);
            s.onerror = () => reject(new Error('No se pudo cargar SweetAlert2'));
            document.head.appendChild(s);
        });

        const confirmStep = async (Swal, options) => {
            const result = await Swal.fire({
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                ...options,
            });
            return result.isConfirmed;
        };

        const runConfirmFlow = async (detail) => {
            const Swal = await loadSwal().catch(() => null);
            const component = window.Livewire?.find?.(detail?.componentId);

            if (!Swal || !component) return;

            const actionIndex = (detail?.actionIndex ?? 0);

            if (detail?.requiereAjuste) {
                const ok = await confirmStep(Swal, {
                    title: 'Confirmar ajuste del monto',
                    text: 'El monto se ajustará automáticamente al total de las facturas seleccionadas.',
                });
                if (!ok) return;

                await component.set(`mountedTableActionsData.${actionIndex}.confirmar_ajuste_monto`, true);
            }

            if (detail?.requiereLiberacion) {
                const ok = await confirmStep(Swal, {
                    title: 'Confirmar liberación de facturas',
                    text: 'Liberarás las facturas no seleccionadas y quedarán disponibles para una nueva solicitud de pago.',
                });
                if (!ok) return;

                await component.set(`mountedTableActionsData.${actionIndex}.confirmar_liberacion`, true);
            }

            // Re-ejecuta la acción del modal
            component.call('callMountedTableAction');
        };

        // ✅ 1) Evento Livewire (esto es lo que dispara $livewire->dispatch(...) en Filament v3)
        document.addEventListener('livewire:init', () => {
            Livewire.on('solicitud-pago:confirmar-aprobacion', (payload) => runConfirmFlow(payload));
        });

        // ✅ 2) (Opcional) fallback si algún día lo disparas como browser event
        window.addEventListener('solicitud-pago:confirmar-aprobacion', (event) => runConfirmFlow(event.detail));
    "
></div>
