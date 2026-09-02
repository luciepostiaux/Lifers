<?php

namespace App\Http\Controllers;

use App\Models\AccountBan;
use App\Services\AccountBanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminBanController extends Controller
{
    public function store(Request $request, AccountBanService $bans): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
            'block_known_ip_addresses' => ['sometimes', 'boolean'],
        ]);

        $ban = $bans->ban(
            $request->user(),
            $validated['email'],
            $validated['reason'],
            (bool) ($validated['block_known_ip_addresses'] ?? false),
        );

        $message = $ban->ipAddresses->isNotEmpty()
            ? 'Le compte, son e-mail et ses adresses IP connues sont maintenant bloqués.'
            : 'Cette adresse e-mail est maintenant bloquée.';

        return back()->with('success', $message);
    }

    public function destroy(Request $request, AccountBan $ban, AccountBanService $bans): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $bans->revoke($request->user(), $ban, $validated['reason']);

        return back()->with('success', 'Le bannissement a été levé.');
    }
}
