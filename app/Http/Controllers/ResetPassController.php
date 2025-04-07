<?php

namespace App\Http\Controllers;

use App\Jobs\ResetPassMail;
use App\Models\NewPass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ResetPassController extends Controller
{
    public function index()
    {
        // dd('reached');

        $reqs = NewPass::with('user')->paginate(10)->map(fn ($req) => [
            'id' => $req->id,
            'email' => $req->user->email,
            'created_at' => $req->created_at,
            'name' => $req->user->name,
            'is_set' => $req->is_set,
        ]);

        return Inertia::render('Admin/ResetPass/ResetPass', [
            'resetRequests' => $reqs,
        ]);
    }

    public function resetPass(NewPass $req)
    {
        // dd($req);
        try {
            return DB::transaction(function () use ($req) {
                $user = User::findOrFail($req->user_id);

                $user->passToken()->update([
                    'is_set' => true,
                ]);

                $user->update([
                    'password' => Hash::make($req->token),
                ]);

                // ResetPassMail::dispatch($req->token, $user->email)->onQueue('emails');
                dispatch(new ResetPassMail($req->token, $user->email));
                Log::info('Password reset successful', [
                    'user_id' => $user->id,
                    'ip' => request()->ip(),
                ]);

                return to_route('admin.reset-password-page')
                    ->with('success', 'Password reset successfully');
            });
        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'error' => $e->getMessage(),
                'user_id' => $req->user_id,
            ]);

            return to_route('admin.reset-password-page')
                ->with('error', 'Failed to reset password. Please try again.');
        }
    }
}
