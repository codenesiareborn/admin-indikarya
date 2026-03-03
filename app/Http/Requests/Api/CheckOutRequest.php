<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class CheckOutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
                function ($attribute, $value, $fail) {
                    // Check if user has active assignment to this project
                    $hasActiveAssignment = DB::table('employee_projects')
                        ->join('projects', 'employee_projects.project_id', '=', 'projects.id')
                        ->where('employee_projects.user_id', auth()->id())
                        ->where('employee_projects.project_id', $value)
                        ->where('projects.status', 'aktif')
                        ->where(function ($query) {
                            $query->whereNull('employee_projects.tanggal_selesai')
                                ->orWhere('employee_projects.tanggal_selesai', '>=', now()->toDateString());
                        })
                        ->exists();

                    if (! $hasActiveAssignment) {
                        $fail('Anda tidak memiliki assignment aktif ke project ini.');
                    }
                },
            ],
            'shift_id' => [
                'nullable',
                'integer',
                'exists:project_shifts,id',
                function ($attribute, $value, $fail) {
                    // If shift_id provided, verify user has active assignment to project
                    if ($value) {
                        $shiftProject = DB::table('project_shifts')
                            ->where('id', $value)
                            ->value('project_id');

                        if ($shiftProject) {
                            $hasActiveAssignment = DB::table('employee_projects')
                                ->join('projects', 'employee_projects.project_id', '=', 'projects.id')
                                ->where('employee_projects.user_id', auth()->id())
                                ->where('employee_projects.project_id', $shiftProject)
                                ->where('projects.status', 'aktif')
                                ->where(function ($query) {
                                    $query->whereNull('employee_projects.tanggal_selesai')
                                        ->orWhere('employee_projects.tanggal_selesai', '>=', now()->toDateString());
                                })
                                ->exists();

                            if (! $hasActiveAssignment) {
                                $fail('Anda tidak memiliki assignment aktif ke project ini.');
                            }
                        }
                    }
                },
            ],
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'], // 5MB
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project wajib dipilih',
            'project_id.exists' => 'Project tidak ditemukan',
            'shift_id.exists' => 'Shift tidak ditemukan',
            'project_id.exists' => 'Project tidak ditemukan',
            'photo.required' => 'Foto selfie wajib diupload',
            'photo.image' => 'File harus berupa gambar',
            'photo.mimes' => 'Format foto harus jpeg, jpg, atau png',
            'photo.max' => 'Ukuran foto maksimal 5MB',
            'latitude.required' => 'Latitude wajib diisi',
            'latitude.between' => 'Latitude tidak valid',
            'longitude.required' => 'Longitude wajib diisi',
            'longitude.between' => 'Longitude tidak valid',
            'address.string' => 'Alamat harus berupa teks',
            'address.max' => 'Alamat maksimal 1000 karakter',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
