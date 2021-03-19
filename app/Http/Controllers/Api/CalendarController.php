<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CalendarServiceHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Exception;

class CalendarController extends Controller
{
    /**
     * List events
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->json()->all(), $this->getListEventsValidationRules());

        if ($validator->fails()) {
            return response()->json(
                $validator->messages(),
                Response::HTTP_BAD_REQUEST
            );
        } else {
            $calendarServiceHelper = new CalendarServiceHelper($request);
            return response()->json($calendarServiceHelper->listEvents($request));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createEvent(Request $request)
    {
        $validator = Validator::make($request->json()->all(), $this->getStoreEventValidationRules());

        if ($validator->fails()) {
            return response()->json(
                $validator->messages(),
                Response::HTTP_BAD_REQUEST
            );
        } else {
            $calendarServiceHelper = new CalendarServiceHelper($request);

            return response()->json($calendarServiceHelper->insertEvent($request));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showEvent(Request $request, $id)
    {
        $calendarServiceHelper = new CalendarServiceHelper($request);
        $event = null;

        try {
            $event = $calendarServiceHelper->getEvent($id);
        } catch (Exception $e) {
            return ResponseHelper::response('No event found', Response::HTTP_NOT_FOUND);
        }

        return ResponseHelper::response($event);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function getStoreEventValidationRules()
    {
        return [
            'title' => 'required|max:255',
            'description' => '',
            'start' => 'required|date_format:Y-m-d H:i',
            'end' => 'required|date_format:Y-m-d H:i',
            'attendees.*' => 'email',
        ];
    }

    private function getListEventsValidationRules()
    {
        return [
            'start' => 'date_format:Y-m-d H:i',
            'end' => 'date_format:Y-m-d H:i',
        ];
    }
}
