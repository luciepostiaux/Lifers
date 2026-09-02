<?php

namespace Tests\Feature;

use App\Models\Diploma;
use App\Models\LiferStudyEnrollment;
use App\Models\Place;
use App\Models\Study;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesLifers;
use Tests\TestCase;

class StudyDiplomaAuthorizationTest extends TestCase
{
    use CreatesLifers;
    use RefreshDatabase;

    public function test_diploma_cannot_be_claimed_without_enrollment(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        [$study, $diploma] = $this->createStudyWithDiploma();

        $this->actingAs($user)
            ->post(route('study.claimDiploma', $study))
            ->assertSessionHasErrors('study');

        $this->assertDatabaseMissing('lifer_diplomas', ['lifer_id' => $lifer->id, 'diploma_id' => $diploma->id]);
    }

    public function test_diploma_cannot_be_claimed_before_enrollment_end_date(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        [$study, $diploma] = $this->createStudyWithDiploma();
        $enrollment = $this->enroll($lifer->id, $study->id, now()->addDay());

        $this->actingAs($user)
            ->post(route('study.claimDiploma', $study))
            ->assertSessionHasErrors('study');

        $this->assertDatabaseMissing('lifer_diplomas', ['lifer_id' => $lifer->id, 'diploma_id' => $diploma->id]);
        $this->assertSame(LiferStudyEnrollment::STATUS_ACTIVE, $enrollment->fresh()->status);
    }

    public function test_diploma_can_be_claimed_after_enrollment_end_date_and_history_is_kept(): void
    {
        [$user, $lifer] = $this->createUserWithLifer();
        [$study, $diploma] = $this->createStudyWithDiploma();
        $enrollment = $this->enroll($lifer->id, $study->id, now()->subDay());

        $this->actingAs($user)
            ->post(route('study.claimDiploma', $study))
            ->assertRedirect(route('study.index'));

        $this->assertDatabaseHas('lifer_diplomas', ['lifer_id' => $lifer->id, 'diploma_id' => $diploma->id]);
        $this->assertSame(LiferStudyEnrollment::STATUS_COMPLETED, $enrollment->fresh()->status);
        $this->assertNotNull($enrollment->fresh()->ended_at);
    }

    public function test_enrollment_charges_price_and_only_one_study_stays_active(): void
    {
        [$user, $lifer] = $this->createUserWithLifer(500);
        [$firstStudy] = $this->createStudyWithDiploma('Première étude');
        [$secondStudy] = $this->createStudyWithDiploma('Deuxième étude');

        $this->actingAs($user)->post(route('study.enroll', $firstStudy));
        $this->actingAs($user)->post(route('study.enroll', $secondStudy));

        $this->assertSame('300.00', $lifer->gameState->fresh()->money);
        $this->assertDatabaseCount('lifer_study_enrollments', 2);
        $this->assertSame(1, LiferStudyEnrollment::where('lifer_id', $lifer->id)->where('status', 'active')->count());
        $this->assertSame(1, LiferStudyEnrollment::where('lifer_id', $lifer->id)->where('status', 'left')->count());
    }

    private function enroll(int $liferId, int $studyId, $endsAt): LiferStudyEnrollment
    {
        return LiferStudyEnrollment::create([
            'lifer_id' => $liferId,
            'study_id' => $studyId,
            'started_at' => now()->subDays(2),
            'ends_at' => $endsAt,
            'status' => LiferStudyEnrollment::STATUS_ACTIVE,
        ]);
    }

    private function createStudyWithDiploma(string $name = 'Étude de test'): array
    {
        $diploma = Diploma::create([
            'name' => 'Diplôme '.$name,
            'description' => 'Diplôme utilisé pour vérifier les règles serveur.',
        ]);
        $place = Place::firstOrCreate(['name' => 'Université de test']);
        $study = Study::create([
            'name' => $name,
            'short_description' => 'Description courte',
            'long_description' => 'Description détaillée',
            'price' => 100,
            'duration_days' => 5,
            'awarded_diploma_id' => $diploma->id,
            'place_id' => $place->id,
        ]);

        return [$study, $diploma];
    }
}
