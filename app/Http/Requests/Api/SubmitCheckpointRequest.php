<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubmitCheckpointRequest extends FormRequest
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
                    // Validate user is assigned to this project
                    $user = auth()->user();
                    $isAssigned = \DB::table('employee_projects')
                        ->where('user_id', $user->id)
                        ->where('project_id', $value)
                        ->exists();
                    
                    if (!$isAssigned) {
                        $fail('Anda tidak memiliki akses ke project ini.');
                    }
                },
            ],
            'project_room_id' => [
                'required',
                'integer',
                'exists:project_rooms,id',
                function ($attribute, $value, $fail) {
                    // Validate room belongs to the specified project
                    $projectId = $this->input('project_id');
                    $room = \App\Models\ProjectRoom::find($value);
                    
                    if ($room && $room->project_id != $projectId) {
                        $fail('Ruangan tidak ditemukan dalam project ini.');
                    }
                },
            ],
            'photo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png',
                'max:5120', // 5MB max
            ],
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'catatan' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'keterangan' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'tasks' => [
                'nullable',
                'array',
            ],
            'tasks.*.task_list_id' => [
                'required',
                'integer',
                'exists:task_lists,id',
            ],
            'tasks.*.is_completed' => [
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project ID wajib diisi.',
            'project_id.exists' => 'Project tidak ditemukan.',
            'project_room_id.required' => 'Ruangan wajib dipilih.',
            'project_room_id.exists' => 'Ruangan tidak ditemukan.',
            'photo.required' => 'Foto wajib diupload.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format foto harus jpeg, jpg, atau png.',
            'photo.max' => 'Ukuran foto maksimal 5MB.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude tidak valid.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude tidak valid.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
            'tasks.array' => 'Format tasks tidak valid.',
            'tasks.*.task_list_id.required' => 'Task ID wajib diisi.',
            'tasks.*.task_list_id.exists' => 'Task tidak ditemukan.',
            'tasks.*.is_completed.required' => 'Status task wajib diisi.',
            'tasks.*.is_completed.boolean' => 'Status task harus true atau false.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
