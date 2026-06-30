<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\CheckoutAcceptance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GuestAcceptanceController extends Controller
{
    /**
     * Passwordless entry point for the accept/decline flow.
     *
     * The route is protected by the `signed` middleware, so reaching this method means the
     * signature (HMAC of the APP_KEY) is valid and not expired. We then authenticate the user
     * the item was assigned to — a "magic link" login — and hand off to the normal acceptance
     * page, which renders the EULA + signature pad. This lets an employee accept/decline an
     * assigned asset directly from the checkout email without knowing a password.
     *
     * Security hardening:
     *  - The resulting session is flagged so RestrictMagicLinkSession confines it to the
     *    accept/decline pages only (no access to the rest of the app).
     *  - Privileged (super) users are never logged in passwordlessly — they must use a password.
     *  - The link only works while the acceptance is still pending (single-use in practice).
     *  - Activation/2FA are still enforced by the normal web middleware stack.
     */
    public function redirectToAcceptance(Request $request, $acceptanceId): RedirectResponse
    {
        $acceptance = CheckoutAcceptance::find($acceptanceId);

        if (! $acceptance) {
            return redirect()->route('login')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        // #5 — Single-use in practice: once accepted/declined, the link no longer logs anyone in.
        if (! $acceptance->isPending()) {
            return redirect()->route('login')->with('error', trans('general.magic_link_already_processed'));
        }

        $assignedUser = $acceptance->assignedTo;

        if (! $assignedUser) {
            return redirect()->route('login')->with('error', trans('admin/users/message.user_not_found'));
        }

        // #2 — Never grant a passwordless session to a privileged account.
        if ($assignedUser->isSuperUser()) {
            return redirect()->route('login')->with('error', trans('general.magic_link_login_required'));
        }

        // Magic-link login: authenticate as the assigned user for this request's session.
        Auth::login($assignedUser);
        $request->session()->regenerate();

        // #1 — Confine this session to the accept/decline pages.
        $request->session()->put('magic_link_acceptance', $acceptance->id);

        // #6 — Audit trail for the passwordless access. Logged at `warning` level on purpose:
        // this app's log threshold is `warning`, and a passwordless login is security-relevant
        // enough to always be recorded.
        Log::warning('AUDIT: magic-link acceptance access', [
            'acceptance_id' => $acceptance->id,
            'user_id' => $assignedUser->id,
            'username' => $assignedUser->username,
            'ip' => $request->ip(),
        ]);

        // Pre-select accept vs decline based on which button was pressed in the email.
        $action = $request->query('action');
        $params = ['acceptance' => $acceptance->id];
        if (in_array($action, ['accepted', 'declined'], true)) {
            $params['action'] = $action;
        }

        return redirect()->route('account.accept.item', $params);
    }
}
