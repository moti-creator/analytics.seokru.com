<?php

namespace Database\Seeders;

use App\Models\TdnetLead;
use Illuminate\Database\Seeder;

class TdnetLeadsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'first_name' => 'Jodika', 'last_name' => 'Gilworth',
                'position' => 'Consultant Medical Librarian',
                'company' => 'Manchester University NHS Foundation Trust',
                'country' => 'United Kingdom',
                'email' => 'jodika.gilworth@mft.nhs.uk',
                'linkedin_url' => 'https://uk.linkedin.com/in/jodika-gilworth-9a443264',
                'segment' => 'hospital',
                'source_meta' => ['note' => 'Verified email score 98 via clearpath/email-finder-api'],
            ],
            [
                'first_name' => 'Katherine', 'last_name' => 'Steiner',
                'position' => 'Library Manager / Outreach Librarian, Girdlestone Memorial Library',
                'company' => 'Bodleian Health Care Libraries, University of Oxford',
                'country' => 'United Kingdom',
                'email' => 'katherine.steiner@bodleian.ox.ac.uk',
                'linkedin_url' => 'https://uk.linkedin.com/in/katherinesteiner',
                'segment' => 'academic',
                'source_meta' => ['note' => 'Catch-all domain; deliverable but unverifiable. Score 75.'],
            ],
            [
                'first_name' => 'Mey', 'last_name' => 'Tang',
                'position' => 'Medical Information Specialist',
                'company' => 'Calian Health (ex-Novartis Canada)',
                'country' => 'Canada',
                'email' => 'mey.tang@calian.com',
                'linkedin_url' => 'https://ca.linkedin.com/in/mey-tang-991750b1',
                'segment' => 'pharma',
                'source_meta' => ['note' => 'Pattern-guess email — verify before sending'],
            ],
            [
                'first_name' => 'Keren', 'last_name' => 'Moskal',
                'position' => 'Clinical Librarian & Education Lead',
                'company' => 'Monash Health',
                'country' => 'Australia',
                'email' => 'keren.moskal@monashhealth.org',
                'linkedin_url' => 'https://au.linkedin.com/in/keren-moskal',
                'segment' => 'hospital',
                'source_meta' => ['note' => 'Pattern-guess email — verify before sending'],
            ],
            [
                'first_name' => 'Linda', 'last_name' => 'Niesink-Boerboom',
                'position' => 'Medisch Informatiespecialist / Clinical Librarian',
                'company' => 'Kennisinstituut van Medisch Specialisten',
                'country' => 'Netherlands',
                'email' => 'l.niesink@demedischspecialist.nl',
                'linkedin_url' => 'https://nl.linkedin.com/in/lindaboerboom',
                'segment' => 'academic',
                'source_meta' => ['note' => 'Pattern-guess email — verify before sending'],
            ],
            [
                'first_name' => 'John', 'last_name' => 'Barbrook',
                'position' => 'Faculty Librarian (former Clinical Librarian)',
                'company' => 'Lancaster University',
                'country' => 'United Kingdom',
                'email' => 'j.barbrook@lancaster.ac.uk',
                'linkedin_url' => 'https://uk.linkedin.com/in/johnbarbrook',
                'segment' => 'academic',
                'source_meta' => ['note' => 'Pattern-guess email (UK uni initial+surname) — verify before sending'],
            ],
        ];

        foreach ($rows as $r) {
            TdnetLead::updateOrCreate(['email' => $r['email']], $r);
        }
    }
}
