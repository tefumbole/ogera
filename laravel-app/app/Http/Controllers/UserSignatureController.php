<?php

namespace App\Http\Controllers;

use App\GeneralSetting;
use App\Support\RoleAccess;
use App\Support\UserSignature;
use App\Support\WhatsAppMessage;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Collects a user's own handwritten signature.
 *
 * An admin creating an account for somebody else rarely has their signature to
 * hand, so instead of blocking on it they send a link. The recipient draws it
 * once on their phone and it is stored against their name, ready to be stamped
 * on every document they go on to issue.
 */
class UserSignatureController extends Controller
{
    /** Admin action: (re)send the signing link to a user. */
    public function send($id)
    {
        if (! RoleAccess::allows('users-edit')) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $user = User::where('is_deleted', false)->find($id);
        if (! $user) {
            return redirect()->back()->with('not_permitted', 'User not found.');
        }

        $result = $this->dispatchRequest($user);

        return redirect()->back()->with($result['ok'] ? 'message1' : 'message3', $result['message']);
    }

    /**
     * Issue a fresh token and deliver the link over whichever channels the
     * user can be reached on.
     *
     * @return array{ok: bool, message: string}
     */
    public function dispatchRequest(User $user)
    {
        $url = $user->signatureRequestUrl();
        $sent = [];

        if (! empty($user->phone)) {
            try {
                $this->wpMessage($user->phone, WhatsAppMessage::userSignatureRequest($user->name, $url));
                $sent[] = 'WhatsApp';
            } catch (\Throwable $e) {
                Log::warning('Signature request WhatsApp failed: '.$e->getMessage());
            }
        }

        if (! empty($user->email)) {
            try {
                Mail::send('mail.user_signature_request', [
                    'user_name' => $user->name,
                    'sign_url' => $url,
                    'company' => WhatsAppMessage::companyName(),
                ], function ($mail) use ($user) {
                    $mail->to($user->email)->subject('Please add your signature');
                });
                $sent[] = 'email';
            } catch (\Throwable $e) {
                Log::warning('Signature request email failed: '.$e->getMessage());
            }
        }

        if (! $sent) {
            return [
                'ok' => false,
                'message' => 'Could not send the signature link — '.$user->name.' has no phone number or email address.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Signature link sent to '.$user->name.' by '.implode(' and ', $sent).'.',
        ];
    }

    /** Public page the recipient of the link lands on. */
    public function show($token)
    {
        $user = $this->findByToken($token);
        if (! $user) {
            return $this->expiredResponse();
        }

        return view('user.public_signature', [
            'user' => $user,
            'token' => $token,
            'general_setting' => GeneralSetting::first(),
        ]);
    }

    /** Public submit from that page. */
    public function store(Request $request, $token)
    {
        $user = $this->findByToken($token);
        if (! $user) {
            return $this->expiredResponse();
        }

        $request->validate([
            'signature_data' => 'required|string',
        ]);

        $filename = UserSignature::storeFromDataUrl($user, 'sign', $request->input('signature_data'));
        if ($filename === null) {
            return back()->with('not_permitted', 'That signature could not be read. Please draw it again.');
        }

        $user->forceFill([
            'sign' => $filename,
            'signature_signed_at' => now(),
            // One use per link; a new one has to be sent to replace the image.
            'signature_token' => null,
        ])->save();

        return view('user.public_signature_done', [
            'user' => $user,
            'general_setting' => GeneralSetting::first(),
        ]);
    }

    protected function findByToken($token)
    {
        if (empty($token)) {
            return null;
        }

        $user = User::where('signature_token', $token)->where('is_deleted', false)->first();

        return ($user && $user->isOpenForSignatureRequest()) ? $user : null;
    }

    protected function expiredResponse()
    {
        return response()->view('user.public_signature_expired', [
            'general_setting' => GeneralSetting::first(),
        ], 410);
    }
}
