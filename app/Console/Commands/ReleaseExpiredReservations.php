<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\GeneratorStatusHistory;
use Illuminate\Support\Facades\DB;

class ReleaseExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired generator reservations that are older than 4 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredReservations = Reservation::with('generator')
            ->where('is_active', true)
            ->where('expires_at', '<', now())
            ->get();

        if ($expiredReservations->isEmpty()) {
            $this->info('No expired reservations found.');
            return;
        }

        $count = 0;

        foreach ($expiredReservations as $reservation) {
            DB::beginTransaction();
            try {
                // 1. Desactivar la reserva
                $reservation->update(['is_active' => false]);

                $systemUser = \App\Models\User::where('role', 'admin')->first();

                // 2. Liberar el equipo (asegurar que está en estado Separado actualmente)
                if ($reservation->generator && $reservation->generator->status === 'Separado') {
                    $oldStatus = $reservation->generator->status;
                    
                    $reservation->generator->update(['status' => 'Disponible']);

                    // 3. Registrar en el historial de estados
                    GeneratorStatusHistory::create([
                        'generator_id' => $reservation->generator->id,
                        'user_id' => $systemUser ? $systemUser->id : 1, // Usuario que registra la acción (Admin)
                        'previous_status' => $oldStatus,
                        'new_status' => 'Disponible',
                        'comment' => 'Separación liberada automáticamente por el sistema tras superar las 4 horas (Cliente: ' . $reservation->client_name . ').'
                    ]);
                }

                DB::commit();
                $count++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Failed to release reservation ID: ' . $reservation->id . '. Error: ' . $e->getMessage());
            }
        }

        $this->info("Successfully released {$count} expired reservations.");
    }
}
