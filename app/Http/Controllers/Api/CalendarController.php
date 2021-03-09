<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CalendarServiceHelper;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Calendar;
use App\Http\Controllers\Controller;

class CalendarController extends Controller {
    protected $client;
    protected $calendarService;

    /**
     * Set default parameters
     */
    public function __construct(Request $request) {
        $token = $request->header('Authorization');

        // Set token for the Google API PHP Client
        $google_client_token = [
            'access_token' => $token,
            'expires_in' => 3600
        ];

        $this->client = new Google_Client();
        $this->client->setAccessToken(json_encode($google_client_token));

        $this->calendarService = new Google_Service_Calendar($this->client);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $calendarServiceHelper = new CalendarServiceHelper($this->calendarService);
        return response()->json($calendarServiceHelper->filter($request));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }
}
