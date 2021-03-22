<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CalendarServiceHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Rules\MultipleDateFormat;
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
        $validator = Validator::make($request->all(), $this->getListEventsValidationRules());

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            $calendarServiceHelper = new CalendarServiceHelper($request);
            return response()->json($calendarServiceHelper->listEvents($request));
        }
    }

    /**
     * Create event
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createEvent(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getStoreEventValidationRules());

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            $calendarServiceHelper = new CalendarServiceHelper($request);

            return response()->json($calendarServiceHelper->insertEvent($request));
        }
    }

    /**
     * Get event by id
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showEvent(Request $request, $id)
    {
        $calendarServiceHelper = new CalendarServiceHelper($request);
        $event = null;

        try {
            $event = $calendarServiceHelper->getEvent($id);
        } catch (Exception $e) {
            return ResponseHelper::response(trans('No event found'), Response::HTTP_NOT_FOUND);
        }

        return ResponseHelper::response($event);
    }

    /**
     * Update event
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEvent(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), $this->getStoreEventValidationRules());

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            $calendarServiceHelper = new CalendarServiceHelper($request);

            try {
                $event = $calendarServiceHelper->updateEvent($request, $id);
            } catch (Exception $e) {
                return ResponseHelper::response(trans('No event found'), Response::HTTP_NOT_FOUND);
            }

            return response()->json($event);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEvent(Request $request, $id)
    {
        $calendarServiceHelper = new CalendarServiceHelper($request);
        $event = null;

        try {
            $event = $calendarServiceHelper->deleteEvent($id);
        } catch (Exception $e) {
            return ResponseHelper::response(trans('No event found'), Response::HTTP_NOT_FOUND);
        }

        return response()->json($event);
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
        $formats = [
            'Y-m-d H:i',
            'Y-m-d'
        ];

        return [
            'start' => new MultipleDateFormat($formats),
            'end' => new MultipleDateFormat($formats),
        ];
    }
}
