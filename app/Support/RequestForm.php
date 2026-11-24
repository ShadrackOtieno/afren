<?php
namespace App\Support;

use Illuminate\Validation\Rule;

/** Shared rules/mapping for the recruitment request (public create + portal edit). */
class RequestForm
{
    public static function rules(bool $create): array
    {
        $r = [
            'org_name' => 'required|string|max:190', 'org_type' => ['required', Rule::in(config('afren.org_types'))],
            'org_country' => ['required', Rule::exists('countries', 'iso2')], 'org_location' => 'nullable|string|max:150',
            'contact_name' => 'required|string|max:150', 'contact_email' => 'required|email|max:190',
            'contact_dial_code' => ['required', Rule::exists('countries', 'dial_code')], 'contact_phone' => 'required|digits_between:6,14',
            'website' => 'nullable|url|max:190',
            'project_title' => 'required|string|max:190', 'project_description' => 'required|string|max:8000',
            'objectives' => 'required|string|max:8000', 'sector' => ['required', Rule::in(config('afren.sectors'))],
            'project_countries' => 'required|array|min:1', 'project_countries.*' => [Rule::exists('countries', 'iso2')],
            'project_locations' => 'nullable|string|max:2000', 'duration' => 'nullable|string|max:100',
            'start_date' => 'nullable|date', 'end_date' => 'nullable|date|after_or_equal:start_date',
            'recruitment_deadline' => $create ? 'nullable|date|after_or_equal:today' : 'nullable|date',
            'personnel_type' => 'required|in:researcher,enumerator,both', 'headcount' => 'required|integer|min:1|max:100000',
            'qualifications_required' => 'nullable|string|max:3000', 'min_education' => ['required', Rule::in(array_keys(config('afren.education_levels')))],
            'min_years' => 'nullable|integer|between:0,60', 'experience_required' => 'nullable|string|max:3000', 'fieldwork_required' => 'nullable|string|max:3000',
            'technical_skills' => 'nullable|string|max:1000', 'languages' => 'nullable|string|max:500', 'tools' => 'nullable|string|max:1000',
            'local_knowledge' => 'nullable|string|max:2000', 'travel_required' => 'nullable|boolean',
            'availability_requirements' => 'nullable|string|max:2000', 'additional_criteria' => 'nullable|string|max:3000',
            'budget_min' => 'nullable|numeric|min:0', 'budget_max' => 'nullable|numeric|gte:budget_min', 'budget_currency' => 'nullable|string|size:3',
            'arrangement' => 'nullable|string|max:190', 'additional_services' => 'nullable|string|max:3000',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240', 'hp' => 'prohibited',
        ];
        if ($create) { $r += ['consent' => 'accepted', 'direct' => 'nullable|boolean']; }
        return $r;
    }

    /** Map to the model's columns (no document, source, subscription or reference). */
    public static function attributes(array $d): array
    {
        $n = fn ($k) => $d[$k] ?? null;
        return [
            'org_name' => $d['org_name'], 'org_type' => $d['org_type'], 'org_country' => $d['org_country'], 'org_location' => $n('org_location'),
            'contact_name' => $d['contact_name'], 'contact_email' => strtolower($d['contact_email']), 'contact_dial_code' => $d['contact_dial_code'],
            'contact_phone' => $d['contact_phone'], 'website' => $n('website'),
            'project_title' => $d['project_title'], 'project_description' => $d['project_description'], 'objectives' => $d['objectives'], 'sector' => $d['sector'],
            'project_countries' => $d['project_countries'], 'project_locations' => $n('project_locations'), 'duration' => $n('duration'),
            'start_date' => $n('start_date'), 'end_date' => $n('end_date'), 'recruitment_deadline' => $n('recruitment_deadline'),
            'personnel_type' => $d['personnel_type'], 'headcount' => $d['headcount'], 'qualifications_required' => $n('qualifications_required'),
            'min_education' => $d['min_education'], 'min_years' => $d['min_years'] ?? 0, 'experience_required' => $n('experience_required'),
            'fieldwork_required' => $n('fieldwork_required'), 'technical_skills' => Tags::parse($n('technical_skills')),
            'languages' => Tags::parse($n('languages')), 'tools' => Tags::parse($n('tools')), 'local_knowledge' => $n('local_knowledge'),
            'travel_required' => (bool) ($d['travel_required'] ?? false), 'availability_requirements' => $n('availability_requirements'),
            'additional_criteria' => $n('additional_criteria'), 'budget_min' => $n('budget_min'), 'budget_max' => $n('budget_max'),
            'budget_currency' => isset($d['budget_currency']) ? strtoupper($d['budget_currency']) : null, 'arrangement' => $n('arrangement'),
            'additional_services' => $n('additional_services'),
        ];
    }

    /** Which wizard step each field lives on (used to reopen the right step after a server-side error). */
    public static function stepOf(string $field): int
    {
        $field = explode('.', $field)[0];
        if (in_array($field, ['org_name','org_type','org_country','org_location','contact_name','contact_email','contact_dial_code','contact_phone','website'])) return 1;
        if (in_array($field, ['project_title','project_description','objectives','sector','project_countries','project_locations','duration','start_date','end_date','recruitment_deadline'])) return 2;
        if (in_array($field, ['personnel_type','headcount','qualifications_required','min_education','min_years','experience_required','fieldwork_required','technical_skills','languages','tools','local_knowledge','travel_required','availability_requirements','additional_criteria'])) return 3;
        if (in_array($field, ['budget_min','budget_max','budget_currency','arrangement','additional_services','document'])) return 4;
        return 5;
    }
}
