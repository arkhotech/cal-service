<?php

/**
 * Controller Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\CalendarRepository;
use App\Response as Resp;
use Validator;

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
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $page = $request->input('page', 0);
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            $calendars = $this->calendars->listCalendar($appkey, $domain, $page);
            
            if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {
                $resp = Resp::error(500, $calendars['error']->getCode(), $calendars['error']);
            } else {
                if (count($calendars['data']) > 0) {
                    $calendar['calendars'] = $calendars['data'];
                    $calendar['count'] = $calendars['count'];
                    $resp = Resp::make(200, $calendar);
                } else {
                    $resp = Resp::error(404, 1010);
                }
            }
        } else {
            $resp = Resp::error(400, 1020);
        }
        
        return $resp;
    }
    
    /**
     * Controller que despliega un calendario por ID
     *
     * @return \Illuminate\Http\Response
     */
    public function findById($id)
    {
        $resp = array();
        
        if ((int)$id > 0) {
            $calendars = $this->calendars->listCalendarById($id);
            
            if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {
                $resp = Resp::error(500, $calendars['error']->getCode(), $calendars['error']);
            } else {
                if (count($calendars['data']) > 0) {
                    $calendar['calendars'] = $calendars['data'];
                    $calendar['count'] = $calendars['count'];
                    $resp = Resp::make(200, $calendar);
                } else {
                    $resp = Resp::error(404, 1010);
                }
            }
        } else {
            $resp = Resp::error(400, 1020);
        }
        
        return $resp;
    }
    
    /**
     * Controller que despliega un calendario por coincidencia de nombre
     *
     * @return \Illuminate\Http\Response
     */
    public function searchByName(Request $request)
    {        
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $text = $request->input('text', '');
        $resp = array();
        
        if (!empty($appkey) && !empty($domain) && !empty($text)) {
            $calendars = $this->calendars->searchByName($appkey, $domain, $text);
            
            if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {
                $resp = Resp::error(500, $calendars['error']->getCode(), $calendars['error']);
            } else {
                if (count($calendars['data']) > 0) {
                    $calendar['calendars'] = $calendars['data'];
                    $calendar['count'] = $calendars['count'];
                    $resp = Resp::make(200, $calendar);
                } else {
                    $resp = Resp::error(404, 1010);
                }
            }
        } else {
            $resp = Resp::error(400, 1020);
        }
        
        return $resp;
    }
    
    /**
     * Crea un nuevo registro de tipo calendario
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $resp = array();
        $data = $request->json()->all();
        
        $validator = Validator::make($data, [
            'name' => 'required',
            'owner_id' => 'required',
            'owner_name' => 'required',
            'is_group' => 'required|boolean',
            'schedule' => 'required',
            'time_attention' => 'bail|required|integer',
            'concurrency' => 'bail|required|integer',
            'ignore_non_working_days' => 'required|boolean',
            'time_cancel_appointment' => 'required|integer',
            'appkey' => 'required',
            'domain' => 'required'
        ]);

        if ($validator->fails()) {
            $resp = Resp::error(400, 1020);            
        } else {
            $calendars = $this->calendars->createCalendar($data);
            
            if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {                
                $resp = Resp::error(500, $calendars['error']->getCode(), $calendars['error']);
            } else {
                $id = isset($calendars['id']) ? (int)$calendars['id'] : 0;
                $resp = Resp::make(201, array('id' => $id));
            }
        }
        
        return $resp;
    }

    /**
     * Actualiza un registro de tipo calendario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {        
        $resp = array();
        $data = $request->json()->all();
        
        $validator = Validator::make($data, [
            'name' => 'required',
            'owner_id' => 'required',
            'owner_name' => 'required',
            'is_group' => 'required|boolean',
            'schedule' => 'required',
            'time_attention' => 'bail|required|integer',
            'concurrency' => 'bail|required|integer',
            'ignore_non_working_days' => 'required|boolean',
            'time_cancel_appointment' => 'required|integer',
            'appkey' => 'required',
            'domain' => 'required'
        ]);

        if ($validator->fails()) {
            $resp = Resp::error(400, 1020);            
        } else {
            $calendars = $this->calendars->updateCalendar($data, $id);
            
            if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {                
                $resp = Resp::error(500, $calendars['error']->getCode(), $calendars['error']);
            } else {
                $id = isset($calendars['id']) ? (int)$calendars['id'] : 0;
                $resp = Resp::make(200);
            }
        }
        
        return $resp;
    }

    /**
     * Deshabilita un registro de tipo calendar
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function disable($id)
    {
        $resp = array();

        if ((int)$id <= 0) {
            $resp = Resp::error(400, 1020);            
        } else {
            $calendars = $this->calendars->disableCalendar($id);
            
            if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {                
                $resp = Resp::error(500, $calendars['error']->getCode(), $calendars['error']);
            } else {
                $resp = Resp::make(200);
            }
        }
        
        return $resp;
    }
}
