<?php

/**
 * Controller DayOff
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\DayOffRepository;
use App\Response as Resp;
use Validator;

class DayOffController extends Controller
{
    /**
     * Instancia de CalendarRepository
     *
     * @var CalendarRepository
     */
    protected $daysoff;

    /**
     * Crea una nueva instancia Controller
     *
     * @param DayOffRepository  $daysoff
     * @return void
     */
    public function __construct(DayOffRepository $daysoff)
    {
        $this->daysoff = $daysoff;
    }
    
    /**
     * Controller que despliega listado de dias no laborales
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {  
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            $daysoff = $this->daysoff->listDayOff($appkey, $domain);
            
            if (isset($daysoff['error']) && is_a($daysoff['error'], 'Exception')) {
                $resp = Resp::error(500, $daysoff['error']->getCode(), $daysoff['error']);
            } else {
                if (count($daysoff['data']) > 0) {
                    $dayoff['calendars'] = $daysoff['data'];
                    $dayoff['count'] = $daysoff['count'];
                    $resp = Resp::make(200, $dayoff);
                } else {
                    $resp = Resp::error(404, 1070);
                }
            }
        } else {
            $resp = Resp::error(400, 1020);
        }
        
        return $resp;
    }
    
    /**
     * Crea un nuevo registro de tipo dayoff
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $resp = array();
        $data = $request->json()->all();
        
        $validator = Validator::make($data, [
            'name' => 'bail|required|max:70',
            'date_dayoff' => 'bail|required|date_format:Y-m-d',
            'appkey' => 'required|max:15',
            'domain' => 'required|max:150'
        ]);

        if ($validator->fails()) {
            $resp = Resp::error(400, 1020);            
        } else {
            $dayoffs = $this->daysoff->createDayOff($data);
            
            if (isset($dayoffs['error']) && is_a($dayoffs['error'], 'Exception')) {                
                $resp = Resp::error(500, $dayoffs['error']->getCode(), $dayoffs['error']);
            } else {                
                $resp = Resp::make(201);
            }
        }
        
        return $resp;
    }

    /**
     * Elimina un registro de tipo dayoff
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $resp = array();

        if ((int)$id <= 0) {
            $resp = Resp::error(400, 1020);            
        } else {
            $daysoff = $this->daysoff->destroyDayOff($id);
            
            if (isset($daysoff['error']) && is_a($daysoff['error'], 'Exception')) {                
                $resp = Resp::error(500, $daysoff['error']->getCode(), $daysoff['error']);
            } else {
                $resp = Resp::make(200);
            }
        }
        
        return $resp;
    }
}
