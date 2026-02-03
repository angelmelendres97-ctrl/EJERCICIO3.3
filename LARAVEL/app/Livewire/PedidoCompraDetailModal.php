<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\PedidoCompraResource;
use Livewire\Attributes\On;
use Filament\Notifications\Notification;

class PedidoCompraDetailModal extends Component
{
    public bool $showModal = false;
    public array $details = [];
    public float $totalGeneral = 0.0;
    public ?string $connectionName = null;
    public ?string $pedi_cod_pedi = null;
    public $id_empresa;

    #[On('open-pedido-modal')]
    public function loadDetails(string $pedi_cod_pedi, string $connectionId)
    {
        $this->pedi_cod_pedi = $pedi_cod_pedi;
        $this->id_empresa = $connectionId;
        $this->connectionName = PedidoCompraResource::getExternalConnectionName($connectionId);

        if ($this->connectionName) {
            $this->details = DB::connection($this->connectionName)
                ->table('saedped')
                ->where('dped_cod_pedi', $this->pedi_cod_pedi)
                ->get()
                ->toArray();

            $this->totalGeneral = collect($this->details)->sum(function ($item) {
                return $item->dped_can_ped * $item->dped_prc_dped;
            });
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset();
    }

    public function deleteDetail($pedi_cod_pedi, $dped_cod_prod)
    {
        $connectionName = PedidoCompraResource::getExternalConnectionName($this->id_empresa);

        if (!$connectionName) {
            return PedidoCompraResource::query()->whereRaw('1 = 0');
        }

        DB::connection($connectionName)
            ->table('saedped')
            ->where('dped_cod_pedi', $pedi_cod_pedi)
            ->where('dped_cod_prod', $dped_cod_prod)
            ->update([
                'dped_can_ent' => DB::raw('dped_can_ped')
            ]);

        // 🔥 Mostrar notificación
        Notification::make()
            ->title('Producto finalizado correctamente')
            ->success()
            ->send();

        $this->closeModal();

    }

    public function render()
    {
        return view('livewire.pedido-compra-detail-modal');
    }
}
