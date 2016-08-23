<?php

/**
 * Repository DayOff
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Repositories;

use App\DayOff;
use App\Repositories\CalendarRepository;
use App\App;
use DB;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Database\QueryException;

class DayOffRepository
{
    /**
     * Obtiene todos los dias no laborales por una appkey y domain del ano actual
     * 
     * @param string $appkey
     * @param string $domain
     * @return Collection
     */
    public function listDayOff($appkey, $domain)
    {
        $res = array();
        $ano = date('Y');
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheDayOffList_'.$appkey.'_'.$domain.'_'.$ano);
            
            $res = Cache::remember($cache_id, $ttl, function() use($appkey, $domain, $ano) {
                
                $daysoff = DayOff::where('appkey', $appkey)
                        ->where('domain', $domain)
                        ->where(DB::raw('YEAR(date_dayoff)'), $ano)->get();
                
                $res['data'] = $daysoff;
                $res['count'] = $daysoff->count();                
                $res['error'] = null;
                
                return $res;
            });
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }
    
    /**
     * Crea un nuevo registro de tipo dayOff
     * 
     * @param array $data
     * @return Collection
     */
    public function createDayOff($data)
    {
        $res = array();
        
        try {            
            $apps = App::where('appkey', $data['appkey'])
                            ->where('domain', $data['domain'])
                            ->where('status', 1)->value('appkey');
            
            if ($apps) {
                $cal = new CalendarRepository();
                
                if ($data['date_dayoff'] >= date('Y-m-d')) {
                    //Verifico que no hayan citas programadas para ese dia
                    if (!$cal->hasAvailableAppointmentByDate($data['appkey'], 
                            $data['domain'], $data['date_dayoff'])) {
                        $dayoff = DayOff::create($data);
                        $res['error'] = null;

                        Cache::flush();
                    } else {
                        $res['error'] = new \Exception('', 1080);
                    }
                } else {
                    $res['error'] = new \Exception('', 1090);
                }
            } else {
                $res['error'] = new \Exception('', 1030);
            }
        } catch (QueryException $qe) {
            if ($qe->getCode() == 23000) {
                $res['error'] = new \Exception('', 1040);
            } else {
                $res['error'] = $qe;
            }
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Elimina un registro de tipo dayOff
     *      
     * @param int $id
     * @return Collection
     */
    public function destroyDayOff($id)
    {
        $res = array();
        
        try {
            $dayoff = DayOff::destroy($id);
            $res['error'] = $dayoff === false ? new \Exception('', 500) : null;
            
            Cache::flush();
        } catch (QueryException $qe) {            
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    public function sanitizeString($string)
    {

        $string = trim($string);

        $string = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );

        $string = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $string
        );

        $string = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $string
        );

        $string = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $string
        );

        $string = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $string
        );

        $string = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C',),
            $string
        );

        return $string;
    }
}
