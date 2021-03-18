<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CalendarServiceHelper;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class CalendarController extends Controller
{
    /**
     * List events
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $calendarServiceHelper = new CalendarServiceHelper($request);
        return response()->json($calendarServiceHelper->listEvents($request));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createEvent(Request $request)
    {
        $calendarServiceHelper = new CalendarServiceHelper($request);

        $calendarServiceHelper->insertEvent($request);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showEvent(Request $request, $id)
    {
        $calendarServiceHelper = new CalendarServiceHelper($request);
        $event = null;

        try {
            $event = $calendarServiceHelper->getEvent($id);
        } catch (Exception $e) {
            return ResponseHelper::response('No event found', 404);
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
}
