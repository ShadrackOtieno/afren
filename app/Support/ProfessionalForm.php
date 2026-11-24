<?php
namespace App\Support;

use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Shared by public registration and the self-service profile editor. */
class ProfessionalForm
{
    public static function rules(Request $request, ?Professional $p = null): array
    {
        return [
            'full_name' => 'required|string|max:150',
            'email' => ['required', 'email:rfc', 'max:190', Rule::unique('professionals', 'email')->ignore($p?->id), Rule::unique('users', 'email')->ignore($p?->user_id)],
            'dial_code' => ['required', Rule::exists('countries', 'dial_code')],
            'phone' => ['required', 'digits_between:6,14', function ($a, $v, $fail) use ($request, $p) {
                if (Professional::where('dial_code', $request->dial_code)->where('phone', $v)->when($p, fn ($q) => $q->where('id', '!=', $p->id))->exists()) { $fail('This phone number is already registered.'); }
            }],
            'country_code' => ['required', Rule::exists('countries', 'iso2')],
            'location' => 'nullable|string|max:150',
            'category' => 'required|in:researcher,enumerator,both',
            'education_level' => ['required', Rule::in(array_keys(config('afren.education_levels')))],
            'field_of_study' => 'nullable|string|max:150', 'qualifications' => 'nullable|string|max:3000',
            'years_experience' => 'required|integer|between:0,60', 'research_years' => 'required|integer|between:0,60', 'fieldwork_years' => 'required|integer|between:0,60',
            'experience_summary' => 'nullable|string|max:5000',
            'skills' => 'nullable|string|max:1000', 'tools' => 'nullable|string|max:1000', 'languages' => 'required|string|max:500',
            'certifications' => 'nullable|string|max:2000',
            'availability' => 'required|in:available,limited,unavailable', 'willing_to_travel' => 'nullable|boolean',
            'preferred_countries' => 'nullable|array', 'preferred_countries.*' => [Rule::exists('countries', 'iso2')],
            'cv' => [$p?->cv_path ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'hp' => 'prohibited',
        ] + ($p ? [] : ['consent' => 'accepted', 'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()]]);
    }

    public static function attributes(array $d): array
    {
        return [
            'full_name' => $d['full_name'], 'email' => strtolower($d['email']), 'dial_code' => $d['dial_code'], 'phone' => $d['phone'],
            'country_code' => $d['country_code'], 'location' => $d['location'] ?? null, 'category' => $d['category'],
            'education_level' => $d['education_level'], 'field_of_study' => $d['field_of_study'] ?? null, 'qualifications' => $d['qualifications'] ?? null,
            'years_experience' => $d['years_experience'], 'research_years' => $d['research_years'], 'fieldwork_years' => $d['fieldwork_years'],
            'experience_summary' => $d['experience_summary'] ?? null,
            'skills' => Tags::parse($d['skills'] ?? null), 'tools' => Tags::parse($d['tools'] ?? null), 'languages' => Tags::parse($d['languages']),
            'certifications' => $d['certifications'] ?? null, 'availability' => $d['availability'],
            'willing_to_travel' => (bool) ($d['willing_to_travel'] ?? false), 'preferred_countries' => $d['preferred_countries'] ?? [],
        ];
    }

    /** Wizard step that owns each field (reopens the right step after a server-side error). */
    public static function stepOf(string $field): int
    {
        $field = explode('.', $field)[0];
        if (in_array($field, ['full_name', 'email', 'category', 'country_code', 'location', 'dial_code', 'phone'])) return 1;
        if (in_array($field, ['education_level', 'field_of_study', 'years_experience', 'research_years', 'fieldwork_years', 'qualifications', 'experience_summary', 'certifications'])) return 2;
        if (in_array($field, ['languages', 'skills', 'tools', 'availability', 'willing_to_travel', 'preferred_countries'])) return 3;
        if (in_array($field, ['cv', 'documents', 'password', 'password_confirmation', 'visible'])) return 4;
        return 5;
    }
}
