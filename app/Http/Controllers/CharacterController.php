<?php

namespace App\Http\Controllers;

use App\Models\BodyType;
use App\Models\FamilyChild;
use App\Services\LiferLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CharacterController extends Controller
{
    public function create()
    {
        if (Auth::user()->activeLifer()->exists()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Character/Create', [
            'bodyTypes' => BodyType::query()
                ->whereIn('code', [BodyType::CODE_MALE, BodyType::CODE_FEMALE])
                ->orderBy('code')
                ->get(['id', 'sex', 'image_path']),
            'availableFamilyLifers' => FamilyChild::query()
                ->where('status', FamilyChild::STATUS_AVAILABLE)
                ->whereNull('claimed_lifer_id')
                ->whereNotNull('first_name')
                ->whereNotNull('last_name')
                ->whereNotNull('adult_at')
                ->where('adult_at', '<=', now())
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name', 'sex']),
        ]);
    }

    public function store(Request $request, LiferLifecycleService $lifecycle)
    {
        $validated = $request->validate([
            'creation_mode' => ['required', Rule::in(['new', 'reincarnation'])],
            'first_name' => ['exclude_unless:creation_mode,new', 'required', 'string', 'max:45'],
            'last_name' => ['exclude_unless:creation_mode,new', 'required', 'string', 'max:45'],
            'family_child_id' => ['exclude_unless:creation_mode,reincarnation', 'required', 'integer', 'exists:family_children,id'],
            'body_type_id' => ['required', 'integer', 'exists:body_types,id'],
        ]);

        $bodyType = BodyType::findOrFail($validated['body_type_id']);

        if ($validated['creation_mode'] === 'reincarnation') {
            $child = FamilyChild::findOrFail($validated['family_child_id']);
            $lifecycle->reincarnate(Auth::user(), $child, $bodyType);

            return redirect()->route('dashboard')->with('success', 'Lifer réincarné avec succès.');
        }

        $lifecycle->create(Auth::user(), $bodyType, $validated);

        return redirect()->route('dashboard')->with('success', 'Lifer créé avec succès.');
    }
}
