<div x-data="{
    init() {
        window.addEventListener('fill-from-tree', async (event) => {
            const data = event.detail.data;
            
            // Construye un selector de CSS válido para el atributo wire:id
            const selector = '[' + CSS.escape('wire:id') + ']';
            const wireEl = $el.closest(selector);

            if (!wireEl) {
                console.error('No se pudo encontrar el elemento raíz del componente Livewire.');
                return;
            }

            // Encuentra el componente Livewire principal que contiene el formulario.
            // Para cerrar el modal, necesitamos usar el componente padre que maneja la acción.
            // El componente del formulario principal de la página es el que debe cerrar el modal.
            const component = Livewire.find(wireEl.getAttribute('wire:id'));

            // 1. Muestra una notificación de carga
            new FilamentNotification()
                .title('Cargando selección')
                .body('Por favor espere mientras se asignan los valores.')
                .info()
                .send();

            // Función de ayuda para la espera
            const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

            // 3. Asigna los valores secuencialmente al formulario principal
            if (data.linea) {
                component.set('data.linea', data.linea);
                await sleep(500); // Espera para que se carguen las opciones del grupo
            }

            if (data.grupo) {
                component.set('data.grupo', data.grupo);
                await sleep(500); // Espera para que se carguen las opciones de la categoría
            }

            if (data.categoria) {
                component.set('data.categoria', data.categoria);
                await sleep(500); // Espera para que se carguen las opciones de la marca
            }

            if (data.marca) {
                component.set('data.marca', data.marca);
                await sleep(200);
            }

            // 4. Muestra un mensaje de éxito
            new FilamentNotification()
                .title('Selección completada')
                .body('Los campos del producto se han actualizado.')
                .success()
                .send();
            
            // 5. CIERRE DEL MODAL: Envía el evento de cierre de modal a Livewire/Filament.
            // El ID debe coincidir con el nombre de tu acción: 'search-inventory-tree-action'
            $dispatch('close-modal', { id: 'search-inventory-tree-action' });
        });
    }
}">
</div>