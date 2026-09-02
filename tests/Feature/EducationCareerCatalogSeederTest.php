<?php

namespace Tests\Feature;

use App\Models\Diploma;
use App\Models\Job;
use App\Models\Study;
use Database\Seeders\EducationCareerCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationCareerCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_historical_education_and_career_catalog_is_complete_and_idempotent(): void
    {
        $this->seed(EducationCareerCatalogSeeder::class);
        $this->seed(EducationCareerCatalogSeeder::class);

        $this->assertSame(36, Study::count());
        $this->assertSame(36, Diploma::count());
        $this->assertSame(37, Job::count());
        $this->assertSame(6, Study::whereNull('required_diploma_id')->count());
        $this->assertSame(7, Job::whereNull('required_diploma_id')->count());

        $generalHealth = Diploma::where('name', 'Année Générale en Sciences de la Santé')->firstOrFail();
        $nursingDiploma = Diploma::where('name', 'Spécialisation en Soins Infirmiers')->firstOrFail();

        $this->assertDatabaseHas('studies', [
            'name' => 'Spécialisation en Soins Infirmiers',
            'price' => 900,
            'duration_days' => 8,
            'required_diploma_id' => $generalHealth->id,
            'awarded_diploma_id' => $nursingDiploma->id,
        ]);
        $this->assertDatabaseHas('jobs', [
            'name' => 'Infirmier / Infirmière',
            'salary' => 90,
            'required_diploma_id' => $nursingDiploma->id,
        ]);
        $this->assertDatabaseHas('jobs', [
            'name' => 'Artisan',
            'salary' => 80,
            'required_diploma_id' => null,
        ]);

        Study::whereNotNull('image_path')->get()->each(function (Study $study) {
            $this->assertFileExists(public_path(ltrim($study->image_path, '/')));
        });
        Job::whereNotNull('image_path')->get()->each(function (Job $job) {
            $this->assertFileExists(public_path(ltrim($job->image_path, '/')));
        });
    }
}
