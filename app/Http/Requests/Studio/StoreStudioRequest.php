<?php

namespace App\Http\Requests\Studio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $voiceKeys = array_column(
            config('studio_voices', []),
            'key'
        );

        return [
            // Senaryo
            'scenario' => ['nullable', 'string'],

            // Video formatı
            'format' => [
                'required',
                Rule::in(['16:9', '9:16', '1:1', '21:9']),
            ],

            // Karakterler
            'characters' => ['nullable', 'array'],
            'characters.*.name' => [
                'nullable',
                'string',
                'max:150',
            ],
            'characters.*.image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
            'characters.*.voice_key' => [
                'nullable',
                'string',
                Rule::in($voiceKeys),
            ],
            'characters.*.custom_voice' => [
                'nullable',
                'file',
                'mimes:mp3,wav,m4a,aac,ogg',
                'max:20480',
            ],

            // Ortamlar
            'environments' => ['nullable', 'array'],
            'environments.*.name' => [
                'nullable',
                'string',
                'max:150',
            ],
            'environments.*.image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            // İlk formdaki isteğe bağlı revize talebi
            'initial_revision_video_url' => [
                'nullable',
                'url',
                'max:2048',
            ],
            'initial_revision_description' => [
                'nullable',
                'string',
                'max:10000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'format.required' => 'Lütfen video formatını seçin.',
            'format.in' => 'Seçilen video formatı geçerli değil.',

            'characters.*.image.image' =>
                'Karakter görseli geçerli bir resim olmalıdır.',
            'characters.*.image.max' =>
                'Karakter görseli en fazla 10 MB olabilir.',

            'characters.*.voice_key.in' =>
                'Seçilen ses listede bulunamadı.',

            'characters.*.custom_voice.mimes' =>
                'Ses dosyası MP3, WAV, M4A, AAC veya OGG olmalıdır.',
            'characters.*.custom_voice.max' =>
                'Ses dosyası en fazla 20 MB olabilir.',

            'environments.*.image.image' =>
                'Ortam görseli geçerli bir resim olmalıdır.',
            'environments.*.image.max' =>
                'Ortam görseli en fazla 10 MB olabilir.',

            'initial_revision_video_url.url' =>
                'Lütfen geçerli bir video bağlantısı girin.',
        ];
    }
}
