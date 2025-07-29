<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'breaks.*.start_time' => ['nullable', 'date_format:H:i'],
            'breaks.*.end_time'   => ['nullable', 'date_format:H:i'],
            'note'    => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);

            if ($start->greaterThanOrEqualTo($end)) {
                $validator->errors()->add('start_time', '出勤時間は退勤時間より前にしてください');
            }

            foreach ($this->breaks ?? [] as $index => $break) {
                $bStart = isset($break['start_time']) ? Carbon::parse($break['start_time']) : null;
                $bEnd   = isset($break['end_time']) ? Carbon::parse($break['end_time']) : null;

                if ($bStart && $bStart->greaterThan($end)) {
                    $validator->errors()->add("breaks.$index.start_time", '休憩開始時間が勤務時間外です');
                }

                if ($bEnd && $bEnd->greaterThan($end)) {
                    $validator->errors()->add("breaks.$index.end_time", '休憩終了時間が勤務時間外です');
                }

                if ($bStart && $bStart->lessThan($start)) {
                    $validator->errors()->add("breaks.$index.start_time", '休憩開始時間が勤務時間外です');
                }

                if ($bEnd && $bEnd->lessThan($start)) {
                    $validator->errors()->add("breaks.$index.end_time", '休憩終了時間が勤務時間外です');
                }

                if ($bStart && $bEnd && $bStart->greaterThanOrEqualTo($bEnd)) {
                    $validator->errors()->add("breaks.$index.start_time", '休憩時間が不正です');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required' => '退勤時間を入力してください',
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',
        ];
    }
}