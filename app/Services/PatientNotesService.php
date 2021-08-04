<?php

namespace App\Services;

use App\Models\Note;

class PatientNotesService
{
    /**
     * [getNotesByRange description]
     *
     * @param   [type]  $patientId
     * @param   [type]  $start
     * @param   [type]  $end
     * @return  [type]
     */
	public function getNotesByRange($patientId, $start, $end)
	{
        return Note::with('user')
            ->where('PatientId', $patientId)
            ->whereBetween('StartDate', [$start, $end])
            ->orderByDesc('StartDate')
            ->limit(100)
            ->get()
            ->map(function ($note) {
                $note->DisplayName = $note->author;
                return $note;
            })
            ->toArray();
	}
}
