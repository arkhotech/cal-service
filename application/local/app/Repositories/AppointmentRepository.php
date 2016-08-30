<?php

/**
 * Repository Appointment
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Repositories;

use DB;
use Log;
use App\Calendar;
use App\Appointment;
use App\Repositories\CalendarRepository;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Database\QueryException;

class AppointmentRepository
{
    /**
     * Obtiene todas las citas futuras por solicitante
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $id
     * @param mixed $page int/null
     * @return Collection
     */
    public function listAppointmentsByApplyerId($appkey, $domain, $id, $page)
    {
        $res = array();
        $page = (int)$page;
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheAppointmentListByApplyer_'.$appkey.'_'.$domain.'_'.$id.'_'.$page);
            $res = Cache::get($cache_id);
            
            if ($res === null) {
                if ((int)$id > 0) {
                    $columns = array(
                        DB::raw('appointments.id as appointment_id'),
                        'subject',
                        'applyer_name',
                        'owner_name',
                        DB::raw('DATE_FORMAT(appointment_start_time, "%Y-%m-%dT%TZ") as appointment_time'),
                        'applyer_attended'
                    );
                    
                    if ($page !== 0) {
                        $per_page = (int)config('calendar.per_page');            

                        $appointments = Appointment::select($columns)
                                ->join('calendars', 'calendars.id', '=', 'appointments.calendar_id')
                                ->where('applyer_id', $id)
                                ->where('appointment_start_time', '>=', date('Y-m-d H:i:s'))
                                ->where('is_canceled', '<>', 1)
                                ->where('is_reserved', 0)
                                ->paginate($per_page);

                        $res['data'] = $appointments->items();
                        $res['count'] = $appointments->total();
                    } else {
                        $appointments = Appointment::select($columns)
                                ->join('calendars', 'calendars.id', '=', 'appointments.calendar_id')
                                ->where('applyer_id', $id)
                                ->where('appointment_start_time', '>=', date('Y-m-d H:i:s'))
                                ->where('is_canceled', '<>', 1)
                                ->where('is_reserved', 0)->get();

                        $res['data'] = $appointments;
                        $res['count'] = $appointments->count();
                    }
                    $res['error'] = null;
                    
                    $tag = sha1($appkey.'_'.$domain);
                    Cache::tags([$tag])->put($cache_id, $res, $ttl);
                }
            }                
            
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }
    
    /**
     * Obtiene todas las citas futuras por propietario de agenda
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $id
     * @param mixed $page int/null
     * @return Collection
     */
    public function listAppointmentsByOwnerId($appkey, $domain, $id, $page)
    {
        $res = array();
        $page = (int)$page;
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheAppointmentListByOwner_'.$appkey.'_'.$domain.'_'.$id.'_'.$page);
            $res = Cache::get($cache_id);
            
            if ($res === null) {
                if ((int)$id > 0) {
                    $columns = array(
                        DB::raw('appointments.id as appointment_id'),
                        'subject',
                        'applyer_name',
                        'owner_name',
                        DB::raw('DATE_FORMAT(appointment_start_time, "%Y-%m-%dT%TZ") as appointment_time'),
                        'applyer_attended'
                    );
                    
                    if ($page !== 0) {
                        $per_page = (int)config('calendar.per_page');            

                        $appointments = Appointment::select($columns)
                                ->join('calendars', 'calendars.id', '=', 'appointments.calendar_id')
                                ->where('owner_id', $id)
                                ->where('appointment_start_time', '>=', date('Y-m-d H:i:s'))
                                ->where('is_canceled', '<>', 1)
                                ->where('is_reserved', 0)
                                ->paginate($per_page);

                        $res['data'] = $appointments->items();
                        $res['count'] = $appointments->total();
                    } else {
                        $appointments = Appointment::select($columns)
                                ->join('calendars', 'calendars.id', '=', 'appointments.calendar_id')
                                ->where('owner_id', $id)
                                ->where('appointment_start_time', '>=', date('Y-m-d H:i:s'))
                                ->where('is_canceled', '<>', 1)
                                ->where('is_reserved', 0)->get();

                        $res['data'] = $appointments;
                        $res['count'] = $appointments->count();
                    }
                    $res['error'] = null;
                    
                    $tag = sha1($appkey.'_'.$domain);
                    Cache::tags([$tag])->put($cache_id, $res, $ttl);
                }
            }                
            
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }
    
    /**
     * Crea un nuevo registro de tipo cita
     *
     * @param string $appkey
     * @param string $domain 
     * @param array $data     
     * @return Collection
     */
    public function createAppointment($appkey, $domain, $data)
    {
        $res = array();
        
        try {            
            $calendar = Calendar::where('id', $data['calendar_id'])->get();
            $time_attention = 0;
            
            if ($calendar->count() > 0) {
                foreach ($calendar as $cal) {
                    $time_attention = (int)$cal->time_attention;
                }
                
                $date = new \DateTime($data['appointment_start_time']);
                $start_date = $date->format('Y-m-d H:i:s');
                $end_date = $date->add(new \DateInterval('PT' . $time_attention . 'M'))->format('Y-m-d H:i:s');
                
                $data['appointment_start_time'] = $start_date;
                $data['appointment_end_time'] = $end_date;
                $data['is_reserved'] = 1;
                $data['reservation_date'] = date('Y-m-d H:i:s');
                $data['is_canceled'] = 0;
                $data['applyer_attended'] = 0;
                
                $appointment = Appointment::create($data);
                $res['id'] = $appointment->id;
                $res['error'] = null;
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            } else {
                $res['error'] = new \Exception('', 1010);
            }
        } catch (QueryException $qe) {
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Actualiza un nuevo registro de tipo cita
     *
     * @param string $appkey
     * @param string $domain 
     * @param int $id 
     * @param array $data
     * @return Collection
     */
    public function updateAppointment($appkey, $domain, $id, $data)
    {
        $res = array();
        
        try {            
            $calendar = Calendar::where('id', $data['calendar_id'])->get();
            $time_attention = 0;
            $email_owner = '';
            
            if ($calendar->count() > 0) {
                foreach ($calendar as $cal) {
                    $time_attention = (int)$cal->time_attention;
                    $email_owner = $cal->owner_email;
                }
                
                $date = new \DateTime($data['appointment_start_time']);
                $start_date = $date->format('Y-m-d H:i:s');
                $end_date = $date->add(new \DateInterval('PT' . $time_attention . 'M'))->format('Y-m-d H:i:s');
                
                $data['appointment_start_time'] = $start_date;
                $data['appointment_end_time'] = $end_date;
                
                $appointment = Appointment::where('id', $id)->update($data);
                $res['error'] = null;
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            } else {
                $res['error'] = new \Exception('', 1010);
            }
        } catch (QueryException $qe) {
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Obtiene una cita por ID
     *      
     * @param string $appkey
     * @param string $domain
     * @param int $id     
     * @return Collection
     */
    public function listAppointmentById($appkey, $domain, $id)
    {
        $res = array();
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheAppointmentListById_'.$id);
            $res = Cache::get($cache_id);
            
            if ($res === null) {
                if ((int)$id > 0) {
                    $appointments = Appointment::where('id', $id)->get();

                    $res['data'] = $appointments;
                    $res['count'] = $appointments->count();
                    $res['error'] = null;
                    
                    $tag = sha1($appkey.'_'.$domain);
                    Cache::tags([$tag])->put($cache_id, $res, $ttl);
                }
            }
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }
    
    /**
     * Obtiene todos los datos de un calendario por ID de cita
     *      
     * @param string $appkey
     * @param string $domain
     * @param int $id     
     * @return Collection
     */
    public function listCalendarByAppointmentId($appkey, $domain, $id)
    {
        $res = array();
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheCalendarByAppointmentId_'.$id);
            $res = Cache::get($cache_id);
            
            if ($res === null) {
                if ((int)$id > 0) {
                    $appointments = Appointment::join('calendars', 'appointments.calendar_id', '=', 'calendars.id')
                            ->select('calendars.*', 'appointments.appointment_start_time')
                            ->where('appointments.id', $id)->get();

                    $res['data'] = $appointments;
                    $res['count'] = $appointments->count();
                    $res['error'] = null;
                    
                    $tag = sha1($appkey.'_'.$domain);
                    Cache::tags([$tag])->put($cache_id, $res, $ttl);
                }
            }
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }
    
    /**
     * Confirma una cita
     *
     * @param string $appkey
     * @param string $domain 
     * @param int $id
     * @return Collection
     */
    public function confirmAppointment($appkey, $domain, $id)
    {
        $res = array();
        
        try {
            
            if ((int)$id > 0) {
                $data['confirmation_date'] = date('Y-m-d H:i:s');
                $data['is_reserved'] = 0;
                $appointment = Appointment::where('id', $id)->update($data);
                $res['error'] = null;
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            }
        } catch (QueryException $qe) {
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Cancela una cita
     *
     * @param string $appkey
     * @param string $domain 
     * @param int $id
     * @param array $data
     * @return Collection
     */
    public function cancelAppointment($appkey, $domain, $id, $data)
    {
        $res = array();
        
        try {
            
            if ((int)$id > 0) {
                $columns['user_id_cancel'] = $data['user_id_cancel'];
                $columns['user_name_cancel'] = $data['user_name_cancel'];                
                if (isset($data['cancelation_cause']))
                    $columns['cancelation_cause'] = $data['cancelation_cause'];
                $columns['cancelation_date'] = date('Y-m-d H:i:s');
                $columns['is_canceled'] = 1;
                $appointment = Appointment::where('id', $id)->update($columns);
                $res['error'] = null;
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            }
        } catch (QueryException $qe) {
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Define una cita a si asistio o no el usuario
     *
     * @param string $appkey
     * @param string $domain 
     * @param int $id
     * @param array $data
     * @return Collection
     */
    public function assistsAppointment($appkey, $domain, $id, $data)
    {
        $res = array();
        
        try {
            
            if ((int)$id > 0) {
                $columns['applyer_attended'] = $data['applyer_attended'];
                Appointment::where('id', $id)->update($columns);
                $res['error'] = null;
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            }
        } catch (QueryException $qe) {
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Verifica si una cita puede agendarse para una fecha determinada
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $calendar_id
     * @param string $start_date
     * @param int $id
     * @return boolean
     */
    public function isValidAppointment($appkey, $domain, $calendar_id, $start_date, $id = 0)
    {
        $val = true;        
        $code = 0;
        
        //Obtengo el calendario por ID y valido que exista
        $calendar = new CalendarRepository();
        $cal_data = $calendar->listCalendarById($appkey, $domain, $calendar_id);
        
        $time_attention = 0;
        $concurrency = 0;
        $ignore_non_working_days = 0;
        $calendar_schedule = '';
        
        if ($cal_data['count'] > 0) {
            foreach ($cal_data['data'] as $cal) {
                $calendar_schedule = $cal['schedule'];
                $time_attention = (int)$cal['time_attention'];
                $concurrency = (int)$cal['concurrency'];
                $ignore_non_working_days = (int)$cal['ignore_non_working_days'];
            }
            
            $date = new \DateTime($start_date);
            $start_date = $date->format('Y-m-d H:i:s');
            $end_date = $date->add(new \DateInterval('PT' . $time_attention . 'M'))->format('Y-m-d H:i:s');
            
            //Valido que la fecha inicial sea mayor o igual a fecha actual
            if ($start_date < date('Y-m-d H:i:s')) {
                $val = false;
                $code = 2010;
            } else {
                
                //valido si la fecha esta en un dia no laboral
                $day_off = false;
                if (!(bool)$ignore_non_working_days) {
                    $dayoff = new DayOffRepository();
                    $day_off = $dayoff->isDayOff($appkey, $domain, $start_date, $end_date);
                }
                
                if ($day_off) {
                    $val = false;
                    $code = 2020;                    
                } else {
                    
                    //Valido bloqueo de horario
                    $block = new BlockScheduleRepository();
                    $block_appointment = $block->validateBlock($appkey, $domain, $calendar_id, $start_date, $end_date);                    
                    if ($block_appointment) {
                        $val = false;
                        $code = 2030;
                    } else {
                        
                        //Valido que la cita este dentro del horario del calendario
                        $cal = $calendar->isIntoSchedule($calendar_schedule, $start_date, $end_date);
                        if (!$cal) {
                            $val = false;
                            $code = 2040;
                        } else {
                            
                            //Valido que no haya cruce de citas
                            $appointment = $this->validateOverlappingAppointment($appkey, $domain, $calendar_id, $concurrency, $start_date, $end_date, $id);
                            if ($appointment) {
                                $val = false;
                                $code = 2050;
                            }
                        }
                    }
                }
            }
        } else {
            $code = 1010;
        }
        
        $result = array(
            'is_ok' => $val, 
            'error_code' => $val ? 0 : $code
        );
        
        return $result;
    }
    
    /**
     * Valido que no haya cruce de horarios entre citas
     * 
     * @param string $appkey
     * @param string $domain 
     * @param int $calendar_id
     * @param int $concurrency
     * @param date $start_date
     * @param date $end_date
     * @param int $id
     * @return boolean
     */
    public function validateOverlappingAppointment($appkey, $domain, $calendar_id, $concurrency, $start_date, $end_date, $id)
    {
        $res = true;        
        
        try {
            if ((int)$calendar_id > 0 && $start_date && $end_date) {
                $start_date = new \DateTime($start_date);
                $start_date = $start_date->format('Y-m-d H:i:s');
                $end_date = new \DateTime($end_date);
                $end_date = $end_date->format('Y-m-d H:i:s');
            
                $ttl = (int)config('calendar.cache_ttl');
                $cache_id = sha1('cacheIsOverlappingAppointment_'.$id.'_'.$calendar_id.'_'.$concurrency.'_'.$start_date.'_'.$end_date);
                $res = Cache::get($cache_id);
                
                if ($res === null) {
                    if ((int)$id > 0) {
                    $appointment = Appointment::where('appointment_end_time', '>=', date('Y-m-d H:i:s'))
                          ->where('appointment_start_time', '<', $end_date)
                          ->Where('appointment_end_time', '>', $start_date)
                          ->where('calendar_id', $calendar_id)
                          ->where('id', '<>', $id)
                          ->where('is_canceled', '<>', 1)->get();
                    } else {
                        $appointment = Appointment::where('appointment_end_time', '>=', date('Y-m-d H:i:s'))
                          ->where('appointment_start_time', '<', $end_date)
                          ->Where('appointment_end_time', '>', $start_date)
                          ->where('calendar_id', $calendar_id)                          
                          ->where('is_canceled', '<>', 1)->get();
                    }
                    
                    $appointments = $appointment->count();
                    if ($appointments == 0) {
                        $res = false;
                    } else {
                        if ($concurrency > 1) {
                            if ($appointments >= $concurrency) {
                                $res = true; 
                            } else {
                                $res = false;
                            }
                        } else {
                            $res = true;
                        }
                    }
                    
                    $tag = sha1($appkey.'_'.$domain);
                    Cache::tags([$tag])->put($cache_id, $res, $ttl);
                }
            }
        } catch (QueryException $qe) {
            Log::error('code: ' .  $qe->getCode() . ' Message: ' . $qe->getMessage());
        } catch (Exception $e) {
            Log::error('code: ' .  $qe->getCode() . ' Message: ' . $qe->getMessage());
        }        
        
        return $res;        
    }
    
    public function sendMail($template, $data)
    {
        $from = config('calendar.email_from');
        $from_name = config('calendar.email_from_name');
        $data_view = array();        
        $data = array(
            'from' => $from,
            'from_name' => $from_name,
            'to' => $data['to'],
            'subject' => $data['subject']
        );
        
        Mail::send($template, $data_view, function ($message) use ($data) {
            $message->from($data['from'], $data['from_name']);
            $message->to($data['to']);
            $message->subject($data['subject']);
        });        
    }
}
