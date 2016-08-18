<?php

/**
 * Unit Test Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */
class CalendarTest extends TestCase
{
    /**
     * Base URL a usar para testear el servicio
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost/apiCalendar/v1/calendars';

    /**
     * Test Get Request calendars
     *
     * @return void
     */
    public function testGetCalendar()
    {
        $response = $this->call('GET', $this->baseUrl . '/');

        $this->assertEquals(200, $response->status());
    }
}