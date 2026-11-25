<?php

namespace App\Services\Pairing;

use App\Models\Customer;
use App\Models\ConnectorPairing;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PairingCodeService
{
    private const TTL_MINUTES = 30;
    private const CODE_LENGTH = 12;
    private const ALPHABET = '23456789ABCDEFGHJKMNPQRSTVWXYZ'; // Crockford-ish, no 0/O/1/I/L to avoid transcription errors

    /**
     * Generates a single-use pairing code. The plain code is returned ONCE —
     * only its hash is stored (brief §7: "store only a secure hash where practical").
     */
    public function generate(Customer $customer, ?User $createdBy = null): array
    {
        $code = $this->randomCode();

        $pairing = ConnectorPairing::create([
            'customer_id' => $customer->id,
            'created_by_user_id' => $createdBy?->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        return [$pairing, $code];
    }

    private function randomCode(): string
    {
        $alphabet = self::ALPHABET;
        $code = '';

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return implode('-', str_split($code, 4));
    }

    /**
     * Finds the pairing matching a submitted code without a lookup column holding
     * the plain code — hashes are checked one at a time against live, unused pairings.
     * Fine at this scale; an indexed lookup hash (not reversible, distinct from the
     * bcrypt verification hash) would replace this if pairing volume ever grows.
     */
    public function findByPlainCode(string $code): ?ConnectorPairing
    {
        return ConnectorPairing::query()
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->get()
            ->first(fn (ConnectorPairing $pairing) => Hash::check($code, $pairing->code_hash));
    }
}
