<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\PatientNotesService;
use Illuminate\Http\Request;
use App\Models\Note;
use DateTime;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(PatientNotesService $service)
	{
		$this->service = $service;
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$user = Auth::getUser();
		$patientId = $user->getPatientId();

		$start = $request->input('start');
		$end = $request->input('end');

		return response()->json(
            $this->service->getNotesByRange($patientId, $start, $end)
        );
	}

	public function store(Request $request)
	{
		$this->validation($request);

		$datetime = $request->get('_StartDate').' '.$request->get('_StartTime');

		$note = new Note;
		$note->fill($request->input());
		$note->StartDate = DateTime::createFromFormat('m/d/Y h:i A', $datetime)->format('Y-m-d H:i:s');
		$note->EndDate = $note->StartDate;
		$note->UserId = Auth::getUser()->getId();
        $note->PatientId = Auth::getUser()->getPatientId();
		$note->save();

		return response()->json(['status' => 'success', 'data' => null]);
	}

	public function validation(Request $request) {
		$this->validate($request, [
		    'Title' => 'required|max:255',
		    '_StartDate' => 'required',
		    '_StartTime' => 'required',
		]);
	}

	public function update(Request $request, $id)
	{
		$this->validation($request);

		$note = Note::find($id);
		if(! $note) {
			return $this->error('Invalid note');
		}

		$datetime = $request->get('_StartDate').' '.$request->get('_StartTime');

		$note->fill($request->input());
		$note->StartDate = DateTime::createFromFormat('m/d/Y h:i A', $datetime)->format('Y-m-d H:i:s');
		$note->EndDate = DateTime::createFromFormat('m/d/Y h:i A', $datetime)->format('Y-m-d H:i:s');
		$note->save();

		return response()->json(['status' => 'success', 'data' => null]);
	}

	public function destroy(Request $request, $id)
	{
		$note = Note::find($id);
		if(! $note) {
			return $this->error('Invalid note');
		}

		$note->delete();

		return response()->json(['status' => 'success', 'data' => null]);
	}

	public function show($id)
	{
		$note = Note::find($id);
		if(! $note) {
			return $this->error('Invalid note');
		}
		return response()->json(['status' => 'success', 'data' => $note]);
	}
}
