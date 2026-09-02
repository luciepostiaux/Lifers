<?php

namespace App\Http\Controllers;

use App\Models\FamilyChild;
use App\Models\FamilyPregnancy;
use App\Models\FamilyRequest;
use App\Models\Item;
use App\Models\Lifer;
use App\Models\LiferIntimacyEvent;
use App\Services\FamilyLifecycleService;
use App\Services\FamilyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FamilyController extends Controller
{
    public function index(): Response
    {
        $lifer = $this->activeLifer(['gameState', 'inventory.items']);
        $marriage = $lifer->activeMarriage();
        $marriage?->load(['firstLifer:id,first_name,last_name', 'secondLifer:id,first_name,last_name']);
        $spouse = $marriage?->spouseOf($lifer);
        $familyIdentity = FamilyChild::query()
            ->where('claimed_lifer_id', $lifer->id)
            ->with([
                'biologicalMother:id,first_name,last_name,status',
                'biologicalFather:id,first_name,last_name,status',
            ])
            ->first();
        $parents = collect([
            ['role' => 'Mère', 'lifer' => $familyIdentity?->biologicalMother],
            ['role' => 'Père', 'lifer' => $familyIdentity?->biologicalFather],
        ])->filter(fn (array $parent) => $parent['lifer'])
            ->map(fn (array $parent) => [
                ...$this->personData($parent['lifer']),
                'role' => $parent['role'],
                'is_active' => $parent['lifer']->status === Lifer::STATUS_ACTIVE,
            ])
            ->values();

        $receivedRequests = $lifer->receivedFamilyRequests()
            ->where('status', FamilyRequest::STATUS_PENDING)
            ->with(['requester:id,first_name,last_name', 'child:id,first_name,last_name'])
            ->latest()
            ->get()
            ->map(fn (FamilyRequest $familyRequest) => $this->requestData($familyRequest, 'received'));

        $sentRequests = $lifer->sentFamilyRequests()
            ->where('status', FamilyRequest::STATUS_PENDING)
            ->with(['recipient:id,first_name,last_name', 'child:id,first_name,last_name'])
            ->latest()
            ->get()
            ->map(fn (FamilyRequest $familyRequest) => $this->requestData($familyRequest, 'sent'));

        $pregnancies = $lifer->pregnanciesAsMother()
            ->where('status', 'active')
            ->with(['mother:id,first_name,last_name', 'father:id,first_name,last_name', 'children:id,pregnancy_id,first_name,last_name,sex,birth_order'])
            ->get()
            ->merge(
                $lifer->pregnanciesAsFather()
                    ->where('status', 'active')
                    ->with(['mother:id,first_name,last_name', 'father:id,first_name,last_name', 'children:id,pregnancy_id,first_name,last_name,sex,birth_order'])
                    ->get(),
            )
            ->unique('id')
            ->sortBy('due_at')
            ->values()
            ->map(fn ($pregnancy) => [
                'id' => $pregnancy->id,
                'children_count' => $pregnancy->children_count,
                'conceived_at' => $pregnancy->conceived_at?->toIso8601String(),
                'due_at' => $pregnancy->due_at?->toIso8601String(),
                'other_parent' => $this->personData(
                    $pregnancy->mother_lifer_id === $lifer->id ? $pregnancy->father : $pregnancy->mother,
                ),
                'available_last_names' => collect([
                    $pregnancy->mother?->last_name,
                    $pregnancy->father?->last_name,
                ])->filter()->unique()->values(),
                'children' => $pregnancy->children->map(fn ($child) => [
                    'id' => $child->id,
                    'birth_order' => $child->birth_order,
                    'first_name' => $child->first_name,
                    'last_name' => $child->last_name,
                    'sex' => $child->sex,
                ])->values(),
            ]);

        $caregiverIds = array_values(array_filter([$lifer->id, $spouse?->id]));
        $children = FamilyChild::query()
            ->whereHas('guardians', function ($query) use ($caregiverIds) {
                $query->whereIn('lifers.id', $caregiverIds)
                    ->where('family_child_guardians.has_custody', true);
            })
            ->whereIn('family_children.status', ['dependent', 'orphaned'])
            ->with(['gauges', 'guardians:id,first_name,last_name', 'biologicalMother:id,first_name,last_name', 'biologicalFather:id,first_name,last_name'])
            ->orderBy('born_at')
            ->get()
            ->unique('id')
            ->map(fn ($child) => [
                'id' => $child->id,
                'name' => trim(($child->first_name ?? 'Prénom à choisir').' '.($child->last_name ?? '')),
                'age' => $child->calculateAge(),
                'status' => $child->status,
                'is_guardian' => $child->guardians->contains(
                    fn (Lifer $guardian) => $guardian->id === $lifer->id && (bool) $guardian->pivot->has_custody,
                ),
                'custodian_count' => $child->guardians->filter(
                    fn (Lifer $guardian) => (bool) $guardian->pivot->has_custody,
                )->count(),
                'gauges' => $child->gauges ? [
                    'hunger' => $child->gauges->hunger,
                    'hygiene' => $child->gauges->hygiene,
                    'affection' => $child->gauges->affection,
                ] : null,
            ]);

        $todayEvents = LiferIntimacyEvent::query()
            ->whereDate('happened_on', today())
            ->where(function ($query) use ($lifer) {
                $query->where('first_lifer_id', $lifer->id)
                    ->orWhere('second_lifer_id', $lifer->id);
            })
            ->get()
            ->countBy('type');

        $protectionQuantity = $lifer->inventory?->items
            ->where('name', Item::FAMILY_PROTECTION_NAME)
            ->sum(fn (Item $item) => (int) $item->pivot->quantity) ?? 0;

        $favoriteIds = $lifer->familyFavorites()
            ->pluck('lifers.id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $sentRequestCounts = $sentRequests->countBy('type');

        $otherLifers = Lifer::active()
            ->whereKeyNot($lifer->id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'sex'])
            ->map(fn (Lifer $other) => [
                ...$this->personData($other),
                'can_attempt_baby' => $other->sex !== $lifer->sex,
                'is_favorite' => in_array($other->id, $favoriteIds, true),
            ]);

        return Inertia::render('Family/Index', [
            'money' => $lifer->gameState?->money,
            'currentLifer' => $this->personData($lifer),
            'parents' => $parents,
            'spouse' => $spouse ? [
                ...$this->personData($spouse),
                'married_at' => $marriage->started_at?->toIso8601String(),
            ] : null,
            'receivedRequests' => $receivedRequests,
            'sentRequests' => $sentRequests,
            'pregnancies' => $pregnancies,
            'children' => $children,
            'otherLifers' => $otherLifers,
            'actionStatus' => [
                'daily_limit' => FamilyService::DAILY_LIMIT,
                'protected_used' => (int) ($todayEvents[LiferIntimacyEvent::TYPE_PROTECTED] ?? 0),
                'protected_pending' => (int) ($sentRequestCounts[FamilyRequest::TYPE_INTIMACY_PROTECTED] ?? 0),
                'baby_attempts_used' => (int) ($todayEvents[LiferIntimacyEvent::TYPE_BABY_ATTEMPT] ?? 0),
                'baby_attempts_pending' => (int) ($sentRequestCounts[FamilyRequest::TYPE_BABY_ATTEMPT] ?? 0),
                'protection_quantity' => $protectionQuantity,
                'feed_cost' => FamilyLifecycleService::FEED_COST,
                'wash_cost' => FamilyLifecycleService::WASH_COST,
                'care_gain' => FamilyLifecycleService::CARE_GAIN,
            ],
        ]);
    }

    public function storeRequest(Request $request, FamilyService $familyService): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_lifer_id' => ['required', 'integer', Rule::exists('lifers', 'id')->where('status', Lifer::STATUS_ACTIVE)],
            'type' => ['required', Rule::in([
                FamilyRequest::TYPE_MARRIAGE,
                FamilyRequest::TYPE_INTIMACY_PROTECTED,
                FamilyRequest::TYPE_BABY_ATTEMPT,
            ])],
        ]);

        $requester = $this->activeLifer();
        $recipient = Lifer::active()->findOrFail($validated['recipient_lifer_id']);
        $familyService->request($requester, $recipient, $validated['type']);

        return to_route('family.index')->with('success', 'La demande a été envoyée. Elle ne produira aucun effet avant son acceptation.');
    }

    public function respond(Request $request, FamilyRequest $familyRequest, FamilyService $familyService): RedirectResponse
    {
        $validated = $request->validate([
            'accepted' => ['required', 'boolean'],
        ]);

        $familyService->respond($familyRequest, $this->activeLifer(), $validated['accepted']);

        return to_route('family.index')->with(
            'success',
            $validated['accepted'] ? 'La demande a été acceptée.' : 'La demande a été refusée.',
        );
    }

    public function cancelRequest(FamilyRequest $familyRequest, FamilyService $familyService): RedirectResponse
    {
        $familyService->cancelRequest($familyRequest, $this->activeLifer());

        return to_route('family.index')->with('success', 'La demande a été annulée.');
    }

    public function divorce(FamilyService $familyService): RedirectResponse
    {
        $familyService->divorce($this->activeLifer());

        return to_route('family.index')->with('success', 'Le divorce a été enregistré. Les gardes des enfants restent inchangées.');
    }

    public function storeFavorite(Lifer $favoriteLifer): RedirectResponse
    {
        $lifer = $this->activeLifer();

        if ($favoriteLifer->id === $lifer->id || $favoriteLifer->status !== Lifer::STATUS_ACTIVE || ! $favoriteLifer->gameState()->exists()) {
            abort(404);
        }

        $lifer->familyFavorites()->syncWithoutDetaching([$favoriteLifer->id]);

        return to_route('family.index')->with('success', "{$favoriteLifer->first_name} a été ajouté aux favoris.");
    }

    public function destroyFavorite(Lifer $favoriteLifer): RedirectResponse
    {
        $this->activeLifer()->familyFavorites()->detach($favoriteLifer->id);

        return to_route('family.index')->with('success', "{$favoriteLifer->first_name} a été retiré des favoris.");
    }

    public function nameExpectedChild(
        Request $request,
        FamilyPregnancy $pregnancy,
        FamilyChild $child,
        FamilyLifecycleService $familyLifecycleService,
    ): RedirectResponse {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:45'],
            'last_name' => ['required', 'string', 'max:45'],
        ]);

        $familyLifecycleService->nameExpectedChild(
            $this->activeLifer(),
            $pregnancy,
            $child,
            $validated['first_name'],
            $validated['last_name'],
        );

        return to_route('family.index')->with('success', 'Le prénom et le nom ont été enregistrés pour la naissance.');
    }

    public function careForChild(
        Request $request,
        FamilyChild $child,
        FamilyLifecycleService $familyLifecycleService,
    ): RedirectResponse {
        $validated = $request->validate([
            'care' => ['required', Rule::in([
                FamilyLifecycleService::CARE_FEED,
                FamilyLifecycleService::CARE_WASH,
                FamilyLifecycleService::CARE_CUDDLE,
            ])],
        ]);

        $familyLifecycleService->careForChild($this->activeLifer(), $child, $validated['care']);

        return to_route('family.index')->with('success', 'Le soin a été apporté à ton enfant.');
    }

    public function careForAllChildren(FamilyLifecycleService $familyLifecycleService): RedirectResponse
    {
        $childrenCount = $familyLifecycleService->careForAllChildren($this->activeLifer());

        return to_route('family.index')->with(
            'success',
            "Une étape de soin a été appliquée à {$childrenCount} enfant(s).",
        );
    }

    public function renounceChild(FamilyChild $child, FamilyLifecycleService $familyLifecycleService): RedirectResponse
    {
        $familyLifecycleService->renounceChild($this->activeLifer(), $child);

        return to_route('family.index')->with('success', 'Ton Lifer a renié cet enfant et n’en a plus la garde.');
    }

    public function abandonChild(FamilyChild $child, FamilyLifecycleService $familyLifecycleService): RedirectResponse
    {
        $request = $familyLifecycleService->requestOrAbandonChild($this->activeLifer(), $child);

        return to_route('family.index')->with(
            'success',
            $request
                ? 'La demande d’abandon a été envoyée à l’autre responsable. Chacun paiera 50 Lif’coins après acceptation.'
                : 'L’enfant a été confié à l’orphelinat pour 100 Lif’coins.',
        );
    }

    private function requestData(FamilyRequest $familyRequest, string $direction): array
    {
        $other = $direction === 'received' ? $familyRequest->requester : $familyRequest->recipient;

        return [
            'id' => $familyRequest->id,
            'type' => $familyRequest->type,
            'direction' => $direction,
            'other_lifer' => $this->personData($other),
            'created_at' => $familyRequest->created_at?->toIso8601String(),
            'child' => $familyRequest->child ? [
                'id' => $familyRequest->child->id,
                'name' => trim($familyRequest->child->first_name.' '.$familyRequest->child->last_name),
            ] : null,
        ];
    }

    private function personData(?Lifer $lifer): ?array
    {
        if (! $lifer) {
            return null;
        }

        return [
            'id' => $lifer->id,
            'name' => trim($lifer->first_name.' '.$lifer->last_name),
        ];
    }
}
