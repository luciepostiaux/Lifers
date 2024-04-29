<?php

namespace App\Http\Controllers;

use App\Events\SmsMessageSent;
use App\Models\Perso;
use App\Models\SmsMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SmsMessageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $perso = $user->perso()->with('friends')->first();
        $personnages = Perso::where('id', '!=', $user->id)->get()->map(function ($perso) {
            return [
                'id' => $perso->id,
                'fullName' => $perso->first_name . ' ' . $perso->last_name,
            ];
        });

        return Inertia::render('Gsm/Index', [
            'perso' => $perso,
            'personnages' => $personnages,
            'user' => $user,

        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer',
            'content' => 'required|string',
        ]);

        $smsMessage = SmsMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
        ]);

        broadcast(new SmsMessageSent($smsMessage))->toOthers();

        return response()->json($smsMessage);
    }

    public function create()
    {
        $personnages = Perso::all()->map(function ($perso) {
            return [
                'id' => $perso->id,
                'fullName' => $perso->first_name . ' ' . $perso->last_name,
            ];
        });

        return Inertia::render('Sms/Create', [
            'personnages' => $personnages
        ]);
    }





    public function show(SmsMessage $message)
    {
        return response()->json($message);
    }

    public function update(Request $request, SmsMessage $message)
    {
        $message->update($request->all());

        return response()->json($message);
    }

    public function destroy(SmsMessage $message)
    {
        $message->delete();

        return response()->json(null, 204);
    }
}
