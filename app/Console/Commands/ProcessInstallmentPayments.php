<?php

namespace App\Console\Commands;

use App\Models\Perso;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessInstallmentPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:installment-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traite les paiements échelonnés des résidences chaque jour.';

    /**
     * Execute the console command.
     * EN PAUSE
     */
    public function handle()
    {
        $today = now()->format('Y-m-d');
        $engagements = Perso::whereHas('residences', function ($query) use ($today) {
            $query->where('remaining_payments', '>', 0)
                ->whereDate('next_payment_due', '<=', $today);
        })->with('residences')->get();

        foreach ($engagements as $perso) {
            foreach ($perso->residences as $residence) {
                if ($residence->pivot->remaining_payments > 0 && $residence->pivot->next_payment_due->isToday()) {
                    $montantQuotidien = ($residence->prix_achat + $residence->taxe_mensuelle * 2) / 3;
                    if ($perso->money >= $montantQuotidien) {
                        // Mise à jour de l'argent
                        $perso->decrement('money', $montantQuotidien);
                        // Mise à jour de la relation pivot
                        DB::table('perso_residences')
                            ->where('perso_id', $perso->id)
                            ->where('residence_id', $residence->id)
                            ->decrement('remaining_payments');
                        DB::table('perso_residences')
                            ->where('perso_id', $perso->id)
                            ->where('residence_id', $residence->id)
                            ->update(['next_payment_due' => now()->addDay()]);
                    } else {
                        $this->info("Paiement échelonné non effectué pour l'utilisateur {$perso->id} faute de fonds suffisants.");
                    }
                }
            }
        }

        $this->info('Les paiements échelonnés ont été traités.');
    }
}
