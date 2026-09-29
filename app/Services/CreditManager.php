<?php

namespace App\Services;

use App\Models\User;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

class CreditManager
{
    /**
     * Checks if a user has sufficient credits for an action.
     */
    public static function hasCredits(User $user, int $cost): bool
    {
        return $user->credits >= $cost;
    }

    /**
     * Deducts credits from a user's account and logs a transaction.
     */
    public static function deduct(User $user, int $cost, string $actionType, string $description = null): bool
    {
        if (!self::hasCredits($user, $cost)) {
            return false;
        }

        DB::transaction(function () use ($user, $cost, $actionType, $description) {
            // Decrement user credits
            $user->decrement('credits', $cost);

            // Log the transaction
            CreditTransaction::create([
                'user_id' => $user->id,
                'amount' => -$cost,
                'action_type' => $actionType,
                'description' => $description ?? "Consumed {$cost} credits for " . ucfirst($actionType)
            ]);
        });

        return true;
    }

    /**
     * Adds credits to a user's account and logs a transaction.
     */
    public static function add(User $user, int $amount, string $actionType, string $description = null): void
    {
        DB::transaction(function () use ($user, $amount, $actionType, $description) {
            $user->increment('credits', $amount);

            CreditTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'action_type' => $actionType,
                'description' => $description ?? "Added {$amount} credits for " . ucfirst($actionType)
            ]);
        });
    }

    /**
     * Resets a user's credits to 5000 (Daily reset).
     */
    public static function resetDaily(User $user): void
    {
        DB::transaction(function () use ($user) {
            $currentCredits = $user->credits;
            $resetAmount = 5000 - $currentCredits;

            if ($resetAmount !== 0) {
                $user->update(['credits' => 5000]);

                CreditTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $resetAmount,
                    'action_type' => 'daily_reset',
                    'description' => "Daily scheduler reset to 5000 credits"
                ]);
            }
        });
    }
}
