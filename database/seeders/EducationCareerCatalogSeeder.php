<?php

namespace Database\Seeders;

use App\Models\Diploma;
use App\Models\Job;
use App\Models\Place;
use App\Models\Study;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EducationCareerCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $university = Place::query()->updateOrCreate(
                ['name' => 'Université de Lifers'],
                ['image_path' => '/images/places/universite.png'],
            );
            $hospital = Place::query()->updateOrCreate(
                ['name' => 'Hôpital de Lifers'],
                ['image_path' => '/images/places/hopital.png'],
            );
            $employmentOffice = Place::query()->updateOrCreate(
                ['name' => 'Maison de l’emploi'],
                ['image_path' => '/images/places/emploi.png'],
            );

            foreach ($this->domains() as $domain) {
                $general = $domain['general'];
                $generalDiploma = $this->diploma($general['name'], $general['completion']);

                Study::query()->updateOrCreate(
                    ['name' => $general['name']],
                    $this->studyData($general, $generalDiploma, null, $university),
                );

                foreach ($domain['specializations'] as $specialization) {
                    $diploma = $this->diploma($specialization['study'], $specialization['completion']);

                    Study::query()->updateOrCreate(
                        ['name' => $specialization['study']],
                        $this->studyData($specialization, $diploma, $generalDiploma, $university),
                    );

                    Job::query()->updateOrCreate(
                        ['name' => $specialization['job']],
                        [
                            'short_description' => $specialization['job_description'],
                            'long_description' => $specialization['job_description'],
                            'salary' => $this->dailySalary($specialization['annual_salary']),
                            'image_path' => $specialization['image'] ?? null,
                            'required_diploma_id' => $diploma->id,
                            'place_id' => $domain['health'] ? $hospital->id : $employmentOffice->id,
                        ],
                    );
                }
            }

            foreach ($this->directAccessJobs() as $job) {
                Job::query()->updateOrCreate(
                    ['name' => $job['name']],
                    [
                        'short_description' => $job['description'],
                        'long_description' => $job['description'],
                        'salary' => $this->dailySalary($job['annual_salary']),
                        'image_path' => null,
                        'required_diploma_id' => null,
                        'place_id' => $employmentOffice->id,
                    ],
                );
            }

            $this->replaceDemoReferences();
        });
    }

    private function diploma(string $name, string $description): Diploma
    {
        return Diploma::query()->updateOrCreate(
            ['name' => $name],
            ['description' => $description],
        );
    }

    private function studyData(array $study, Diploma $awarded, ?Diploma $required, Place $place): array
    {
        return [
            'short_description' => $study['description'],
            'long_description' => $study['completion'],
            'price' => $study['price'],
            'duration_days' => $study['duration'],
            'image_path' => $study['image'] ?? null,
            'awarded_diploma_id' => $awarded->id,
            'required_diploma_id' => $required?->id,
            'place_id' => $place->id,
        ];
    }

    private function dailySalary(int $annualSalary): int
    {
        return (int) round($annualSalary / 500);
    }

    private function replaceDemoReferences(): void
    {
        $this->replaceDemoStudy('Formation infirmière — démo', 'Spécialisation en Soins Infirmiers');
        $this->replaceDemoStudy('Atelier créatif — démo', 'Spécialisation en Arts Visuels');
        $this->replaceDemoJob('Assistant de quartier — démo', 'Artisan');
        $this->replaceDemoJob('Infirmier — démo', 'Infirmier / Infirmière');
        $this->replaceDemoDiploma('Certificat de premiers soins — démo', 'Année Générale en Sciences de la Santé');
        $this->replaceDemoDiploma('Diplôme infirmier — démo', 'Spécialisation en Soins Infirmiers');
        $this->replaceDemoDiploma('Certificat créatif — démo', 'Spécialisation en Arts Visuels');
    }

    private function replaceDemoStudy(string $legacyName, string $targetName): void
    {
        $legacy = Study::query()->where('name', $legacyName)->first();
        $target = Study::query()->where('name', $targetName)->firstOrFail();

        if (! $legacy || $legacy->is($target)) {
            return;
        }

        DB::table('lifer_study_enrollments')->where('study_id', $legacy->id)->update(['study_id' => $target->id]);
        $legacy->delete();
    }

    private function replaceDemoJob(string $legacyName, string $targetName): void
    {
        $legacy = Job::query()->where('name', $legacyName)->first();
        $target = Job::query()->where('name', $targetName)->firstOrFail();

        if (! $legacy || $legacy->is($target)) {
            return;
        }

        DB::table('lifer_employments')->where('job_id', $legacy->id)->update(['job_id' => $target->id]);
        $legacy->delete();
    }

    private function replaceDemoDiploma(string $legacyName, string $targetName): void
    {
        $legacy = Diploma::query()->where('name', $legacyName)->first();
        $target = Diploma::query()->where('name', $targetName)->firstOrFail();

        if (! $legacy || $legacy->is($target)) {
            return;
        }

        DB::table('lifer_diplomas')->where('diploma_id', $legacy->id)->get()->each(function ($row) use ($target) {
            DB::table('lifer_diplomas')->updateOrInsert(
                ['lifer_id' => $row->lifer_id, 'diploma_id' => $target->id],
                ['earned_at' => $row->earned_at],
            );
        });
        DB::table('lifer_diplomas')->where('diploma_id', $legacy->id)->delete();
        $legacy->delete();
    }

    private function domains(): array
    {
        return [
            [
                'health' => true,
                'general' => $this->study('Année Générale en Sciences de la Santé', 'Plonge dans le monde fascinant de la santé, où chaque découverte et chaque soin compte. Ces premiers pas te préparent à comprendre le corps humain et les façons de le soigner.', 4, 500, 'Tu disposes désormais d’une base solide dans les principes fondamentaux de la santé humaine.', '/images/jobs/science-sante.png'),
                'specializations' => [
                    $this->specialization('Spécialisation en Médecine Générale', 'Médecin Généraliste', 'Deviens le premier rempart contre la maladie, le confident et le guide de tes patients.', 12, 1200, 'Tu es maintenant médecin généraliste, prêt à fournir des soins complets à tes patients.', 70000, 'Le médecin généraliste diagnostique les affections courantes, assure le suivi des patients et les oriente vers des spécialistes lorsque nécessaire.', '/images/jobs/science-sante.png'),
                    $this->specialization('Spécialisation en Chirurgie', 'Chirurgien', 'Entre dans le monde de précision de la chirurgie, où chaque geste peut sauver une vie.', 15, 1500, 'Tu maîtrises désormais les compétences techniques et la précision nécessaires à la chirurgie.', 100000, 'Le chirurgien traite blessures, maladies et malformations par des interventions qui exigent expertise, précision et sang-froid.', '/images/jobs/science-sante.png'),
                    $this->specialization('Spécialisation en Psychiatrie', 'Psychiatre', 'Explore les profondeurs de l’esprit humain et soutiens celles et ceux qui luttent contre des troubles invisibles.', 12, 1300, 'Tu es maintenant spécialiste de l’évaluation, du diagnostic et du traitement des troubles mentaux.', 85000, 'Le psychiatre prévient, diagnostique et traite les troubles mentaux en associant suivi clinique, thérapies et traitements adaptés.', '/images/jobs/science-sante.png'),
                    $this->specialization('Spécialisation en Soins Infirmiers', 'Infirmier / Infirmière', 'Sois le cœur battant du secteur de la santé en veillant sur les patients avec compassion et dévouement.', 8, 900, 'Tu es désormais qualifié pour fournir des soins attentifs et compétents aux patients.', 45000, 'L’infirmier ou l’infirmière administre les soins, surveille l’état des patients et soutient quotidiennement l’équipe médicale.', '/images/jobs/science-sante.png'),
                    $this->specialization('Spécialisation en Pharmacologie', 'Pharmacologue', 'Plonge dans l’univers complexe des médicaments et étudie leur impact sur le corps humain.', 10, 1100, 'Tu es maintenant spécialiste des médicaments et de leurs interactions avec le corps humain.', 75000, 'Le pharmacologue recherche et développe des médicaments, étudie leur efficacité, leur sécurité et leurs interactions.', '/images/jobs/science-sante.png'),
                ],
            ],
            [
                'health' => false,
                'general' => $this->study('Année Générale en Ingénierie et Technologie', 'Découvre comment créativité et logique permettent de résoudre les problèmes et de construire le monde de demain.', 4, 500, 'Tu possèdes maintenant les bases nécessaires pour aborder les défis de l’ingénierie et de la technologie.'),
                'specializations' => [
                    $this->specialization('Spécialisation en Génie Civil', 'Ingénieur Civil', 'Construis les fondations d’un avenir meilleur, des bâtiments aux ponts qui relient les communautés.', 10, 1800, 'Tu peux désormais concevoir et superviser la construction de structures et d’infrastructures essentielles.', 65000, 'L’ingénieur civil conçoit, construit et entretient les infrastructures essentielles comme les routes, ponts, barrages et bâtiments.'),
                    $this->specialization('Spécialisation en Génie Mécanique', 'Ingénieur Mécanique', 'Conçois et améliore machines et systèmes pour rendre la vie plus efficace.', 10, 1800, 'Tu sais désormais concevoir, analyser et améliorer différents systèmes mécaniques.', 70000, 'L’ingénieur mécanique conçoit, développe et teste des machines, des outils et des systèmes mécaniques.'),
                    $this->specialization('Spécialisation en Génie Électrique', 'Ingénieur Électrique', 'Maîtrise le pouvoir de l’électricité et développe les technologies qui alimentent le monde moderne.', 10, 1800, 'Tu peux désormais concevoir et superviser des systèmes électriques complexes.', 72000, 'L’ingénieur électrique conçoit et développe des équipements, réseaux et systèmes électriques complexes.'),
                    $this->specialization('Spécialisation en Informatique', 'Développeur / Ingénieur Informatique', 'Entre dans l’ère numérique en apprenant à coder, créer et connecter.', 8, 1500, 'Tu peux désormais créer des solutions informatiques innovantes.', 75000, 'Le développeur ou ingénieur informatique imagine, programme et maintient des logiciels et des systèmes numériques.'),
                    $this->specialization('Spécialisation en Aérospatiale', 'Ingénieur Aérospatial', 'Vise les étoiles en concevant avions, satellites et véhicules spatiaux.', 12, 2000, 'Tu maîtrises maintenant la conception et la fabrication de véhicules aériens et spatiaux.', 80000, 'L’ingénieur aérospatial conçoit et développe des avions, satellites et systèmes destinés à l’exploration spatiale.'),
                ],
            ],
            [
                'health' => false,
                'general' => $this->study('Année Générale en Sciences Humaines et Sociales', 'Explore comment nos pensées, nos cultures et nos systèmes façonnent le monde et la société.', 4, 500, 'Tu comprends désormais les fondements de la société humaine et de ses dynamiques.'),
                'specializations' => [
                    $this->specialization('Spécialisation en Psychologie', 'Psychologue', 'Décrypte les pensées, les émotions et les comportements pour guider les autres vers le bien-être.', 9, 1200, 'Tu es maintenant spécialiste de l’étude du comportement humain.', 60000, 'Le psychologue étudie les comportements et accompagne les personnes dans leurs difficultés émotionnelles ou relationnelles.'),
                    $this->specialization('Spécialisation en Sociologie', 'Sociologue', 'Observe et analyse les structures, les tendances et les transformations de nos sociétés.', 9, 1200, 'Tu sais désormais analyser les structures sociales et les interactions humaines.', 55000, 'Le sociologue analyse les comportements collectifs, les institutions et les transformations sociales.'),
                    $this->specialization('Spécialisation en Anthropologie', 'Anthropologue', 'Voyage à travers les cultures et les époques pour mieux comprendre la diversité humaine.', 9, 1200, 'Tu es désormais spécialiste des cultures et des sociétés humaines.', 57000, 'L’anthropologue étudie les cultures, les sociétés et leurs évolutions à travers le terrain et la recherche.'),
                    $this->specialization('Spécialisation en Droit', 'Avocat / Juriste', 'Apprends à défendre, conseiller et guider grâce à la maîtrise du droit.', 11, 1800, 'Tu peux désormais utiliser tes connaissances juridiques pour défendre les droits et les intérêts de tes clients.', 70000, 'L’avocat ou juriste conseille, représente et défend ses clients en interprétant et en appliquant le droit.'),
                    $this->specialization('Spécialisation en Économie', 'Économiste', 'Analyse les forces qui façonnent les marchés et les politiques économiques.', 9, 1200, 'Tu es désormais spécialiste de l’analyse économique et des politiques publiques.', 65000, 'L’économiste étudie les marchés, les politiques et les données afin d’éclairer les décisions économiques.'),
                ],
            ],
            [
                'health' => false,
                'general' => $this->study('Année Générale en Arts et Lettres', 'Explore les fondements de la création artistique et de l’expression littéraire.', 4, 500, 'Tu as exploré les fondements de la création artistique et de l’expression littéraire.'),
                'specializations' => [
                    $this->specialization('Spécialisation en Arts Visuels', 'Artiste Visuel', 'Exprime ta vision du monde à travers la toile, la sculpture ou les médias numériques.', 8, 1200, 'Tu peux désormais exprimer ta vision à travers une grande variété de médiums.', 50000, 'L’artiste visuel crée des œuvres, organise des expositions et développe des projets artistiques personnels ou commandés.'),
                    $this->specialization('Spécialisation en Musique', 'Musicien / Compositeur', 'Compose la bande-son de la vie en mêlant rythmes, mélodies et harmonies.', 8, 1400, 'Tu peux désormais créer des mélodies et des harmonies capables de toucher le public.', 55000, 'Le musicien ou compositeur crée, répète, enregistre et interprète des œuvres musicales.'),
                    $this->specialization('Spécialisation en Littérature', 'Écrivain / Auteur', 'Tisse des récits qui éveillent l’imagination et explorent l’expérience humaine.', 8, 1000, 'Tu peux désormais créer des récits et des textes littéraires aboutis.', 48000, 'L’écrivain ou auteur rédige, révise et publie des romans, articles, essais ou poèmes.'),
                    $this->specialization('Spécialisation en Histoire de l’Art', 'Historien de l’Art', 'Explore les chefs-d’œuvre et les mouvements qui ont façonné notre culture visuelle.', 8, 1100, 'Tu es désormais capable d’étudier et de préserver la mémoire artistique.', 60000, 'L’historien de l’art recherche, documente et valorise les œuvres et les périodes artistiques.'),
                    $this->specialization('Spécialisation en Théâtre et Cinéma', 'Réalisateur / Metteur en Scène', 'Donne vie aux histoires sur scène ou à l’écran.', 8, 1600, 'Tu peux désormais concevoir et diriger des récits visuels et scéniques.', 70000, 'Le réalisateur ou metteur en scène dirige des productions, accompagne les interprètes et supervise la création d’une œuvre.'),
                ],
            ],
            [
                'health' => false,
                'general' => $this->study('Année Générale en Gestion et Commerce', 'Découvre les fondamentaux du monde des affaires, de la stratégie au leadership.', 4, 500, 'Tu possèdes maintenant une base solide dans les principes de la gestion et du commerce.'),
                'specializations' => [
                    $this->specialization('Spécialisation en Administration des Affaires', 'Directeur Administratif', 'Maîtrise la gestion d’entreprise et participe aux décisions qui façonnent les organisations.', 9, 1500, 'Tu peux désormais guider une organisation grâce à ton expertise en gestion.', 85000, 'Le directeur administratif coordonne les opérations, les budgets, les équipes et la stratégie de l’organisation.'),
                    $this->specialization('Spécialisation en Marketing', 'Responsable Marketing', 'Combine créativité et analyse pour construire des marques et des campagnes mémorables.', 9, 1500, 'Tu sais désormais créer des campagnes capables d’attirer et de fidéliser les clients.', 75000, 'Le responsable marketing conçoit des campagnes, analyse les tendances et développe l’image de la marque.'),
                    $this->specialization('Spécialisation en Comptabilité', 'Comptable', 'Assure la transparence et la santé financière des organisations grâce à la maîtrise des chiffres.', 9, 1300, 'Tu es désormais capable de garantir la fiabilité et la conformité des comptes.', 55000, 'Le comptable tient les comptes, prépare les états financiers et veille au respect des obligations fiscales.'),
                    $this->specialization('Spécialisation en Finance', 'Analyste Financier', 'Analyse les marchés, les risques et les investissements afin d’éclairer les décisions.', 9, 1500, 'Tu peux désormais analyser les marchés et construire des stratégies financières.', 70000, 'L’analyste financier étudie les données, les investissements et les risques pour conseiller les décideurs.'),
                    $this->specialization('Spécialisation en Gestion des Ressources Humaines', 'Responsable des Ressources Humaines', 'Cultive le potentiel humain et crée des environnements de travail épanouissants.', 9, 1500, 'Tu peux désormais accompagner les talents et développer une organisation humaine et productive.', 65000, 'Le responsable des ressources humaines recrute, forme et accompagne les équipes tout au long de leur parcours professionnel.'),
                ],
            ],
            [
                'health' => false,
                'general' => $this->study('Année Générale en Sciences Naturelles', 'Explore le monde naturel, ses organismes, sa matière et les lois qui le régissent.', 4, 500, 'Tu possèdes maintenant une base solide pour explorer les mystères du monde naturel.'),
                'specializations' => [
                    $this->specialization('Spécialisation en Biologie', 'Biologiste', 'Étudie la vie sous toutes ses formes, de la cellule aux écosystèmes.', 10, 1500, 'Tu es désormais spécialiste de l’étude du vivant et de ses interactions.', 65000, 'Le biologiste mène des recherches sur les organismes vivants, collecte des échantillons et analyse les écosystèmes.'),
                    $this->specialization('Spécialisation en Chimie', 'Chimiste', 'Analyse les substances et découvre comment les éléments interagissent pour former notre monde.', 10, 1500, 'Tu peux désormais comprendre et manipuler les substances qui composent la matière.', 70000, 'Le chimiste analyse les substances, conduit des réactions et développe de nouveaux composés et procédés.'),
                    $this->specialization('Spécialisation en Physique', 'Physicien', 'Sonde les lois fondamentales de l’univers, des particules aux galaxies.', 10, 1500, 'Tu es désormais capable d’étudier les forces et les phénomènes qui régissent notre réalité.', 72000, 'Le physicien conduit des expériences, analyse des phénomènes et développe des modèles ou technologies.'),
                    $this->specialization('Spécialisation en Géologie', 'Géologue', 'Étudie la Terre, ses roches et ses processus afin de comprendre son histoire.', 10, 1300, 'Tu peux désormais explorer et interpréter les structures et l’évolution de notre planète.', 60000, 'Le géologue explore les terrains, analyse les roches et évalue les ressources ou les risques naturels.'),
                    $this->specialization('Spécialisation en Astronomie', 'Astronome', 'Observe les étoiles, les planètes et les galaxies pour comprendre notre place dans l’univers.', 10, 1300, 'Tu es désormais capable d’étudier les corps célestes et les phénomènes cosmiques.', 75000, 'L’astronome observe les corps célestes, analyse les données et développe des modèles de l’univers.'),
                ],
            ],
        ];
    }

    private function directAccessJobs(): array
    {
        return [
            ['name' => 'Artisan', 'annual_salary' => 40000, 'description' => 'L’artisan crée, restaure et vend des objets uniques grâce à son savoir-faire et à sa maîtrise des matières.'],
            ['name' => 'Commerçant', 'annual_salary' => 45000, 'description' => 'Le commerçant sélectionne et vend des produits tout en développant une relation durable avec sa clientèle.'],
            ['name' => 'Restaurateur', 'annual_salary' => 50000, 'description' => 'Le restaurateur prépare des plats, imagine ses menus et dirige l’activité quotidienne de son établissement.'],
            ['name' => 'Agriculteur', 'annual_salary' => 35000, 'description' => 'L’agriculteur cultive la terre, entretient ses installations et fournit les produits essentiels à la communauté.'],
            ['name' => 'Coach Sportif', 'annual_salary' => 45000, 'description' => 'Le coach sportif conçoit des entraînements, accompagne les progrès et encourage une vie active et saine.'],
            ['name' => 'Coiffeur', 'annual_salary' => 32000, 'description' => 'Le coiffeur conseille ses clients et maîtrise la coupe, la couleur et le coiffage.'],
            ['name' => 'Styliste', 'annual_salary' => 50000, 'description' => 'Le styliste imagine des vêtements, développe des collections et crée des univers visuels autour de la mode.'],
        ];
    }

    private function study(string $name, string $description, int $duration, int $price, string $completion, ?string $image = null): array
    {
        return compact('name', 'description', 'duration', 'price', 'completion', 'image');
    }

    private function specialization(
        string $study,
        string $job,
        string $description,
        int $duration,
        int $price,
        string $completion,
        int $annualSalary,
        string $jobDescription,
        ?string $image = null,
    ): array {
        return [
            'study' => $study,
            'job' => $job,
            'description' => $description,
            'duration' => $duration,
            'price' => $price,
            'completion' => $completion,
            'annual_salary' => $annualSalary,
            'job_description' => $jobDescription,
            'image' => $image,
        ];
    }
}
