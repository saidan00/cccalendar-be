<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarEvent as CalendarEventResource;
use App\Http\Resources\CalendarEventColor as CalendarEventColorResource;
use App\Repositories\CalendarEventRepository;
use App\Repositories\EventRepository;
use App\Repositories\TagRepository;
use App\Rules\MultipleDateFormat;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    /**
     * @var \App\Repositories\CalendarEventRepository
     */
    protected $calendarEventRepository;

    /**
     * @var \App\Repositories\EventRepository
     */
    protected $eventRepository;

    /**
     * @var \App\Repositories\TagRepository
     */
    protected $tagRepository;

    public function __construct(CalendarEventRepository $calendarEventRepository, EventRepository $eventRepository, TagRepository $tagRepository)
    {
        $this->calendarEventRepository = $calendarEventRepository;
        $this->eventRepository = $eventRepository;
        $this->tagRepository = $tagRepository;
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
     * List colors
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listColors(Request $request)
    {
        $colors = $this->calendarEventRepository->listColors();
        return new CalendarEventColorResource($colors->getEvent());
    }

    /**
     * Create event
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createEvent(Request $request)
    {
        $data = $request->except(['user_id']);

        $validator = Validator::make(
            $data,
            $this->getStoreEventValidationRules(),
            $this->getStoreEventValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            // gọi api của google để thêm event
            $event = $this->calendarEventRepository->insertEvent($request);

            // lấy user từ middleware VerifyGoogleToken
            $user = $request->get('user');

            DB::transaction(function () use ($data, $user, $event) {
                // thêm các tag vào database (nếu trùng thì bỏ qua)
                $this->tagRepository->insertNewTags($data['tags'], $user->id);

                // lấy các tag vừa thêm
                $tags = $this->tagRepository->findByTagsName($data['tags'], $user->id);

                // thêm các tag mới vào event
                $this->eventRepository->addTags($event->id, $tags, $user->id);
            });

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
        $data = $request->all();
        $validator = Validator::make(
            $data,
            $this->getStoreEventValidationRules(),
            $this->getStoreEventValidationMessages()
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        } else {
            try {
                // gọi api của google để update event
                $event = $this->calendarEventRepository->updateEvent($request, $id);

                // lấy user từ middleware VerifyGoogleToken
                $user = $request->get('user');

                DB::transaction(function () use ($data, $user, $event) {
                    // xóa tất cả tag cũ của event (nếu có)
                    $this->tagRepository->deleteReferenceByEventId($event->id);

                    // thêm các tag vào database (nếu trùng thì bỏ qua)
                    $this->tagRepository->insertNewTags($data['tags'], $user->id);

                    // lấy các tag vừa thêm
                    $tags = $this->tagRepository->findByTagsName($data['tags'], $user->id);

                    // thêm các tag mới vào event
                    $this->eventRepository->addTags($event->id, $tags, $user->id);
                });
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
            // gọi api của google để xóa event
            $this->calendarEventRepository->deleteEvent($id);

            // xoá các event_tag
            $this->tagRepository->deleteReferenceByEventId($id);
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
            'attendees' => 'array',
            'attendees.*' => 'email',
            'tags' => 'array',
            'tags.*' => 'required|string',
            'colorId' => 'integer|between:1,11',
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
            'attendess.array' => trans('The attendees must be type of array'),
            'attendees.*.email' => trans('The attendees field must be valid email format'),
            'tags.array' => trans('The tags must be type of array'),
            'tags.*.required' => trans('The tag name is required'),
            'tags.*.string' => trans('The tag must be type of string'),
            'colorId.between' => trans('Colord ID must be integer in range [1,11]'),
        ];
    }

    private function getListEventsValidationRules()
    {
        $formats = [
            'Y-m-d H:i',
            'Y-m-d'
        ];

        return [
            'tags' => 'array',
            'tags.*' => 'string|max:100',
            'start' => new MultipleDateFormat($formats),
            'end' => new MultipleDateFormat($formats),
        ];
    }
}
