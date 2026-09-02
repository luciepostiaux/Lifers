<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileCommentRequest;
use App\Models\Lifer;
use App\Models\ProfileComment;
use Illuminate\Http\RedirectResponse;

class ProfileCommentController extends Controller
{
    public function store(StoreProfileCommentRequest $request, Lifer $lifer): RedirectResponse
    {
        $author = $this->activeLifer();
        abort_unless($lifer->status === Lifer::STATUS_ACTIVE && $lifer->gameState()->exists(), 404);

        ProfileComment::create([
            'author_lifer_id' => $author->id,
            'receiver_lifer_id' => $lifer->id,
            'content' => trim($request->validated('content')),
            'status' => ProfileComment::STATUS_PENDING,
        ]);

        return back()->with('success', 'Ton commentaire attend maintenant la validation du propriétaire.');
    }

    public function approve(ProfileComment $comment): RedirectResponse
    {
        abort_unless($comment->receiver_lifer_id === $this->activeLifer()->id, 403);

        $comment->update([
            'status' => ProfileComment::STATUS_APPROVED,
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Le commentaire est maintenant visible sur ton profil.');
    }

    public function destroy(ProfileComment $comment): RedirectResponse
    {
        $liferId = $this->activeLifer()->id;
        abort_unless(
            $comment->author_lifer_id === $liferId || $comment->receiver_lifer_id === $liferId,
            403,
        );

        $comment->delete();

        return back()->with('success', 'Le commentaire a été supprimé.');
    }
}
