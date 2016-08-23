<?php

/**
 * Controller Appointment
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\AppointmentRepository;
use App\Response as Resp;
use Validator;

class AppointmentController extends Controller
{
    /**
     * Instancia de BlockScheduleRepository
     *
     * @var BlockScheduleRepository
     */
    protected $appointments;

    /**
     * Crea una nueva instancia Controller
     *
     * @param AppointmentRepository  $appointments
     * @return void
     */
    public function __construct(AppointmentRepository $appointments)
    {
        $this->appointments = $appointments;
    }
}
