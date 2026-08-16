<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $currencyId = DB::table('currencies')
            ->where('default', true)
            ->value('id') ?? DB::table('currencies')
            ->where('status', true)
            ->value('id');

        if (! $currencyId) {
            return;
        }

        $autoApprove = (bool) DB::table('settings')
            ->where('key', 'agent_auto_approve')
            ->value('val');

        $status = $autoApprove ? 'approved' : 'pending';
        $now    = now();

        DB::table('users')
            ->where('role', 'agent')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('agents')
                    ->whereColumn('agents.user_id', 'users.id')
                    ->whereNull('agents.deleted_at');
            })
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($currencyId, $status, $now) {
                foreach ($users as $user) {
                    DB::table('agents')->insert([
                        'user_id'     => $user->id,
                        'agent_code'  => $this->uniqueAgentCode(),
                        'currency_id' => $currencyId,
                        'agent_name'  => $this->agentName($user),
                        'commission'  => (float) DB::table('settings')->where('key', 'agent_default_commission')->value('val'),
                        'status'      => $status,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function uniqueAgentCode(): string
    {
        $prefix = (string) (DB::table('settings')->where('key', 'agent_code_prefix')->value('val') ?: 'AGT-');

        do {
            $code = $prefix.strtoupper(Str::random(10));
        } while (DB::table('agents')->where('agent_code', $code)->exists());

        return $code;
    }

    private function agentName(object $user): string
    {
        $name = trim((string) $user->first_name.' '.(string) $user->last_name);

        return $name !== '' ? $name : (string) $user->username;
    }
};
