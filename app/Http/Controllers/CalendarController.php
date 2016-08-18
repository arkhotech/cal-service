<?php

/**
 * Controller Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\CalendarRepository;
use Request as Req;
use App\Response as Resp;

class CalendarController extends Controller
{
    /**
     * Instancia de CalendarRepository
     *
     * @var CalendarRepository
     */
    protected $calendars;

    /**
     * Crea una nueva instancia Controller
     *
     * @param CalendarRepository  $calendars
     * @return void
     */
    public function __construct(CalendarRepository $calendars)
    {
        $this->calendars = $calendars;
    }
    
    /**
     * Controller que despliega listado de calendarios
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        $appkey = Req::header('appkey');
        $domain = Req::header('domain');
        $page = $request->input('page', 0);        
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            $calendars = $this->calendars->listCalendar($appkey, $domain, $page);
            
            if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {
                $resp = Resp::error(500, $calendars['error']);
            } else {
                if (isset($calendars['count']) && (int)$calendars['count'] > 0) {
                    $calendar['calendars'] = $calendars['data'];
                    $calendar['count'] = $calendars['count'];
                    $resp = Resp::make(200, $calendar);
                } else {
                    $resp = Resp::error(404);
                }
            }
        } else {
            $resp = Resp::error(400);
        }
        
        return $resp;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
    public function show($id)
    {
        //
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
