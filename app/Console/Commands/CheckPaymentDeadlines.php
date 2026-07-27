<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentDeadlineNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CheckPaymentDeadlines extends Command
{
    protected $signature = 'payments:check-deadlines {--days=3 : Nombre de jours avant échéance pour envoyer un rappel}';

    protected $description = 'Check payment deadlines, mark overdue payments as late, and notify concerned users.';

    public function handle(): int
    {
        $today = now()->startOfDay();
        $limitDate = now()->addDays((int) $this->option('days'))->endOfDay();

        // Payments crossing their due date for the first time today —
        // transition due -> late, which also triggers their first
        // 'late' notification.
        $newlyLatePayments = Payment::query()
            ->where('status', 'due')
            ->whereDate('due_date', '<', $today->toDateString())
            ->get();

        foreach ($newlyLatePayments as $payment) {
            $payment->forceFill([
                'status' => 'late',
            ])->save();

            $this->notifyUsers($payment, 'late');
        }

        // Payments that were ALREADY 'late' on a previous run and are
        // still unpaid: re-notify every time this command runs, until
        // someone records a payment against them (status becomes
        // 'paid') or the payment is cancelled. Previously this command
        // only ever queried status = 'due' to find late payments, so
        // once a payment flipped to 'late' it fell out of that query
        // forever and got exactly one reminder, total — contradicting
        // the requirement that clients keep getting reminded until
        // they actually pay. notifyOnceToday() below still guards
        // against sending more than one notification per user per day
        // even though this command may run more than once a day.
        $stillLatePayments = Payment::query()
            ->where('status', 'late')
            ->get();

        foreach ($stillLatePayments as $payment) {
            $this->notifyUsers($payment, 'late');
        }

        $dueSoonPayments = Payment::query()
            ->where('status', 'due')
            ->whereDate('due_date', '>=', $today->toDateString())
            ->whereDate('due_date', '<=', $limitDate->toDateString())
            ->get();

        foreach ($dueSoonPayments as $payment) {
            $this->notifyUsers($payment, 'due_soon');
        }

        $this->info("Paiements passés en retard aujourd’hui : {$newlyLatePayments->count()}");
        $this->info("Rappels renvoyés pour paiements déjà en retard : {$stillLatePayments->count()}");
        $this->info("Rappels de paiement envoyés : {$dueSoonPayments->count()}");

        return self::SUCCESS;
    }

    private function notifyUsers(Payment $payment, string $kind): void
    {
        $client = Client::query()->find($payment->client_id);

        $users = collect();

        $admins = User::query()
            ->where('role', 'admin')
            ->get();

        $users = $users->merge($admins);

        if ($client?->user_id) {
            $clientUser = User::query()->find($client->user_id);

            if ($clientUser) {
                $users->push($clientUser);
            }
        }

        $commercial = $this->responsibleCommercial($client);

        if ($commercial) {
            $users->push($commercial);
        }

        $users
            ->unique('id')
            ->each(function (User $user) use ($payment, $kind) {
                $this->notifyOnceToday($user, $payment, $kind);
            });
    }

    private function responsibleCommercial(?Client $client): ?User
    {
        if (!$client) {
            return null;
        }

        if ($client->prospect_id) {
            $commercialId = DB::table('prospects')
                ->where('id', $client->prospect_id)
                ->value('assigned_to');

            if ($commercialId) {
                return User::query()
                    ->where('role', 'commercial')
                    ->where('id', $commercialId)
                    ->first();
            }
        }

        if ($client->main_campus_id) {
            return User::query()
                ->where('role', 'commercial')
                ->whereHas('staffAssignments', function ($query) use ($client) {
                    $query->where('campus_id', $client->main_campus_id);
                })
                ->first();
        }

        return null;
    }

    private function notifyOnceToday(User $user, Payment $payment, string $kind): void
    {
        $alreadySentToday = $user->notifications()
            ->where('type', PaymentDeadlineNotification::class)
            ->where('created_at', '>=', now()->startOfDay())
            ->get()
            ->contains(function ($notification) use ($payment, $kind) {
                return (int) ($notification->data['payment_id'] ?? 0) === (int) $payment->id
                    && ($notification->data['kind'] ?? null) === $kind;
            });

        if (!$alreadySentToday) {
            $user->notify(new PaymentDeadlineNotification($payment, $kind));
        }
    }
}