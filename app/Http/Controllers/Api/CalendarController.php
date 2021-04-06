<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarEvent as CalendarEventResource;
use App\Repositories\CalendarEventRepository;
use App\Rules\MultipleDateFormat;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    /**
     * @var \App\Repositories\CalendarEventRepository
     */
    protected $calendarEventRepository;

    public function __construct(CalendarEventRepository $calendarEventRepository)
    {
        $this->calendarEventRepository = $calendarEventRepository;
    }

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
            $events = $this->calendarEventRepository->listEvents($request);

            return CalendarEventResource::collection($events);
            // return response()->json($this->calendarEventRepository->listEvents($request));
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
        $validator = Validator::make(
            $request->all(),
            $this->getStoreEventValidationRules(),
            $this->getStoreEventValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            $event = $this->calendarEventRepository->insertEvent($request);
            return new CalendarEventResource($event);
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
        $event = null;

        try {
            $event = $this->calendarEventRepository->getEvent($id);
        } catch (Exception $e) {
            return ResponseHelper::response(trans('No event found'), Response::HTTP_NOT_FOUND);
        }

        return new CalendarEventResource($event);
        // return response()->json($event);
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
        $validator = Validator::make(
            $request->all(),
            $this->getStoreEventValidationRules(),
            $this->getStoreEventValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            try {
                $event = $this->calendarEventRepository->updateEvent($request, $id);
            } catch (Exception $e) {
                return ResponseHelper::response(trans('No event found'), Response::HTTP_NOT_FOUND);
            }

            return new CalendarEventResource($event);
            // return response()->json($event);
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
        try {
            $this->calendarEventRepository->deleteEvent($id);
        } catch (Exception $e) {
            return ResponseHelper::response(trans('No event found'), Response::HTTP_NOT_FOUND);
        }

        return response()->json();
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

    private function getStoreEventValidationMessages()
    {
        return [
            'title.required' => trans('The title field is required'),
            'title.max' => trans('The max length of title field is 255'),
            'start.required' => trans('The start field is required'),
            'start.date_format' => trans('The start field must be in format Y-m-d H:i'),
            'end.required' => trans('The end field is requiredddd'),
            'end.date_format' => trans('The end field must be in format Y-m-d H:i'),
            'attendees.*.email' => trans('The attendees field must be valid email format'),
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
