<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLiferProfileRequest;
use App\Models\Lifer;
use App\Models\LiferProfile;
use App\Models\ProfileComment;
use App\Services\ProfileContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfilPersoController extends Controller
{
    public function index(): Response
    {
        return $this->show($this->activeLifer());
    }

    public function show(Lifer $lifer): Response
    {
        $viewer = $this->activeLifer(['gameState']);
        abort_unless($lifer->status === Lifer::STATUS_ACTIVE && $lifer->gameState()->exists(), 404);

        $lifer->load([
            'gameState.bodyType',
            'employment.job',
            'diplomas',
            'profile',
        ]);

        $isOwner = $viewer->is($lifer);
        $activeMarriage = $lifer->activeMarriage();
        $activeMarriage?->load([
            'firstLifer:id,first_name,last_name',
            'secondLifer:id,first_name,last_name',
        ]);
        $spouse = $activeMarriage?->spouseOf($lifer);
        $relationshipStatus = $lifer->profile?->relationship_status;
        $relationship = $relationshipStatus && isset(LiferProfile::RELATIONSHIP_LABELS[$relationshipStatus])
            ? [
                'code' => $relationshipStatus,
                'label' => $relationshipStatus === LiferProfile::RELATIONSHIP_MARRIED_WITH
                    ? 'Marié·e avec'
                    : LiferProfile::RELATIONSHIP_LABELS[$relationshipStatus],
                'spouse' => $relationshipStatus === LiferProfile::RELATIONSHIP_MARRIED_WITH && $spouse
                    ? [
                        'id' => $spouse->id,
                        'name' => trim($spouse->first_name.' '.$spouse->last_name),
                    ]
                    : null,
            ]
            : null;

        if ($relationshipStatus === LiferProfile::RELATIONSHIP_MARRIED_WITH && ! $spouse) {
            $relationship = null;
        }
        $comments = ProfileComment::query()
            ->where('receiver_lifer_id', $lifer->id)
            ->when(! $isOwner, fn ($query) => $query->where(function ($visibility) use ($viewer) {
                $visibility
                    ->where('status', ProfileComment::STATUS_APPROVED)
                    ->orWhere('author_lifer_id', $viewer->id);
            }))
            ->with('author:id,first_name,last_name')
            ->latest()
            ->get()
            ->map(fn (ProfileComment $comment) => [
                'id' => $comment->id,
                'content' => $comment->content,
                'status' => $comment->status,
                'created_at' => $comment->created_at,
                'author' => [
                    'id' => $comment->author->id,
                    'name' => $comment->author->first_name.' '.$comment->author->last_name,
                ],
                'can_moderate' => $isOwner && $comment->status === ProfileComment::STATUS_PENDING,
                'can_delete' => $isOwner || $comment->author_lifer_id === $viewer->id,
            ]);

        $diplomas = $lifer->diplomas
            ->filter(fn ($diploma) => $isOwner || (bool) $diploma->pivot->is_public)
            ->map(fn ($diploma) => [
                'id' => $diploma->id,
                'name' => $diploma->name,
                'earned_at' => $diploma->pivot->earned_at,
                'is_public' => (bool) $diploma->pivot->is_public,
            ])
            ->values();

        return Inertia::render('ProfilPerso/Index', [
            'profileLifer' => [
                'id' => $lifer->id,
                'name' => $lifer->first_name.' '.$lifer->last_name,
                'age' => $lifer->calculateAge(),
                'body_image_url' => $lifer->gameState?->bodyType?->image_path,
                'job' => $lifer->employment?->job?->name,
                'money' => $isOwner || $lifer->profile?->show_money
                    ? $lifer->gameState?->money
                    : null,
                'show_money' => (bool) $lifer->profile?->show_money,
                'relationship_status' => $relationshipStatus,
                'relationship' => $relationship,
                'content' => $lifer->profile?->content,
            ],
            'relationshipOptions' => collect(LiferProfile::RELATIONSHIP_LABELS)
                ->reject(fn ($label, $value) => $value === LiferProfile::RELATIONSHIP_MARRIED_WITH && ! $spouse)
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values(),
            'diplomas' => $diplomas,
            'comments' => $comments,
            'isOwner' => $isOwner,
            'viewerLiferId' => $viewer->id,
            'money' => $viewer->gameState?->money,
        ]);
    }

    public function update(
        UpdateLiferProfileRequest $request,
        ProfileContentSanitizer $sanitizer,
    ): RedirectResponse {
        $lifer = $this->activeLifer(['diplomas']);
        $validated = $request->validated();
        $ownedDiplomaIds = $lifer->diplomas->pluck('id');
        $publicDiplomaIds = collect($validated['public_diploma_ids']);

        abort_if($publicDiplomaIds->diff($ownedDiplomaIds)->isNotEmpty(), 422, 'Un diplôme sélectionné ne t’appartient pas.');

        if (
            ($validated['relationship_status'] ?? null) === LiferProfile::RELATIONSHIP_MARRIED_WITH
            && ! $lifer->activeMarriage()
        ) {
            throw ValidationException::withMessages([
                'relationship_status' => 'Cette option nécessite un mariage actif.',
            ]);
        }

        $content = $sanitizer->sanitize($validated['content'] ?? null, $lifer->id);

        DB::transaction(function () use ($lifer, $validated, $ownedDiplomaIds, $publicDiplomaIds, $content) {
            $lifer->profile()->updateOrCreate(
                ['lifer_id' => $lifer->id],
                [
                    'content' => $content,
                    'show_money' => $validated['show_money'],
                    'relationship_status' => $validated['relationship_status'] ?? null,
                ],
            );

            foreach ($ownedDiplomaIds as $diplomaId) {
                $lifer->diplomas()->updateExistingPivot($diplomaId, [
                    'is_public' => $publicDiplomaIds->contains($diplomaId),
                ]);
            }
        });

        return back()->with('success', 'Ton profil public a été mis à jour.');
    }
}
